<?php


namespace Acelle\Http\Controllers;


use Acelle\Model\MailList;
use Acelle\Model\Subscriber;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
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
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $lead_data = Subscriber::where('customer_id', $request->customer_id)->get();

        return view('leads.index', [
            'lead_data' => $lead_data,
        ]);
    }

    /**
     * @param Request $request
     * @return Factory|Application|View
     */
    public function listing(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));

        $lead_data = Subscriber::search($request)->paginate($request->per_page);

        return view('leads._list', [
            'lead_data' => $lead_data,
        ]);
    }
}