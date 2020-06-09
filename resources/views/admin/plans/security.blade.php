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
            @slot('li4') {{ trans('messages.plan.speed_limit') }} @endslot
        @endcomponent
    </div>
    
    @include('admin.plans._menu')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-muted text-center"><strong>{{ trans('messages.plan.speed_limit') }}</strong></h4>
            <p>{{ trans('messages.plan.speed_limit.intro') }}</p>
            <form enctype="multipart/form-data" action="{{ action('Admin\PlanController@save', $plan->uid) }}" method="POST" class="form-validate-jqueryx">
                {{ csrf_field() }}

                <div class="row">
                    <div class="col-md-12">
                        <div class="mc_section">
                            <div class="select-custom" data-url="{{ action('Admin\PlanController@sendingLimit', $plan->uid) }}">
                                @include ('admin.plans._sending_limit')
                            </div>
                            <p>{{ trans('messages.plan.process_limit.intro') }}</p>
                            <div class="boxing">
                                <div class="row">
                                    <div class="col-md-12">
                                        @include('helpers.form_control', ['type' => 'select',
                                            'name' => 'plan[options][max_process]',
                                            'value' => $plan->getOption('max_process'),
                                            'label' => trans('messages.max_number_of_processes'),
                                            'options' => \Acelle\Model\Plan::multiProcessSelectOptions(),
                                            'help_class' => 'plan',
                                            'rules' => $plan->validationRules()['general'],
                                        ])
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary mr-10">{{ trans('messages.save') }}</button>
                            <a href="{{ action('Admin\PlanController@index') }}" type="button" class="btn btn-mc_inline">
                                {{ trans('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
        
@endsection
