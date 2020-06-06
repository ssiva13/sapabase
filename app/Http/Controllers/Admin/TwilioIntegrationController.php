<?php


namespace Acelle\Http\Controllers\Admin;


use Acelle\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Twilio\TwiML\Messaging\Message;
use Twilio\TwiML\MessagingResponse;
use Twilio\TwiML\VoiceResponse;

class TwilioIntegrationController extends Controller
{
    private array $config;
    private string $account_sid;
    private string $auth_token;
    private Client $client;
    private string $twilio_number;
    private array $call_url;
    private string $fax_url;

    /**
     * TwilioIntegrationController constructor.
     */
    public function __construct()
    {
//        $this->config = config('twilio.credentials');
//        $this->account_sid = $this->config['twilio_sid'];
//        $this->auth_token = $this->config['twilio_auth_token'];
//        $this->twilio_number = $this->config['twilio_number'];
//
//        try {
//            $this->client = new Client($this->account_sid, $this->auth_token);
//        } catch (ConfigurationException $e) {
//        }

        parent::__construct();
    }

    public function index(Request $request)
    {
        if (!$request->user()->admin->can('read', new \Acelle\Model\SendingServer())) {
            return $this->notAuthorized();
        }

        // If admin can view all sending domains
        if (!$request->user()->admin->can("readAll", new \Acelle\Model\SendingServer())) {
            $request->merge(array("admin_id" => $request->user()->admin->id));
        }

        // exlude customer seding servers
        $request->merge(array("no_customer" => true));

        $items = \Acelle\Model\SendingServer::search($request);

        return view('admin.sending_servers.index', [
            'items' => $items,
        ]);
    }

    /**
     * automated sms sending function
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function automatedNotification(Request $request)
    {
        $response = null;
        try{
            switch ($request->type){
                case 'sms':
                    $response = $this->sendMessage($request->message, $request->recipients);
                    break;
                case 'mail':
                    $response = $this->sendMessage($request->message, $request->recipients, 'mail');
                    break;
                case 'voice':
                    $response = $this->voiceCallCustomer($request->recipients);
                    break;
            }
            return $response;

        }catch (Exception $exception){
            throw new Exception('Error =>  '.$exception->getMessage());
        }
    }

    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message   Body of sms
     * @param array $recipients string or array of phone number of recepient
     * @param string $type      Type of message
     * @return bool|mixed
     * @throws TwilioException
     */
    private function sendMessage($message, array $recipients, $type = 'sms')
    {
        $response = null;
        $messages = [];
        foreach ($recipients as $recipient){
            switch ($type){
                case 'sms':
                    $response = $this->client->messages->create($recipient,
                        [
                            'from' => $this->twilio_number,
                            'body' => 'Welcome Test Message'
                        ] );
                    $messages[$recipient] = $response->sid;
                    break;
                case 'mail': // replace with mail
                    $response = $this->sendMessage($message, $recipient, $type);
                    break;
            }
        }
        return $response;
    }

    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param string $type
     * @param string $message
     * @return Message
     */
    private function replyMessage($type, $message)
    {
        $response = new MessagingResponse();
        switch ($type){
            case 'sms':
                header("content-type: text/xml");
                break;
            default:
                break;
        }

        return $response->message( $message );
    }

    /**
     * Send Fax Response from set/request
     * @param String $message Body of sms
     * @return VoiceResponse
     */
    private function faxResponseCustomer($message, $count = 2){
        $response = new VoiceResponse();
        $response->say($message);
        $response->play(
            $this->fax_url,
            [
                'loop' => $count
            ]
            // 'https://api.twilio.com/cowbell.mp3',
        );
        return $response;
    }

    /**
     * Function that handles the purchase of a mobile number from twilio
     * @param Request $request
     * @return string|TwilioException
     */
    public function purchaseNumber(Request $request){
        try {
            $purchase_number = $this->client->incomingPhoneNumbers->create(
                [
                    "phoneNumber" => "+15005550006",
                    "voiceUrl" => "http://demo.twilio.com/docs/voice.xml" // get set value from env
                ]);
            return $purchase_number->sid;

        }catch (TwilioException $exception){
            return new TwilioException('Error '.$exception->getMessage());
        }

    }

    /**
     * Function to call Customer (call recipient)
     * @param string $recipient
     * @return string|TwilioException
     */
    private function voiceCallCustomer($recipient){

        try {
            $call = $this->client->calls->create(
                $recipient, // to
                $this->twilio_number, //from
                [
                    'url' => $this->call_url
                ]
                // ["url" => "http://demo.twilio.com/docs/voice.xml"]

            );
            return $call->sid;
        } catch (TwilioException $exception) {
            return new TwilioException('Response => '. $exception->getMessage());
        }

    }

    /**
     * Function to answer call with set message
     * @param string $message
     * @param array $voice
     * @return VoiceResponse
     */
    private function autoAnswerCall($message, array $voice){
        $response = new VoiceResponse;
        $response->say(
            $message,
            $voice
        );

        return $response;
    }

    /**
     * Function to fetch all call logs associated with number
     * @param string $starttime
     * @param string $endtime
     * @return array
     */
    private function fetchCallLog($starttime = null, $endtime = null){
        $call_log = array();
        if (!empty($this->client->account)) {
            foreach ($this->client->account->calls->read() as $key => $call) {
                $time = $call->startTime->format("Y-m-d H:i:s");
                $call_log[$key] = array(
                    'from' => $call->from,
                    'to' => $call->to,
                    'duration' => $call->duration,
                    'time' => $time
                );
            }
        }
        return $call_log;
    }

}