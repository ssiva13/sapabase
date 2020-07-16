<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class TwilioSmsLogs extends Model
{
    protected $table = 'twilio_sms_logs';

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'to', 'from', 'body'
    ];

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
     * Filter items.
     * @param $request
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('twilio_sms_logs.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('twilio_sms_logs.to', 'like', '%'.$keyword.'%');
                    $q->orwhere('twilio_sms_logs.from', 'like', '%'.$keyword.'%');
                    $q->orwhere('twilio_sms_logs.body', 'like', '%'.$keyword.'%');
                    $q->orwhere('twilio_sms_logs.status', 'like', '%'.$keyword.'%');
                    $q->orwhere('twilio_sms_logs.direction', 'like', '%'.$keyword.'%');
                });
            }
        }

        return $query;
    }

    /**
     * Search items.
     * @param $request
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
     * @param $uid
     * @return mixed
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }


    /**
     * @param $sid
     * @return bool
     */
    public static function findBySid($sid)
    {
        if (self::where('sid', '=', $sid)->first() === null) {
            return false;
        }
        return true;
    }

}
