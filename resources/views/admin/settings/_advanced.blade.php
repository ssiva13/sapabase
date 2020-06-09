{{--class="inline-editable editable editable-click"--}}
<div class="card">
    <div class="card-body">
        <div class="sub-section">
            <div class="row">
                <div class="col-md-12">
                    <div class="scrollbar-boxx dim-box">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title text-muted text-center"><strong>{{ trans('messages.advanced_settings') }}</strong></h4>
                                <div class="ui-sortable">
                                    <div class="pml-table-container">
                                        <table class="table table-editable table-nowrap">
                                            <thead>
                                                <tr>
                                                    <th class="trans-upcase text-semibold">{{ trans('messages.setting.name') }}</th>
                                                    <th class="trans-upcase text-semibold">{{ trans('messages.setting.value') }}</th>
                                                    <th class="trans-upcase text-semibold"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                @foreach ($settings as $name => $setting)
                                                    <tr>
                                                        <td width="30%">
                                                            <span class="list-status pull-left">
                                                                <span class="text-semibold">{{ $name }}</span>
                                                            </span>
                                                        </td>
                                                        <td width="20%">
                                                            <a href="#" data-type="text"
                                                              data-pk="1"
                                                              data-setting_val="{{ $setting['value'] }}"
                                                              data-setting_name="{{ ucwords(str_replace("_"," ", $name) )}}"
                                                              data-url="{{ action('Admin\SettingController@advancedUpdate', ['name' => $name]) }}"
                                                              data-title="{{ trans('messages.setting.enter', ['name' => $name]) }}"
                                                               class="showModalButton editable editable-click"
                                                            >
                                                                {{ $setting['value'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-muted2">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>