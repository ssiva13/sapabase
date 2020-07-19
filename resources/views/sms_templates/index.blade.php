@extends('layouts.frontend')

@section('title', trans('messages.'.$type.'_templates'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

			<div class="page-title">				
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="icon-list2"></i> {{ trans('messages.'.$type.'_templates') }}</span>
				</h1>				
			</div>

@endsection

@section('content')
				<form class="listing-form"
					sort-url="{{ action('SmsTemplateController@sort') }}"
					data-url="{{ action('SmsTemplateController@listing',$type ) }}"
					per-page="{{ Acelle\Model\SmsTemplate::$itemsPerPage }}"
				>				
					<div class="row top-list-controls">
						<div class="col-md-9">
							@if ($templates->count() >= 0)					
								<div class="filter-box">
									<span class="filter-group">
										<span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
										<select class="select" name="sort-order">
											<option value="content" class="active">{{ trans('messages.automation.sms.body') }}</option>
											<option value="name">{{ trans('messages.name') }}</option>
											<option value="created_at">{{ trans('messages.created_at') }}</option>
										</select>										
										<button class="btn btn-xs sort-direction" rel="asc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
											<i class="icon-sort-amount-asc"></i>
										</button>
									</span>
									<span class="filter-group">
										<span class="title text-semibold text-muted">{{ trans('messages.from') }}</span>
										<select class="select" name="from">
											<option value="all">{{ trans('messages.all') }}</option>
											<option value="mine" selected='selected'>{{ trans('messages.my_templates') }}</option>
										</select>										
									</span>
									<span class="text-nowrap">
										<input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
										<i class="icon-search4 keyword_search_button"></i>
									</span>
								</div>
							@endif
						</div>
						<div class="col-md-3 text-right">
							@if($type == 'call')
								<button onclick="CallTemplateSetup()" type="button" class="btn bg-grey-800">
									<i class="icon icon-upload4"></i> {{ trans('messages.upload_audio') }}
								</button>
							@elseif($type == 'sms')
								<button onclick="SmsTemplateSetup()" type="button" class="btn bg-info-800">
									<i class="icon icon-plus2"></i> {{ trans('messages.create') }}
								</button>
							@endif()
						</div>
					</div>
					
					<div class="pml-table-container">
						
						
						
					</div>
				</form>
				<script>
					var popup = new Popup(undefined, undefined, {
						onclose: function() {
						}
					});
					function SmsTemplateSetup() {
						var url = '{{ action('SmsTemplateController@create') }}';

						popup.load(url, function() {
							// set back event
							popup.back = function() {
								Popup.hide();
							};
						});
					}
					function SmsTemplateEdit(url) {
						popup.load(url, function() {
							// set back event
							popup.back = function() {
								Popup.hide();
							};
						});
					}

					function CallTemplateSetup() {
						var urll = '{{ action('SmsTemplateController@create',['call' => 'call']) }}';
						popup.load(urll, function() {
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

