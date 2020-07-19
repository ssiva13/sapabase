<?php


namespace Acelle\Http\Controllers;


use Acelle\Model\Automation2;
use Acelle\Model\Setting;
use Acelle\Model\SmsTemplate;
use Acelle\Model\Subscriber;
use Acelle\Model\TwilioCallLogs;
use Acelle\Model\TwilioMessage;
use Acelle\Model\TwilioNumber;
use Acelle\Model\TwilioSmsLogs;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client as Twilio;
use Twilio\TwiML\Messaging\Message;
use Twilio\TwiML\MessagingResponse;
use Twilio\TwiML\VoiceResponse;

class TwilioController extends Controller
{
//    private static  $charges;
//    private  $twilio;
//    private  $call_url;
//    private  $fax_url;
//    private  $twilio_account_sid;
//    private  $twilio_auth_token;
//    private  $twilio_application_sid;
//    private  $purchase_charge;

     private static array $charges;
     private Twilio $twilio;
     private array $call_url;
     private string $fax_url;
     private string $twilio_account_sid;
     private string $twilio_auth_token;
     private string $twilio_application_sid;
     private string $purchase_charge;

    /**
     * TwilioIntegrationController constructor.
     */
    public function __construct()
    {

        $this->twilio_account_sid = Setting::get('twilio_account_sid');
        $this->twilio_auth_token = Setting::get('twilio_auth_token');
        $this->twilio_application_sid = Setting::get('twilio_application_sid');
        $this->purchase_charge = Setting::get('purchase_charge');

        try {
            $this->twilio = new Twilio($this->twilio_account_sid, $this->twilio_auth_token);
        } catch (ConfigurationException $e) {
        }

        parent::__construct();
    }

