@extends('layouts.frontend')

@section('title', trans('messages.create_list'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
			<li><a href="{{ action("TwilioController@index") }}">{{ trans('messages.phone_numbers') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-plus-circle2"></i> {{ trans('messages.purchase_number') }}</span>
		</h1>
	</div>
@endsection

@section('content')
	<form action="{{ action('TwilioController@store') }}" method="POST" class="form-validate-jqueryz" id="twilio_numbers_form">
		{{ csrf_field() }}
		@include("twilio_numbers._form")
		<hr>
		<div class="text-left">
			<button class="btn bg-teal mr-10"><i class="icon-check"></i> {{ trans('messages.save') }}</button>
			<a href="{{ action('TwilioController@index') }}" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('messages.cancel') }}</a>
		</div>
	</form>


	<script>


		let popup = new Popup(undefined, undefined, {
			onclose: function() {
			}
		});
		let countries = $(`#countries`);

		function LoadStates(country) {
			// loading effect
			popup.loading();

			$.ajax({
				method: "GET",
				url: '{{ action('TwilioController@getStates') }}',
				data: {'country': country},
			})
			.done(function (resp) {
				let state_province_region = $('#state_province_region');
				state_province_region.empty();
				state_province_region.append(`<option value=""> {{ trans('messages.choose') . ' ' .trans('messages.state_province_region') }} </option>`);
				$.each(resp, function( index, state ) {
					state_province_region.append(`<option value="${state.value}"> ${state.text} </option>`);
				});
				state_province_region.select({
					minimumSelectionLength: 5
				});
				state_province_region.select2({
					minimumSelectionLength: 3
				});
			});
		}

		function LoadPhoneNumbers() {
			// loading effect
			popup.loading();

			let url1 = 'twilio/country';
			let url2 = 'country/numbers';
			let twilio_url = 'https://api.twilio.com';

			$.ajax({
				method: "GET",
				url: url1,
				data: $('#twilio_numbers_form').serialize(),
			})
			.done(function( msg ) {
				let phone_numbers = $(`#phone_numbers`);
				if(msg.body !== undefined){
					let uri = twilio_url + msg.body;
					$.ajax({
						method: "GET",
						url: url2,
						data: {'uri': uri},
					})
					.done(function (resp) {
						phone_numbers.prop('disabled', false)
						phone_numbers.empty();
						phone_numbers.append(`<option value="">Select</option>`);
						$.each(resp, function( index, value ) {
							let data = $.parseJSON(value);
							let {available_phone_numbers: numbers} = data;
							$('#phone_numbers').append(`<optgroup label="${index}"></optgroup>`);
							$.each(numbers, function(index, {capabilities, friendly_name, phone_number} ) {
								const {MMS, SMS, voice, fax} = capabilities;
								if(
										($("#sms_enabled").val() == 1 && SMS) ||
										($("#call_enabled").val() == 1 && voice) ||
										($("#mms_enabled").val() == 1 && MMS) ||
										($("#fax_enabled").val() == 1 && fax)
								){
									$('#phone_numbers').append(`<option value="${phone_number}"> ${friendly_name} </option>`);
								}

							})
						});
						phone_numbers.select({
							minimumSelectionLength: 5
						});
						phone_numbers.select2({
							minimumSelectionLength: 3
						});
					});
				}
				else if(msg != 20404){
					phone_numbers.empty();
					phone_numbers.prop('disabled', false)
					phone_numbers.append(`<option value="">Select</option>`);
					$.each(msg, function( index, value ) {
						phone_numbers.append(`<option value="${value}"> ${index} </option>`);
					})
					phone_numbers.select({
						minimumSelectionLength: 5
					});
					phone_numbers.select2({
						minimumSelectionLength: 3
					});
				}
				else if(msg == 20404){
					phone_numbers.empty();
					phone_numbers.prop('disabled', false)
					phone_numbers.append(`<option value="">No Numbers Found</option>`);
				}
			}).fail(function( err ) {
				console.log(err)
			})
		}


		countries.change(function(e) {
			e.preventDefault();
			let formdata = $('#twilio_numbers_form').serialize();
			console.log(formdata)
			if($(this).val() != '' || $(this).val() != null || $(this).val() != undefined){
				LoadStates($(this).val());
				LoadPhoneNumbers();
			}
		});

		$('#state_province_region').change(function(e) {
			e.preventDefault();
			let formdata = $('#twilio_numbers_form').serialize();
			console.log(formdata)
			if($(this).val() != '' || $(this).val() != null || $(this).val() != undefined){
				LoadPhoneNumbers();
			}
		});

		$("input[name$='_enabled']").change(function() {
			let name = $(this).attr("name");
			if(this.checked) {
				$(`#${name}`).val(1)
			}else{
				$(`#${name}`).val(0)
			}
			
			if(countries.val() != '' || countries.val() != null || countries.val() != undefined){
				LoadPhoneNumbers();
			}
		})
		
		$("input[name='number_type']").change(function() {
		    if(countries.val() != '' || countries.val() != null || countries.val() != undefined){
				LoadPhoneNumbers();
			}
		})
		$("input[name='extras']").change(function() {
			if(this.checked) {
				$('#advanced').show();
			}else{
				$('#advanced').hide();
			}
		});

	</script>
@endsection
