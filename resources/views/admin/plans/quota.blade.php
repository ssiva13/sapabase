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
        </ul>
        <h1 class="mc-h1">
            <span class="text-semibold">{{ $plan->name }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <div class="row">
        @component('admin.common-components.settings_breadcrumb')
            @slot('title') {{ trans('messages.plans') }}  @endslot
            @slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
            @slot('li2') Admin  @endslot
            @slot('li3') {{ trans('messages.plans') }} @endslot
            @slot('li4') {{ trans('messages.plan.resources') }} @endslot
        @endcomponent
    </div>
    
    @include('admin.plans._menu')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title text-muted text-center"><strong>{{ trans('messages.plan.resources') }}</strong></h4>
        <p>{{ trans('messages.plan.resource.intro') }}</p>
            <div class="mc_section">
                <form enctype="multipart/form-data" action="{{ action('Admin\PlanController@save', $plan->uid) }}" method="POST" class="form-validate-jqueryx">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-6">
                            @include('helpers.form_control', [
                                'type' => 'text',
                                'class' => 'numeric',
                                'name' => 'plan[options][email_max]',
                                'value' => $options['email_max'],
                                'label' => trans('messages.max_emails'),
                                'help_class' => 'plan',
                                'options' => ['true', 'false'],
                                'rules' => $plan->resourcesRules(),
                                'unlimited_check' => true,
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('helpers.form_control', [
                                'type' => 'text',
                                'class' => 'numeric',
                                'name' => 'plan[options][list_max]',
                                'value' => $options['list_max'],
                                'label' => trans('messages.max_lists'),
                                'help_class' => 'plan',
                                'options' => ['true', 'false'],
                                'rules' => $plan->resourcesRules(),
                                'unlimited_check' => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                                @include('helpers.form_control', [
                                    'type' => 'text',
                                    'class' => 'numeric',
                                    'name' => 'plan[options][subscriber_max]',
                                    'value' => $options['subscriber_max'],
                                    'label' => trans('messages.max_subscribers'),
                                    'help_class' => 'plan',
                                    'options' => ['true', 'false'],
                                    'rules' => $plan->resourcesRules(),
                                    'unlimited_check' => true,
                                ])
                        </div>
                        <div class="col-md-6">
                            @include('helpers.form_control', [
                                'type' => 'text',
                                'class' => 'numeric',
                                'name' => 'plan[options][subscriber_per_list_max]',
                                'value' => $options['subscriber_per_list_max'],
                                'label' => trans('messages.max_subscribers_per_list'),
                                'help_class' => 'plan',
                                'rules' => $plan->resourcesRules(),
                                'unlimited_check' => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            @include('helpers.form_control', ['type' => 'text',
                                'class' => 'numeric',
                                'name' => 'plan[options][campaign_max]',
                                'value' => $options['campaign_max'],
                                'label' => trans('messages.max_campaigns'),
                                'help_class' => 'plan',
                                'rules' => $plan->resourcesRules(),
                                'unlimited_check' => true,
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('helpers.form_control', [
                                'type' => 'text',
                                'class' => 'numeric',
                                'name' => 'plan[options][segment_per_list_max]',
                                'value' => $options['segment_per_list_max'],
                                'label' => trans('messages.segment_per_list_max'),
                                'help_class' => 'plan',
                                'rules' => $plan->resourcesRules(),
                                'unlimited_check' => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            @include('helpers.form_control', ['type' => 'text',
                                'class' => 'numeric',
                                'name' => 'plan[options][automation_max]',
                                'value' => $options['automation_max'],
                                'label' => trans('messages.max_automations'),
                                'help_class' => 'plan',
                                'rules' => $plan->resourcesRules(),
                                'unlimited_check' => true,
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('helpers.form_control', [
                                'type' => 'text',
                                'class' => 'numeric',
                                'name' => 'plan[options][max_size_upload_total]',
                                'value' => $options['max_size_upload_total'],
                                'label' => trans('messages.max_size_upload_total'),
                                'help_class' => 'plan',
                                'rules' => $plan->resourcesRules(),
                                'unlimited_check' => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            @include('helpers.form_control', [
                                'type' => 'text',
                                'class' => 'numeric',
                                'name' => 'plan[options][max_file_size_upload]',
                                'value' => $options['max_file_size_upload'],
                                'label' => trans('messages.max_file_size_upload'),
                                'help_class' => 'plan',
                                'rules' => $plan->resourcesRules(),
                                'unlimited_check' => true,
                            ])
                        </div>
                        <div class="col-md-6 mt-2">
                                @include('helpers.form_control', [
                                    'type' => 'checkbox',
                                    'class' => '',
                                    'name' => 'plan[options][unsubscribe_url_required]',
                                    'value' => $options['unsubscribe_url_required'],
                                    'label' => trans('messages.unsubscribe_url_required'),
                                    'options' => ['no','yes'],
                                    'help_class' => 'plan',
                                    'rules' => $plan->resourcesRules()
                                ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mt-2">
                                @include('helpers.form_control', ['type' => 'checkbox',
                                    'class' => '',
                                    'name' => 'plan[options][list_import]',
                                    'value' => $options['list_import'],
                                    'label' => trans('messages.can_import_list'),
                                    'options' => ['no','yes'],
                                    'help_class' => 'plan',
                                    'rules' => $plan->resourcesRules()
                                ])
                        </div>
                        <div class="col-md-6 mt-2">
                                @include('helpers.form_control', ['type' => 'checkbox',
                                    'class' => '',
                                    'name' => 'plan[options][access_when_offline]',
                                    'value' => $options['access_when_offline'],
                                    'label' => trans('messages.access_when_offline'),
                                    'options' => ['no','yes'],
                                    'help_class' => 'plan',
                                    'rules' => $plan->resourcesRules()
                                ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mt-2">
                                @include('helpers.form_control', ['type' => 'checkbox',
                                    'class' => '',
                                    'name' => 'plan[options][api_access]',
                                    'value' => $options['api_access'],
                                    'label' => trans('messages.can_use_api'),
                                    'options' => ['no','yes'],
                                    'help_class' => 'plan',
                                    'rules' => $plan->resourcesRules()
                                ])
                        </div>
                        <div class="col-md-6 mt-2">
                                @include('helpers.form_control', ['type' => 'checkbox',
                                    'class' => '',
                                    'name' => 'plan[options][list_export]',
                                    'value' => $options['list_export'],
                                    'label' => trans('messages.can_export_list'),
                                    'options' => ['no','yes'],
                                    'help_class' => 'plan',
                                    'rules' => $plan->resourcesRules()
                                ])
                        </div>
                    </div>
                    <div class="row"></div>
                    <div class="row mt-4">
                        <div class="col-xl-6 ml-4">
                            <button class="btn btn-primary mr-10 m4-4">{{ trans('messages.save') }}</button>
                            <a href="{{ action('Admin\PlanController@index') }}" type="button" class="btn btn-danger">
                                {{ trans('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
