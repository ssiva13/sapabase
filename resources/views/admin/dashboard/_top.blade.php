<div class="row">

    @component('admin.common-components.breadcrumb')
        @slot('title') Admin Dashboard  @endslot
        @slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
        @slot('li2') Admin  @endslot
        @slot('li3') Dashboard @endslot
    @endcomponent

    @component('admin.common-components.chart')
        @slot('chart1_id') header-chart-1  @endslot
        @slot('chart1_title') {{trans('messages.customers')}} - {{ Acelle\Model\Customer::count() }} @endslot

        @slot('chart2_id') header-chart-2  @endslot
        @slot('chart2_title') {{trans('messages.subscriptions')}} - {{ Acelle\Model\Subscriber::count() }} @endslot
    @endcomponent

</div>