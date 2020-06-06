@extends('layouts.backend')

@section('title', trans('messages.dashboard'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/visualization/echarts/echarts.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/chart.js') }}"></script>
@endsection

@section('content')

    @include('admin.dashboard._top')

    <p>{{ trans('messages.backend_dashboard_hello', ['name' => Auth::user()->admin->displayName()]) }}.
        {{ trans('messages.backend_dashboard_welcome', [ 'site_name'=> \Acelle\Model\Setting::get("site_name") ]) }} </p>

    @include('admin.dashboard._panel_cards')

    <div class="row">

        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-semibold card-title mb-3"><i class="icon-users"></i> {{ trans('messages.customers_growth') }}</h4>
                    @include('admin.customers._growth_chart')
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-semibold card-title mb-3"><i class="icon-clipboard2"></i> {{ trans('messages.plans_chart') }}</h4>
                    @include('admin.plans._pie_chart')
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4 link-inline">
                        <i class="icon-quill4"></i>
                        {{ trans('messages.subscriptions') }}
                    </h4>
                    <div class="card">
                        <div class="card-body">
                            <p style="margin-bottom: 30px" class="link-inline">
                                {!! trans('messages.admin.dashboard.recent_subscriptions.wording', [ 'here' => action('Admin\SubscriptionController@index') ]) !!}
                            </p>
                            <div class="table-responsive">
                                <table class="table table-centered table-vertical table-nowrap">
                                    <tbody>
                                    @forelse (Auth::user()->admin->recentSubscriptions() as $subscription)
                                        <tr>
                                            <td>
                                                {{ $subscription->user->displayName() }}
                                            </td>
                                            <td> {{ $subscription->plan->name }}</td>
                                            <td>
                                                @if ($subscription->ended())
                                                    <span class="label label-flat bg-ended">{{ trans('messages.subscription.status.ended') }}</span>
                                                @elseif ($subscription->active())
                                                    <span class="label label-flat bg-active">{{ trans('messages.subscription.status.active') }}</span>
                                                @elseif ($subscription->cancelled() && !$subscription->ended())
                                                    <span class="label label-flat bg-cancelled">{{ trans('messages.subscription.status.cancelled') }}</span>
                                                @elseif ($subscription->recurring())
                                                    <span class="label label-flat bg-info">{{ trans('messages.subscription.status.recurring') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($subscription->ended())
                                                    <p class="m-0 text-muted font-14">{{ trans('messages.subscription.ended_on') }}</p>
                                                    {{ Acelle\Library\Tool::formatDate($subscription->ends_at) }}
                                                @elseif ($subscription->cancelled())
                                                    <p class="m-0 text-muted font-14">{{ trans('messages.subscription.ends_on') }}</p>
                                                    {{ Acelle\Library\Tool::formatDate($subscription->ends_at) }}
                                                @else
                                                    <p class="m-0 text-muted font-14">{{ trans('messages.subscription.updated_at') }}</p>
                                                    {{ Acelle\Library\Tool::formatDate($subscription->updated_at) }}
                                                @endif

                                            </td>
                                            <td>
                                                <a class="btn btn-primary btn-sm waves-effect waves-light" href="{{ action('Admin\CustomerController@subscriptions', $subscription->user->uid) }}">
                                                    <i class="icon-clipboard2"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="empty-li">
                                            <td>
                                                {{ trans('messages.empty_record_message') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="icon-users"></i>
                        {{ trans('messages.recent_customers') }}
                    </h4>
                    <div class="card">
                        <div class="card-body">
                            <p style="margin-bottom: 30px" class="link-inline">
                                {!! trans('messages.admin.dashboard.recent_subscriptions.wording', [ 'here' => action('Admin\SubscriptionController@index') ]) !!}
                            </p>
                            <div class="table-responsive">
                                    <table class="table table-centered table-vertical table-nowrap">
                                        <tbody>
                                        @forelse(Auth::user()->admin->recentCustomers() as $customer)
                                            <tr>
                                                <td>
                                                    <img src="{{ action('CustomerController@avatar', $customer->uid) }}" alt="user-image" class="avatar-xs rounded-circle mr-2" />
                                                </td>
                                                <td>
                                                    <h6 class="mt-0 mb-0 text-semibold">
                                                        <a href="{{ action('Admin\CustomerController@edit', $customer->uid) }}">
                                                            {{ $customer->displayName() }}
                                                        </a>
                                                    </h6>
                                                    {{ $customer->user->email }}
                                                </td>
                                                <td>
                                                    <p class="m-0 text-muted font-14">{{ trans('messages.created_at') }}</p>
                                                    {{ Tool::formatDateTime($customer->created_at) }}
                                                </td>
                                                <td>
                                                    <span class="label label-flat bg-{{ $customer->status }}">{{ trans('messages.subscription_status_' . $customer->status) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="empty-li">
                                                <td>
                                                    {{ trans('messages.empty_record_message') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <h3 class="text-semibold">
            <i class="icon-history position-left"></i>
            {{ trans('messages.activities') }}
            </h3>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4 link-inline">
                        {!! trans('messages.admin.dashboard.recent_activity.wording', [ 'here' => action('Admin\CustomerController@index') ]) !!}
                    </h4>

                    @if (\Auth::user()->admin->getLogs()->count() == 0)
                        <div class="empty-list">
                            <i class="icon-history"></i>
                            <span class="line-1">{{ trans('messages.no_activity_logs') }}</span>
                        </div>
                    @else
                        <ol class="activity-feed mb-0">
                            @foreach (\Auth::user()->admin->getLogs()->take(20)->get() as $log)
                                <li class="feed-item">
                                    <div class="feed-item-list">
                                        <span class="date">
                                            @if ($log->created_at)
                                                @if ($log->created_at->lessThan(\Carbon\Carbon::now()->subMonth(1)))
                                                    {{ \Acelle\Library\Tool::formatDateTime($log->created_at) }}
                                                @else
                                                    {{ $log->created_at->diffForHumans() }}
                                                @endif
                                            @endif
                                        </span>
                                        <span class="activity-text">{!! $log->message() !!}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                    <div class="text-center">
                        <a href="{{ action('Admin\CustomerController@index') }}" class="btn btn-sm btn-primary">Load More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

    <div class="col-xl-12 col-lg-12">
        <h3 class="text-semibold mt-40">{{ trans('messages.resources_statistics') }}</h3>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4 link-inline">
                                    {{ trans('messages.resources_statistics_intro') }}
                                </h4>
                                <ul class="dotted-list topborder section">
                                <li>
                                    <div class="unit size1of2">
                                        <strong><i class="icon-users"></i> {{ trans('messages.customers') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllCustomers()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li class="selfclear">
                                    <div class="unit size1of2">
                                        <strong><i class="icon-quill4"></i> {{ trans('messages.subscriptions') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllSubscriptions()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li class="selfclear">
                                    <div class="unit size1of2">
                                        <strong><i class="icon-clipboard2"></i> {{ trans('messages.plans') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllPlans()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li>
                                    <div class="unit size1of2">
                                        <strong><i class="icon-credit-card2"></i> {{ trans('messages.payment_methods') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllPaymentMethods()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li>
                                    <div class="unit size1of2">
                                        <strong><i class="icon-address-book2"></i> {{ trans('messages.lists') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllLists()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li>
                                    <div class="unit size1of2">
                                        <strong><i class="icon-users"></i> {{ trans('messages.subscribers') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllSubscribers()->count() }}</mc:flag>
                                    </div>
                                </li>
                            </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4 link-inline">
                                    {{ trans('messages.resources_statistics_intro') }}
                                </h4>
                                <ul class="dotted-list topborder section p-3">
                                <li>
                                    <div class="unit size1of2">
                                        <strong><i class="icon-user-tie"></i> {{ trans('messages.admins') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllAdmins()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li class="selfclear">
                                    <div class="unit size1of2">
                                        <strong><i class="icon-users4"></i> {{ trans('messages.admin_groups') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllAdminGroups()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li class="selfclear">
                                    <div class="unit size1of2">
                                        <strong><i class="icon-server"></i> {{ trans('messages.sending_servers') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllSendingServers()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li>
                                    <div class="unit size1of2">
                                        <strong><i class="icon-earth"></i> {{ trans('messages.sending_domains') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllSendingDomains()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li>
                                    <div class="unit size1of2">
                                        <strong><i class="icon-paperplane"></i> {{ trans('messages.campaigns') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllCampaigns()->count() }}</mc:flag>
                                    </div>
                                </li>
                                <li>
                                    <div class="unit size1of2">
                                        <strong><i class="icon-alarm-check"></i> {{ trans('messages.automations') }}</strong>
                                    </div>
                                    <div class="lastUnit size1of2">
                                        <mc:flag>{{ Auth::user()->admin->getAllAutomations()->count() }}</mc:flag>
                                    </div>
                                </li>
                            </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection