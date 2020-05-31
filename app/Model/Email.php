<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use File;
use Sunra\PhpSimple\HtmlDomParser;
use Acelle\Library\Tool;
use Acelle\Library\StringHelper;
use Acelle\Library\Log as MailLog;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Validation\ValidationException;
use ZipArchive;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Email extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject', 'from', 'from_name', 'reply_to', 'sign_dkim', 'track_open', 'track_click', 'action_id',
    ];

    /**
     * Association with mailList through mail_list_id column.
     */
    public function automation()
    {
        return $this->belongsTo('Acelle\Model\Automation2', 'automation2_id');
    }

    /**
     * Association with attachments.
     */
    public function attachments()
    {
        return $this->hasMany('Acelle\Model\Attachment');
    }

    /**
     * Association with email links.
     */
    public function emailLinks()
    {
        return $this->hasMany('Acelle\Model\EmailLink');
    }

    /**
     * Association with open logs.
     */
    public function trackingLogs()
    {
        return $this->hasMany('Acelle\Model\TrackingLog');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating automation.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });

        static::deleted(function ($item) {
            $uploaded_dir = $item->getEmailStoragePath();
            \Acelle\Library\Tool::xdelete($uploaded_dir);
        });
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Create automation rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'subject' => 'required',
            'from' => 'required|email',
            'from_name' => 'required',
        ];
    }

    /**
     * Get public campaign upload uri.
     */
    public function getUploadUri()
    {
        return 'app/email/template_'.$this->uid;
    }

    /**
     * Get public campaign upload dir.
     */
    public function getUploadDir()
    {
        return storage_path('app/email/template_'.$this->uid);
    }

    /**
     * Create template from layout.
     *
     * All availabel template tags
     */
    public function addTemplateFromLayout($layout)
    {
        $sDir = database_path('layouts/'.$layout);
        $this->loadFromDirectory($sDir);
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function getBuilderTemplates($customer)
    {
        $result = [];

        // Gallery
        $templates = Template::where('customer_id', '=', null)
            ->orWhere('customer_id', '=', $customer->id)
            ->orderBy('customer_id')
            ->get();

        foreach ($templates as $template) {
            $result[] = [
                'name' => $template->name,
                'thumbnail' => action('TemplateController@image', $template->uid),
            ];
        }

        return $result;
    }

    /**
     * Upload attachment.
     *
     * @return mixed
     */
    public function uploadAttachment($file)
    {
        $att = new Attachment();
        $att->email_id = $this->id;
        $att->size = $file->getSize();
        $att->name = $file->getClientOriginalName();
        $att->file = Storage::disk('local')->put('attachments/'.$this->uid, $file);

        $att->save();
    }

    /**
     * Copy new template.
     */
    public function copyFromTemplate($template)
    {
        Tool::xdelete($this->getStoragePath());

        \Acelle\Library\Tool::xcopy($template->getStoragePath(), $this->getStoragePath());

        // replace image url
        $this->content = $template->content;
        $this->save();
    }

    /**
     * Get thumb.
     */
    public function getThumb()
    {
        // find index
        $names = array('thumbnail.jpg', 'thumbnail.png', 'thumb.jpg', 'thumb.png');
        foreach ($names as $name) {
            if (is_file($file = $this->getStoragePath().$name)) {
                return $file;
            }
        }

        return;
    }

    /**
     * Load from directory.
     */
    public function loadFromDirectory($tmp_path)
    {
        // remove current folder
        // exec("rm -r {$tmp_path}");
        Tool::xdelete($this->getStoragePath());

        // try to find the main file, index.html | index.html | file_name.html | ...
        $main_file = null;
        $sub_path = '';
        $possible_main_file_names = array('index.html', 'index.htm');

        $possible_main_file_names = array('index.html', 'index.htm');
        foreach ($possible_main_file_names as $name) {
            if (is_file($file = $tmp_path.'/'.$name)) {
                $main_file = $file;
                break;
            }
            $dirs = array_filter(glob($tmp_path.'/'.'*'), 'is_dir');
            foreach ($dirs as $sub) {
                if (is_file($file = $sub.'/'.$name)) {
                    $main_file = $file;
                    $sub_path = explode('/', $sub)[count(explode('/', $sub)) - 1].'/';
                    break;
                }
            }
        }
        // try to find first htm|html file
        if ($main_file === null) {
            $objects = scandir($tmp_path);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (!is_dir($tmp_path.'/'.$object)) {
                        if (preg_match('/\.html?$/i', $object)) {
                            $main_file = $tmp_path.'/'.$object;
                            break;
                        }
                    }
                }
            }
            $dirs = array_filter(glob($tmp_path.'/'.'*'), 'is_dir');
            foreach ($dirs as $sub) {
                $objects = scandir($sub);
                foreach ($objects as $object) {
                    if ($object != '.' && $object != '..') {
                        if (!is_dir($sub.'/'.$object)) {
                            if (preg_match('/\.html?$/i', $object)) {
                                $main_file = $sub.'/'.$object;
                                $sub_path = explode('/', $sub)[count(explode('/', $sub)) - 1].'/';
                                break;
                            }
                        }
                    }
                }
            }
        }

        // // main file not found
        // if ($main_file === null) {
        //     $validator->errors()->add('file', 'Cannot find index file (index.html) in the template folder');
        //     throw new ValidationException($validator);
        // }

        // read main file content
        $html_content = trim(file_get_contents($main_file));
        $this->content = $html_content;
        $this->save();

        // upload path
        $upload_path = $this->getStoragePath();

        // copy all folder to public path
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        // exec("cp -r {$tmp_path}/* {$public_upload_path}/");
        Tool::xcopy($tmp_path, $upload_path);
    }

    /**
     * Get public campaign upload dir.
     */
    public function getEmailStoragePath()
    {
        return storage_path('app/users/'.$this->automation->customer->uid.'/emails/'.$this->uid.'/');
    }

    /**
     * Get public campaign upload dir.
     */
    public function getStoragePath($path = '')
    {
        return $this->getEmailStoragePath().'content/'.$path;
    }

    /**
     * Upload a template.
     */
    public function uploadTemplate($request)
    {
        $rules = array(
            'file' => 'required|mimetypes:application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip',
        );

        $validator = Validator::make($request->all(), $rules, [
            'file.mimetypes' => 'Input must be a valid .zip file',
        ]);

        if ($validator->fails()) {
            return [false, $validator];
        }

        // move file to temp place
        $tmp_path = storage_path('tmp/uploaded_template_'.$this->uid.'_'.time());
        $file_name = $request->file('file')->getClientOriginalName();
        $request->file('file')->move($tmp_path, $file_name);
        $tmp_zip = join_paths($tmp_path, $file_name);

        // read zip file check if zip archive invalid
        $zip = new ZipArchive();
        if ($zip->open($tmp_zip, ZipArchive::CREATE) !== true) {
            // @todo hack
            // $validator = Validator::make([], []); // Empty data and rules fields
            $validator->errors()->add('file', 'Cannot open .zip file');

            return [false, $validator];
        }

        // unzip template archive and remove zip file
        $zip->extractTo($tmp_path);
        $zip->close();
        unlink($tmp_zip);

        $this->loadFromDirectory($tmp_path);

        // remove tmp folder
        // exec("rm -r {$tmp_path}");
        Tool::xdelete($tmp_path);

        return [true, $validator];
    }

    /**
     * transform URL.
     */
    public function transform()
    {
        // replace relative urls
        $this->content = '<!DOCTYPE html>'.\Acelle\Library\Tool::replaceTemplateUrl($this->content, route('email_assets', ['uid' => $this->uid, 'path' => '']));

        return $this->content;
    }

    /**
     * transform URL.
     */
    public function untransform()
    {
        // replace absolute urls
        $this->content = str_replace(route('email_assets', ['uid' => $this->uid, 'path' => '']).'/', '', $this->content);
    }

    /**
     * transform URL.
     */
    public function render()
    {
        $this->transform();

        return $this->content;
    }

    /**
     * Upload asset.
     */
    public function uploadAsset($file)
    {
        $file_name = $file->getClientOriginalName();
        $upload_dir = 'upload/';

        $new_name = $file_name;
        $i = 1;
        while (file_exists($this->getStoragePath($upload_dir).$new_name)) {
            $new_name = $i.'_'.$file_name;
            ++$i;
        }

        $path = $file->move(
            $this->getStoragePath($upload_dir), $new_name
        );

        return $upload_dir.$new_name;
    }

    /**
     * Find and update email links.
     */
    public function updateLinks()
    {
        if (!$this->content) {
            return false;
        }

        $links = [];

        // find all links from contents
        $document = HtmlDomParser::str_get_html($this->content);
        foreach ($document->find('a') as $element) {
            if (preg_match('/^http/', $element->href) != 0) {
                $links[] = trim($element->href);
            }
        }

        // delete al bold links
        $this->emailLinks()->whereNotIn('link', $links)->delete();

        foreach ($links as $link) {
            $exist = $this->emailLinks()->where('link', '=', $link)->count();

            if (!$exist) {
                $this->emailLinks()->create([
                    'link' => $link,
                ]);
            }
        }
    }

    // @note: complicated dependencies
    // It is just fine, we can think Email as an object depends on User/Customer
    public function deliverTo($subscriber)
    {
        // @todo: code smell here, violation of Demeter Law
        // @todo: performance
        while ($this->automation->customer->overQuota()) {
            MailLog::warning(sprintf('Email `%s` (%s) to `%s` halted, user exceeds sending limit', $this->subject, $this->uid, $subscriber->email));
            sleep(60);
        }

        $server = $subscriber->mailList->pickSendingServer();

        MailLog::info("Sending to subscriber `{$subscriber->email}`");

        list($message, $msgId) = $this->prepare($subscriber);

        $sent = $server->send($message);

        // additional log
        MailLog::info("Sent to subscriber `{$subscriber->email}`");
        $this->trackMessage($sent, $subscriber, $server, $msgId);
    }

    /**
     * Prepare the email content using Swift Mailer.
     *
     * @input object subscriber
     * @input object sending server
     *
     * @return MIME text message
     */
    public function prepare($subscriber)
    {
        // build the message
        $customHeaders = $this->getCustomHeaders($subscriber, $this);
        $msgId = $customHeaders['X-Acelle-Message-Id'];

        $message = new \Swift_Message();
        $message->setId($msgId);

        // fixed: HTML type only
        $message->setContentType('text/html; charset=utf-8');

        foreach ($customHeaders as $key => $value) {
            $message->getHeaders()->addTextHeader($key, $value);
        }

        // @TODO for AWS, setting returnPath requires verified domain or email address
        $server = $subscriber->mailList->pickSendingServer();
        if ($server->allowCustomReturnPath()) {
            $returnPath = $server->getVerp($subscriber->email);
            if ($returnPath) {
                $message->setReturnPath($returnPath);
            }
        }
        $message->setSubject($this->getSubject($subscriber, $msgId));
        $message->setFrom(array($this->from => $this->from_name));
        $message->setTo($subscriber->email);
        $message->setReplyTo($this->reply_to);
        $message->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        $message->addPart($this->getHtmlContent($subscriber, $msgId, $server), 'text/html');

        if ($this->sign_dkim) {
            $message = $this->sign($message);
        }

        //@todo attach function used for any attachment of Campaign
        $path_campaign = public_path().'/campaign_attachment'.'/'.$this->uid;
        if (is_dir($path_campaign)) {
            $files = File::allFiles($path_campaign);
            foreach ($files as $file) {
                $message->attach(\Swift_Attachment::fromPath((string) $file));
            }
        }

        return array($message, $msgId);
    }

    /**
     * Build Email Custom Headers.
     *
     * @return Hash list of custom headers
     */
    public function getCustomHeaders($subscriber, $server)
    {
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from));

        return array(
            'X-Acelle-Campaign-Id' => $this->uid,
            'X-Acelle-Subscriber-Id' => $subscriber->uid,
            'X-Acelle-Customer-Id' => $this->automation->customer->uid,
            'X-Acelle-Message-Id' => $msgId,
            'X-Acelle-Sending-Server-Id' => $server->uid,
            'List-Unsubscribe' => '<'.$this->generateUnsubscribeUrl($msgId, $subscriber).'>',
            'Precedence' => 'bulk',
        );
    }

    public function generateUnsubscribeUrl($msgId, $subscriber)
    {
        // OPTION 1: immediately opt out
        $path = route('unsubscribeUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        // OPTION 2: unsubscribe form
        $path = route('unsubscribeForm', ['list_uid' => $subscriber->mailList->uid, 'code' => $subscriber->getSecurityToken('unsubscribe'), 'uid' => $subscriber->uid], false);

        return $this->buildPublicUrl($path);
    }

    /**
     * Log delivery message, used for later tracking.
     */
    public function trackMessage($response, $subscriber, $server, $msgId)
    {
        // @todo: customerneedcheck
        $params = array_merge(array(
                'email_id' => $this->id,
                'message_id' => $msgId,
                'subscriber_id' => $subscriber->id,
                'sending_server_id' => $server->id,
                'customer_id' => $this->automation->customer->id,
            ), $response);

        if (!isset($params['runtime_message_id'])) {
            $params['runtime_message_id'] = $msgId;
        }

        // create tracking log for message
        TrackingLog::create($params);

        // increment customer quota usage
        $this->automation->customer->countUsage();
        $server->countUsage();
    }

    /**
     * Get tagged Subject.
     *
     * @return string
     */
    public function getSubject($subscriber, $msgId)
    {
        return $this->tagMessage($this->subject, $subscriber, $msgId, null);
    }

    public function buildPublicUrl($path)
    {
        $host = config('app.url');

        return join_url($host, $path);
    }

    public function tagMessage($message, $subscriber, $msgId, $server = null)
    {
        if (!is_null($server) && $server->isElasticEmailServer()) {
            $message = $server->addUnsubscribeUrl($message);
        }

        $tags = array(
            'CAMPAIGN_NAME' => $this->name,
            'CAMPAIGN_UID' => $this->uid,
            'CAMPAIGN_SUBJECT' => $this->subject,
            'CAMPAIGN_FROM_EMAIL' => $this->from,
            'CAMPAIGN_FROM_NAME' => $this->from_name,
            'CAMPAIGN_REPLY_TO' => $this->reply_to,
            'SUBSCRIBER_UID' => $subscriber->uid,
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'CONTACT_NAME' => $subscriber->mailList->contact->company,
            'CONTACT_COUNTRY' => $subscriber->mailList->contact->country->name,
            'CONTACT_STATE' => $subscriber->mailList->contact->state,
            'CONTACT_CITY' => $subscriber->mailList->contact->city,
            'CONTACT_ADDRESS_1' => $subscriber->mailList->contact->address_1,
            'CONTACT_ADDRESS_2' => $subscriber->mailList->contact->address_2,
            'CONTACT_PHONE' => $subscriber->mailList->contact->phone,
            'CONTACT_URL' => $subscriber->mailList->contact->url,
            'CONTACT_EMAIL' => $subscriber->mailList->contact->email,
            'LIST_NAME' => $subscriber->mailList->name,
            'LIST_SUBJECT' => $subscriber->mailList->default_subject,
            'LIST_FROM_NAME' => $subscriber->mailList->from_name,
            'LIST_FROM_EMAIL' => $subscriber->mailList->from_email,
        );

        # Subscriber specific
        if (!$this->isStdClassSubscriber($subscriber)) {
            $tags['UPDATE_PROFILE_URL'] = $this->generateUpdateProfileUrl($subscriber);
            $tags['UNSUBSCRIBE_URL'] = $this->generateUnsubscribeUrl($msgId, $subscriber);
            $tags['WEB_VIEW_URL'] = $this->generateWebViewerUrl($msgId);

            # Subscriber custom fields
            foreach ($subscriber->mailList->fields as $field) {
                $tags['SUBSCRIBER_'.$field->tag] = $subscriber->getValueByField($field);
            }
        } else {
            $tags['SUBSCRIBER_EMAIL'] = $subscriber->email;
        }

        // Actually transform the message
        foreach ($tags as $tag => $value) {
            $message = str_replace('{'.$tag.'}', $value, $message);
        }

        return $message;
    }

    /**
     * Check if the given variable is a subscriber object (for actually sending a campaign)
     * Or a stdClass subscriber (for sending test email).
     *
     * @param object $object
     */
    public function isStdClassSubscriber($object)
    {
        return get_class($object) == 'stdClass';
    }

    public function generateUpdateProfileUrl($subscriber)
    {
        $path = route('updateProfileUrl', ['list_uid' => $subscriber->mailList->uid, 'uid' => $subscriber->uid, 'code' => $subscriber->getSecurityToken('update-profile')], false);

        return $this->buildPublicUrl($path);
    }

    public function generateWebViewerUrl($msgId)
    {
        $path = route('webViewerUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        return $this->buildPublicUrl($path);
    }

    /**
     * Build Email HTML content.
     *
     * @return string
     */
    public function getHtmlContent($subscriber, $msgId, $server = null)
    {
        // @note: IMPORTANT: the order must be as follows
        // * addTrackingURL
        // * appendOpenTrackingUrl
        // * tagMessage

        // @note: addTrackingUrl() must go before appendOpenTrackingUrl()
        $body = $this->transform();

        // Enable click tracking
        if ($this->track_click) {
            $body = $this->addTrackingUrl($body, $msgId);
        }

        // Enable open tracking
        if ($this->track_open) {
            $body = $this->appendOpenTrackingUrl($body, $msgId);
        }

        // Append footer
        if ($this->footerEnabled()) {
            $body = $this->appendFooter($body, $this->getHtmlFooter());
        }

        // Transform tags
        $body = $this->tagMessage($body, $subscriber, $msgId, $server);

        // Transform CSS/HTML content to inline CSS
        $body = $this->inlineHtml($body);

        return $body;
    }

    /**
     * Replace link in text by click tracking url.
     *
     * @return text
     * @note addTrackingUrl() must go before appendOpenTrackingUrl()
     */
    public function addTrackingUrl($email_html_content, $msgId)
    {
        if (preg_match_all('/<a[^>]*href=["\'](?<url>http[^"\']*)["\']/i', $email_html_content, $matches)) {
            foreach ($matches[0] as $key => $href) {
                $url = $matches['url'][$key];

                $newUrl = route('clickTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId), 'url' => StringHelper::base64UrlEncode($url)], false);
                $newUrl = $this->buildTrackingUrl($newUrl);
                $newHref = str_replace($url, $newUrl, $href);

                // if the link contains UNSUBSCRIBE URL tag
                if (strpos($href, '{UNSUBSCRIBE_URL}') !== false) {
                    // just do nothing
                } elseif (preg_match('/{[A-Z0-9_]+}/', $href)) {
                    // just skip if the url contains a tag. For example: {UPDATE_PROFILE_URL}
                    // @todo: do we track these clicks?
                } else {
                    $email_html_content = str_replace($href, $newHref, $email_html_content);
                }
            }
        }

        return $email_html_content;
    }

    /**
     * Append Open Tracking URL
     * Append open-tracking URL to every email message.
     */
    public function appendOpenTrackingUrl($body, $msgId)
    {
        $path = route('openTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);
        $url = $this->buildTrackingUrl($path);

        return $body.'<img src="'.$url.'" width="0" height="0" alt="" style="visibility:hidden" />';
    }

    public function buildTrackingUrl($path)
    {
        $host = $this->getTrackingHost();

        return join_url($host, $path);
    }

    public function getTrackingHost()
    {
        $root = config('app.url');

        return $root;

        /*
        if (!$this->trackingDomain()->exists()) {
            return $root;
        }

        preg_match('/^(?<scheme>https{0,1}:\/\/)/', $root, $result);
        $protocol = $result['scheme'];

        return sprintf('%s%s', $protocol, $this->trackingDomain->name);
        */
    }

    /**
     * Check if email footer enabled.
     *
     * @return string
     */
    public function footerEnabled()
    {
        return ($this->automation->customer->getCurrentSubscription()->plan->getOption('email_footer_enabled') == 'yes') ? true : false;
    }

    /**
     * Get HTML footer.
     *
     * @return string
     */
    public function getHtmlFooter()
    {
        return $this->automation->customer->getCurrentSubscription()->plan->getOption('html_footer');
    }

    /**
     * Append footer.
     *
     * @return string.
     */
    public function appendFooter($body, $footer)
    {
        return $body.$footer;
    }

    /**
     * Check if template is layout.
     *
     * @return string.
     */
    public function isLayout($html = null)
    {
        if (!$html) {
            $html = $this->render();
        }

        if (strpos($html, 'AcelleSystemLayouts') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Convert html to inline.
     *
     * @todo not very OOP here, consider moving this to a Helper instead
     */
    public function inlineHtml($html)
    {
        if ($this->isLayout($html)) {
            // Convert to inline css if template source is builder
            $cssToInlineStyles = new CssToInlineStyles();
            $css = file_get_contents(public_path('assets2/css/email/main.css'));
            // output
            $html = $cssToInlineStyles->convert(
                $html,
                $css
            );
        }

        return $html;
    }

    /**
     * Find sending domain from email.
     *
     * @return mixed
     */
    public function findSendingDomain($email)
    {
        $domainName = substr(strrchr($email, '@'), 1);

        if ($domainName == false) {
            return;
        }

        $domain = $this->customer->activeDkimSendingDomains()->where('name', $domainName)->first();
        if (is_null($domain)) {
            $domain = SendingDomain::getAllAdminActive()->where('name', $domainName)->first();
        }

        return $domain;
    }

    /**
     * Sign the message with DKIM.
     *
     * @return mixed
     */
    public function sign($message)
    {
        $sendingDomain = $this->findSendingDomain($this->from_email);

        if (empty($sendingDomain)) {
            return $message;
        }

        $privateKey = $sendingDomain->dkim_private;
        $domainName = $sendingDomain->name;
        $selector = $sendingDomain->getDkimSelectorParts()[0];
        $signer = new \Swift_Signers_DKIMSigner($privateKey, $domainName, $selector);
        $signer->ignoreHeader('Return-Path');
        $message->attachSigner($signer);

        return $message;
    }

    public function isOpened($subscriber)
    {
        return $this->trackingLogs()->where('subscriber_id', $subscriber->id)
                            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')->exists();
    }

    /**
     * Check if email has template.
     */
    public function hasTemplate()
    {
        return $this->content;
    }

    /**
     * Remove email template.
     */
    public function removeTemplate()
    {
        $this->content = null;
        \Acelle\Library\Tool::xdelete($this->getStoragePath());
        $this->save();
    }

    /**
     * Update email plain text.
     */
    public function updatePlainFromContent()
    {
        if (!$this->plain) {
            $this->plain = preg_replace('/\s+/', ' ', preg_replace('/\r\n/', ' ', strip_tags($this->content)));
            $this->save();
        }
    }
}
