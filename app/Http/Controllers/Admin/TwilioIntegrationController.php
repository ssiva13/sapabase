<?php


namespace Acelle\Http\Controllers\Admin;


use Acelle\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Twilio\TwiML\MessagingResponse;
use Twilio\TwiML\VoiceResponse;

class TwilioIntegrationController extends Controller
{
    private $config;
    private $account_sid;
    private $auth_token;
    private $client;
    private $twilio_number;

    public function __construct()
    {
        $this->config = config('twilio.credentials');
        $this->account_sid = $this->config['twilio_sid'];
        $this->auth_token = $this->config['twilio_auth_token'];
        $this->twilio_number = $this->config['twilio_number'];

        $this->client = new Client($this->account_sid, $this->auth_token);
    }
    /**
     * automated sms sending function +1 513-900-1935
     * @return \Illuminate\Http\Response
     */
    public function automatedNotification(Request $request)
    {
//        return $request;
        switch ($request->type){
            case 'sms':
                $sent = $this->sendMessage($request->message, $request->recipients);
                break;
            case 'mail':
                $sent = $this->sendMessage($request->message, $request->recipients, $request->type);
                break;
            case 'voice':
        }

    }
    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message Body of sms
     * @param Number $recipients string or array of phone number of recepient
     */
    private function sendMessage($message, $recipients, $type = 'sms')
    {
        switch ($type){
            case 'sms':
                $sent = $this->client->messages->create($recipients, ['from' => $this->twilio_number, 'body' => $message] );
                break;
            case 'mail':
                $sent = $this->sendMessage($request->message, $request->recipients, $request->type);
                break;
            case 'voice':
                break;
        }
    }

    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message Body of sms
     * @param Number $recipients string or array of phone number of recepient
     */
    private function replyMessage(Request $request, $message, $recipients)
    {
        $response = new MessagingResponse();
        switch ($request->type){
            case 'sms':
                header("content-type: text/xml");
                break;
            default:
                break;
        }
        $response->message( $message );
    }

    private function callCustomer($recipient = null){
        $response = new Twilio\TwiML\VoiceResponse();
        $response->say('Hello');
        $response->play('https://api.twilio.com/cowbell.mp3', ['loop' => 5]);
        print $response;
    }

}