@extends('layouts.popup.medium')

{{--@section('title', trans('messages.'.$type.'.send'))--}}

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="empty-list">
                <i class="icon-phone-wave"></i>
                <span class="line-1">
                    {{ trans('messages.') }}
                </span>
                <span class="line-2">
                    {{ trans('messages.call.on') }}
                </span>
            </div>
        </div>
    </div>
@endsection