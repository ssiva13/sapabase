
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">{{ trans('messages.call.logs') }}</h5>
        <button type="button" class="close bg-gray text-bold text-dark" data-dismiss="modal" aria-hidden="true">×</button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-20">
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-12">
                            <div class="sub-section">
                                <div class="row">
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <p>{{ trans('messages.call.logs.intro') }}</p>
                                        <ul class="nav nav-tabs">
                                            <li class="active">
                                                <a data-toggle="tab" href="#call_logs">
                                                    {{ trans('messages.call.logs') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a data-toggle="tab" href="#sms_logs">
                                                    {{ trans('messages.sms.logs') }}
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div id="call_logs" class="tab-pane fade in active">
                                                <table class="table table-box pml-table table-log mt-10">
                                                    <tr>
                                                        <th>{{ trans('messages.from_number') }}</th>
                                                        <th>{{ trans('messages.to_number') }}</th>
                                                        <th>{{ trans('messages.direction') }}</th>
                                                        <th>{{ trans('messages.price') }}</th>
                                                        <th>{{ trans('messages.status') }}</th>
                                                        <th>{{ trans('messages.duration') }}</th>
                                                    </tr>
                                                    @forelse ($call_log as $key => $call)
                                                        <tr>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ $call['from'] }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ $call['to'] }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ ucwords(str_replace('-', ' ', $call['direction'])) }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ $call['price'] }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ ucwords(str_replace('-', ' ', $call['status'])) }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ $call['duration'] }}
                                                                    </span>
                                                            </td>

                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td class="text-center" colspan="5">
                                                                {{ trans('messages.subscription.logs.empty') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </table>
                                            </div>

                                            <div id="sms_logs" class="tab-pane fade in">
                                                <table class="table table-box pml-table table-log mt-10">
                                                    <tr>
                                                        <th>{{ trans('messages.from_number') }}</th>
                                                        <th>{{ trans('messages.to_number') }}</th>
                                                        <th>{{ trans('messages.direction') }}</th>
                                                        <th>{{ trans('messages.price') }}</th>
                                                        <th>{{ trans('messages.status') }}</th>
                                                    </tr>
                                                    @forelse ($sms_log as $key => $sms)
                                                        <tr>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ $sms['from'] }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ $sms['to'] }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ ucwords(str_replace('-', ' ', $call['direction'])) }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ $sms['price'] }}
                                                                    </span>
                                                            </td>
                                                            <td>
                                                                    <span class="no-margin kq_search">
                                                                        {{ ucwords(str_replace('-', ' ', $sms['status'])) }}
                                                                    </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td class="text-center" colspan="5">
                                                                {{ trans('messages.subscription.logs.empty') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse
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
    </div>
</div>
