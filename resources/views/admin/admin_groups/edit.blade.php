@extends('layouts.backend')

@section('title', $group->name)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\AdminGroupController@index") }}">{{ trans('messages.admin_groups') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-pencil"></i> {{ $group->name }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <div class="row">
        @component('admin.common-components.settings_breadcrumb')
            @slot('title') {{ trans('messages.admin') }}  @endslot
            @slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
            @slot('li2') Admin  @endslot
            @slot('li3') {{ trans('messages.admin_group') }} @endslot
            @slot('li4') {{ trans('messages.edit') }} @endslot
        @endcomponent
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ action('Admin\AdminGroupController@update', $group->id) }}" method="POST" class="form-validate-jqueryz">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="PATCH">

                @include("admin.admin_groups._form")
                <hr />
                <div class="text-left">
                    <button class="btn btn-primary mr-10"><i class="icon-check"></i> {{ trans('messages.save') }}</button>
                    <a href="{{ action('Admin\AdminGroupController@index') }}" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('messages.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

@endsection
