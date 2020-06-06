<div class="dropdown d-inline-block ml-1">
	<button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
			data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >
		<i class="lnr lnr-alarm top-notification-icon"></i>
		<span class="visible-xs-inline-block position-center"></span>
		@if (Auth::user()->admin->notifications()->count() > 0)
			 <span class="badge badge-danger top-notification-alert">{{Auth::user()->admin->notifications()->count()}}</span>
		@endif
	</button>
	<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0" aria-labelledby="page-header-notifications-dropdown">
		<div class="p-2">
			<div class="row align-items-center">
				<div class="col text-center">
					<h5 class="m-0"> {{ trans('messages.activity_log') }}</h5>
				</div>
			</div>
		</div>
		<div data-simplebar style="max-height: 230px;" >
			@if (Auth::user()->admin->notifications()->count() == 0)
				<a href="" class="text-reset notification-item">
					<div class="media">
						<div class="avatar-xs mr-3">
							<span class="avatar-title border-info rounded-circle ">
								<i class="mdi mdi-message"></i>
							</span>
						</div>
						<div class="media-body">
							<div class="text-muted">
								<p class="mb-1">{{ trans('messages.no_notifications') }}</p>
							</div>
						</div>
					</div>
				</a>
			@endif
			@foreach (Auth::user()->admin->notifications()->take(20)->get() as $notification)
				<a href="" class="text-reset notification-item">
					<div class="media">
						<div class="avatar-xs mr-3">
							@if ($notification->level == \Acelle\Model\Notification::LEVEL_WARNING)
								<span class="avatar-title border-warning rounded-circle ">
									<i class="mdi mdi-message"></i>
								</span>
							@elseif ( false &&$notification->level == \Acelle\Model\Notification::LEVEL_ERROR)
								<span class="avatar-title border-danger rounded-circle ">
									<i class="mdi mdi-message"></i>
								</span>
							@else
								<span class="avatar-title border-info rounded-circle ">
									<i class="mdi mdi-message"></i>
								</span>
							@endif
						</div>
						<div class="media-body">
							<h6 class="mt-0 mb-1">{{ $notification->title }} <span style="float: right"> {{ $notification->created_at->diffForHumans() }}</span></h6>
							<div class="text-muted">
								<p class="mb-1">{{ $notification->message }}</p>
							</div>
						</div>
					</div>
				</a>
			@endforeach
		</div>
		<div class="p-2 border-top">
			<a class="btn btn-sm btn-link font-size-14 btn-block text-center" href="{{ action("Admin\NotificationController@index") }}" title="{{ trans('messages.all_notifications') }}">
				{{ trans('messages.all_notifications') }}
			</a>
		</div>
	</div>
</div>