@if ($lead_data->count() > 0)
    <table class="table table-box pml-table"
           current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        @foreach ($lead_data as $key => $lead)
            <tr>
                <td>
                    <div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{$lead->phone}}
						</span>
                        <br />
                        <span class="text-muted">
							{{ trans('messages.phone_number') }}
						</span>
                        <br/>
                        <span class="text-muted">
                            {{ trans('messages.created_at') }}: {{ Tool::formatDateTime($lead->created_at) }}
                        </span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{$lead->email}}
						</span>
                        <br />
                        <span class="text-muted">
							{{ trans('messages.email') }}
						</span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{ ucwords(str_replace('-', ' ', $lead->mailList->name )) }}
						</span>
                        <br/>
                        <span class="text-muted">
							{{ trans('messages.list') }}
						</span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
                            <span class="label label-flat bg-{{ $lead->status }}">
                                {{ trans('messages.' . $lead->status) }}
                            </span>
                        <br/>
                        <span class="text-muted">
							{{ trans('messages.status') }}
						</span>
                    </div>
                </td>
                @if($lead->phone)
                    <td class="stat-fix-size-sm">
                        <div class="single-stat-box pull-right">
                            <a href="{{ action('CallLogsController@log', $lead->phone) }}" type="button" class="modal_link btn bg-grey btn-icon"
                                title="{{trans('messages.call.logs')}}">
                                <i class="icon-phone-outgoing"> {{trans('messages.call.logs')}}</i>
                            </a>
                        </div>
                    </td>
                    <td class="stat-fix-size-sm">
                        <div class="single-stat-box pull-left">
                            <a href="{{ action('SmsLogsController@log', $lead->phone) }}" type="button" class="modal_link btn bg-grey btn-icon"
                                title="{{trans('messages.sms.logs')}}">
                                <i class="icon-envelop5"> {{trans('messages.sms.logs')}}</i>
                            </a>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">
                                <span class="text-bold ml-1">Actions</span>
                                <span class="caret ml-0"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a href="#" onclick="makeRequest('{{ action('TwilioController@createRequest', ['phone' => $lead->phone, 'type' => 'call']) }}')" title="{{trans('messages.call.send')}}">
                                        <i class="icon-phone-outgoing"></i>
                                        {{trans('messages.call.send')}}
                                    </a>
                                </li>
                                <li>
                                    <a href="#" onclick="makeRequest('{{ action('TwilioController@createRequest', ['phone' => $lead->phone, 'type' => 'sms']) }}')" title="{{trans('messages.sms.send')}}">
                                        <i class="icon-envelop4"></i>
                                        {{trans('messages.sms.send')}}
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', ["items" => $lead_data])
    {{ $lead_data->links() }}


@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-address-book2"></i>
        <span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-phone-wave"></i>
        <span class="line-1">
			{{ trans('messages.list_empty_logs_1') }}
		</span>
        <span class="line-2">
			{{ trans('messages.list_empty_line_2') }}
		</span>
    </div>
@endif
