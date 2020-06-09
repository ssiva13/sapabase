<div class="card">
	<div class="card-body">
		<form enctype="multipart/form-data" action="{{ action('Admin\PaymentController@update', $gateway['name']) }}" method="POST" class="form-validate-jquery">
			{{ csrf_field() }}
			<div class="row">
				<div class="col-md-6">
				@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'options[merchant_id]',
					'value' => $gateway['fields']['merchant_id'],
					'label' => trans('messages.payment.coinpayments.merchant_id'),
					'help_class' => 'payment',
					'rules' => ['options.merchant_id' => 'required'],
				])
				</div>
				<div class="col-md-6">
					@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'options[public_key]',
					'value' => $gateway['fields']['public_key'],
					'label' => trans('messages.payment.coinpayments.public_key'),
					'help_class' => 'payment',
					'rules' => ['options.public_key' => 'required'],
				])
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
				@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'options[private_key]',
					'value' => $gateway['fields']['private_key'],
					'label' => trans('messages.payment.coinpayments.private_key'),
					'help_class' => 'payment',
					'rules' => ['options.private_key' => 'required'],
				])
				</div>
				<div class="col-md-6">
				@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'options[ipn_secret]',
					'value' => $gateway['fields']['ipn_secret'],
					'label' => trans('messages.payment.coinpayments.ipn_secret'),
					'help_class' => 'payment',
					'rules' => ['options.ipn_secret' => 'required'],
				])
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">

				@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'options[receive_currency]',
					'value' => $gateway['fields']['receive_currency'],
					'label' => trans('messages.payment.coinpayments.receive_currency'),
					'help_class' => 'payment',
					'rules' => ['options.receive_currency' => 'required'],
				])
				</div>
			</div>

				<hr>
				<div class="text-left">
					<button class="btn btn-primary mr-5">{{ trans('messages.save') }}</button>
					@if (\Acelle\Model\Setting::get('system.payment_gateway') !== $gateway['name'])
						<input type="submit" class="btn btn-success bg-teal  mr-5" name="save_and_set_primary" value="{{ trans('messages.save_and_set_primary') }}" />
					@endif
					<a class="btn btn-danger" href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.cancel') }}</a>
				</div>

			</div>
		</form>
	</div>
</div>