@extends('layouts.backend')

@section('title', $server->name)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
    @foreach ($notices as $n)
        @include('elements._notification', [
            'level' => 'warning',
            'title' => $n['title'],
            'message' => htmlspecialchars($n['message']),
        ])
    @endforeach

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\SendingServerController@index") }}">{{ trans('messages.sending_servers') }}</a></li>
            <li>{{ trans('messages.edit') }}</li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-pencil"></i>
                {{ $server->name }}
            </span>
                
            <span class="label label-flat bg-{{$server->status}}">{{$server->status}}</span>
        </h1>
    </div>

@endsection

@section('content')
    <div class="row">
        @component('admin.common-components.settings_breadcrumb')
            @slot('title') {{ trans('messages.sending_servers') }}  @endslot
            @slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
            @slot('li2') Admin  @endslot
            @slot('li3') {{ trans('messages.sending_servers') }} @endslot
            @slot('li4') {{ trans('messages.edit') }} @endslot
        @endcomponent
    </div>
    <div class="card">
        <div class="card-body">
            @include('admin.sending_servers.form.' . $server->type, ['identities' => $identities])
        </div>
    </div>

@endsection
