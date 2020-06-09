@extends('layouts.frontend')

@section('title', $twilio_number->number)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection
@section('page_header')
    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("TwilioController@index") }}">{{ trans('messages.phone_numbers') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-pen"></i> {{ trans('messages.purchase_number') }}</span>
        </h1>
    </div>
@endsection

@section('content')

    <form action="{{ action('TwilioController@update', [$twilio_number->uid, $twilio_number->id]) }}" method="POST" class="form-validate-jqueryz">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PATCH">

        @include("twilio_numbers._form")
        <hr>
        <div class="text-left">
            <button class="btn bg-teal mr-10"><i class="icon-check"></i> {{ trans('messages.save') }}</button>
            <a href="{{ action('TwilioController@index') }}" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('messages.cancel') }}</a>
        </div>
    </form>
@endsection
