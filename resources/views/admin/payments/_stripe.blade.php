<div class="card">
	<div class="card-body">
		<form enctype="multipart/form-data" action="{{ action('Admin\PaymentController@update', $gateway['name']) }}" method="POST" class="form-validate-jquery">
			{{ csrf_field() }}
			<div class="row">
				<div class="col-md-6">
					@include('helpers.form_control', [
						'type' => 'text',
						'name' => 'options[publishable_key]',
						'value' => $gateway['fields']['publishable_key'],
						'label' => trans('messages.payment.stripe.publishable_key'),
						'help_class' => 'payment',
						'rules' => ['options.publishable_key' => 'required'],
					])
				</div>
				<div class="col-md-6">
					@include('helpers.form_control', [
						'type' => 'text',
						'name' => 'options[secret_key]',
						'value' => $gateway['fields']['secret_key'],
						'label' => trans('messages.payment.stripe.secret_key'),
						'help_class' => 'payment',
						'rules' => ['options.secret_key' => 'required'],
					])
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<h2 class="mt-40 mb-4">{{ trans('messages.stripe.require_valid_card') }}</h2>
					<p>{{ trans('messages.stripe.require_valid_card.intro') }}</p>

					@include('helpers.form_control', ['type' => 'checkbox2',
						'class' => '',
						'name' => 'options[always_ask_for_valid_card]',
						'value' => $gateway['fields']['always_ask_for_valid_card'],
						'label' => trans('messages.stripe.always_ask_for_valid_card'),
						'options' => ['no','yes'],
						'help_class' => 'payment',
					])
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-md-6">
					<div class="text-left">
						<button class="btn btn-info mr-5">{{ trans('messages.save') }}</button>
						@if (\Acelle\Model\Setting::get('system.payment_gateway') !== $gateway['name'])
							<input type="submit" class="btn btn-primary bg-teal  mr-5" name="save_and_set_primary" value="{{ trans('messages.save_and_set_primary') }}" />
						@endif
						<a class="btn btn-danger" href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.cancel') }}</a>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>