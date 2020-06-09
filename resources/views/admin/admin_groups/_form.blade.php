<div class="sub_section">
    <div class="row">
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'text', 'name' => 'name', 'value' => $group->name, 'help_class' => 'admin_group', 'rules' => Acelle\Model\AdminGroup::$rules])
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="card-title text-muted text-center"><strong><i class="icon-gear"></i> {{ trans('messages.admin_group_options') }}</strong></h2>
        <div class="tabbable">
            <ul class="nav nav-tabs nav-tabs-top">
                <li class="active text-semibold">
                    <a href="#top-tab1" data-toggle="tab">
                        <i class="icon-user"></i> {{ trans('messages.permissions') }}
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="top-tab1">
                    <div class="row ">
                        @foreach (Acelle\Model\AdminGroup::allPermissions() as $key => $items)
                            <div class="card card-body h-100 py-2 col-sm-6">
                                    <h2 class="card-title text-muted text-teal-800 text-left"><strong>{{ trans('messages.' . $key) }}</strong></h2>
                                        <div class="row w-100">
                                            @foreach ($items as $act => $ops)
                                                <div class="h-100 col-xl-6">
                                                    @if (count($ops["options"]) > 2)
                                                        @include('helpers.form_control', [
                                                            'type' => 'select',
                                                            'class' => 'numeric',
                                                            'name' => 'permissions[' . $key . "_" . $act .']',
                                                            'value' => $permissions[$key . "_" . $act],
                                                            'label' => trans('messages.' . $act),
                                                            'options' => $ops["options"],
                                                            'help_class' => 'admin_group',
                                                            'rules' => Acelle\Model\AdminGroup::rules()
                                                        ])
                                                    @else
                                                        <div class="checkbox-box-group" >
                                                            @include('helpers.form_control', [
                                                                'type' => 'checkbox',
                                                                'class' => 'numeric',
                                                                'name' => 'permissions[' . $key . "_" . $act .']',
                                                                'value' => $permissions[$key . "_" . $act],
                                                                'label' => trans('messages.' . $act),
                                                                'options' => ['no','yes'],
                                                                'help_class' => 'admin_group',
                                                                'rules' => Acelle\Model\AdminGroup::rules()
                                                            ])
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

