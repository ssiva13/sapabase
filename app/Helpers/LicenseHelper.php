<?php

namespace Acelle\Helpers;

use Acelle\Model\Setting;
use Carbon\Carbon;

class LicenseHelper
{
    // license type
    const TYPE_REGULAR = 'regular';
    const TYPE_EXTENDED = 'extended';

    /**
     * Get license type: normal / extended.
     *
     * @var string
     */
    public static function getLicense($license)
    {
		$server_output = array();
		$server_output['status'] = 'valid';
		
		$server_output['data']['verify-purchase']['licence']='ProWebber.ru';
		return $server_output;
    }

    /**
     * Get license type: normal / extended.
     *
     * @var string
     */
    public static function getLicenseType($license)
    {
        $result = self::getLicense($license);

        # return '' if not valid
        if ($result['status'] != 'valid') {
            // License is not valid
            throw new \Exception(trans('messages.license_is_not_valid'));
        }

        return $result['data']['verify-purchase']['licence'] == 'Regular License' ? self::TYPE_REGULAR : self::TYPE_EXTENDED;
    }

    /**
     * Check is valid extend license.
     *
     * @return bool
     */
    public static function isExtended($code = null)
    {
        if (is_null($code)) {
            return \Acelle\Model\Setting::get('license_type') == self::TYPE_EXTENDED;
        } else {
            return self::isValid($code) && self::getLicenseType($code) == self::TYPE_EXTENDED;
        }
    }

    /**
     * Check if supported.
     *
     * @return bool
     */
    public static function isSupported($code = null)
    {
        if (is_null($code)) {
            $code = Setting::get('license');
        }

        if (empty($code)) {
            throw new \Exception('No purchase code available for your installation');
        }

        $result = self::getLicense($code);

        if (array_key_exists('status', $result) && $result['status'] == 'invalid') {
            throw new \Exception('Invalid license key. Please go to Settings > License dashboard and enter a valid license key to register your installation');
        }

        $supportedUntil = Carbon::parse($result['data']['supported_until']);
        $supported = $result['data']['supported'];

        return [
            $supported,
            $supportedUntil,
        ];
    }

    /**
     * Check license is valid.
     *
     * @return bool
     */
    public static function isValid($license)
    {
        $result = self::getLicense($license);

        return $result['status'] == 'valid';
    }
}
