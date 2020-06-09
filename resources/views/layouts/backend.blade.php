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

<body class="navbar-top  color-scheme-"
	  data-sidebar="{{ Auth::user()->admin->getSideBarScheme() }}"
	  data-topbar="{{ Auth::user()->admin->getTopBarScheme() }}"
>
	@include('layouts.partials.preloader')

	<div id="layout-wrapper">
		@include('layouts.partials.header')

		@include('layouts.partials.sidebar')

		<div class="main-content">

			{{--<div class="page-content">--}}
				<div class="container-fluid">
					<!-- display flash message -->
					@include('common.errors')
					<!-- main inner content -->
					@yield('content')

				</div>
				<!-- end main content-->
			{{--</div>--}}

			<!-- END layout-wrapper -->
		</div>
		<!-- END layout-wrapper -->
		@include('layouts.partials.rightbar')

		<!-- /page container -->
		@include("layouts._modals")
		{!! \Acelle\Model\Setting::get('custom_script') !!}
	</div>
	@yield('page_script')
	@yield('table_script')

</body>
</html>
