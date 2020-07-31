<?php

namespace Acelle\Model;

use Acelle\Http\Controllers\TwilioController;
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
        return self::where('sid', '=', $sid)->first();
    }

    public static function smsSum($customer_id){
        return self::where('customer_id', '=', $customer_id)->sum('price');
    }

    public static function smsRefresh($customer_id, $user_id = null){
        $new_sms = 0;
        $sms_log = array();
        $twilio = new TwilioController();
        foreach ($twilio->connectTwilio()->account->messages->read() as $key => $sms) {
            $sms_record = self::findBySid($sms->sid);
            if($sms_record === false){
                $twiliosms = new TwilioSmsLogs();
                $twiliosms->customer_id = $customer_id;
                $twiliosms->sid = $sms->sid;
                $twiliosms->from = $sms->from;
                $twiliosms->to = $sms->to;
                $twiliosms->price = ($sms->price) ? $sms->price : 0;
                $twiliosms->price_unit = $sms->priceUnit;
                $twiliosms->body = $sms->body;
                $twiliosms->direction = $sms->direction;
                $twiliosms->date_sent = $sms->dateSent->format("Y-m-d H:i:s");
                $twiliosms->status = $sms->status;
                $twiliosms->save();
                ++ $new_sms;

                $sms_log[$key] = $twiliosms;
            }elseif($customer_id != null && $sms_record->customer_id === null){
                $number = TwilioNumber::where('number', '=' ,$sms_record->from)->orWhere('number', '=' ,$sms_record->to)->first();
                if($user_id == $number->user_id){
                    $sms_record->customer_id = $customer_id;
                    $sms_record->save();
                    ++ $new_sms;
                }
            }
            $to_file = storage_path('app/messages_'.$sms->to.'.json');
            $from_file = storage_path('app/messages_'.$sms->from.'.json');

            if(file_exists($to_file)) {
                $data = file_get_contents($to_file);
                $to_arr = json_decode($data, TRUE);
                $array = array_diff( $to_arr, array($sms->to) );
                file_put_contents($to_file, json_encode($array));
            }
            if(file_exists($from_file)){
                $data = file_get_contents($from_file);
                $from_arr = json_decode($data, TRUE);
                $array = array_diff( $from_arr, array($sms->from) );
                file_put_contents($from_file, json_encode($array));
            }   }
        return $new_sms;
    }
}
