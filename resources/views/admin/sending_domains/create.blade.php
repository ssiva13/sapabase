@extends('layouts.backend')

@section('title', trans('messages.create_sending_domain'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\SendingDomainController@index") }}">{{ trans('messages.sending_domains') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-plus-circle2"></i> {{ trans('messages.create_sending_domain') }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <div class="row">
        @component('admin.common-components.settings_breadcrumb')
            @slot('title') {{ trans('messages.sending_domain') }}  @endslot
            @slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
            @slot('li2') Admin  @endslot
            @slot('li3') {{ trans('messages.sending_domain') }} @endslot
            @slot('li4') {{ trans('messages.create') }} @endslot
        @endcomponent
    </div>
    <p>{!! trans('messages.sending_domain.wording') !!}</p>
    <div class="card">
        <div class="card-body">
            <form action="{{ action('Admin\SendingDomainController@store') }}" method="POST" class="form-validate-jqueryz">
                @include('admin.sending_domains._form')
            </form>
        </div>
    </div>

@endsection
