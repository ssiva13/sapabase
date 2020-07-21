<?php

namespace Acelle\Model;

use Acelle\Http\Controllers\TwilioController;
use Illuminate\Database\Eloquent\Model;

class TwilioCallLogs extends Model
{
    protected $table = 'twilio_call_logs';

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
        'to', 'from', 'duration'
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
        $query = self::select('twilio_call_logs.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('twilio_call_logs.to', 'like', '%'.$keyword.'%');
                    $q->orwhere('twilio_call_logs.from', 'like', '%'.$keyword.'%');
                    $q->orwhere('twilio_call_logs.status', 'like', '%'.$keyword.'%');
                    $q->orwhere('twilio_call_logs.direction', 'like', '%'.$keyword.'%');
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
        return self::where('sid', '=', $sid)->first();
    }

    public static function callSum($customer_id){
        return self::where('customer_id', '=', $customer_id)->sum('price');
    }

    public static function callRefresh($customer_id, $user_id = null){
        $new_calls = 0;
        $twilio = new TwilioController();
        foreach ($twilio->connectTwilio()->account->calls->read() as $key => $call) {
            $call_record = self::findBySid($call->sid);
            if($call_record === false){
                $twiliocall = new TwilioCallLogs();
                $twiliocall->customer_id = $customer_id;
                $twiliocall->sid = $call->sid;
                $twiliocall->from = $call->from;
                $twiliocall->to = $call->to;
                $twiliocall->price = ($call->price) ? $call->price : 0;
                $twiliocall->price_unit = $call->priceUnit;
                $twiliocall->duration = $call->duration;
                $twiliocall->direction = $call->direction;
                $twiliocall->start_time = $call->startTime->format("Y-m-d H:i:s");
                $twiliocall->end_time = $call->endTime->format("Y-m-d H:i:s");
                $twiliocall->status = $call->status;
                $twiliocall->save();
                ++ $new_calls;
            }elseif($customer_id != null && $call_record->customer_id === null){
                $number = TwilioNumber::where('number', '=' ,$call_record->from)->orWhere('number', '=' ,$call_record->to)->first();
                if($user_id == $number->user_id){
                    $call_record->customer_id = $customer_id;
                    $call_record->save();
                    ++ $new_calls;
                }
            }
        }
        return $new_calls;
    }
}
