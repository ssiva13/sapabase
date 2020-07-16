@if ($sms_logs->count() > 0)
    <table class="table table-box pml-table"
           current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        @foreach ($sms_logs as $key => $sms_log)
            <tr>
                <td>
                    <div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{$sms_log->to}}
						</span>
                        <br />
                        <span class="text-muted">
							{{ trans('messages.to_number') }}
						</span>
                        <br/>
                        <span class="text-muted">
                            {{ trans('messages.created_at') }}: {{ Tool::formatDateTime($sms_log->created_at) }}
                        </span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{$sms_log->from}}
						</span>
                        <br />
                        <span class="text-muted">
							{{ trans('messages.from_number') }}
						</span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{ ucwords(str_replace('-', ' ', $sms_log->direction )) }}
						</span>
                        <br/>
                        <span class="text-muted">
							{{ trans('messages.direction') }}
						</span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
                        <span class="no-margin stat-num">
							{{ ucwords(str_replace('-', ' ', $sms_log->status )) }}
						</span>
                        <br/>
                        <span class="text-muted">
							{{ trans('messages.status') }}
						</span>
                        <br/>
                        <span class="text-muted">
							{{ trans('messages.sent_at') }}: {{ Tool::formatTwilioDateTime($sms_log->date_sent) }}
						</span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
                        <span class="text-black-50 text-capitalize">
                        {{ substr($sms_log->body, 0, 200) }} ...
						</span>
                        <br/>
                        <br/>
                        <span class="text-muted">
                            {{ trans('messages.automation.sms.body') }}
						</span>
                    </div>
                </td>

            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', ["items" => $sms_logs])
    {{ $sms_logs->links() }}


@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-address-book2"></i>
        <span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-envelop5"></i>
        <span class="line-1">
			{{ trans('messages.list_empty_logs_1') }}
		</span>
        <span class="line-2">
			{{ trans('messages.list_empty_line_2') }}
		</span>
    </div>
@endif
