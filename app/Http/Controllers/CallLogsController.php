<?php


namespace Acelle\Http\Controllers;


use Acelle\Model\TwilioCallLogs;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CallLogsController extends Controller
{
    /**
     * CustomerLogsController constructor.
     */
    public function  __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return Factory|Application|View
     */
    public function index(Request $request)
    {
        TwilioCallLogs::callRefresh($request->user()->customer->id, $request->user()->customer->user_id);
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $call_logs = TwilioCallLogs::search($request);

        return view('logs_call.index', [
            'call_logs' => $call_logs,
        ]);
    }

    /**
     * @param Request $request
     * @return Factory|Application|View
     */
    public function listing(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $call_logs = TwilioCallLogs::search($request)->paginate($request->per_page);

        return view('logs_call._list', [
            'call_logs' => $call_logs,
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
            $item = TwilioCallLogs::findByUid($row[0]);

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
        $new_calls = TwilioCallLogs::callRefresh($customer_id, $user_id);
        if($new_calls > 0){
            $request->session()->flash('alert-success', trans('messages.list.updated'));
        }else{
            $request->session()->flash('alert-success', trans('messages.list.already_updated'));
        }
        return redirect()->action('CallLogsController@index');
    }

    public function log(Request $request, $phone){
        $call_log = TwilioCallLogs::where('to', $phone)->orWhere('from', $phone)->get();

        return view('leads.call_logs', [
            'phone' => $phone,
            'call_log' => $call_log,
        ]);
    }
}