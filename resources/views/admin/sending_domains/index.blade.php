@extends('layouts.backend')

@section('title', trans('messages.sending_domains'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-list2"></i> {{ trans('messages.sending_domains') }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <div class="row">
        @component('admin.common-components.breadcrumb')
            @slot('title') {{ trans('messages.sending_domains') }}  @endslot
            @slot('li1') {{ \Acelle\Model\Setting::get("site_name") }}  @endslot
            @slot('li2') Admin  @endslot
            @slot('li3') {{ trans('messages.sending_domains') }} @endslot
        @endcomponent
    </div>
    <p>{!! trans('messages.sending_domain.wording') !!}</p>

    <div class="card">
        <div class="card-body">
            <form class="listing-form"
                sort-url="{{ action('Admin\SendingDomainController@sort') }}"
                data-url="{{ action('Admin\SendingDomainController@listing') }}"
                per-page="{{ Acelle\Model\SendingDomain::$itemsPerPage }}"
            >
                <div class="row top-list-controls">
                    <div class="col-md-9">
                        @if ($items->count() >= 0)
                            <div class="filter-box">
                                <div class="btn-group list_actions hide">
                                    <button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
                                        {{ trans('messages.actions') }} <span class="caret"></span>
                                    </button>
                                    <div data-simplebar style="max-height: 230px;" class="dropdown-menu dropdown-menu-right" >
                                        <div class="media-body">
                                            <ol class="activity-feed mb-0">
                                                <li class="dropdown-item">
                                                    <a delete-confirm="{{ trans('messages.delete_sending_domains_confirm') }}" href="{{ action('Admin\SendingDomainController@delete') }}">
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
                                <div class="checkbox inline check_all_list ml-1">
                                    <label>
                                        <input type="checkbox" class="styled check_all">
                                    </label>
                                </div>
                                <span class="filter-group">
                                    <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                                    <select class="select" name="sort-order">
                                        <option value="sending_domains.name">{{ trans('messages.name') }}</option>
                                        <option value="sending_domains.created_at">{{ trans('messages.created_at') }}</option>
                                        <option value="sending_domains.updated_at">{{ trans('messages.updated_at') }}</option>
                                    </select>
                                    <button class="btn btn-xs sort-direction" rel="asc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
                                        <i class="icon-sort-amount-asc"></i>
                                    </button>
                                </span>
                                <span class="text-nowrap">
                                    <input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
                                    <i class="icon-search4 keyword_search_button"></i>
                                </span>
                            </div>
                        @endif
                    </div>
                    @if (Auth::user()->admin->can('create', new Acelle\Model\SendingDomain()))
                        <div class="col-md-3 text-right">
                            <a href="{{ action('Admin\SendingDomainController@create') }}" type="button" class="btn btn-info">
                                <i class="icon icon-plus2"></i> {{ trans('messages.create_sending_domain') }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="pml-table-container">



                </div>
            </form>
        </div>
    </div>
@endsection
