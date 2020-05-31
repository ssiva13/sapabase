@extends('layouts.backend')

@section('title', $plan->name)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\PlanController@index") }}">{{ trans('messages.plans') }}</a></li>
            <li><a href="{{ action('Admin\PlanController@sendingServer', $plan->uid) }}">
                    {{ $plan->name }}
                </a>
            </li>
        </ul>
        <h1 class="mc-h1">
            <span class="text-semibold">{{ $plan->name }}</span>
        </h1>
    </div>

@endsection

@section('content')
    
    @include('admin.plans._menu')
    
    <form action="{{ action('Admin\PlanController@sendingServerOwn', $plan->uid) }}" method="POST" class="form-validate-jqueryx">
        {{ csrf_field() }}
        
        <div class="mc-section">
            <div class="announce_box">
                <div class="row flex-center">
                    <div class="col-md-8">
                        <i class="announce_box-icon lnr lnr-users"></i>
                        <label>{{ trans('messages.plan_option.delivery_setting') }}</label>
                        <h4>{{ trans('messages.plan_option.sub_account') }}</h4>
                        <p>{{ trans('messages.plan_option.sub_account.intro') }}</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <a class="btn btn-mc_primary mr-20 mc-modal-control" modal-size="lg" href="{{ action('Admin\PlanController@sendingServerOption', [
                            'id' => $plan->uid]) }}">
                                {{ trans('messages.plan_option.change') }}</a>
                    </div>
                </div>
            </div>
                
            <div class="row flex-center">
                <div class="col-md-12">
                    <h2 class="mc-h2 mt-0 mb-10">
                        <span class="text-semibold">{{ trans('messages.plan.sending_servers.sub_account.setting') }}</span>
                    </h2>
                    <p>{{ trans('messages.plan.sending_servers.sub_account.setting.intro') }}</p>
                </div>
            </div>
            
            @if (Auth()->user()->admin->getSubaccountSendingServers()->count())
                <div class="row">
                    <div class="col-md-12">
                        @include('helpers.form_control', [
                            'type' => 'select',
                            'class' => 'numeric',
                            'name' => 'plan[options][sending_server_subaccount_uid]',
                            'value' => $plan->getOption('sending_server_subaccount_uid'),
                            'label' => '',
                            'help_class' => 'plan',
                            'include_blank' => trans('messages.select_sending_server_with_subaccount'),
                            'options' => Auth()->user()->admin->getSubaccountSendingServersSelectOptions(),
                            'rules' => $plan->validationRules()['options'],
                        ])
                    </div>
                </div>
            @else
                <div class="alert alert-danger">
                    {!! trans('messages.plan_option.there_no_subaccount_sending_server') !!}
                </div>
            @endif
        </div>
    </form>
    
    <script>
        var current_option = $("input[name='plan[options][sending_server_option]']:checked").val();
    
        $(document).ready(function() {
            // all sending servers checking
            $(document).on("change", "input[name='plan[options][all_sending_servers]']", function(e) {
                if($("input[name='plan[options][all_sending_servers]']:checked").length) {
                    $(".sending-servers").find("input[type=checkbox]").each(function() {
                        if($(this).is(":checked")) {
                            $(this).parents(".form-group").find(".switchery").eq(1).click();
                        }
                    });
                    $(".sending-servers").hide();
                } else {
                    $(".sending-servers").show();
                }
            });
            $("input[name='plan[options][all_sending_servers]']").trigger("change");
    
            // Sending domains checking setting
            $(document).on("change", "input[name='plan[options][create_sending_domains]']", function(e) {
                if($('input[name="plan[options][create_sending_domains]"]:checked').val() == 'yes') {
                    $(".sending-domains-yes").show();
                    $(".sending-domains-no").hide();
                } else {
                    $(".sending-domains-no").show();
                    $(".sending-domains-yes").hide();
                }
            });
            $('input[name="plan[options][create_sending_domains]"]').trigger("change");
    
            // all email verification servers checking
            $(document).on("change", "input[name='plan[options][all_email_verification_servers]']", function(e) {
                if($("input[name='plan[options][all_email_verification_servers]']:checked").length) {
                    $(".email-verification-servers").find("input[type=checkbox]").each(function() {
                        if($(this).is(":checked")) {
                            $(this).parents(".form-group").find(".switchery").eq(1).click();
                        }
                    });
                    $(".email-verification-servers").hide();
                } else {
                    $(".email-verification-servers").show();
                }
            });
            $("input[name='plan[options][all_email_verification_servers]']").trigger("change");
    
    
            // Email verification servers checking setting
            $(document).on("change", "input[name='plan[options][create_email_verification_servers]']", function(e) {
                if($('input[name="plan[options][create_email_verification_servers]"]:checked').val() == 'yes') {
                    $(".email-verification-servers-yes").show();
                    $(".email-verification-servers-no").hide();
                } else {
                    $(".email-verification-servers-no").show();
                    $(".email-verification-servers-yes").hide();
                }
            });
            $('input[name="plan[options][create_email_verification_servers]"]').trigger("change");
    
            // Sending servers type checking setting
            $(document).on("change", "input[name='plan[options][all_sending_server_types]']", function(e) {
                if($('input[name="plan[options][all_sending_server_types]"]:checked').val() == 'yes') {
                    $(".all_sending_server_types_yes").show();
                    $(".all_sending_server_types_no").hide();
                } else {
                    $(".all_sending_server_types_no").show();
                    $(".all_sending_server_types_yes").hide();
                }
            });
            $('input[name="plan[options][all_sending_server_types]"]').trigger("change");
            
        });
    </script>
    
@endsection


