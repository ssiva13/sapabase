<?php

/**
 * Template class.
 *
 * Model class for template
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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Validator;
use Acelle\Library\Tool;
use ZipArchive;
use Illuminate\Validation\ValidationException;

class SmsTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'content',
    ];

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

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
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    public function admin()
    {
        return $this->belongsTo('Acelle\Model\Admin');
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $query = self::select('sms_templates.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        // filters
        $filters = $request->filters;

        // Customer
        if (!empty($request->customer_id)) {
            if (!empty($filters) && !empty($filters['from'])) {
                if ($filters['from'] == 'mine') {
                    $query = $query->where('customer_id', '=', $request->customer_id);
                } elseif ($filters['from'] == 'gallery') {
                    $query = $query->where('customer_id', '=', null);
                } else {
                    $query = $query->where('customer_id', '=', null)
                        ->orWhere('customer_id', '=', $request->customer_id);
                }
            }
        }

        // from
        if ($request->from) {
            if ($request->from == 'mine') {
                $query = $query->where('customer_id', '=', $request->customer_id);
            } elseif ($request->from == 'gallery') {
                $query = $query->where('customer_id', '=', null);
            } else {
                $query = $query->where('customer_id', '=', null)
                    ->orWhere('customer_id', '=', $request->customer_id);
            }
        }

        // Admin
        if (!empty($request->admin_id)) {
            $query = $query->where('admin_id', '=', $request->admin_id);
        }

        return $query;
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
     * Filter items.
     *
     * @return collect
     */
    public static function adminFilter($request)
    {
        $user = $request->user();
        $query = self::where('shared', '=', true);

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function adminSearch($request)
    {
        $query = self::adminFilter($request);

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
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
     * Display creator name.
     *
     * @return string
     */
    public function displayCreatorName()
    {
        return is_object($this->admin) ? $this->admin->displayName() : (is_object($this->customer) ? $this->customer->displayName() : '');
    }

    public static function setDefault($customer_id){
        $query = self::where('custom_order', 100)->where('customer_id', $customer_id)->where('type', 'call');
        if($query->count() > 0){
            $query->update(['custom_order' => 0]);
            return true;
        }
        return false;
    }

    public static function getDefault($customer_id){
        $query = self::where('custom_order', 100)->where('customer_id', $customer_id)->where('type', 'call');
        if(! $query->count() > 0) {
            return;
        }
        return $query->first();
    }

}
