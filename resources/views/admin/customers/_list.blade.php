<div class="row w-100 m-3" >
	<div class="col-xl-12">
		@if ($customers->count() > 0)
			<div class="table-responsive mb-0" data-pattern="priority-columns">
				<table id="tech-companies-1" class="table table-striped" current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}">
					<tbody>
					@foreach ($customers as $key => $item)
							<tr>
								<th>
									<img src="{{ action('CustomerController@avatar', $item->uid) }}" alt="" width="80" class="avatar-xs rounded-circle mr-2" />
								</th>
								<td>
									<h5 class="no-margin text-bold">
										<a class="kq_search" href="{{ action('Admin\CustomerController@edit', $item->uid) }}">{{ $item->displayName() }}</a>
									</h5>
									<span class="text-muted kq_search">{{ $item->user->email }}</span>
									@can('readAll', $item)
										<br />
											@include ('admin.modules.admin_line', ['admin' => $item->admin])
									@endcan
									<br />
									<span class="text-muted2">{{ trans('messages.created_at') }}: {{ Tool::formatDateTime($item->created_at) }}</span>
								</td>
								<td>
									@if ($item->currentPlanName())
										<h5 class="no-margin">
											<span><i class="icon-clipboard2"></i> {{ $item->currentPlanName() }}</span>
										</h5>
										<span class="text-muted2">{{ trans('messages.current_plan') }}</span>
									@else
										<span class="text-muted2">{{ trans('messages.customer.no_active_subscription') }}</span>
									@endif
								</td>
								<td class="stat-fix-size">
									@if (is_object($item->subscription))
										<div class="single-stat-box pull-left ml-20">
											<span class="no-margin text-teal-800 stat-num">{{ $item->displaySendingQuotaUsage() }}</span>
											<div class="progress progress-xxs">
												<div class="progress-bar progress-bar-info" style="width: {{ $item->getSendingQuotaUsagePercentage() }}%">
												</div>
											</div>
											<span class="text-muted">
				                                <strong>{{ \Acelle\Library\Tool::format_number($item->getSendingQuotaUsage()) }}/{{ ($item->getSendingQuota() == -1) ? '∞' : \Acelle\Library\Tool::format_number($item->getSendingQuota()) }}</strong>
				                                <div class="text-nowrap">{{ trans('messages.sending_credits_used') }}</div>
				                            </span>
										</div>
										<div class="single-stat-box pull-left ml-20">
											<span class="no-margin text-teal-800 stat-num">{{ $item->displaySubscribersUsage() }}</span>
											<div class="progress progress-xxs">
												<div class="progress-bar progress-bar-info" style="width: {{ $item->readCache('SubscriberUsage') }}%">
												</div>
											</div>
											<span class="text-muted"><strong>{{ number_with_delimiter($item->readCache('SubscriberCount')) }}/{{ number_with_delimiter($item->maxSubscribers()) }}</strong>
											<br /> {{ trans('messages.subscribers') }}</span>
										</div>
									@endif
								</td>
								<td>
									<span class="text-muted2 list-status pull-left">
										<span class="label label-flat bg-{{ $item->status }}">{{ trans('messages.user_status_' . $item->status) }}</span>
									</span>
								</td>
								<td class="text-right">

								</td>
								<td class="text-right">
									@can('loginAs', $item)
										<a href="{{ action('Admin\CustomerController@loginAs', $item->uid) }}" data-popup="tooltip" title="{{ trans('messages.login_as_this_customer') }}" type="button" class="btn btn-secondary"><i class="icon icon-shuffle"></i></a>
									@endcan
									@can('update', $item)
										<a href="{{ action('Admin\CustomerController@edit', $item->uid) }}" data-popup="tooltip" title="{{ trans('messages.edit') }}" type="button" class="btn btn-info btn-icon"><i class="icon icon-pencil pr-0 mr-0"></i></a>
									@endcan
									@if (Auth::user()->can('delete', $item) ||
										Auth::user()->can('enable', $item) ||
										Auth::user()->can('disable', $item) ||
										Auth::user()->can('assignPlan', $item)
									)
										<div class="btn-group">
											<button type="button" class="btn dropdown-toggle btn-primary" data-toggle="dropdown"><span class="caret ml-0"></span></button>
											<div data-simplebar style="max-height: 230px;" class="dropdown-menu dropdown-menu-right" >
												<div class="media-body">
														<ol class="activity-feed mb-0">
															@can('assignPlan', $item)
																<li class="dropdown-item">
																	<a href="{{ action('Admin\CustomerController@assignPlan', [ "uid" => $item->uid,]) }}" class="dropdown-item assign_plan_button">
																	<div class="text-muted">
																		<p class="mb-1">
																			<i class="icon-clipboard2"></i>{{ trans('messages.customer.assign_plan') }}
																		</p>
																	</div>
																	</a>
																</li>
															@endcan
															@can('enable', $item)
																<li class="dropdown-item">
																	<a link-confirm="{{ trans('messages.enable_customers_confirm') }}" class="dropdown-item"
																	   href="{{ action('Admin\CustomerController@enable', ["uids" => $item->uid]) }}">
																		<div class="text-muted">
																			<p class="mb-1">
																				<i class="icon-checkbox-checked2"></i> {{ trans('messages.enable') }}
																			</p>
																		</div>
																	</a>
																</li>
															@endcan
															@can('disable', $item)
																<li class="dropdown-item">
																	<a link-confirm="{{ trans('messages.disable_customers_confirm') }}" class="dropdown-item"
																	   href="{{ action('Admin\CustomerController@disable', ["uids" => $item->uid]) }}">
																		<div class="text-muted">
																			<p class="mb-1">
																				<i class="icon-checkbox-unchecked2"></i> {{ trans('messages.disable') }}
																			</p>
																		</div>
																	</a>
																</li>
															@endcan
															@can('read', $item)
																<li class="dropdown-item">
																	<a href="{{ action('Admin\CustomerController@subscriptions', $item->uid) }}" class="dropdown-item">
																		<div class="text-muted">
																			<p class="mb-1">
																				<i class="icon-quill4"></i> {{ trans('messages.subscriptions') }}
																			</p>
																		</div>
																	</a>
																</li>
															@endcan
															<li class="dropdown-item">
																<a delete-confirm="{{ trans('messages.delete_users_confirm') }}" class="dropdown-item"
																   href="{{ action('Admin\CustomerController@delete', ['uids' => $item->uid]) }}">
																	<div class="text-muted">
																		<p class="mb-1">
																			<i class="icon-trash"></i> {{ trans('messages.delete') }}
																		</p>
																	</div>
																</a>
															</li>
														</ol>
													</div>
											</div>
										</div>
									@endcan
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			@include('elements/_per_page_select', ["items" => $customers])
			{{ $customers->links() }}

			<script>
					$(function() {
						$('.assign_plan_button').click(function(e) {
							e.preventDefault();

							var src = $(this).attr('href');
							assignPlanModal.load(src);
						});
					});
				</script>

		@elseif (!empty(request()->keyword))
			<div class="empty-list">
				<i class="icon-users"></i>
				<span class="line-1">
					{{ trans('messages.no_search_result') }}
				</span>
			</div>
		@else
			<div class="empty-list">
				<i class="icon-users"></i>
				<span class="line-1">
					{{ trans('messages.customer_empty_line_1') }}
				</span>
			</div>
		@endif

	</div>
</div>
