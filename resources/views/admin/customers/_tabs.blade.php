<div class="topnav mb-3 ml-2 text-bold">
    <nav class="navbar navbar-inverse navbar-expand-lg topnav-menu" style="">

        <div class="collapse navbar-collapse" id="topnav-menu-content">
            <ul class="navbar-nav nav nav-tabs nav-tabs-top w-100">

                <li class=" nav-item {{ request()->route()->getActionName() == 'Acelle\Http\Controllers\Admin\CustomerController@edit' ? 'active' : '' }}
                text-semibold "><a class="nav-link" href="{{ action('Admin\CustomerController@edit', $customer->uid) }}">
                    <i class="icon-user"></i> {{ trans('messages.profile') }}</a>
                </li>
                <li class=" nav-item {{ request()->route()->getActionName() == 'Acelle\Http\Controllers\Admin\CustomerController@contact' ? 'active' : '' }}
                text-semibold"><a class="nav-link" href="{{ action('Admin\CustomerController@contact', $customer->uid) }}">
                    <i class="icon-office position-left"></i> {{ trans('messages.contact_information') }}</a>
                </li>
                <li class=" nav-item {{ request()->route()->getActionName() == 'Acelle\Http\Controllers\Admin\CustomerController@subscriptions' ? 'active' : '' }}
                text-semibold"><a class="nav-link" href="{{ action('Admin\CustomerController@subscriptions', $customer->uid) }}">
                    <i class="icon-quill4"></i> {{ trans('messages.subscriptions') }}</a>
                </li>
                @can('viewSubAccount', $customer))
                    <li class=" nav-item {{ request()->route()->getActionName() == 'Acelle\Http\Controllers\Admin\CustomerController@subAccount' ? 'active' : '' }}
                    text-semibold"><a class="nav-link" href="{{ action('Admin\CustomerController@subAccount', $customer->uid) }}">
                        <i class="icon-drive"></i> {{ trans('messages.customer.sub_account') }}</a>
                    </li>
                @endcan
            </ul>
        </div>
    </nav>
</div>
