<?php

/**
 * SendingServerElasticEmail class.
 *
 * Abstract class for Mailjet sending server
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Acelle\Library\Log as MailLog;
use Acelle\Library\StringHelper;

class SendingServerElasticEmail extends SendingServer
{
    const WEBHOOK = 'elasticemail';
    const WEBHOOK_NAME = 'acellemail';
    const API_ENDPOINT = 'https://api.elasticemail.com/v2';

    protected $table = 'sending_servers';
    public static $client = null;
    public static $isWebhookSetup = false;
    public static $isCustomHeadersEnabled = false;

    /**
     * Get authenticated to Mailgun and return the session object.
     *
     * @return mixed
     */
    public function client()
    {
        if (!self::$client) {
            self::$client = new \ElasticEmail\ElasticEmailV2($this->api_key);
        }

        return self::$client;
    }

    /**
     * Process unsubscribe URL
     * See documentation at: https://elasticemail.com/support/user-interface/unsubscribe/#Unsubscribe-Link.
     *
     * @return string message
     */
    public function addUnsubscribeUrl($message)
    {
        return $message;
    }

    /**
     * Handle notification from ElasticEmail
     * Handle Bounce/Feedback/Error.
     *
     * @return mixed
     */
    public static function handleNotification($params)
    {
        // bounce
        if (strcasecmp($params['status'], 'Error') == 0) {
            $bounceLog = new BounceLog();

            // use Elastic Email transaction id as runtime-message-id
            $bounceLog->runtime_message_id = $params['transaction'];
            $trackingLog = TrackingLog::where('runtime_message_id', $bounceLog->runtime_message_id)->first();
            if ($trackingLog) {
                $bounceLog->message_id = $trackingLog->message_id;
            }

            $bounceLog->bounce_type = BounceLog::HARD;
            $bounceLog->raw = json_encode($params);
            $bounceLog->save();
            MailLog::info('Bounce recorded for message '.$bounceLog->runtime_message_id);

            // add subscriber's email to blacklist
            $subscriber = $bounceLog->findSubscriberByRuntimeMessageId();
            if ($subscriber) {
                $subscriber->sendToBlacklist($bounceLog->raw);
                MailLog::info('Email added to blacklist');
            } else {
                MailLog::warning('Cannot find associated tracking log for ElasticEmail message '.$bounceLog->runtime_message_id);
            }
        } elseif (strcasecmp($params['status'], 'AbuseReport') == 0) {
            $feedbackLog = new FeedbackLog();

            // use Elastic Email transaction id as runtime-message-id
            $feedbackLog->runtime_message_id = $params['transaction'];

            // retrieve the associated tracking log in Acelle
            $trackingLog = TrackingLog::where('runtime_message_id', $feedbackLog->runtime_message_id)->first();
            if ($trackingLog) {
                $feedbackLog->message_id = $trackingLog->message_id;
            }

            // ElasticEmail only notifies in case of SPAM reported
            $feedbackLog->feedback_type = 'spam';
            $feedbackLog->raw_feedback_content = json_encode($params);
            $feedbackLog->save();
            MailLog::info('Feedback recorded for message '.$feedbackLog->runtime_message_id);

            // update the mail list, subscriber to be marked as 'spam-reported'
            // @todo: the following lines of code should be wrapped up in one single method: $feedbackLog->markSubscriberAsSpamReported();
            $subscriber = $feedbackLog->findSubscriberByRuntimeMessageId();
            if ($subscriber) {
                $subscriber->markAsSpamReported();
                MailLog::info('Subscriber marked as spam-reported');
            } else {
                MailLog::warning('Cannot find associated tracking log for ElasticEmail message '.$feedbackLog->runtime_message_id);
            }
        }
    }

    /**
     * Enable custom headers.
     * By default, customers headers are suppressed by Elastic Email.
     *
     * @return mixed
     */
    public function enableCustomHeaders()
    {
        if (self::$isCustomHeadersEnabled) {
            return true;
        }

        $uri = self::API_ENDPOINT.'/account/updateadvancedoptions?apikey='.$this->api_key.'&allowCustomHeaders=true';

        $response = file_get_contents($uri);
        $responseJson = json_decode($response);

        if ($responseJson->success == true) {
            MailLog::info('Custom headers enabled');
            self::$isCustomHeadersEnabled = true;
        } else {
            throw new \Exception('Cannot enable customer headers: '.$response);
        }
    }

    /**
     * Setup webhooks for processing bounce and feedback loop.
     *
     * @return mixed
     */
    public function setupWebhooks()
    {
        if (self::$isWebhookSetup) {
            return true;
        }

        // delete existing webhook
        $this->deleteWebHook();

        // add webhook
        $subscribeUrl = urlencode($this->getSubscribeUrl());
        $endpoint = self::API_ENDPOINT.'/account/addwebhook?apikey='.$this->api_key.'&webNotificationUrl='.$subscribeUrl.'&name='.self::WEBHOOK_NAME.'&notifyOncePerEmail=&notificationForSent=&notificationForOpened=&notificationForClicked=&notificationForUnsubscribed=&notificationForAbuseReport=true&notificationForError=true';

        $response = json_decode(file_get_contents($endpoint), true);

        if ($response['success'] == true) {
            MailLog::info('webhook set!');
            self::$isWebhookSetup = true;
        } else {
            $msg = 'ElasticEmail Erorr. Cannot setup webhook for "'.$subscribeUrl.'". Make sure your Elastic API key has full access. Also, please note that the webhook feature is reserved for Elastic Unlimited PRO & API PRO plans only. Response from server: '.json_encode($response);
            MailLog::error($msg);
            MailLog::error('Elastic endpoint: '.$endpoint);
            throw new \Exception($msg);
        }
    }

    public function deleteWebHook()
    {
        MailLog::info('Deleting webhooks if any');
        $loadUri = self::API_ENDPOINT.'/account/loadwebhook?apikey='.$this->api_key.'&limit=1000';
        $webhooks = json_decode(file_get_contents($loadUri), true)['data'];
        MailLog::info(sizeof($webhooks).' webhooks found');

        foreach ($webhooks as $webhook) {
            MailLog::info('Checking '.$webhook['url'].' ('.$webhook['name'].')');
            if ($this->isSubscribeUrl($webhook['url']) || $webhook['name'] == self::WEBHOOK_NAME) {
                MailLog::info('Deleting '.$webhook['url'].' ('.$webhook['name'].')');
                $deleteUri = $deleteUri = self::API_ENDPOINT.'/account/deletewebhook?apikey='.$this->api_key.'&webhookID='.$webhook['webhookid'];
                $result = json_decode(file_get_contents($deleteUri), true);
                if ($result['success'] != true) {
                    MailLog::error('Cannot delete webhook '.$webhook['url'].' ('.$webhook['name'].')');
                    throw new \Exception('Cannot delete webhook '.$webhook['url'].' ('.$webhook['name'].')');
                }
            } else {
                MailLog::info('Skipped');
            }
        }
    }

    public function getSubscribeUrl()
    {
        return StringHelper::joinUrl(Setting::get('url_delivery_handler'), self::WEBHOOK);
    }

    private function isSubscribeUrl($url)
    {
        // simpley compare the provided url to see if it is the same as the subscribe one
        return join_paths($this->getSubscribeUrl(), '/') == join_paths($url, '/');
    }

    /**
     * Unescape the HTML attributes escaped by DOMDocument (inline CSS maker)
     * For example: conver <a href="%7Bunsubscribe:...%7D" to <a href="{unsubscribe:...}".
     *
     * @return mixed
     */
    public function unescapeUnsubscribeUrl($message)
    {
        preg_match_all('/(?<matched>\%7Bunsubscribe:.*?\%7D)/', $message, $result);
        foreach ($result['matched'] as $occurrence) {
            $message = str_replace($occurrence, urldecode($occurrence), $message);
        }

        return $message;
    }

    /**
     * Delivery message.
     *
     * @return mixed
     */
    public function sendElasticEmailV2($message)
    {
        // @todo: what if there are more than 2 parts?
        $html = null;
        $plain = null;
        foreach ($message->getChildren() as $part) {
            $contentType = $part->getContentType();
            if ($contentType == 'text/html') {
                $html = $part->getBody();
            } elseif ($contentType == 'text/plain') {
                $plain = $part->getBody();
            }
        }

        if (!is_null($html)) {
            $html = $this->unescapeUnsubscribeUrl($html);
        }

        // @todo: custom headers not correctly supported by Elastic Email API v2
        $fromEmail = array_keys($message->getFrom())[0];
        $fromName = (is_null($message->getFrom())) ? null : array_values($message->getFrom())[0];
        $toEmail = array_keys($message->getTo())[0];
        $replyToEmail = (is_null($message->getReplyTo())) ? null : array_keys($message->getReplyTo())[0];

        $result = $this->client()->email()->send([
            'to' => $toEmail,
            'replyTo' => $replyToEmail,
            'subject' => $message->getSubject(),
            'from' => $fromEmail,
            'fromName' => $fromName,
            'bodyHtml' => $html,
            'bodyText' => $plain,
            'charset' => 'utf-8',
        ]);

        $jsonResponse = json_decode($result->getData());

        // Use transactionid returned from ElasticEmail as runtime_message_id
        return $jsonResponse->data->transactionid;
    }

    /**
     * Get verified domains.
     *
     * @return array
     */
    public function getVerifiedIdentities()
    {
        $response = file_get_contents(self::API_ENDPOINT.'/domain/list?apikey='.$this->api_key);
        $json = json_decode($response, true);
        if ($json['success'] == false) {
            throw new \Exception($json['error']);
        }

        // just in case the 'domain' value comes in "domain.com (admin@emailaddress.com)"
        $domains = array_map(function ($e) { return preg_split('/\s+/', $e['domain'])[0]; }, $json['data']);

        return $domains;
    }

    /**
     * Check the sending server settings, make sure it does work.
     *
     * @return bool
     */
    public function test()
    {
        $response = file_get_contents(self::API_ENDPOINT.'/domain/list?apikey='.$this->api_key);
        $json = json_decode($response, true);
        if ($json['success'] == false) {
            throw new \Exception('Failed to connect to ElasticEmail: '.$response);
        }

        return true;
    }
}
