<?php


namespace Acelle\Model;


use Illuminate\Database\Eloquent\Model;

class TwilioNumber extends Model
{
    protected $table = 'twilio_numbers';

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;
}