<?php

/**
 * IpLocation class.
 *
 * Model class for IP Locations
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

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log as LaravelLog;
use Acelle\Library\Notification\BackendError;

class IpLocation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_code', 'country_name', 'region_code',
        'region_name', 'city', 'zipcode',
        'latitude', 'longitude', 'metro_code', 'areacode',
    ];

    /**
     * Add new IP.
     *
     * return Location
     */
    public static function add($ip)
    {
        //SELECT * FROM `ip2location_db11` WHERE INET_ATON('116.109.245.204') <= ip_to LIMIT 1
        $location = self::firstOrNew(['ip_address' => $ip]);

        // Get info
        try {
            $geoip = App::make('Acelle\Library\Contracts\GeoIpInterface');
            $geoip->resolveIp($ip);

            $location->ip_address = $ip;
            $location->country_code = $geoip->getCountryCode();
            $location->country_name = $geoip->getCountryName();
            $location->region_name = $geoip->getRegionName();
            $location->city = $geoip->getCity();
            $location->zipcode = $geoip->getZipcode();
            $location->latitude = $geoip->getLatitude();
            $location->longitude = $geoip->getLongitude();
            $location->save();
        } catch (\Exception $e) {
            // Note log
            $title = 'GeoIP Error';
            BackendError::cleanupDuplicateNotifications($title);
            BackendError::warning([
                'title' => $title,
                'message' => 'Cannot resolve IP address. '.$e->getMessage(),
            ]);

            LaravelLog::warning('Cannot get IP location info: '.$e->getMessage());
        }

        return $location;
    }

    /**
     * Location name.
     *
     * return Location
     */
    public function name()
    {
        $str = [];
        if (!empty($this->city)) {
            $str[] = $this->city;
        }
        if (!empty($this->region_name)) {
            $str[] = $this->region_name;
        }
        if (!empty($this->country_name)) {
            $str[] = $this->country_name;
        }
        $name = implode(', ', $str);

        return empty($name) ? trans('messages.unknown') : $name;
    }
}
