<?php


namespace Acelle\Model;


use Acelle\Http\Controllers\TwilioController;
use Acelle\Library\Log as MailLog;
use Illuminate\Database\Eloquent\Model;
use Twilio\Exceptions\TwilioException;
use Twilio\TwiML\VoiceResponse;

class TwilioMessage extends Model
{

    protected $table = 'twilio_mesages';

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'from' => 'required',
        'subject' => 'required',

    );

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message', 'action_id', 'from', 'from_name', 'type', 'reply_to', 'subject'
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
     * Create automation rules.
     *
     * @return array
     */
    public function rules()
    {
        return self::$rules;
    }
    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('twiliomessages.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('twilio_messages.message', 'like', '%'.$keyword.'%');
                });
            }
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

    /** @noinspection PhpUndefinedMethodInspection */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    public function deliverTo($subscriber)
    {
        MailLog::info("Sending sms to subscriber `{}`");
        $twilio = new TwilioController();
        $twilio_si = $twilio->sendMessage($subscriber, $this);
//        $twilio_sid = $this->sendMessage($subscriber);
        try {
            if ($twilio_si) {
                MailLog::info("Sent to subscriber `{$subscriber->phone}`");
            } else {
                MailLog::error("Not sent to subscriber `{$subscriber->phone}`");
            }
        } catch (TwilioException $e) {
        }
    }

    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param $recipient
     * @return bool|mixed
     * @throws TwilioException
     */
    private function sendMessage($recipient)
    {
        $sid = null;
        $twilio = new TwilioController();
        $twilio_sid = $twilio->connectTwilio();
        switch ($this->type){
            case 'sms':
                $response = $twilio_sid->messages->create(
                    $recipient->phone,
                    [
                        'from' => $this->from,
                        'body' => $this->message,

                    ] );
                $sid = $response->sid;
                break;

            case 'call':
                $response = $twilio_sid->calls->create(
                    $recipient->phone,
                    $this->from,
                    [
                        'url' => $this->message
                    ]
                );
                $sid = $response->sid;
                break;

            case 'fax':
                $response = new VoiceResponse();
                $response->play(
                    $this->message,
                    [
                        'loop' => 1
                    ]
                );
                $sid = $response;
                break;
        }

        return $sid;
    }
}