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
	<h1>
		<span class="text-semibold"><i class="icon-profile"></i> {{ $sender->name }}</span>
	</h1>
    
    <div class="row">
        <div class="col-sm-12 col-md-8">
            <ul class="dotted-list topborder section section-flex">
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.name') }}</strong>
                    </div>
                    <div class="size2of3">
                        <mc:flag class="text-bold">{{ $sender->name }}</mc:flag>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.email') }}</strong>
                    </div>
                    <div class="size2of3">
                        <mc:flag class="text-bold">{{ $sender->email }}</mc:flag>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans("messages.status") }}</strong>
                    </div>
                    <div class="size2of3">
                        <mc:flag class="text-bold">
							<span class="label label-primary bg-{{ $sender->status }}">
								{{ trans('messages.sender.status.' . $sender->status) }}
							</span>
						</mc:flag>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.created_at') }}</strong>
                    </div>
                    <div class="size2of3">
                        <mc:flag class="text-bold">{{ Tool::formatDateTime($sender->created_at) }}</mc:flag>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="subsection">
        <h3>{{ trans('messages.sender.verification_status') }}</h3>
		
		<p>{!! trans('messages.sender.satus_info.' . $sender->type . '.' . $sender->status) !!}</p>
    </div>


				
@endsection