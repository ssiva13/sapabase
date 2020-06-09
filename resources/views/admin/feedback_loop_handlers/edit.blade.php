@extends('layouts.backend')

@section('title', $server->name)

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

			<div class="page-title">
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
					<li><a href="{{ action("Admin\FeedbackLoopHandlerController@index") }}">{{ trans('messages.feedback_loop_handlers') }}</a></li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="icon-pencil"></i> {{ $server->name }}</span>
				</h1>
			</div>

@endsection

@section('content')
	<div class="row">
		@component('admin.common-components.settings_breadcrumb')
			@slot('title') {{ trans('messages.feedback_loop_handlers') }}  @endslot
			@slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
			@slot('li2') Admin  @endslot
			@slot('li3') {{ trans('messages.feedback_loop_handlers') }} @endslot
			@slot('li4') {{ trans('messages.edit') }} @endslot
		@endcomponent
	</div>
	<div class="card">
		<div class="card-body">

				<form enctype="multipart/form-data" action="{{ action('Admin\FeedbackLoopHandlerController@update', $server->uid) }}" method="POST" class="form-validate-jquery">
					{{ csrf_field() }}
					<input type="hidden" name="_method" value="PATCH">

					@include('admin.feedback_loop_handlers._form')

				</form>
		</div>
	</div>
@endsection
