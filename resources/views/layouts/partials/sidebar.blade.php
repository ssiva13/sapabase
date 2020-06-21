<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">

        <!--- Side menu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li rel0="HomeController">
                    <a href="{{ action('Admin\HomeController@index') }}">
                        <i class="icon-home"></i> <span>{{ trans('messages.dashboard') }}</span>
                    </a>
                </li>
                @if (Auth::user()->can("read", new Acelle\Model\Customer()))
                    <li rel0="CustomerGroupController" rel1="CustomerController">
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="icon-user"></i> <span>{{ trans('messages.customer') }}</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            @if (Auth::user()->can("read", new Acelle\Model\Customer()))
                                <li rel0="CustomerController">
                                    <a href="{{ action('Admin\CustomerController@index') }}">
                                        <i class="icon-users"></i> <span>{{ trans('messages.customers') }}</span>
                                    </a>
                                </li>
                            @endif
                            <li rel0="SubscriptionController">
                                <a href="{{ action('Admin\SubscriptionController@index') }}">
                                    <i class="icon-quill4"></i> <span>{{ trans('messages.subscriptions') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ( Auth::user()->can("read", new Acelle\Model\Plan()) || Auth::user()->can("read", new Acelle\Model\Currency()) )
                    <li rel0="PlanController" rel1="CurrencyGroupController">
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="icon-credit-card2"></i> <span>{{ trans('messages.plan') }}</span>
                        </a>
                        <ul aria-expanded="false" class="sub-menu">
                            @if (Auth::user()->can("read", new Acelle\Model\Plan()))
                                <li rel0="PlanController">
                                    <a href="{{ action('Admin\PlanController@index') }}">
                                        <i class="icon-clipboard2"></i> <span>{{ trans('messages.plans') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->can("read", new Acelle\Model\Currency()))
                                <li rel0="CurrencyController">
                                    <a href="{{ action('Admin\CurrencyController@index') }}">
                                        <i class="icon-coins"></i> <span>{{ trans('messages.currencies') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if ( Auth::user()->admin->getPermission("admin_read") != 'no' || Auth::user()->admin->getPermission("admin_group_read") != 'no' )
                    <li rel0="AdminGroupController" rel1="AdminController">
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="icon-user-tie"></i> <span>{{ trans('messages.admin') }}</span>
                        </a>
                        <ul aria-expanded="false" class="sub-menu">
                            @if (Auth::user()->admin->getPermission("admin_read") != 'no')
                                <li rel0="AdminController">
                                    <a href="{{ action('Admin\AdminController@index') }}">
                                        <i class="icon-users"></i> <span>{{ trans('messages.admins') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("admin_group_read") != 'no')
                                <li rel0="AdminGroupController">
                                    <a href="{{ action('Admin\AdminGroupController@index') }}">
                                        <i class="icon-users4"></i> <span>{{ trans('messages.admin_groups') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (
                        Auth::user()->admin->getPermission("sending_domain_read") != 'no'
                        || Auth::user()->admin->getPermission("sending_server_read") != 'no'
                        || Auth::user()->admin->getPermission("bounce_handler_read") != 'no'
                        || Auth::user()->admin->getPermission("fbl_handler_read") != 'no'
                        || Auth::user()->admin->getPermission("email_verification_server_read") != 'no'
                        || Auth::user()->admin->can('read', new \Acelle\Model\SubAccount())
                )
                    <li rel0="BounceHandlerController"
                        rel1="FeedbackLoopHandlerController"
                        rel2="SendingServerController"
                        rel3="SendingDomainController"
                        rel3="SubAccountController"
                    >
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
{{--                            <i class="mdi mdi-send-check"></i> <span>{{ trans('messages.sending') }}</span>--}}
                            <i class="mdi mdi-shuffle" ></i> <span>{{ trans('messages.sending') }}</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            @if (Auth::user()->admin->getPermission("sending_server_read") != 'no')
                                <li rel0="SendingServerController">
                                    <a href="{{ action('Admin\SendingServerController@index') }}">
                                        <i class="mdi mdi-server"></i> <span>{{ trans('messages.sending_severs') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->can('read', new \Acelle\Model\SubAccount()))
                                <li rel0="SubAccountController">
                                    <a href="{{ action('Admin\SubAccountController@index') }}">
                                        <i class="icon-drive"></i> <spa>{{ trans('messages.sub_accounts') }}</spa>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("bounce_handler_read") != 'no')
                                <li rel0="BounceHandlerController">
                                    <a href="{{ action('Admin\BounceHandlerController@index') }}">
                                        <i class="mdi mdi-mixcloud"></i> <span>{{ trans('messages.bounce_handlers') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("fbl_handler_read") != 'no')
                                <li rel0="FeedbackLoopHandlerController">
                                    <a href="{{ action('Admin\FeedbackLoopHandlerController@index') }}">
                                        <i class="mdi mdi-repeat"></i> <span>{{ trans('messages.feedback_loop_handlers') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("sending_domain_read") != 'no')
                            <li rel0="SendingDomainController">
                            <a href="{{ action('Admin\SendingDomainController@index') }}">
                                <i class="icon-earth"></i> <span>{{ trans('messages.sending_domains') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("email_verification_server_read") != 'no')
                                <li rel0="EmailVerificationServerController">
                                    <a href="{{ action('Admin\EmailVerificationServerController@index') }}">
                                        <i class="icon-database-check"></i> <span>{{ trans('messages.email_verification_servers') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <li rel0="TemplateController"
                    rel1="LayoutController"
                    rel2="LanguageController"
                    rel3="SettingController"
                >
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="icon-gear"></i> <span>{{ trans('messages.setting') }}</span>
                    </a>
                    <ul class="sub-menu">
                        @if (
                            Auth::user()->admin->getPermission("setting_general") != 'no' ||
                            Auth::user()->admin->getPermission("setting_sending") != 'no' ||
                            Auth::user()->admin->getPermission("setting_system_urls") != 'no' ||
                            Auth::user()->admin->getPermission("setting_twilio_manager") != 'no' ||
                            Auth::user()->admin->getPermission("setting_background_job") != 'no'
                        )
                            <li rel0="SettingController">
                                <a href="{{ action('Admin\SettingController@index') }}">
                                    <i class="icon-equalizer2"></i> <span>{{ trans('messages.all_settings') }}</span>
                                </a>
                            </li>
                        @endif
                        @if (Auth::user()->admin->getPermission("template_read") != 'no')
                            <li rel0="TemplateController">
                                <a href="{{ action('Admin\TemplateController@index') }}">
                                    <i class="icon-magazine"></i> <span>{{ trans('messages.template_gallery') }}</span>
                                </a>
                            </li>
                        @endif
                        @if (Auth::user()->admin->getPermission("layout_read") != 'no')
                            <li rel0="LayoutController">
                                <a href="{{ action('Admin\LayoutController@index') }}">
                                    <i class="mdi mdi-responsive"></i><span>{{ trans('messages.page_form_layout') }}</span>
                                </a>
                            </li>
                        @endif
                        @if (Auth::user()->admin->getPermission("language_read") != 'no')
                            <li rel0="LanguageController">
                                <a href="{{ action('Admin\LanguageController@index') }}">
                                    <i class="mdi mdi-flag"></i> <span>{{ trans('messages.language') }}</span>
                                </a>
                            </li>
                        @endif
                        <li rel0="PaymentController">
                            <a href="{{ action('Admin\PaymentController@index') }}">
                                <i class="icon-credit-card2"></i> <span>{{ trans('messages.payment_gateways') }}</span>
                            </a>
                        </li>
                    </ul>
                </li>

                @if (
                        Auth::user()->admin->getPermission("report_blacklist") != 'no'
                        || Auth::user()->admin->getPermission("report_tracking_log") != 'no'
                        || Auth::user()->admin->getPermission("report_bounce_log") != 'no'
                        || Auth::user()->admin->getPermission("report_feedback_log") != 'no'
                        || Auth::user()->admin->getPermission("report_open_log") != 'no'
                        || Auth::user()->admin->getPermission("report_click_log") != 'no'
                        || Auth::user()->admin->getPermission("report_unsubscribe_log") != 'no'
                    )
                    <li rel0="TrackingLogController"
                        rel1="OpenLogController"
                        rel2="ClickLogController"
                        rel3="FeedbackLogController"
                        rel4="BlacklistController"
                        rel5="UnsubscribeLogController"
                        rel6="BounceLogController"
                    >
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="icon-file-text2"></i> <span>{{ trans('messages.report') }}</span>
                        </a>
                        <ul class="sub-menu">
                            @if (Auth::user()->admin->getPermission("report_blacklist") != 'no')
                                <li rel0="BlacklistController">
                                    <a href="{{ action('Admin\BlacklistController@index') }}">
                                        <i class="mdi mdi-minus-circle"></i> <span>{{ trans('messages.blacklist') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_tracking_log") != 'no')
                                <li rel0="TrackingLogController">
                                    <a href="{{ action('Admin\TrackingLogController@index') }}">
                                        <i class="icon-file-text2"></i> <span>{{ trans('messages.tracking_log') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_bounce_log") != 'no')
                                <li rel0="BounceLogController">
                                    <a href="{{ action('Admin\BounceLogController@index') }}">
                                        <i class="icon-file-text2"></i> <span>{{ trans('messages.bounce_log') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_feedback_log") != 'no')
                                <li rel0="FeedbackLogController">
                                    <a href="{{ action('Admin\FeedbackLogController@index') }}">
                                        <i class="icon-file-text2"></i> <span>{{ trans('messages.feedback_log') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_open_log") != 'no')
                                <li rel0="OpenLogController">
                                    <a href="{{ action('Admin\OpenLogController@index') }}">
                                        <i class="icon-file-text2"></i> <span>{{ trans('messages.open_log') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_click_log") != 'no')
                                <li rel0="ClickLogController">
                                    <a href="{{ action('Admin\ClickLogController@index') }}">
                                        <i class="icon-file-text2"></i> <span>{{ trans('messages.click_log') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_unsubscribe_log") != 'no')
                                <li rel0="UnsubscribeLogController">
                                    <a href="{{ action('Admin\UnsubscribeLogController@index') }}">
                                        <i class="icon-file-text2"></i> <span>{{ trans('messages.unsubscribe_log') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->