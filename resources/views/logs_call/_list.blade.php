@if ($call_logs->count() > 0)
    <table class="table table-box pml-table"
           current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        @foreach ($call_logs as $key => $call_log)
            <tr>
                <td>
                    <div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{$call_log->to}}
						</span>
                        <br />
                        <span class="text-muted">
							{{ trans('messages.to_number') }}
						</span>
                        <br/>
                        <span class="text-muted">
                            {{ trans('messages.created_at') }}: {{ Tool::formatDateTime($call_log->created_at) }}
                        </span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{$call_log->from}}
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
							{{ ucwords(str_replace('-', ' ', $call_log->direction )) }}
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
							{{ ucwords(str_replace('-', ' ', $call_log->status )) }}
						</span>
                        <br/>
                        <span class="text-muted">
							{{ trans('messages.status') }}
						</span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
                        <span class="no-margin stat-num">
							{{ ucwords(str_replace('-', ' ', $call_log->duration )) }} seconds
						</span>
                        <br/>
                        <span class="text-muted">
							{{ trans('messages.duration') }}
						</span>
                    </div>
                </td>
                <td class="stat-fix-size-sm">
                    <div class="single-stat-box pull-left">
                        <span class="text-muted ">
							{{ trans('messages.start_at') }}: {{ Tool::formatTwilioDateTime($call_log->start_time) }}
						</span>
                        <br/>
                        <span class="text-muted">
							{{ trans('messages.end') }}: {{ Tool::formatTwilioDateTime($call_log->start_time) }}
						</span>
                    </div>
                </td>

            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', ["items" => $call_logs])
    {{ $call_logs->links() }}


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
