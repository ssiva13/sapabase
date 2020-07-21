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
        $updatesms = TwilioSmsLogs::smsRefresh($request->user()->customer->id, $request->user()->customer->user_id);
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
        $customer_id = $request->user()->customer->id;
        $user_id = $request->user()->customer->user_id;
        $new_sms = TwilioSmsLogs::smsRefresh($customer_id, $user_id);
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