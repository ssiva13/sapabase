@extends('layouts.popup.medium')

@section('title', trans('messages.create_template'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('page_header')

			<div class="page-title">				
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
                    <li><a href="{{ action("SmsTemplateController@index") }}">{{ trans('messages.sms_templates') }}</a></li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="icon-pencil"></i> {{ trans('messages.create_template') }}</span>
				</h1>				
			</div>

@endsection

@section('content')
    
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ action('SmsTemplateController@store') }}" method="POST" class="ajax_upload_form form-validate-jquery">
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
