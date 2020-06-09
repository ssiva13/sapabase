@extends('layouts.backend')

@section('title', trans('messages.license'))

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
            <span class="text-gear"><i class="icon-key"></i> {{ trans('messages.license') }}</span>
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
			@slot('li4') {{ trans('messages.license_tab') }} @endslot
		@endcomponent
	</div>

    <form action="{{ action('Admin\SettingController@license') }}" method="POST" class="form-validate-jqueryz">
        {{ csrf_field() }}

        <div class="tabbable">
            @include("admin.settings._tabs")

            <div class="tab-content">

				@if ($license_error)
					<div class="alert alert-danger">
						{{ $license_error }}
					</div>
				@endif

					<div class="card">
						<div class="card-body">
							<h4 class="card-title text-muted text-center"><strong>{{ trans('messages.license') }}</strong></h4>
							<div class="card">
								<div class="card-body">
									@foreach ($settings as $name => $setting)
							@if (array_key_exists('cat', $setting) && $setting['cat'] == 'license')
								@if ($current_license)
									<div class="sub-section">
										<h3 class="text-muted">{{ trans('messages.license.your_license') }}</h3>
										<p>{{ trans('messages.your_current_license') }} <strong>{{ trans('messages.license_label_' . \Acelle\Model\Setting::get('license_type')) }}</strong></p>
										<h4 class="text-muted">
											{{ $current_license }}
										</h4>
									</div>
								@else
									<div class="sub-section">
										<h3 class="text-muted">{{ trans('messages.license.your_license') }}</h3>
										<p> {{ trans('messages.license.no_license') }} </p>
									</div>
								@endif

								<div class="sub-section">
									<h3 class="text-muted">{{ trans('messages.license.license_types') }}</h3>
									{!! trans('messages.license_guide') !!}
								</div>

								<div class="sub-section">
									@if (!$current_license)
										<h3 class="text-muted">{{ trans('messages.verify_license') }}</h3>
									@else
										<h3 class="text-muted">{{ trans('messages.change_license') }}</h3>
									@endif
									<div class="row license-line">
										<div class="col-md-6">
											@include('helpers.form_control', [
												'type' => $setting['type'],
												'class' => (isset($setting['class']) ? $setting['class'] : "" ),
												'name' => $name,
												'value' => (request()->license ? request()->license : ''),
												'label' => trans('messages.enter_license_and_click_verify'),
												'help_class' => 'setting',
												'options' => (isset($setting['options']) ? $setting['options'] : "" ),
												'rules' => Acelle\Model\Setting::rules(),
											])
										</div>
										<div class="col-md-6">
											<br />
											<div class="text-left">
												@if ($current_license)
													<button class="btn btn-primary"><i class="icon-check"></i> {{ trans('messages.change_license') }}</button>
												@else
													<button class="btn btn-primary"><i class="icon-check"></i> {{ trans('messages.verify_license') }}</button>
												@endif
											</div>
										</div>
									</div>
								</div>
							@endif
						@endforeach
								</div>
							</div>
						</div>
					</div>
				</div>
            </div>
        </div>
    </form>
@endsection
