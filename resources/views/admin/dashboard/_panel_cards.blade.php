<div class="row">

    @component('admin.common-components.dashboard-widget')
        @slot('icons') mdi mdi-human-male-female float-right  @endslot
        @slot('title') {{trans('messages.customers')}}  @endslot
        @slot('count') {{ Acelle\Model\Customer::count() }}  @endslot
        @slot('badgeClass') badge-info @endslot
        @slot('per') {{ growthRate(trans('messages.customers')) }} %  @endslot
        @slot('narration') {{ growthRate(trans('messages.customers')) }} %  @endslot
    @endcomponent

    @component('admin.common-components.dashboard-widget')
        @slot('icons') icon-credit-card2 float-right  @endslot
        @slot('title') {{trans('messages.subscriptions')}}  @endslot
        @slot('count') {{ Acelle\Model\Subscriber::count() }}  @endslot
        @slot('badgeClass') badge-success @endslot
        @slot('per') {{ growthRate(trans('messages.subscriptions')) }} %  @endslot
        @slot('narration') {{ growthRate(trans('messages.subscriptions')) }} %  @endslot
    @endcomponent


    @component('admin.common-components.dashboard-widget')
        @slot('icons') mdi mdi-briefcase-check float-right  @endslot
        @slot('title') {{trans('messages.plans')}}  @endslot
        @slot('count') {{ Acelle\Model\Plan::count() }}  @endslot
        @slot('badgeClass') badge-info @endslot
        @slot('per') {{trans('messages.plans')}} @endslot
        @slot('narration')  . @endslot
    @endcomponent

    @component('admin.common-components.dashboard-widget')
        @slot('icons') icon-user-tie float-right  @endslot
        @slot('title') {{trans('messages.admins')}}  @endslot
        @slot('count') {{ Acelle\Model\Admin::count() }}  @endslot
        @slot('badgeClass') badge-danger @endslot
        @slot('per') {{trans('messages.admins')}} @endslot
        @slot('narration')  . @endslot
    @endcomponent

</div>
<!-- end row -->