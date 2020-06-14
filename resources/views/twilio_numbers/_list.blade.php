@if ($twilio_numbers->count() > 0)
	<table class="table table-box pml-table"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		@foreach ($twilio_numbers as $key => $item)
			<tr>
				<td width="1%">
					<div class="text-nowrap">
						<div class="checkbox inline">
							<label>
								<input type="checkbox" class="node styled" custom-order="{{ $item->number }}" name="ids[]" value="{{ $item->uid }}" />
							</label>
						</div>
						@if (request()->sort_order == 'custom_order' && empty(request()->keyword))
							<i data-action="move" class="icon icon-more2 list-drag-button"></i>
						@endif
					</div>
				</td>
				<td>
					<span class="text-muted">{{ trans('messages.created_at') }}: {{ Tool::formatDateTime($item->created_at) }}</span>
				</td>
				<td class="stat-fix-size-sm">
					<div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{$item->number}}
						</span>
						<br />
						<span class="text-muted">
							{{ trans('messages.twilio.phone_number') }}
						</span>
					</div>
				</td>
				<td class="stat-fix-size-sm">
					<div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{trans('messages.'.$item->inbound_recording)}}
						</span>
						<br/>
						<span class="text-muted">
							{{ trans('messages.twilio.inbound') }}
						</span>
					</div>
				</td>
				<td class="stat-fix-size-sm">
					<div class="single-stat-box pull-left">
						<span class="no-margin stat-num">
							{{trans('messages.'.$item->outbound_recording)}}
						</span>
						<br/>
						<span class="text-muted">
							{{ trans('messages.twilio.outbound') }}
						</span>
					</div>
				</td>
				<td class="stat-fix-size-sm">
					<div class="single-stat-box pull-left">
						@if($item->status != 'active')
							<a title="{{trans('messages.activate')}}" href="{{ action('TwilioController@activate', ['id' => $item->id]) }}">
						@else
							<a title="{{trans('messages.deactivate')}}" href="{{ action('TwilioController@deactivate', ['id' =>$item->id]) }}">
						@endif
							<span class="label label-flat bg-{{$item->status}}">
								{{$item->status}}
							</span>
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
								<a href="{{ action('TwilioController@statitics', [$item->uid, $item->id]) }}">
									<i class="icon-phone-incoming"></i>{{ trans('messages.twilio.overview_statistics') }}
								</a>
							</li>
							<li>
								@if($item->status != 'active')
									<a title="{{trans('messages.activate')}}" href="{{ action('TwilioController@activate', ['id' => $item->id]) }}">
										<i class="icon-checkbox-unchecked"></i> {{ trans('messages.activate') }}
									</a>
								@else
									<a title="{{trans('messages.deactivate')}}" href="{{ action('TwilioController@deactivate', ['id' =>$item->id]) }}">
										<i class="icon-checkbox-checked"></i> {{ trans('messages.deactivate') }}
									</a>
								@endif
							</li>
						</ul>
					</div>
				</td>
			</tr>
		@endforeach
	</table>
	@include('elements/_per_page_select', ["items" => $twilio_numbers])
	{{ $twilio_numbers->links() }}

	
@elseif (!empty(request()->keyword))
	<div class="empty-list">
		<i class="icon-address-book2"></i>
		<span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
	</div>
@else
	<div class="empty-list">
		<i class="icon-address-book2"></i>
		<span class="line-1">
			{{ trans('messages.list_empty_line_1') }}
		</span>
		<span class="line-2">
			{{ trans('messages.list_empty_line_2') }}
		</span>
	</div>
@endif
