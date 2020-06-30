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


		var popup = new Popup(undefined, undefined, {
			onclose: function() {
			}
		});

		function LoadPhoneNumbers() {
			// loading effect
			popup.loading();

			let url1 = 'twilio/country';
			let url2 = 'country/numbers';
			let url3 = 'country/numbers/list';
			let twilio_url = 'https://api.twilio.com';

			$.ajax({
				method: "GET",
				url: url1,
				data: $('#twilio_numbers_form').serialize(),
			})
			.done(function( msg ) {
				if(msg.body !== undefined){
					let uri = twilio_url + msg.body;
					$.ajax({
						method: "GET",
						url: url2,
						data: {'uri': uri},
					})
					.done(function (resp) {
						$('#phone_numbers').prop('disabled', false)
						$('#phone_numbers').empty();
						$('#phone_numbers').append(`<option value="">Select</option>`);
						$.each(resp, function( index, value ) {
							let data = $.parseJSON(value);
							let numbers = data.available_phone_numbers;
							$('#phone_numbers').append(`<optgroup label="${index}"></optgroup>`);
							$.each(numbers, function( index, value ) {
								if(
										($("#sms_enabled").val() == 1 && value.capabilities.SMS) ||
										($("#call_enabled").val() == 1 && value.capabilities.voice) ||
										($("#mms_enabled").val() == 1 && value.capabilities.MMS) ||
										($("#fax_enabled").val() == 1 && value.capabilities.fax)
								){
									$('#phone_numbers').append(`<option value="${value.phone_number}"> ${value.friendly_name} </option>`);
								}

							})
						});
						$('#phone_numbers').select({
							minimumSelectionLength: 5
						});
						$('#phone_numbers').select2({
							minimumSelectionLength: 3
						});
					});
				}
				else if(msg != 20404){
					$('#phone_numbers').empty();
					$('#phone_numbers').prop('disabled', false)
					$('#phone_numbers').append(`<option value="">Select</option>`);
					$.each(msg, function( index, value ) {
						$('#phone_numbers').append(`<option value="${value}"> ${index} </option>`);
					})
					$('#phone_numbers').select({
						minimumSelectionLength: 5
					});
					$('#phone_numbers').select2({
						minimumSelectionLength: 3
					});
				}
				else if(msg == 20404){
					$('#phone_numbers').empty();
					$('#phone_numbers').prop('disabled', false)
					$('#phone_numbers').append(`<option value="">No Numbers Found</option>`);
				}
			}).fail(function( err ) {
				console.log(err)
			})
		}


		$('#countries').change(function(e) {
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
			
			if($('#countries').val() != '' || $('#countries').val() != null || $('#countries').val() != undefined){
				LoadPhoneNumbers();
			}
		})
		
		$("input[name='number_type']").change(function() {
		    if($('#countries').val() != '' || $('#countries').val() != null || $('#countries').val() != undefined){
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