    /**
     * Connect to Twilio API
     */
    public function connectTwilio(){
        return $this->twilio;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
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
     * @param Request $request
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
     * @param Request $request
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
                $twilio_number->charges = $this->purchase_charge;
                $twilio_number->user_id = $customer->user_id;
                $twilio_number->admin_id = $customer->admin_id;
                if ($twilio_number->save()) {
                    $request->session()->flash('alert-success', trans('messages.twilio.purchased'));
                }else{
                    $request->session()->flash('alert-danger', trans('messages.twilio.not_purchased_not_stored'));
                }
            }else{
                $request->session()->flash('alert-danger', trans('messages.twilio.not_purchased'));
            }
        }
        return redirect()->action('TwilioController@index');
    }

    /**
     * Show the form for editing the specified resource.
     * @param Request $request
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
                $request->session()->flash('alert-success', trans('messages.fields.updated'));
            }else{
                $request->session()->flash('alert-danger', trans('messages.failed'));
            }
        }
        return redirect()->action('TwilioController@index');
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function deactivate(Request $request)
    {
        $twilio_number = TwilioNumber::where('id', $request->id)->first();
        $twilio_number->status = TwilioNumber::STATUS_INACTIVE;
        if ($twilio_number->save()) {
            // Redirect to my numbers page
            $request->session()->flash('alert-success', trans('messages.twilio.deactivated'));
        }else{
            $request->session()->flash('alert-success', trans('messages.failed'));
        }
        return redirect()->action('TwilioController@index');
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function activate(Request $request)
    {
        $twilio_number = TwilioNumber::where('id', $request->id)->first();
        $twilio_number->status = TwilioNumber::STATUS_ACTIVE;
        if ($twilio_number->save()) {
            // Redirect to my numbers page
            $request->session()->flash('alert-success', trans('messages.twilio.deactivated'));
        }
        return redirect()->action('TwilioController@index');
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

    /**
     * Get Country Specific Phone Numbers
     * @param Request $request
     * @return array|false|string
     */
    public function searchCountryResource(Request $request){
        $country =  $request->country;
        try {
            if($request->number_type){
                $number_type = $request->number_type;
                if($request->state){
                    $availablePhoneNumbers = $this->twilio->availablePhoneNumbers($country)->$number_type->read(["inRegion" => $request->state]);
                }else{
                    $availablePhoneNumbers = $this->twilio->availablePhoneNumbers($country)->$number_type->read();
                }
                $localPhoneNumbers = [];
                $response = [];
                foreach ($availablePhoneNumbers as $record) {

                    $localPhoneNumbers[$record->friendlyName] = array(
                        'number' => $record->phoneNumber,
                        'capabilities' => $record->capabilities
                    );
                    $capabality = $this->filterNumberCapabilities(
                        $localPhoneNumbers[$record->friendlyName]['number'],
                        $localPhoneNumbers[$record->friendlyName]['capabilities'],
                        $request->sms_enabled,
                        $request->mms_enabled,
                        $request->call_enabled,
                        $request->fax_enabled
                    );
                    if($capabality){
                        $response[$record->friendlyName] = ($record->phoneNumber);
                    }
                }

            }else {
                if ($request->state) {
                    $availablePhoneNumbers = $this->twilio->availablePhoneNumbers($country)->local->read(["inRegion" => $request->state]);
                }else{
                    $availablePhoneNumbers = $this->twilio->availablePhoneNumbers($country)->fetch();
                }
                $response = array(
                    'code' => 200,
                    'body' => $availablePhoneNumbers->uri
                );
            }

            return $response;

        } catch (TwilioException $e) {
            $request->session()->flash('alert-danger', $e->getMessage());
            return $e->getCode();
        }
    }

    /**
     * get numbers in country resource
     * @param Request $request
     * @return array|int|mixed
     */
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
                $numbers[$key] = $this->callTwilioApi($obj);
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
    public function callTwilioApi($uri){
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

    /**
     * Function to fetch all call logs associated with number
     * @param Request $request
     * @param $uid
     * @return Factory|Application|View
     */
    public function fetchCallLog(Request $request, $uid){
        $twilioNumber = TwilioNumber::findByUid($uid);
        $sms_log = TwilioSmsLogs::where('from', $twilioNumber->number)->get();
        $call_log = TwilioCallLogs::where('from', $twilioNumber->number)->get();

        return view('twilio_numbers.twilio_logs', [
            'call_log' => $call_log,
            'sms_log' => $sms_log,
        ]);
    }

    /**
     * @param $recipient
     * @param $message
     * @return string|VoiceResponse|null
     * @throws TwilioException
     */
    public function sendMessage($recipient, $message){
        $sid = null;
        switch ($message->type){
            case 'sms':
                $response = $this->twilio->messages->create(
                    $recipient->phone,
                    [
                        'from' => $message->from,
                        'body' => $message->message,
                        'statusCallbackEvent' => 'initiated ringing answered completed',
                        'statusCallback' => 'https://app.markitbase.com/voice/status',
                        'statusCallbackMethod' => 'GET',
                    ] );
                $sid = $response->sid;
                break;

            case 'call':
                $response = $this->twilio->calls->create(
                    $recipient->phone,
                    $message->from,
                    [
                        "twiml" => "<Response><Play> $message->message </Play></Response>",
                        'statusCallbackEvent' => 'initiated ringing answered completed',
                        'statusCallback' => 'https://app.markitbase.com/voice/status',
                        'statusCallbackMethod' => 'GET',
                    ]
                );
                $sid = $response->sid;
                break;
        }

        return $sid;
    }

    /**
     * Function to update sms/call cost
     * @param Request $request
     * @return bool|RedirectResponse|Response
     */
    public function updateTwilioCost(Request $request){
        $smsSum = TwilioSmsLogs::smsSum($request->user()->customer->id);
        $callSum = TwilioCallLogs::callSum($request->user()->customer->id);
        $phoneNumbers = TwilioNumber::where('status', TwilioNumber::STATUS_ACTIVE)->where('user_id', $request->user()->customer->user_id)->count();

        return  (abs($callSum + $smsSum) + ((int)$this->purchase_charge * $phoneNumbers));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $uid
     * @param $cost
     * @return bool|RedirectResponse|Response
     */
    public function setcost()
    {
        $count = 0;
        foreach (self::$charges as $key => $charge){
            $twilio_number = TwilioNumber::findByUid($key);
            $twilio_number->status = TwilioNumber::STATUS_ACTIVE;
            $twilio_number->charges = (string)(abs($charge) + (int)$this->purchase_charge);
            if($twilio_number->save()){
                $count += 1;
            }
        }
        if(count(self::$charges) == $count){
            return true;
        }else{
            return false;
        }

    }

    /**
     * Function that handles the purchase of a mobile number from twilio
     * @param $number
     * @return string|TwilioException
     */
    public function purchaseNumber($number){
        $purchase_number = $this->twilio->incomingPhoneNumbers->create(
            [
                "phoneNumber" => $number,
            ]);
        return $purchase_number->sid;
    }

    /**
     * filter number by capabilities
     * @param $number
     * @param $capabilities
     * @param $sms
     * @param $mms
     * @param $call
     * @param $fax
     * @return bool
     */
    private function filterNumberCapabilities($number, $capabilities, $sms, $mms, $call,$fax){
        $phoneNumber = ($call && $capabilities['voice']) || ($mms  && $capabilities['MMS']) || ($fax  && $capabilities['fax']) || ($sms && $capabilities['SMS']);
        return $phoneNumber ? true : false;
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @param $phone
     * @return Factory|Application|Response|View
     */
    public function createRequest(Request $request, $phone)
    {
        $twiliomsg = new TwilioMessage();
        $customer = $request->user()->customer;
        $numbers = $customer->getPhoneNumberSelectOptions($customer->user_id);

        return view('leads.send_sms', [
            'twiliomsg' => $twiliomsg,
            'numbers' => $numbers,
            'phone' => $phone,
            'type' => $request->type,
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function processRequest(Request $request){
        try {
            return [
                'message' => $this->sendMessage($request, $request),
                'type' => $request->type,
                'code' => 200,
                'phone' => $request->phone,
                'from' => $request->from,
            ];
        } catch (TwilioException $e) {
            return [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'type' => $request->type,
                'phone' => $request->phone,
                'from' => $request->from,
            ];
        }
    }

    /**
     * @param Request $request
     * @return string
     * @throws TwilioException
     */
    public function callStatus(Request $request){
        $call = $this->twilio->calls($request->message)->fetch();
        return $call->status;
    }

    public function callAnswer(Request $request){

        $template = SmsTemplate::getDefault($request->user()->customer->id);
        $city = $_REQUEST['FromCity'] ?? 'New York';
        $response = new VoiceResponse();
        $response->say("Hello, {$city}!", array('voice' => 'alice'));
        $response->play(
            $template->content,
            [
                'loop' => 1
            ]
        );

        echo $response;


    }

    /**
     * @param Request $request
     * @return Factory|Application|View
     */
    public function processedRequest(Request $request){

        return view('leads.processing', [
            'phone' => $request->phone,
            'type' => $request->type,
            'message' => $request->message,
            'from' => $request->from,
            'code' => $request->code,
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getStates(Request $request){
        if (isset($request->country)) {
            return Setting::states($request->country);
        }
        return Setting::states('US');
    }
}