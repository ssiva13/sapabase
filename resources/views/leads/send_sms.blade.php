@extends('layouts.popup.medium')

@section('title', trans('messages.'.$type.'.send').' to '. $phone)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("SmsTemplateController@index") }}">{{ trans('messages.'.$type.'.send') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-pencil"></i> {{ trans('messages.'.$type.'.send') }}</span>
        </h1>
    </div>

@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <form id="processRequest" action="{{ action('TwilioController@processRequest', ['phone' => $phone, 'type' => $type]) }}" method="GET" class="ajax_upload_form form-validate-jquery">
                {{ csrf_field() }}
                    @include('leads._form')
                <hr>
                <div class="text-right">
                    <button class="btn bg-teal mr-10"><i class="icon-check"></i> {{ trans('messages.'.$type.'.send') }}</button>
                    <a href="#" onclick="hidePopUp()" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('messages.cancel') }}</a>
                </div>

                <script>
                    $('#sms_template').change(function() {
                        var uri = '{{ action('SmsTemplateController@get') }}';
                        $.ajax({
                            url: uri,
                            method: 'GET',
                            data: {template_uid : $(this).val()},
                            statusCode: {
                                // validate error
                                400: function (res) {
                                }
                            },
                            success: function (response) {
                                $('[name="message"]').val(response);
                            }
                        });
                    });
                    $('#twilio_numbers').change(function() {
                        $('[name="from"]').val($(this).val());
                    });

                    $('#processRequest').submit(function(e) {
                        e.preventDefault();
                        // loading effect
                        popup.loading();

                        $.ajax({
                            url: $(this).attr('action'),
                            method: 'GET',
                            data: $(this).serialize(),
                            statusCode: {
                                // validate error
                                400: function (res) {
                                    console.log(res)
                                    // popup.loadHtml(res.responseText);
                                }
                            },
                            success: function (response) {
                                let url = '{{ action('TwilioController@processedRequest') }}';
                                popup.load(url);
                            }
                        });

                        // loadHtml
                        // popup.load($('#processRequest').submit(), function() {
                        //     // set back event
                        //     popup.back = function() {
                        //         Popup.hide();
                        //     };
                        // });
                    });

                </script>

            </form>
        </div>
    </div>
@endsection