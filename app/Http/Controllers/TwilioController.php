<?php


namespace Acelle\Http\Controllers;


use Acelle\Model\Automation2;
use Acelle\Model\Setting;
use Acelle\Model\Subscriber;
use Acelle\Model\TwilioMessage;
use Acelle\Model\TwilioNumber;
use Carbon\Carbon;
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
                $twilio_number->charges = $this->purchase_charge;
                $twilio_number->user_id = $customer->user_id;
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
                $availablePhoneNumbers = $this->twilio->availablePhoneNumbers($country)->$number_type->read();
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

            }else{
                $availablePhoneNumbers = $this->twilio->availablePhoneNumbers($country)->fetch();
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
     * @param string $starttime
     * @param string $endtime
     * @return Factory|Application|View
     */
    public function fetchCallLog(Request $request, $uid){
        $twilioNumber = TwilioNumber::findByUid($uid);
        $call_log = array();
        $sms_log = array();
        foreach ($this->twilio->account->calls->read() as $key => $call) {
            $time = $call->startTime->format("Y-m-d H:i:s");
            if($call->from == $twilioNumber->number){
                $call_log[$key] = array(
                    'from' => $call->from,
                    'to' => $call->to,
                    'duration' => $call->duration,
                    'price' => $call->price ? $call->price : 'N/A',
                    'status' => $call->status,
                    'direction' => $call->direction,
                    'time' => $time,
                );
            }
        }
        foreach ($this->twilio->account->messages->read() as $key => $sms) {
            if($sms->from == $twilioNumber->number){
                $sms_log[$key] = array(
                    'from' => $sms->from,
                    'to' => $sms->to,
                    'price' => $sms->price ? $sms->price : 'N/A',
                    'status' => $sms->status,
                    'direction' => $sms->direction,
                );
            }
        }

        return view('twilio_numbers.call_logs', [
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

                    ] );
                $sid = $response->sid;
                break;

            case 'call':
                $response = $this->twilio->calls->create(
                    $recipient->phone,
                    $message->from,
                    [
                        'url' => $message->message
                    ]
                );
                $sid = $response->sid;
                break;

            case 'fax':
                $response = new VoiceResponse();
                $response->play(
                    $message->message,
                    [
                        'loop' => 1
                    ]
                );
                $sid = $response;
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
        $phonenumbers = $request->user()->phoneNumbers;
        foreach ($phonenumbers as $key => $twilioNumber){
            $call_cost = 0;
            $sms_cost = 0;
            foreach ($this->twilio->account->calls->read() as $keyy => $call) {
                if($call->from == $twilioNumber->number){
                    $call_cost += $call->price;
                }
            }
            foreach ($this->twilio->account->messages->read() as $keyy => $sms) {
                if($sms->from == $twilioNumber->number){
                    $sms_cost += $sms->price;
                }
            }
            $charges = $sms_cost + $call_cost;
            self::$charges[$twilioNumber->uid] = $charges;
        }
         if($this->setcost()){
             $request->session()->flash('alert-success', trans('messages.update'));
             return redirect()->back();
         }else{
             $request->session()->flash('alert-danger', trans('messages.failed'));
             return redirect()->back();
         }

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
        $number = '+15005550006';
        $twilio = new Twilio('ACfdf30451329bf8936ee07edeb909d7b0', '3662bd5d9d6fcd58b49e25d4f31550fd');
        $purchase_number = $twilio->incomingPhoneNumbers->create(
        //$purchase_number = $this->twilio->incomingPhoneNumbers->create(
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
     */
    private function filterNumberCapabilities($number, $capabilities, $sms, $mms, $call,$fax){
        $phoneNumber = ($call && $capabilities['voice']) || ($mms  && $capabilities['MMS']) || ($fax  && $capabilities['fax']) || ($sms && $capabilities['SMS']);
        return $phoneNumber ? true : false;
    }
}