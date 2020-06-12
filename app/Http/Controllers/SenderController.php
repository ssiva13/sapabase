<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use Acelle\Model\Sender;
use Acelle\Model\SendingDomain;

class SenderController extends Controller
{

    /**
     * Search items.
     */
    public function search($request)
    {
        $request->merge(array("customer_id" => $request->user()->customer->id));
        $senders = Sender::search($request);

        return $senders;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (\Gate::denies('listing', new Sender())) {
            return $this->notAuthorized();
        }

        $subscription = $request->user()->customer->subscription;
        $server = $subscription->plan->primarySendingServer();

        if ($subscription->plan->useOwnSendingServer()) {
            $email = false;
            $domain = true;
        } else {    
            $email = $server->allowVerifyingOwnEmails() || $server->allowVerifyingOwnEmailsRemotely();
            $domain = $server->allowVerifyingOwnDomains();    
        }

        if ($email || $domain ) {
            //
        } else {
            return view('senders.available', [
                'identities' => $subscription->plan->getVerifiedIdentities(),
            ]);
        }

        if ($domain && !$email) {
            return redirect(url('sending_domains'));
        } else {
            return view('senders.index', [
                'senders' => $this->search($request),
            ]);
        }
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        if (\Gate::denies('listing', new Sender())) {
            return $this->notAuthorized();
        }

        return view('senders._list', [
            'senders' => $this->search($request)->paginate($request->per_page),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $sender = new Sender();

        $sender->fill($request->old());

        // authorize
        if (\Gate::denies('create', $sender)) {
            return $this->notAuthorized();
        }

        return view('senders.create', [
            'sender' => $sender,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $sender = new Sender();

        // authorize
        if (\Gate::denies('create', $sender)) {
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('post')) {
            $sender->fill($request->all());
            $sender->customer_id = $request->user()->customer->id;
            $sender->status = Sender::STATUS_NEW;

            $this->validate($request, $sender->rules());

            if ($sender->save()) {
                $request->session()->flash('alert-success', trans('messages.sender.created'));
                return redirect()->action('SenderController@show', $sender->uid);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $sender = Sender::findByUid($id);
        $subscription = $request->user()->customer->subscription;
        $server = $subscription->plan->primarySendingServer();

        // authorize
        if (\Gate::denies('read', $sender)) {
            return $this->notAuthorized();
        }

        return view('senders.show', [
            'sender' => $sender,
            'verificationOptions' => Sender::verificationTypeSelectOptions($server)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $sender = Sender::findByUid($id);

        // authorize
        if (\Gate::denies('update', $sender)) {
            return $this->notAuthorized();
        }

        $sender->fill($request->old());

        return view('senders.edit', [
            'sender' => $sender,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $sender = Sender::findByUid($id);

        // authorize
        if (\Gate::denies('update', $sender)) {
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('patch')) {
            $sender->name = $request->name;

            $this->validate($request, $sender->editRules());

            if ($sender->save()) {
                $request->session()->flash('alert-success', trans('messages.sender.updated'));
                return redirect()->action('SenderController@show', $sender->uid);
            }
        }
    }

    /**
     * Verify sender.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, $id)
    {
        $sender = Sender::findByUid($id);

        // authorize
        if (\Gate::denies('verify', $sender)) {
            return $this->notAuthorized();
        }

        // save posted data
        if ($request->isMethod('post')) {
            $sender->fill($request->all());

            $this->validate($request, $sender->verificationRules());

            $sender->save();

            if (!$sender->isVerified()) {
                if ($sender->type == Sender::VERIFICATION_TYPE_ACELLE) {
                    // Set pending and return to show with guide
                    $sender->setPending();
                    return redirect()->action('SenderController@show', $sender->uid);
                } else if ($sender->type = Sender::VERIFICATION_TYPE_AMAZON_SES) {
                    // Wroking with Amazon SES here...
                    $sender->setPending();

                    // Trigger AWS API to send a verification email
                    $sender->sendVerificationEmail();

                    return redirect()->action('SenderController@show', $sender->uid);
                }
            } else {
                $request->session()->flash('alert-success', trans('messages.sender.verified'));
                return redirect()->action('SenderController@show', $sender->uid);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if ($request->select_tool == 'all_items') {
            $senders = $this->search($request);
        } else {
            $senders = Sender::whereIn('uid', explode(',', $request->uids));
        }

        foreach ($senders->get() as $sender) {
            // authorize
            if ($request->user()->customer->can('delete', $sender)) {
                $sender->delete();
            }
        }

        // Redirect to my lists page
        echo trans('messages.senders.deleted');
    }

    /**
     * Start import process.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $customer = $request->user()->customer;

        if ($request->isMethod('post')) {
            // authorize
            if (\Gate::denies('import', new Sender())) {
                return $this->notAuthorized();
            }

            if ($request->hasFile('file')) {
                // Start system job
                $job = new \Acelle\Jobs\ImportSenderJob($request->file('file')->path(), $request->user()->customer);
                $this->dispatch($job);
            } else {
                // @note: use try/catch instead
                echo "max_file_upload";
            }
        }

        // Get current job
        $system_job = $customer->getLastActiveImportSenderJob();

        return view('senders.import', [
            'system_job' => $system_job
        ]);
    }

    /**
     * Check import proccessing.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function importProcess(Request $request)
    {
        $customer = $request->user()->customer;
        $system_job = \Acelle\Model\SystemJob::find($request->system_job_id);

        // authorize
        if (\Gate::denies('read', new Sender())) {
            return $this->notAuthorized();
        }

        return view('senders.import_process', [
            'system_job' => $system_job
        ]);
    }

    /**
     * Cancel importing job.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $customer = $request->user()->customer;
        $system_job = $customer->getLastActiveImportSenderJob();

        // authorize
        if (\Gate::denies('importCancel', new Sender())) {
            return $this->notAuthorized();
        }

        $system_job->setCancelled();

        $request->session()->flash('alert-success', trans('messages.sender.import.cancelled'));
        return redirect()->action('SenderController@index');
    }

    /**
     * Dropbox list.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function dropbox(Request $request)
    {
        //$droplist = [];
        //$keyword = strtolower(trim($request->keyword));
        //// Find all senders
        //$senders = $request->user()->customer->senders()->whereRAW('LOWER(email) LIKE ?', $keyword.'%')->limit(10)->get();
        //
        //foreach($senders as $key => $sender) {
        //    $sender->email = strtolower(trim($sender->email));
        //    $row = [
        //        'text' => $sender->name,
        //        'value' => $sender->email,
        //        'desc' => str_replace($keyword, '<span class="text-semibold text-primary">'.$keyword.'</span>', $sender->email),
        //    ];
        //    $droplist[] = $row;
        //}
        //
        //// Domains
        //$parts = explode('@', $keyword);
        //$keyword = isset($parts[1]) ? $parts[1] : null;
        //
        //$domains = $request->user()->customer->getAllSystemAndOwnDomains();
        //
        //if (!empty($keyword)) {
        //    $domains = $domains->whereRaw('LOWER(name) LIKE ?', $keyword.'%')->limit(10);
        //}
        //
        //$domains = $domains->get();
        //foreach($domains as $key => $domain) {
        //    $domain->name = strtolower(trim($domain->name));
        //    $row = [
        //        'text' => '****@'.str_replace($keyword, '<span class="text-semibold text-primary">'.$keyword.'</span>', $domain->name),
        //        'subfix' => $domain->name,
        //        'desc' => null,
        //    ];
        //    $droplist[] = $row;
        //}
        //
        //if (Sender::getAllVerified()->count() == 0 && $request->user()->customer->getAllSystemAndOwnDomains()->count() == 0) {
        //    $row = [
        //        '_warning' => trans('messages.senders.empty_verified_domain_sender.warning', [
        //            'domain_link' => action('SendingDomainController@index'),
        //            'sender_link' => action('SenderController@index'),
        //        ]),
        //    ];
        //    $droplist[] = $row;
        //}

        //return view('senders.dropbox', [
        //    'droplist' => $droplist,
        //]);

        $droplist = $request->user()->customer->verifiedIdentitiesDroplist(strtolower(trim($request->keyword)));
        return response()->json($droplist);
    }
}
