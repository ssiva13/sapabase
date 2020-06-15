@extends('layouts.popup.medium')

@section('title', trans('messages.edit_template'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <form action="{{ action('SmsTemplateController@update', $template->uid) }}" method="POST" class="ajax_upload_form form-validate-jquery">
                {{ csrf_field() }}
                @include('sms_templates._form')
                <hr>
                <div class="text-right">
                    <button class="btn bg-teal mr-10"><i class="icon-check"></i> {{ trans('messages.save') }}</button>
                    <a href="#" onclick="hidePopUp()" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('messages.cancel') }}</a>
                </div>

            </form>

        </div>
    </div>
@endsection
