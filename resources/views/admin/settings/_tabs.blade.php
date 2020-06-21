<div class="topnav mb-3 text-bold">
	<nav class="navbar navbar-inverse navbar-expand-lg topnav-menu" style="">

		<div class="collapse navbar-collapse" id="topnav-menu-content">
			<ul class="navbar-nav nav nav-tabs nav-tabs-top w-100">
				@if (Auth::user()->admin->getPermission("setting_general") == 'yes')
					<li class="nav-item {{ $action == "general" ? "active" : "" }} text-semibold">
						<a class="nav-link" href="{{ action('Admin\SettingController@general') }}">
							<i class="icon-equalizer2"></i> {{ trans('messages.general') }}
						</a>
					</li>
				@endif
				@if (Auth::user()->admin->getPermission("setting_general") == 'yes')
					<li class="nav-item {{ $action == "mailer" ? "active" : "" }} text-semibold">
						<a class="nav-link" href="{{ action('Admin\SettingController@mailer') }}">
							<i class="icon-envelop"></i> {{ trans('messages.system_email') }}
						</a>
					</li>
				@endif
				@if (Auth::user()->admin->getPermission("setting_sending") == 'yes' && false)
					<li class="nav-item {{ $action == "sending" ? "active" : "" }} text-semibold">
						<a class="nav-link" href="{{ action('Admin\SettingController@sending') }}">
							<i class="icon-paperplane"></i> {{ trans('messages.sending') }}
						</a>
					</li>
				@endif
				@if (Auth::user()->admin->getPermission("setting_system_urls") == 'yes')
					<li class="nav-item {{ $action == "urls" ? "active" : "" }} text-semibold">
						<a class="nav-link" href="{{ action('Admin\SettingController@urls') }}">
							<i class="icon-link"></i> {{ trans('messages.system_urls') }}
						</a>
					</li>
				@endif
				@if (Auth::user()->admin->getPermission("setting_background_job") == 'yes')
					<li class="nav-item {{ $action == "cronjob" ? "active" : "" }} text-semibold">
						<a class="nav-link" href="{{ action('Admin\SettingController@cronjob') }}">
							<i class="icon-alarm"></i> {{ trans('messages.background_job') }}
						</a>
					</li>
				@endif
				@if (Auth::user()->admin->getPermission("setting_general") == 'yes')
					<li class="nav-item {{ $action == "license" ? "active" : "" }} text-semibold">
						<a class="nav-link" href="{{ action('Admin\SettingController@license') }}">
							<i class="icon-key"></i> {{ trans('messages.license_tab') }}
						</a>
					</li>
				@endif
				@if (Auth::user()->admin->getPermission("setting_upgrade_manager") == 'yes')
					<li class="nav-item {{ $action == "upgrade" ? "active" : "" }} text-semibold">
						<a class="nav-link" href="{{ action('Admin\SettingController@upgrade') }}">
							<i class="icon-wrench"></i> {{ trans('messages.upgrade.title.upgrade') }}
						</a>
					</li>
				@endif

			</ul>
		</div>
	</nav>
</div>
