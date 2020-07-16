@extends('layouts.frontend')

@section('title', trans('messages.subscribers'))

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
            <span class="text-semibold"><i class="icon-address-book3"></i> {{ trans('messages.subscribers') }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <form class="listing-form"
          sort-url="{{ action('LeadController@sort') }}"
          data-url="{{ action('LeadController@listing') }}"
          per-page="{{ Acelle\Model\Subscriber::$itemsPerPage }}"
    >
        <div class="row top-list-controls">
            <div class="col-md-10">
                <div class="filter-box">
                    <span class="filter-group">
						<span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                        <label for="sort_twilio"></label>
						<select id="sort_twilio" class="select" name="sort-order">
							<option value="phone">{{ trans('messages.phone_number') }}</option>
							<option value="email">{{ trans('messages.email') }}</option>
                            <option value="status">{{ trans('messages.status') }}</option>
							<option value="created_at">{{ trans('messages.created_at') }}</option>
						</select>
						<button class="btn btn-xs sort-direction" data-popup="tooltip"
                                rel="asc" title="{{ trans('messages.change_sort_direction') }}" type="button">
							<i class="icon-sort-amount-asc"></i>
						</button>
					</span>
                    <span class="text-nowrap">
						<input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
						<i class="icon-search4 keyword_search_button"></i>
					</span>
                </div>
            </div>
        </div>

        <div class="pml-table-container"></div>
    </form>


    <script>
        var popup = new Popup(undefined, undefined, {
            onclose: function() {
            }
        });

        function makeRequest(url) {
            popup.load(url, function() {
                // set back event
                popup.back = function() {
                    Popup.hide();
                };
            });
        }


        function makeCall(url) {
            popup.load(url, function() {
                // set back event
                popup.back = function() {
                    Popup.hide();
                };
            });
        }


        function hidePopUp() {
            Popup.hide();
        }
    </script>

@endsection
