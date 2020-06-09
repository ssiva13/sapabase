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
			@slot('li4') {{ trans('messages.background_job') }} @endslot
		@endcomponent
	</div>
	<div class="tabbable">

		@include("admin.settings._tabs")
		<div class="card">
			<div class="card-body">
				<h4 class="card-title text-muted text-center"><strong>{{ trans('messages.background_job') }}</strong></h4>
				<div class="card">
					<div class="card-body">
						<form action="{{ action('Admin\SettingController@cronjob') }}" method="POST" class="form-validate-jqueryz">
							{!! csrf_field() !!}

							@include('elements._cron_jobs', ['show_all' => true])

							<hr>
							<div class="text-left">
								<button class="btn btn-primary bg-teal">
									{!! trans('messages.save') !!}
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
