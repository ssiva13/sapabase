
        <header id="page-topbar" style="">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a class="logo logo-light" href="{{ action('Admin\HomeController@index') }}">
                            @if (\Acelle\Model\Setting::get('site_logo_small'))
                                <span class="logo-sm">
                                    <img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="">
                                </span>
                            @else
                                <span class="logo-lg">
                                    <img src="{{ URL::asset('images/default_site_logo_small_' . (Auth::user()->admin->getColorScheme() == "white" ? "dark" : "light") . '.png') }}" alt="">
                                </span>
                            @endif
                        </a>
                    </div>
                    <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                        <i class="mdi mdi-backburger"></i>
                    </button>

                </div>

                <div class="ml-4 d-flex">

                     <!-- App Search-->
                     <form class="app-search d-none d-lg-block">
                        <div class="position-relative">
                            <input type="text" class="form-control" placeholder="Search...">
                            <span class="fa fa-search"></span>
                        </div>
                    </form>

                    <div class="dropdown d-inline-block d-lg-none ml-2">
                        <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0"
                            aria-labelledby="page-header-search-dropdown">
                    
                            <form class="p-3">
                                <div class="form-group m-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="dropdown d-none d-lg-inline-block">
                        <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                            <i class="mdi mdi-fullscreen font-size-24"></i>
                        </button>
                    </div>

                    @include('layouts._top_notifications')

                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="{{ action('AdminController@avatar', Auth::user()->admin->uid) }}"
                                 alt="{{ Auth::user()->admin->displayName() }}">
                            <span class="text-bold">{{ Auth::user()->admin->displayName() }}</span>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right">
                            <!-- item-->
                            @can("customer_access", Auth::user())
                                <a class="dropdown-item d-block"  href="{{ action("HomeController@index") }}">
                                    <div class="text-muted">
                                        <p class="mb-1 mt-1">
                                            <i class="mdi mdi-exit-to-app font-size-17 text-muted align-middle mr-1"></i> {{ trans('messages.customer_view') }}
                                        </p>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                            @endif
                            <a class="dropdown-item " href="{{ action("Admin\AccountController@profile") }}">
                                <div class="text-muted">
                                    <p class="mb-1">
                                        <i class="icon-profile font-size-17 text-muted align-middle mr-1"></i> {{ trans('messages.account') }}
                                    </p>
                                </div>
                            </a>
                            <a class="dropdown-item"  rel0="AccountController/api" href="{{ action("Admin\AccountController@api") }}" >
                                <div class="text-muted">
                                    <p class="mb-1">
                                        <i class="mdi mdi-key font-size-17 text-muted align-middle mr-1"></i> {{ trans('messages.api') }}
                                    </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="{{ url("/logout") }}">
                                <div class="text-muted">
                                    <p class="mb-1">
                                        <i class="icon-switch2 font-size-17 text-muted align-middle mr-1"></i> {{ trans('messages.logout') }}
                                    </p>
                                </div>
                            </a>
                        </div>

                    </div>

{{--                    <div class="dropdown d-inline-block">--}}
{{--                        <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">--}}
{{--                            <i class="mdi mdi-spin mdi-settings"></i>--}}
{{--                        </button>--}}
{{--                    </div>--}}
            
                </div>
            </div>
        </header>