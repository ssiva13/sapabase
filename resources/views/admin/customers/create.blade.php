@extends('layouts.backend')

@section('title', trans('messages.create_customer'))
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	
			<div class="page-title">
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
					<li><a href="{{ action("Admin\CustomerController@index") }}">{{ trans('messages.customers') }}</a></li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="icon-plus-circle2"></i> {{ trans('messages.create_customer') }}</span>
				</h1>
			</div>

@endsection

@section('content')
	<div class="row">
		@component('admin.common-components.breadcrumb')
			@slot('title') {{ trans('messages.create_customer') }}  @endslot
			@slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
			@slot('li2') Admin  @endslot
			@slot('li3') {{ trans('messages.create_customer') }} @endslot
		@endcomponent
	</div>
	<div class="card">
		<div class="card-body">
			<form enctype="multipart/form-data" action="{{ action('Admin\CustomerController@store') }}" method="POST" class="form-validate-jqueryz">
				{{ csrf_field() }}
				@include('admin.customers._form')
			</form>
		</div>
	</div>
				
@endsection
