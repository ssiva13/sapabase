<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\SmsTemplate;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Acelle\Model\Template;

class SmsTemplateController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return Factory|Application|Response|View
     */
    public function index(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $templates = SmsTemplate::search($request);

        return view('sms_templates.index', [
            'templates' => $templates,
            'type' => $request->type,
        ]);
    }

    /**
     * Display a listing of the resource.
     * @return Factory|Application|Response|View
     */
    public function listing(Request $request, $type)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $templates = SmsTemplate::search($request)->paginate($request->per_page);

        return view('sms_templates._list', [
            'templates' => $templates,
            'type' => $type,
        ]);
    }

    /**
     * Display a listing of the resource for choose one.
     *
     * @return Response
     */
    public function choosing(Request $request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $templates = SmsTemplate::search($request)->paginate($request->per_page);
        $campaign = \Acelle\Model\Campaign::findByUid($request->campaign_uid);

        return view('sms_templates._list_choose', [
            'templates' => $templates,
            'campaign' => $campaign,
        ]);
    }

    /**
     * Content of template.
     * @param Request $request
     * @return Response
     */
    public function content(Request $request)
    {
        $template = SmsTemplate::findByUid($request->uid);

        // authorize
        if (!$request->user()->customer->can('view', $template)) {
            return $this->notAuthorized();
        }

        echo $template->content;
    }

    /**
     * Show the form for creating a new resource.
     * @return Factory|Application|Response|View
     */
    public function create(Request $request)
    {
        // Generate info
        $user = $request->user();
        $template = new Template();
        if($request->call){
            $template->type = 'call';
        }

        // authorize
        if (!$request->user()->customer->can('create', Template::class)) {
            return $this->notAuthorized();
        }

        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        return view('sms_templates.create', [
            'template' => $template,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|Response
     */
    public function store(Request $request)
    {
        // Generate info
        $user = $request->user();
        $customer = $request->user()->customer;

        $template = new SmsTemplate();
        $template->customer_id = $customer->id;
        $template->admin_id = $customer->admin_id;

        // validate and save posted data
        if ($request->isMethod('post')) {
            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            $this->validate($request, $rules);

            // Save template
            $template->fill($request->all());
            if ($request->hasFile('content')) {
                $audio_file = $request->file('content');
                $filename = $audio_file->getClientOriginalName();
                $location = public_path('files/audio');
                $path = 'files/audio/'.$filename;
                $audio_file->move($location,$filename);
                $template->content = \URL::asset($path);
                $template->type = 'call';
            }

            $template->save();

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.template.created'));

        }
        return redirect()->action('SmsTemplateController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Factory|Application|Response|View
     */
    public function edit(Request $request, $uid)
    {
        // Generate info
        $user = $request->user();
        $template = SmsTemplate::findByUid($uid);
        // Get old post values
        if (null !== $request->old()) {
            $template->fill($request->old());
        }

        return view('sms_templates.edit', [
            'template' => $template,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|Response
     */
    public function update(Request $request)
    {
        // Generate info
        $user = $request->user();
        $template = SmsTemplate::findByUid($request->uid);

        // validate and save posted data
        if ($request->isMethod('patch') || $request->isMethod('post')) {
            // Save template
            $template->fill($request->all());

            $rules = array(
                'name' => 'required',
                'content' => 'required',
            );

            // make validator
            $validator = \Validator::make($request->all(), $rules);
            
            // redirect if fails
            if ($validator->fails()) {
                // faled
                return response()->json($validator->errors(), 400);
            }
            if ($request->hasFile('content')) {
                $audio_file = $request->file('content');
                $filename = $audio_file->getClientOriginalName();
                $location = public_path('files/audio');
                $path = 'files/audio/'.$filename;
                $audio_file->move($location,$filename);
                $template->content = \URL::asset($path);
            }
            $template->save();

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.template.updated'));

        }
        return redirect()->action('SmsTemplateController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
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
            $item = SmsTemplate::findByUid($row[0]);

            // authorize
            if (!$request->user()->customer->can('update', $item)) {
                return $this->notAuthorized();
            }

            $item->custom_order = $row[1];
            $item->untransform();
            $item->save();
        }

        echo trans('messages.templates.custom_order.updated');
    }

    /**
     * Remove the specified resource from storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|Response
     */
    public function delete(Request $request)
    {
        if (isSiteDemo()) {
            return response()->json([
                'status' => 'notice',
                'message' => trans('messages.operation_not_allowed_in_demo'),
            ]);
        }

        $items = SmsTemplate::whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            // authorize
            if ($request->user()->customer->can('delete', $item)) {
                $item->delete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.templates.deleted');
    }

    public function get(Request $request)
    {
        $template = SmsTemplate::where('uid',$request->template_uid)->first();
        return $template->content;
    }
}
