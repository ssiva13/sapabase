<?php


namespace Acelle\Model;


use Illuminate\Database\Eloquent\Model;

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
        'message' => 'required',
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

    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

}