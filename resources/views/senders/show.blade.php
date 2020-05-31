@extends('layouts.frontend')

@section('title', $sender->name)
    
@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("SenderController@index") }}">{{ trans('messages.verified_senders') }}</a></li>
            <li><a href="{{ action("SendingDomainController@index") }}">{{ trans('messages.email_addresses') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold">{{ trans('messages.verified_senders') }}</span>
        </h1>        
    </div>
@endsection

@section('content')
    @include('senders._menu')
    
    <h2>
        <span class="text-semibold"><i class="icon-profile"></i> {{ $sender->name }} </span>
        <span class="label label-primary bg-{{ $sender->status }}">
            {{ trans('messages.sender.status.' . $sender->status) }}
        </span>
    </h2>
        
    <div class="row sub_section">
        <div class="col-sm-12 col-md-8">
			<p>{{ trans('messages.sender.show.wording') }}</p>
            <ul class="dotted-list topborder section section-flex">
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.name') }}</strong>
                    </div>
                    <div class="size2of3">
                        <span>{{ $sender->name }}</span>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.email') }}</strong>
                    </div>
                    <div class="size2of3">
                        <span>{{ $sender->email }}</span>
                    </div>
                </li>
				<!--
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.type') }}</strong>
                    </div>
                    <div class="size2of3">
                        <span>{{ $sender->type ? trans('messages.sender.type.' . $sender->type) : trans('messages.sender.type.none') }}</span>
                    </div>
                </li>
				-->
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.created_at') }}</strong>
                    </div>
                    <div class="size2of3">
                        <span>{{ Tool::formatDateTime($sender->created_at) }}</span>
                    </div>
                </li>
            </ul>
                
            <a href="{{ action('SenderController@edit', $sender->uid) }}" class="btn btn-primary bg-grey" style="min-width: 100px"><i class="icon-pencil"></i> {{ trans('messages.sender.edit') }}</a>
        </div>
    </div>
    
    @if ($sender->status != Acelle\Model\Sender::STATUS_NEW)
        <div class="sub_section">
            <div class="row">
                <div class="col-sm-12 col-md-8">
                   <h3>{{ trans('messages.sender.verification_status') }}</h3>
                    
                    <div class="row mb-10">
                        <div class="col-sm-12 col-md-8">
                            <ul class="dotted-list topborder section section-flex">                                
                                <li>
                                    <div class="unit size1of3">
                                        <strong>{{ trans("messages.status") }}</strong>
                                    </div>
                                    <div class="size2of3">
                                        <span class="label label-primary bg-{{ $sender->status }}">
											{{ trans('messages.sender.status.' . $sender->status) }}
										</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
            
                    <p>{!! trans('messages.sender.status_info.' . $sender->type . '.' . $sender->status, [
                        'domain' => $sender->getDomain(),
                        'email' => $sender->email,
                        'name' => $sender->name,
                        'type' => trans('messages.sender.type.' . $sender->type),
                        'link' => action('SendingDomainController@index'),
                    ]) !!}</p>
                        
                    @if ($sender->type == Acelle\Model\Sender::VERIFICATION_TYPE_ACELLE)
                        <a class="btn btn-primary bg-grey" href="{{ action('SendingDomainController@index') }}">
                            <i class="icon icon-earth"></i> {{ trans('messages.sending_domains') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
    
    @if ($sender->status != Acelle\Model\Sender::STATUS_NEW)
        <a href="javascript:;" onclick="$('.verify_sender_container').removeClass('hide'); $(this).hide()">
            {!! trans('messages.sender.verify_toggle.' . $sender->status, [
                'email' => $sender->email,
            ]) !!}
        </a>
    @endif
        
    <div class="sub_section verify_sender_container {{ $sender->status != Acelle\Model\Sender::STATUS_NEW ? 'hide' : '' }}">
        <h3>{{ trans('messages.sender.verify_sender') }}</h3>
        
        <form enctype="multipart/form-data" action="{{ action('SenderController@verify', $sender->uid) }}" method="POST" class="form-vsalidate-jquery">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    @include('helpers.form_control', [
                        'type' => 'select',
                        'name' => 'type',
                        'value' => $sender->type,
                        'options' => $verificationOptions,
                        'include_blank' => trans('messages.choose'),
                        'rules' => $sender->verificationRules()
                    ])
                </div>
            </div>
            
            <div class="text-left">
                <button class="btn btn-primary bg-grey">{{ trans('messages.sending_domain.verify') }}</button>
            </div>
        </form>
    </div>
        
@endsection