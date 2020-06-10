<?php


namespace Acelle\Http\Controllers;


use Acelle\Model\Setting;
use Acelle\Model\TwilioNumber;
use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client as Twilio;
use Twilio\TwiML\Messaging\Message;
use Twilio\TwiML\MessagingResponse;
use Twilio\TwiML\VoiceResponse;

class TwilioController extends Controller
{
    private Twilio $twilio;
    private array $call_url;
    private string $fax_url;
    private string $twilio_account_sid;
    private string $twilio_auth_token;
    private string $twilio_application_sid;

    /**
     * TwilioIntegrationController constructor.
     */
    public function __construct()
    {

        $this->twilio_account_sid = Setting::get('twilio_account_sid');
        $this->twilio_auth_token = Setting::get('twilio_auth_token');
        $this->twilio_application_sid = Setting::get('twilio_application_sid');

        try {
            $this->twilio = new Twilio($this->twilio_account_sid, $this->twilio_auth_token);
        } catch (ConfigurationException $e) {
        }

        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|Application|Response|View
     */
    public function index(Request $request)
    {

        $request->merge(array("customer_id" => $request->user()->customer->id));
        $twilio_numbers = TwilioNumber::search($request);

        return view('twilio_numbers.index', [
            'twilio_numbers' => $twilio_numbers,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|Application|Response|View
     */
    public function listing(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $twilio_numbers = TwilioNumber::search($request)->paginate($request->per_page);

        return view('twilio_numbers._list', [
            'twilio_numbers' => $twilio_numbers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|Application|Response|View
     */
    public function create(Request $request)
    {
        $twilio_number = new TwilioNumber();

        return view('twilio_numbers.create', [
            'twilio_number' => $twilio_number,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function store(Request $request)
    {
        $customer = $request->user()->customer;
        $twilio_number = new TwilioNumber();
        // save posted data
        if ($request->isMethod('post')) {
            $this->validate($request, TwilioNumber::$rules);
            // Save current user info
            $twilio_number->fill($request->all());
            if($this->purchaseNumber($twilio_number->number)){
                $twilio_number->user_id = $customer->user_id;
                $twilio_number->uid = $customer->uid;
                $twilio_number->admin_id = $customer->admin_id;
                if ($twilio_number->save()) {
                    $request->session()->flash('alert-success', trans('messages.twilio.purchased'));
                    return redirect()->action('TwilioController@index');
                }else{
                    $request->session()->flash('alert-danger', trans('messages.twilio.not_purchased_not_stored'));
                    return redirect()->action('TwilioController@create');
                }
            }else{
                $request->session()->flash('alert-danger', trans('messages.twilio.not_purchased'));
                return redirect()->action('TwilioController@index');
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Factory|Application|Response|View
     */
    public function edit(Request $request, $id)
    {
        $twilio_number = TwilioNumber::findByUid($id);

        $twilio_number->fill($request->old());

        return view('twilio_numbers.edit', [
            'twilio_number' => $twilio_number,
        ]);
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int                      $id
     * @return RedirectResponse|Response
     */
    public function update(Request $request, $id)
    {
        // Get current user
        $current_user = $request->user();
        $twilio_number = TwilioNumber::findByUid($id);

        // save posted data
        if ($request->isMethod('patch')) {
            $this->validate($request, TwilioNumber::$rules);

            // Save current user info
            $twilio_number->fill($request->all());

            if ($twilio_number->save()) {
                $request->session()->flash('alert-success', trans('messages.twilio.updated'));
                return redirect()->action('TwilioController@index');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function deactivate(Request $request)
    {
        $twilio_number = TwilioNumber::where('id', $request->id)->first();
        $twilio_number->status = TwilioNumber::STATUS_INACTIVE;
        if ($twilio_number->save()) {
            // Redirect to my numbers page
            $request->session()->flash('alert-success', trans('messages.twilio.deactivated'));
            return redirect()->action('TwilioController@index');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function activate(Request $request)
    {
        $twilio_number = TwilioNumber::where('id', $request->id)->first();
        $twilio_number->status = TwilioNumber::STATUS_ACTIVE;
        if ($twilio_number->save()) {
            // Redirect to my numbers page
            $request->session()->flash('alert-success', trans('messages.twilio.deactivated'));
            return redirect()->action('TwilioController@index');
        }
    }


    /**
     * Delete confirm message.
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function deactivateConfirm(Request $request)
    {
        $twilio_numbers = TwilioNumber::whereIn('uid', explode(',', $request->uids))->where('status', 'active');

        foreach ($twilio_numbers->get() as $twilio_number) {
            $twilio_number->status = TwilioNumber::STATUS_INACTIVE;
            $twilio_number->save();
        }
        $request->session()->flash('alert-success', trans('messages.twilio.all_deactivated'));
        return redirect()->action('TwilioController@index');
    }

    public function connectTwilio(){
        return $this->twilio;
    }
    /**
     * Get All Phone Numbers
     * */
    public function searchPhoneNumbers(){

        $availablePhoneNumbers = $this->twilio->availablePhoneNumbers->read();
        foreach ($availablePhoneNumbers as $record) {
            print($record->uri).PHP_EOL;
        }
        return 13;
    }

    /**
     * Get Country Specific Phone Numbers
     * @param Request $request
     * @return array|false|string
     */
    public function searchCountryResource(Request $request){
        $country =  $request->country;
        try {
            $availablePhoneNumbers = $this->twilio->availablePhoneNumbers($country)->fetch();
            $response = array(
                'code' => 200,
                'body' => $availablePhoneNumbers->uri
            );
        } catch (TwilioException $e) {
            $response = array(
                'code' => $e->getCode(),
                'body' => $e->getMessage()
            );
        }
        if($response['code'] == 200){
            return $response;
        }else{
            $request->session()->flash('alert-danger', trans('messages.twilio.no_numbers'));
            return redirect()->action('TwilioController@index');
        }
    }

    public function readCountryResources(Request $request){
        $uri =  $request->uri;
        $guzzle = new Guzzle();
        try {
            $guzzle_response = $guzzle->request('GET', $uri, [
                'auth' => [
                    $this->twilio_account_sid,
                    $this->twilio_auth_token
                ],
                array('stream' => true)
            ]);

            $guzzle_response = $guzzle_response->getBody()->getContents();
            $stream = json_decode($guzzle_response);
            $response = ($stream->subresource_uris);

            $numbers = array();
            foreach ($response as $key => $obj) {
                $numbers[$key] = $this->readCountryResource($obj);
            }
            $response = $numbers;
        } catch (GuzzleException $e) {
            $response = $e->getCode();
        }
        return $response;
    }

    /**
     * @param $uri
     * @return int|mixed|string
     */
    public function readCountryResource($uri){
        $uri =  'https://api.twilio.com'.$uri;
        $guzzle = new Guzzle();
        try {
            $guzzle_response = $guzzle->request('GET', $uri, [
                'auth' => [
                    $this->twilio_account_sid,
                    $this->twilio_auth_token
                ]
            ]);
            $response = $guzzle_response->getBody()->getContents();
        } catch (GuzzleException $e) {
            $response = $e->getCode();
        }
        return $response;
    }
    public function statitics(Request $request, $number){
        return 12;
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
                    $response = $this->twilio->messages->create($recipient,
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
     * @param $number
     * @return string|TwilioException
     */
    public function purchaseNumber($number){
        try {
            $number = '+15005550006';
//            $purchase_number = $this->twilio->incomingPhoneNumbers->create(
            $twilio = new Twilio('ACfdf30451329bf8936ee07edeb909d7b0', '3662bd5d9d6fcd58b49e25d4f31550fd');
            $purchase_number = $twilio->incomingPhoneNumbers->create(
                [
                    "phoneNumber" => $number,
                    "voiceUrl" => "http://demo.twilio.com/docs/voice.xml" // get set value from env
                ]);
            return $purchase_number->sid;

        }catch (TwilioException $exception){
             throw $exception->getCode();
        }
    }

    /**
     * Function to call Customer (call recipient)
     * @param string $recipient
     * @return string|TwilioException
     */
    private function voiceCallCustomer($recipient){

        try {
            $call = $this->twilio->calls->create(
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
        if (!empty($this->twilio->account)) {
            foreach ($this->twilio->account->calls->read() as $key => $call) {
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