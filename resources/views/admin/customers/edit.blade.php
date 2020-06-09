@extends('layouts.backend')

@section('title', $customer->displayName())
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>		
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	
			<div class="page-title">
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
					<li><a href="{{ action("Admin\CustomerController@index") }}">{{ trans('messages.customers') }}</a></li>
					<li class="active">{{ trans('messages.update') }}</li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="icon-profile"></i> {{ $customer->displayName() }}</span>
				</h1>
			</div>
				
@endsection

@section('content')
	<div class="row">
		@component('admin.common-components.breadcrumb')
			@slot('title') {{ trans('messages.customer') }}  @endslot
			@slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
			@slot('li2') Admin  @endslot
			@slot('li3') {{$customer->first_name}}  {{$customer->last_name}}@endslot
		@endcomponent
	</div>

	@include('admin.customers._tabs')

	<form enctype="multipart/form-data" action="{{ action('Admin\CustomerController@update', $customer->uid) }}" method="POST" class="form-validate-jquery">
		{{ csrf_field() }}
		<input type="hidden" name="_method" value="PATCH">
		<div class="card">
			<div class="card-body">
				<h4 class="card-title text-muted text-center"><strong>{{ trans('messages.profile') }}</strong></h4>
				<div class="card">
					<div class="card-body">
						@include('admin.customers._form')
					</div>
				</div>
			</div>
		</div>
		
	<form>
@endsection