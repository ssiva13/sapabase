<?php

/**
 * SendingServerAmazon class.
 *
 * An abstract class for different types of Amazon sending servers
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
use Acelle\Library\Lockable;

class SendingServerAmazon extends SendingServer
{
    const SNS_TOPIC = 'ACELLEHANDLER';
    const SNS_TYPE = 'amazon'; // @TODO

    public $notificationTypes = array('Bounce', 'Complaint');
    public static $snsClient = null;
    public static $sesClient = null;
    public static $isSnsSetup = false;

    /**
     * Initiate a AWS SNS session and return the session object (snsClient).
     *
     * @return mixed
     */
    public function snsClient()
    {
        if (!self::$snsClient) {
            self::$snsClient = \Aws\Sns\SnsClient::factory(array(
                'credentials' => array(
                    'key' => trim($this->aws_access_key_id),
                    'secret' => trim($this->aws_secret_access_key),
                ),
                'region' => $this->aws_region,
                'version' => '2010-03-31',
            ));
        }

        return self::$snsClient;
    }

    /**
     * Initiate a AWS SES session and return the session object (snsClient).
     *
     * @return mixed
     */
    public function sesClient()
    {
        if (!self::$sesClient) {
            self::$sesClient = \Aws\Ses\SesClient::factory(array(
                'credentials' => array(
                    'key' => trim($this->aws_access_key_id),
                    'secret' => trim($this->aws_secret_access_key),
                ),
                'region' => $this->aws_region,
                'version' => '2010-12-01',
            ));
        }

        return self::$sesClient;
    }

    /**
     * Setup AWS SNS for bounce and feedback loop.
     *
     * @return mixed
     */
    public function setupSns($message)
    {
        if (self::$isSnsSetup) {
            return true;
        }

        MailLog::info('Set up Amazon SNS for email delivery tracking');

        $fromEmail = array_keys($message->getFrom())[0];

        $awsIdentity = $fromEmail;
        $verifyByDomain = false;
        try {
            $this->sesClient()->setIdentityFeedbackForwardingEnabled(array(
                'Identity' => $awsIdentity,
                'ForwardingEnabled' => true,
            ));
        } catch (\Exception $e) {
            $verifyByDomain = true;
            MailLog::warning("From Email address {$fromEmail} not verified by Amazon SES, using domain instead");
        }

        if ($verifyByDomain) {
            // Use domain name as Aws Identity
            $awsIdentity = substr(strrchr($fromEmail, '@'), 1); // extract domain from email
            $this->sesClient()->setIdentityFeedbackForwardingEnabled(array(
                'Identity' => $awsIdentity, // extract domain from email
                'ForwardingEnabled' => true,
            ));
        }

        $topicResponse = $this->snsClient()->createTopic(array('Name' => self::SNS_TOPIC));
        $subscribeUrl = StringHelper::joinUrl(Setting::get('url_delivery_handler'), self::SNS_TYPE);

        $subscribeResponse = $this->snsClient()->subscribe(array(
            'TopicArn' => $topicResponse->get('TopicArn'),
            'Protocol' => stripos($subscribeUrl, 'https') === 0 ? 'https' : 'http',
            'Endpoint' => $subscribeUrl,
        ));

        if (stripos($subscribeResponse->get('SubscriptionArn'), 'pending') === false) {
            $this->subscription_arn = $result->get('SubscriptionArn');
        }

        foreach ($this->notificationTypes as $type) {
            $this->sesClient()->setIdentityNotificationTopic(array(
                'Identity' => $awsIdentity,
                'NotificationType' => $type,
                'SnsTopic' => $topicResponse->get('TopicArn'),
            ));
        }

        self::$isSnsSetup = true;
    }

    /**
     * Setup SNS, make sure the request limit (in case of multi-process) is less than 1 request / second.
     *
     * @return mixed
     */
    public function setupSnsThreadSafe($message)
    {
        if (self::$isSnsSetup) {
            return true;
        }

        $lock = new Lockable(storage_path('locks/sending-server-sns-'.$this->uid));
        $lock->getExclusiveLock(function () use ($message) {
            $this->setupSns($message);
            sleep(1); // SNS request rate limit
        });
    }

    /**
     * Check an email address if it is verified against AWS.
     *
     * @return bool
     */
    public function verifyIdentity($email)
    {
        $domain = extract_domain($email);
        $verifiedIdentities = $this->getVerifiedIdentities();

        return in_array($email, $verifiedIdentities) || in_array($domain, $verifiedIdentities);
    }

    /**
     * Get verified identities (domains and email addresses).
     *
     * @return bool
     */
    public function getVerifiedIdentities()
    {
        $identities = $this->sesClient()->listIdentities([
            'MaxItems' => 1000, # @todo, need pagination here
        ])->toArray()['Identities'];

        $identitiesWithAttributes = $this->sesClient()->getIdentityVerificationAttributes([
            'Identities' => $identities,
        ])->toArray()['VerificationAttributes'];

        $verifiedIdentities = array_keys(array_filter($identitiesWithAttributes, function ($r) { return $r['VerificationStatus'] == 'Success'; }));

        return $verifiedIdentities;
    }

    /**
     * Check an email address if it is verified against AWS.
     *
     * @return bool
     */
    public function sendVerificationEmail($email)
    {
        $this->sesClient()->verifyEmailIdentity([
            'EmailAddress' => $email,
        ]);
    }

    /**
     * Check if AWS actions are allowed.
     *
     * @return bool
     */
    public static function testConnection($key, $secret, $region)
    {
        $iamClient = \Aws\Iam\IamClient::factory(array(
            'credentials' => array(
                'key' => trim($key),
                'secret' => trim($secret),
            ),
            'region' => $region,
            'version' => '2010-05-08',
        ));

        // getting API caller
        $arn = $iamClient->getUser()->get('User')['Arn'];

        $username = array_values(array_slice(explode(':', $arn), -1))[0];
        if ($username == 'root') {
            return true;
        }

        $actions = ['ses:VerifyEmailIdentity', 'ses:GetIdentityVerificationAttributes', 'ses:ListIdentities', 'ses:SetIdentityFeedbackForwardingEnabled', 'sns:CreateTopic', 'sns:Subscribe', 'sns:SetIdentityNotificationTopic'];
        $results = $iamClient->simulatePrincipalPolicy(['PolicySourceArn' => $arn, 'ActionNames' => $actions])->toArray();
        foreach ($results['EvaluationResults'] as $result) {
            $action = $result['EvalActionName'];
            $decision = $result['EvalDecision'];

            if ($decision != 'allowed') {
                throw new \Exception("Action {$action} is not allowed");
            }
        }

        return true;
    }

    /**
     * Check if AWS actions are allowed for the corresponding instance.
     *
     * @return bool
     */
    public function test()
    {
        return self::testConnection(
            $this->aws_access_key_id,
            $this->aws_secret_access_key,
            $this->aws_region
        );
    }
}
