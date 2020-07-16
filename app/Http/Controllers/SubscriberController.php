<?php

namespace Acelle\Http\Controllers;

use Acelle\Events\MailListUpdated;
use Acelle\Helpers\ImportSubscribersHelper;
use Acelle\Jobs\ExportSubscribersJob;
use Acelle\Jobs\ImportSubscribersJob;
use Acelle\Jobs\SendConfirmationEmailJob;
use Exception;
use Gate;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Acelle\Model\Subscriber;
use Acelle\Model\EmailVerificationServer;
use Acelle\Library\Log as MailLog;
use Acelle\Model\MailList;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Image;
use function optimized_paginate;

class SubscriberController extends Controller
{

    /**
     * Search items.
     */
    public function search($list, $request)
    {
        $subscribers = Subscriber::search($request)
            ->where('mail_list_id', '=', $list->id);

        return $subscribers;
    }

    /**
     * Display a listing of the resource.
     * @return Factory|Application|Response|View
     */
    public function index(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        return view('subscribers.index', [
            'list' => $list
        ]);
    }

    /**
     * Display a listing of the resource.
     * @return Factory|Application|Response|View
     */
    public function listing(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (Gate::denies('read', $list)) {
            return;
        }

        $subscribers = $this->search($list, $request);
        $total = distinctCount($subscribers);
        $subscribers->with(['mailList', 'subscriberFields']);
        $subscribers = optimized_paginate($subscribers, $request->per_page, null, null, null, $total);

        $fields = $list->getFields->whereIn('uid', explode(',', $request->columns));

        return view('subscribers._list', [
            'subscribers' => $subscribers,
            'total' => $total,
            'list' => $list,
            'fields' => $fields,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $subscriber = new Subscriber();
        $subscriber->mail_list_id = $list->id;

        // authorize
        if (Gate::denies('create', $subscriber)) {
            return $this->noMoreItem();
        }

        // Get old post values
        $values = [];
        if (null !== $request->old()) {
            foreach ($request->old() as $key => $value) {
                if (is_array($value)) {
                    $values[str_replace('[]', '', $key)] = implode(',', $value);
                } else {
                    $values[$key] = $value;
                }
            }
        }

        return view('subscribers.create', [
            'list' => $list,
            'subscriber' => $subscriber,
            'values' => $values,
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
        $list = MailList::findByUid($request->list_uid);
        $subscriber = new Subscriber();
        $subscriber->customer_id = $customer->id;
        $subscriber->mail_list_id = $list->id;
        $subscriber->status = 'subscribed';

        // authorize
        if (Gate::denies('create', $subscriber)) {
            return $this->noMoreItem();
        }

        // validate and save posted data
        if ($request->isMethod('post')) {
            $this->validate($request, $subscriber->getRules());

            // Save subscriber
            $subscriber->email = $request->EMAIL;
            $subscriber->phone = $request->PHONE;
            $subscriber->save();
            // Update field
            $subscriber->updateFields($request->all());

            // update MailList cache
            event(new MailListUpdated($subscriber->mailList));

            // Log
            $subscriber->log('created', $customer);

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.subscriber.created'));
        }
        return redirect()->action('SubscriberController@index', $list->uid);
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
    public function edit(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $subscriber = Subscriber::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $subscriber)) {
            return $this->notAuthorized();
        }

        // Get old post values
        $values = [];
        foreach ($list->getFields as $key => $field) {
            $values[$field->tag] = $subscriber->getValueByField($field);
        }
        if (null !== $request->old()) {
            foreach ($request->old() as $key => $value) {
                if (is_array($value)) {
                    $values[str_replace('[]', '', $key)] = implode(',', $value);
                } else {
                    $values[$key] = $value;
                }
            }
        }

        return view('subscribers.edit', [
            'list' => $list,
            'subscriber' => $subscriber,
            'values' => $values,
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
        $customer = $request->user()->customer;
        $list = MailList::findByUid($request->list_uid);
        $subscriber = Subscriber::findByUid($request->uid);

        // authorize
        if (Gate::denies('update', $subscriber)) {
            return $this->notAuthorized();
        }

        // validate and save posted data
        if ($request->isMethod('patch')) {
            $this->validate($request, $subscriber->getRules());

            // Upload
            if ($request->hasFile('image')) {
                if ($request->file('image')->isValid()) {
                    // Remove old images
                    $subscriber->uploadImage($request->file('image'));
                }
            }
            // Remove image
            if ($request->_remove_image == 'true') {
                $subscriber->removeImage();
            }

                // Update field
            $subscriber->updateFields($request->all());

            event(new MailListUpdated($subscriber->mailList));

            // Log
            $subscriber->log('updated', $customer);

            // Redirect to my lists page
            $request->session()->flash('alert-success', trans('messages.subscriber.updated'));


        }
        return redirect()->action('SubscriberController@index', $list->uid);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return Response
     */
    public function destroy($id)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function delete(Request $request)
    {
        $customer = $request->user()->customer;
        $subscribers = Subscriber::whereIn('uid', explode(',', $request->uids));
        $list = MailList::findByUid($request->list_uid);

        // Select all items
        if ($request->select_tool == 'all_items') {
            $subscribers = $this->search($list, $request);
        }

        // get related mail lists to update the cached information
        $lists = $subscribers->get()->map(function($e) { return MailList::find($e->mail_list_id); })->unique();

        // actually delete the subscriber
        foreach ($subscribers->get() as $subscriber) {
            // authorize
            if (Gate::allows('delete', $subscriber)) {
                $subscriber->delete();

                // Log
                $subscriber->log('deleted', $customer);
            }
        }

        foreach ($lists as $list) {
            event(new MailListUpdated($list));
        }

        // Redirect to my lists page
        return response()->json([
            "status" => 'success',
            "message" => trans('messages.subscribers.deleted'),
        ]);
    }

    /**
     * Subscribe subscriber.
     * @param Request $request
     * @return void
     */
    public function subscribe(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $customer = $request->user()->customer;

        if ($request->select_tool == 'all_items') {
            $subscribers = $this->search($list, $request);
        } else {
            $subscribers = Subscriber::whereIn('uid', explode(',', $request->uids));
        }

        foreach ($subscribers->get() as $subscriber) {
            // authorize
            if (Gate::allows('subscribe', $subscriber)) {
                $subscriber->status = 'subscribed';
                $subscriber->save();
                // update MailList cache
                event(new MailListUpdated($subscriber->mailList));

                // Log
                $subscriber->log('subscribed', $customer);
            }
        }

        // Redirect to my lists page
        echo trans('messages.subscribers.subscribed');
    }

    /**
     * Unsubscribe subscriber.
     * @param Request $request
     * @return Response
     */
    public function unsubscribe(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);
        $customer = $request->user()->customer;

        if ($request->select_tool == 'all_items') {
            $subscribers = $this->search($list, $request);
        } else {
            $subscribers = Subscriber::whereIn('uid', explode(',', $request->uids));
        }

        foreach ($subscribers->get() as $subscriber) {
            // authorize
            if (Gate::allows('unsubscribe', $subscriber)) {
                $subscriber->status = 'unsubscribed';
                $subscriber->save();

                // Log
                $subscriber->log('unsubscribed', $customer);

                // update MailList cache
                event(new MailListUpdated($subscriber->mailList));
            }
        }

        // Redirect to my lists page
        echo trans('messages.subscribers.unsubscribed');
    }

    /**
     * Import from file.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function import(Request $request)
    {
        $customer = $request->user()->customer;
        $list = MailList::findByUid($request->list_uid);

        $system_jobs = $list->importJobs();

        // authorize
        if (Gate::denies('import', $list)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            if ($request->hasFile('file')) {
                // Start system job
                $job = new ImportSubscribersJob($list, $request->user()->customer, $request->file('file')->path());
                $this->dispatch($job);

                // Action Log
                $list->log('import_started', $request->user()->customer);
            } else {
                // @note: use try/catch instead
                echo "max_file_upload";
            }
        } else {
            return view('subscribers.import', [
                'list' => $list,
                'system_jobs' => $system_jobs
            ]);
        }
    }

    /**
     * Check import proccessing.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function importProccess(Request $request)
    {
        $list = MailList::findByUid($request->current_list_uid);
        $system_job = $list->getLastImportJob();

        // authorize
        if (Gate::denies('import', $list)) {
            return $this->notAuthorized();
        }

        if(!is_object($system_job)) {
            return "none";
        }

        // authorize
        if (Gate::denies('import', $list)) {
            return $this->notAuthorized();
        }

        // Messages
        $message = ImportSubscribersHelper::getMessage($system_job);

        return response()->json([
            "job" => $system_job,
            "data" => json_decode($system_job->data),
            "timer" => $system_job->runTime(),
            "message" => $message,
        ]);
    }

    /**
     * Download import log.
     * @param Request $request
     * @return Response
     * @todo move this to the MailList controller
     */
    public function downloadImportLog(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (Gate::denies('import', $list)) {
            return $this->notAuthorized();
        }

        // @todo: should be the exact MailList here
        $log = $list->getLastImportLog();
        // @todo what if log does not exist (removed)?
        return response()->download($log);
    }

    /**
     * Display a listing of subscriber import job.
     *
     * @return Response
     */
    public function importList(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (Gate::denies('import', $list)) {
            return $this->notAuthorized();
        }

        $system_jobs = $list->importJobs();
        $system_jobs = $system_jobs->orderBy($request->sort_order, $request->sort_direction);
        $system_jobs = $system_jobs->paginate($request->per_page);

        return view('subscribers._import_list', [
            'system_jobs' => $system_jobs,
            'list' => $list
        ]);
    }

    /**
     * Export to csv.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function export(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (Gate::denies('export', $list)) {
            return $this->notAuthorized();
        }

        $system_jobs = $list->exportJobs();

        $customer = $request->user()->customer;

        // authorize
        if (Gate::denies('export', $list)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {

            // Start system job
            $job = new ExportSubscribersJob($list, $request->user()->customer);
            $this->dispatch($job);

            // Action Log
            $list->log('export_started', $request->user()->customer);
        } else {
            return view('subscribers.export', [
                'list' => $list,
                'system_jobs' => $system_jobs
            ]);
        }
    }

    /**
     * Check export proccessing.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportProccess(Request $request)
    {
        $list = MailList::findByUid($request->current_list_uid);
        $system_job = $list->getLastExportJob();

        // authorize
        if (Gate::denies('export', $list)) {
            return $this->notAuthorized();
        }

        if(!is_object($system_job)) {
            return "none";
        }

        // authorize
        if (Gate::denies('export', $list)) {
            return $this->notAuthorized();
        }

        return response()->json([
            "job" => $system_job,
            "data" => json_decode($system_job->data),
            "timer" => $system_job->runTime(),
        ]);
    }

    /**
     * Download exported csv file after exporting.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function downloadExportedCsv(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (Gate::denies('export', $list)) {
            return $this->notAuthorized();
        }

        $system_job = $list->getLastExportJob();

        return response()->download(storage_path('job/'.$system_job->id.'/data.csv'));
    }

    /**
     * Display a listing of subscriber import job.
     *
     * @return Response
     */
    public function exportList(Request $request)
    {
        $list = MailList::findByUid($request->list_uid);

        // authorize
        if (Gate::denies('export', $list)) {
            return $this->notAuthorized();
        }

        $system_jobs = $list->exportJobs();
        $system_jobs = $system_jobs->orderBy($request->sort_order, $request->sort_direction);
        $system_jobs = $system_jobs->paginate($request->per_page);

        return view('subscribers._export_list', [
            'system_jobs' => $system_jobs,
            'list' => $list
        ]);
    }

    /**
     * Copy subscribers to lists.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function copy(Request $request)
    {
        $from_list = MailList::findByUid($request->from_uid);
        $to_list = MailList::findByUid($request->to_uid);

        if ($request->select_tool == 'all_items') {
            $subscribers = $this->search($from_list, $request)->select('subscribers.*');
        } else {
            $subscribers = Subscriber::whereIn('uid', explode(',', $request->uids));
        }

        foreach ($subscribers->get() as $subscriber) {
            // authorize
            if (Gate::allows('update', $to_list)) {
                $subscriber->copy($to_list, $request->type);
            }
        }

        // Trigger updating related campaigns cache
        event(new MailListUpdated($to_list));

        // Log
        $to_list->log('copied', $request->user()->customer, [
            'count' => $subscribers->count(),
            'from_uid' => $from_list->uid,
            'to_uid' => $to_list->uid,
            'from_name' => $from_list->name,
            'to_name' => $to_list->name,
        ]);

        // Redirect to my lists page
        echo trans('messages.subscribers.copied');
    }

    /**
     * Move subscribers to lists.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function move(Request $request)
    {
        $from_list = MailList::findByUid($request->from_uid);
        $to_list = MailList::findByUid($request->to_uid);

        if ($request->select_tool == 'all_items') {
            $subscribers = $this->search($from_list, $request)->select('subscribers.*');
        } else {
            $subscribers = Subscriber::whereIn('uid', explode(',', $request->uids));
        }

        foreach ($subscribers->get() as $subscriber) {
            // authorize
            if (Gate::allows('update', $to_list)) {
                $subscriber->move($to_list, $request->type);
            }
        }

        // Trigger updating related campaigns cache
        event(new MailListUpdated($from_list));
        event(new MailListUpdated($to_list));

        // Log
        $to_list->log('moved', $request->user()->customer, [
            'count' => $subscribers->count(),
            'from_uid' => $from_list->uid,
            'to_uid' => $to_list->uid,
            'from_name' => $from_list->name,
            'to_name' => $to_list->name,
        ]);

        // Redirect to my lists page
        echo trans('messages.subscribers.moved');
    }

    /**
     * Copy Move subscribers form.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function copyMoveForm(Request $request)
    {
        $from_list = MailList::findByUid($request->from_uid);

        if ($request->select_tool == 'all_items') {
            $subscribers = $this->search($from_list, $request);
        } else {
            $subscribers = Subscriber::whereIn('uid', explode(',', $request->uids));
        }

        return view('subscribers.copy_move_form', [
            'subscribers' => $subscribers,
            'from_list' => $from_list
        ]);
    }

    /**
     * Start the verification process
     *
     */
    public function startVerification(Request $request)
    {
        $subscriber = Subscriber::findByUid($request->uid);
        $server = EmailVerificationServer::findByUid($request->email_verification_server_id);
        try {
            $subscriber->verify($server);

            // success message
            $request->session()->flash('alert-success', trans('messages.verification.finish'));

            // update MailList cache
            event(new MailListUpdated($subscriber->mailList));

            return redirect()->action('SubscriberController@edit', ['list_uid' => $request->list_uid, 'uid' => $subscriber->uid]);
        } catch (Exception $e) {
            MailLog::error(sprintf("Something went wrong while verifying %s (%s). Error message: %s", $subscriber->email, $subscriber->id, $e->getMessage()));
            return view('somethingWentWrong', ['message' => sprintf("Something went wrong while verifying %s (%s). Error message: %s", $subscriber->email, $subscriber->id, $e->getMessage())]);
        }
    }

    /**
     * Reset the verification data
     *
     */
    public function resetVerification(Request $request)
    {
        $subscriber = Subscriber::findByUid($request->uid);

        try {
            MailLog::info(sprintf("Cleaning up verification data for %s (%s)", $subscriber->email, $subscriber->id));
            $subscriber->emailVerification->delete();
            // success message
            $request->session()->flash('alert-success', trans('messages.verification.reset'));

            MailLog::info(sprintf("Finish cleaning up verification data for %s (%s)", $subscriber->email, $subscriber->id));
            return redirect()->action('SubscriberController@edit', ['list_uid' => $request->list_uid, 'uid' => $subscriber->uid]);
        } catch (Exception $e) {
            MailLog::error(sprintf("Something went wrong while cleaning up verification data for %s (%s). Error message: %s", $subscriber->email, $subscriber->id, $e->getMessage()));
            return view('somethingWentWrong', ['message' => sprintf("Something went wrong while cleaning up verification data for %s (%s). Error message: %s", $subscriber->email, $subscriber->id, $e->getMessage())]);
        }
    }

    /**
     * Render customer image.
     */
    public function avatar(Request $request)
    {
        // Get current customer
        if ($request->uid != '0') {
            $subscriber = Subscriber::findByUid($request->uid);
        } else {
            $subscriber = new Subscriber();
        }
        if (!empty($subscriber->imagePath())) {
            $img = Image::make($subscriber->imagePath());
        } else {
            $img = Image::make(public_path('assets/images/placeholder.jpg'));
        }

        return $img->response();
    }

    /**
     * Resend confirmation email.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function resendConfirmationEmail(Request $request)
    {
        $subscribers = Subscriber::whereIn('uid', explode(',', $request->uids));
        $list = MailList::findByUid($request->list_uid);

        // Select all items
        if ($request->select_tool == 'all_items') {
            $subscribers = $this->search($list, $request);
        }

        // Launch re-sending job
        dispatch(new SendConfirmationEmailJob($subscribers->get(), $list));

        // Redirect to my lists page
        echo trans('messages.subscribers.resend_confirmation_email.being_sent');
    }

    /**
     * Update tags.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function updateTags(Request $request, $list_uid, $uid)
    {
        $list = MailList::findByUid($list_uid);
        $subscriber = Subscriber::findByUid($uid);
        
        // authorize
        if (Gate::denies('update', $subscriber)) {
            return $this->notAuthorized();
        }

        // saving
        if($request->isMethod('post')) {
            $subscriber->updateTags($request->tags);

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.subscriber.tagged', [
                    'subscriber' => $subscriber->getFullName(),
                ]),
            ], 201);
        }

        return view('subscribers.updateTags', [
            'list' => $list,
            'subscriber' => $subscriber,
        ]);
    }

    /**
     * Automation remove contact tag.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function removeTag(Request $request, $list_uid, $uid)
    {
        $list = MailList::findByUid($list_uid);
        $subscriber = Subscriber::findByUid($uid);
        
        // authorize
        if (Gate::denies('delete', $subscriber)) {
            return $this->notAuthorized();
        }

        $subscriber->removeTag($request->tag);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.automation.contact.tag.removed', [
                'tag' => $request->tag,
            ]),
        ], 201);
    }
}
