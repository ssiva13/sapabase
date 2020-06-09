@extends('layouts.backend')

@section('title', trans('messages.payments.' . $gateway['name']))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\PaymentController@index") }}">{{ trans('messages.payment_gateways') }}</a></li>
            <li class="active">{{ trans('messages.update') }}</li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-credit-card2"></i> {{ trans('messages.payments.' . $gateway['name']) }}</span>
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
            @slot('li4') {{ trans('messages.payment.options') }} @endslot
        @endcomponent
    </div>
    <p>
        {!! trans('messages.payment.' . $gateway['name'] . '.wording') !!}
    </p>
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-muted text-center"><strong>{{ trans('messages.payments.' . $gateway['name']) }}  {{ trans('messages.payment.options') }} </strong></h4>
            @include('admin.payments._' . $gateway['name'])
        </div>
    </div>

@endsection