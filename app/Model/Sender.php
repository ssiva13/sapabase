<?php

/**
 * Sender class.
 *
 * Model class for countries
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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log as LaravelLog;

class Sender extends Model
{
    // Statuses
    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';

    // Verifiation types
    const VERIFICATION_TYPE_ACELLE = 'acelle';
    const VERIFICATION_TYPE_AMAZON_SES = 'amazon_ses';

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('senders.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('senders.email', 'like', '%'.$keyword.'%')
                        ->orwhere('senders.name', 'like', '%'.$keyword.'%');
                });
            }
        }

        // Other filter
        if (!empty($request->customer_id)) {
            $query = $query->where('senders.customer_id', '=', $request->customer_id);
        }

        return $query;
    }

    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    public static function pending()
    {
        return self::where('status', self::STATUS_PENDING);
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request)
    {
        $query = self::filter($request);

        if (!empty($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'type',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function rules()
    {
        $rules = array(
            'email' => 'required|email|unique:senders,email,'.$this->id.',id',
            'name' => 'required',
        );

        return $rules;
    }

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function editRules()
    {
        $rules = array(
            'name' => 'required',
        );

        return $rules;
    }

    /**
     * Set sender status to pending.
     *
     * @var void
     */
    public function setPending()
    {
        $this->status = self::STATUS_PENDING;
        $this->save();
    }

    /**
     * The rules for verification.
     *
     * @var array
     */
    public function verificationRules()
    {
        $rules = array(
            'type' => 'required',
        );

        return $rules;
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
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
     * Verification type select options.
     *
     * @return array
     */
    public static function verificationTypeSelectOptions($server)
    {
        $options = [];

        if ($server->allowVerifyingOwnEmails()) {
            $options[] = ['value' => self::VERIFICATION_TYPE_ACELLE, 'text' => \Acelle\Model\Setting::get('site_name')];
        }

        if ($server->allowVerifyingOwnEmailsRemotely()) {
            // AWS server name
            $options[] = ['value' => self::VERIFICATION_TYPE_AMAZON_SES, 'text' => $server->name];
        }

        return $options;
    }

    /**
     * Check if sender is verified.
     *
     * @return object
     */
    public function isVerified()
    {
        return $this->status == self::STATUS_VERIFIED;
    }

    /**
     * Get domain name from email.
     *
     * @return string
     */
    public function getDomain()
    {
        return explode('@', $this->email)[1];
    }

    /**
     * Get domain name from email.
     *
     * @return string
     */
    public static function getAllVerified()
    {
        return self::where('status', '=', self::STATUS_VERIFIED);
    }

    /**
     * Verify sender.
     *
     * @return string
     */
    public function updateVerificationStatus()
    {
        LaravelLog::info(sprintf('Verifying sender %s (%s)', $this->email, $this->type));
        if ($this->type == self::VERIFICATION_TYPE_AMAZON_SES) {
            $this->status = $this->getAwsVerificationServer()->verifyIdentity($this->email) ? self::STATUS_VERIFIED : self::STATUS_PENDING;
        } else {
            $domain = extract_domain($this->email);
            $this->status = $this->customer->getSendingDomains()->contains(function ($r) use ($domain) { return $r->name == $domain; }) ? self::STATUS_VERIFIED : self::STATUS_PENDING;
        }

        $this->save();
        LaravelLog::info(sprintf('Verify sender %s (%s) done, status: %s', $this->email, $this->type, $this->status));
    }

    /**
     * Get AWS Verification Server.
     *
     * @return string
     */
    public function getAwsVerificationServer()
    {
        $subscription = $this->customer->subscription;
        $server = $subscription->plan->primarySendingServer();

        return SendingServerAmazonApi::find($server->id);
    }

    /**
     * Get AWS Verification Server.
     *
     * @return string
     */
    public function sendVerificationEmail()
    {
        LaravelLog::info(sprintf('Sending verification email for %s (%s)', $this->email, $this->type));
        $this->getAwsVerificationServer()->sendVerificationEmail($this->email);
    }
}
