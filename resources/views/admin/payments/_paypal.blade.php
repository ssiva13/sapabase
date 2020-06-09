<div class="card">
    <div class="card-body">
        <form enctype="multipart/form-data" action="{{ action('Admin\PaymentController@update', $gateway['name']) }}" method="POST" class="form-validate-jquery">
        {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    @include('helpers.form_control', [
                        'type' => 'select',
                        'name' => 'options[environment]',
                        'value' => $gateway['fields']['environment'],
                        'label' => trans('messages.payment.paypal.environment'),
                        'help_class' => 'payment',
                        'options' => [['text' => 'Sandbox', 'value' => 'sandbox'],['text' => 'Production', 'value' => 'production']],
                        'rules' => ['options.environment' => 'required'],
                    ])
                </div>
                <div class="col-md-6">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => '',
                        'name' => 'options[client_id]',
                        'value' => $gateway['fields']['client_id'],
                        'label' => trans('messages.payment.paypal.client_id'),
                        'help_class' => 'payment',
                        'rules' => ['options.client_id' => 'required'],
                    ])
                </div>
                <div class="col-md-12">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => '',
                        'name' => 'options[secret]',
                        'value' => $gateway['fields']['secret'],
                        'label' => trans('messages.payment.paypal.secret'),
                        'help_class' => 'payment',
                        'rules' => ['options.secret' => 'required'],
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

        </form>
    </div>
</div>