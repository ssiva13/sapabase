@extends('layouts.backend')

@section('title', trans('messages.create_email_verification_server'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
			<li><a href="{{ action("Admin\EmailVerificationServerController@index") }}">{{ trans('messages.email_verification_servers') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-plus-circle2"></i> {{ trans('messages.create_email_verification_server') }}</span>
		</h1>
	</div>

@endsection

@section('content')
	<div class="row">
		@component('admin.common-components.settings_breadcrumb')
			@slot('title') {{ trans('messages.email_verification_servers') }}  @endslot
			@slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
			@slot('li2') Admin  @endslot
			@slot('li3') {{ trans('messages.email_verification_server') }} @endslot
			@slot('li4') {{ trans('messages.create') }} @endslot
		@endcomponent
	</div>
	<div class="card">
		<div class="card-body">
	<form action="{{ action('Admin\EmailVerificationServerController@store', ["type" => request()->type]) }}" method="POST" class="form-validate-jqueryz email-verification-server-form">
		{{ csrf_field() }}

		@include('admin.email_verification_servers._form')
	</form>
		</div>
	</div>

@endsection
