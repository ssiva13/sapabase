@extends('layouts.frontend')

@section('title', trans('messages.twilio_numbers'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-phone"></i> {{ trans('messages.my_numbers') }}</span>
		</h1>
	</div>

@endsection

@section('content')
    <form class="listing-form"
        sort-url="{{ action('TwilioController@sort') }}"
        data-url="{{ action('TwilioController@listing') }}"
        per-page="{{ Acelle\Model\TwilioNumber::$itemsPerPage }}"
    >
        <div class="row top-list-controls">
            <div class="col-md-10">
				<div class="filter-box">
					<div class="btn-group list_actions hide">
						<button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
							{{ trans('messages.actions') }} <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li>
								<a href="{{ action('TwilioController@deactivateConfirm') }}">
									<i class="icon-trash"></i> {{ trans('messages.deactivate') }}
								</a>
							</li>
						</ul>
					</div>
					<div class="checkbox inline check_all_list">
						<label>
							<input type="checkbox" class="styled check_all">
						</label>
					</div>
					<span class="filter-group">
						<span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
						<select class="select" name="sort-order">
							<option value="number">{{ trans('messages.number') }}</option>
							<option value="created_at">{{ trans('messages.created_at') }}</option>
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
            </div>
            <div class="col-md-2 text-right">
                <a href="{{ action("TwilioController@create") }}" type="button" class="btn bg-info-800">
                    <i class="icon icon-plus2"></i> {{ trans('messages.purchase_number') }}
                </a>
            </div>
        </div>

        <div class="pml-table-container"></div>
    </form>
@endsection
