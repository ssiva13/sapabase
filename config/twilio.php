<?php
return [
    'credentials' => [
        'twilio_sid' => env('TWILIO_SID', null),
        'twilio_auth_token' => env('TWILIO_AUTH_TOKEN', null),
        'twilio_number' => env('TWILIO_NUMBER', null),
    ],
];
