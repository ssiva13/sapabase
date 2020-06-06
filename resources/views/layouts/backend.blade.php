<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>

	@include('layouts._favicon')

	@include('layouts._head')

	@include('layouts._css')

	@include('layouts._js')

	<script>
		$.cookie('last_language_code', '{{ Auth::user()->admin->getLanguageCode() }}');
	</script>
	<!-- Custom langue -->
	<script>
		var LANG_CODE = 'en-US';
	</script>

	@if (Auth::user()->admin->getLanguageCodeFull())
		<script type="text/javascript" src="{{ URL::asset('assets/datepicker/i18n/datepicker.' . Auth::user()->admin->getLanguageCodeFull() . '.js') }}"></script>
		<script>
			LANG_CODE = '{{ Auth::user()->admin->getLanguageCodeFull() }}';
		</script>
	@endif

</head>

<body class="navbar-top  color-scheme-{{ Auth::user()->admin->getColorScheme() }}" data-sidebar="dark">
	<div id="layout-wrapper">
		@include('layouts.partials.header')

		@include('layouts.partials.sidebar')


		<!-- /page header -->
		<div class="main-content">
			<!-- Page container -->
				<div class="page-content">
					<div class="container-fluid">
						<!-- display flash message -->
						@include('common.errors')
						<!-- main inner content -->
						@yield('content')

					</div>
					<!-- /main content -->
				</div>
			<!-- end main content-->
		</div>
	</div>
	<!-- END layout-wrapper -->
	@include('layouts.partials.rightbar')

	<!-- /page container -->
	@include("layouts._modals")
	{!! \Acelle\Model\Setting::get('custom_script') !!}

	@yield('page_script')
</body>
</html>
