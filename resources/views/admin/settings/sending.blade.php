@extends('layouts.backend')

@section('title', trans('messages.settings'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>		
@endsection

@section('page_header')

			<div class="page-title">				
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
				</ul>
				<h1>
					<span class="text-gear"><i class="icon-list2"></i> {{ trans('messages.settings') }}</span>
				</h1>
			</div>

@endsection

@section('content')
	<div class="row">
		@component('admin.common-components.settings_breadcrumb')
			@slot('title') {{ trans('messages.settings') }}  @endslot
			@slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
			@slot('li2') Admin  @endslot
			@slot('li3') {{ trans('messages.settings') }} @endslot
			@slot('li4') {{ trans('messages.sending') }} @endslot
		@endcomponent
	</div>
	<form action="{{ action('Admin\SettingController@sending') }}" method="POST" class="form-validate-jqueryz">
		{{ csrf_field() }}

		<div class="tabbable">

			@include("admin.settings._tabs")

			<div class="tab-content">

				@include("admin.settings._sending")

			</div>
		</div>


	</form>
@endsection
