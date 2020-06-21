<?php


namespace Acelle\Model;


use Illuminate\Database\Eloquent\Model;

class TwilioNumber extends Model
{

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';


    protected $table = 'twilio_numbers';

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
        'number' => 'required',
    );

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number'
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
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('twilio_numbers.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('twilio_numbers.number', 'like', '%'.$keyword.'%')
                        ->orWhere('twilio_numbers.inbound_recording', 'like', '%'.$keyword.'%')
                        ->orWhere('twilio_numbers.outbound_recording', 'like', '%'.$keyword.'%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
            if (!empty($filters['type'])) {
                $query = $query->where('sending_servers.type', '=', $filters['type']);
            }
        }


        if (!empty($request->admin_id)) {
            $query = $query->where('sending_servers.admin_id', '=', $request->admin_id);
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


}