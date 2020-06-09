@extends('layouts.backend')

@section('title', $currency->name)
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	
			<div class="page-title">
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
					<li><a href="{{ action("Admin\CurrencyController@index") }}">{{ trans('messages.currencies') }}</a></li>
					<li class="active">{{ trans('messages.update') }}</li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="icon-profile"></i> {{ $currency->name }}</span>
				</h1>
			</div>
				
@endsection

@section('content')
	<div class="row">
		@component('admin.common-components.settings_breadcrumb')
			@slot('title') <a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a>@endslot
			@slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
			@slot('li2') Admin  @endslot
			@slot('li3') {{ trans('messages.currency') }} @endslot
			@slot('li4') {{ trans('messages.edit') }} @endslot
		@endcomponent
	</div>

	<div class="card">
		<div class="card-body">
				<form enctype="multipart/form-data" action="{{ action('Admin\CurrencyController@update', $currency->uid) }}" method="POST" class="form-validate-jqueryx">
					{{ csrf_field() }}
					<input type="hidden" name="_method" value="PATCH">
					
					@include('admin.currencies._form')
					
				</form>
		</div>
	</div>
	
@endsection