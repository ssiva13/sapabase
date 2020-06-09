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
			@slot('title') {{ trans('messages.advanced_settings') }}  @endslot
			@slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
			@slot('li2') Admin  @endslot
			@slot('li3') {{ trans('messages.settings') }} @endslot
			@slot('li4') {{ trans('messages.advanced_settings') }} @endslot
		@endcomponent
	</div>
    <form action="{{ action('Admin\SettingController@advanced') }}" method="POST" class="form-validate-jqueryz">
        {{ csrf_field() }}

        <div class="tabbable">

            <div class="tab-content">

                @include("admin.settings._advanced")

            </div>
        </div>

    </form>

	<div id="advanced_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<form class="form-control" id="advanced_settings_form">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mt-0" id="advanced_modal_label"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<div class="modal-body">
						<div class="form-group row">
							<label id="advanced_modal_input_name" for="example-text-input" class="col-sm-2 col-form-label">Text</label>
							<div class="col-sm-10">
								<input class="form-control" name="value" type="text" id="advanced_modal_input_value">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary waves-effect waves-light">Save changes</button>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
		</form>
		<!-- /.modal-dialog -->
	</div>
@endsection
