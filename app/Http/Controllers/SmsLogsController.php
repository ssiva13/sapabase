<?php


namespace Acelle\Http\Controllers;


use Acelle\Model\TwilioSmsLogs;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsLogsController extends Controller
{
    /**
     * SmsLogsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return Factory|Application|View
     */
    public function index(Request $request)
    {

        $request->merge(array("customer_id" => $request->user()->customer->id));
        $sms_logs = TwilioSmsLogs::search($request);

        return view('logs_sms.index', [
            'sms_logs' => $sms_logs,
        ]);
    }

    /**
     * @param Request $request
     * @return Factory|Application|View
     */
    public function listing(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $sms_logs = TwilioSmsLogs::search($request)->paginate($request->per_page);

        return view('logs_sms._list', [
            'sms_logs' => $sms_logs,
        ]);
    }

    /**
     * Custom sort items.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Response
     */
    public function sort(Request $request)
    {
        $sort = json_decode($request->sort);
        foreach ($sort as $row) {
            $item = TwilioSmsLogs::findByUid($row[0]);

            $item->custom_order = $row[1];
            $item->untransform();
            $item->save();
        }

        echo trans('messages.templates.custom_order.updated');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function refresh(Request $request){
        $twilio = new TwilioController();
        $customer_id = $request->user()->customer->id;
        $new_sms = 0;
        $sms_log = array();
        foreach ($twilio->connectTwilio()->account->messages->read() as $key => $sms) {
            if(TwilioSmsLogs::findBySid($sms->sid) === false){
                $twiliosms = new TwilioSmsLogs();
                $twiliosms->customer_id = $customer_id;
                $twiliosms->sid = $sms->sid;
                $twiliosms->from = $sms->from;
                $twiliosms->to = $sms->to;
                $twiliosms->price = ($sms->price) ? $sms->price : 0;
                $twiliosms->price_unit = $sms->priceUnit;
                $twiliosms->body = $sms->body;
                $twiliosms->direction = $sms->direction;
                $twiliosms->date_sent = $sms->dateSent->format("Y-m-d H:i:s");
                $twiliosms->status = $sms->status;
                $twiliosms->save();
                ++ $new_sms;

                $sms_log[$key] = $twiliosms;
            }
        }

        if($new_sms > 0){
            $request->session()->flash('alert-success', trans('messages.list.updated'));
        }else{
            $request->session()->flash('alert-success', trans('messages.list.already_updated'));
        }
        return redirect()->action('SmsLogsController@index');
    }

    public function log(Request $request, $phone){
        $sms_log = TwilioSmsLogs::where('to', $phone)->orWhere('from', $phone)->get();

        return view('leads.sms_logs', [
            'phone' => $phone,
            'sms_log' => $sms_log,
        ]);
    }

}