{{--@if (Auth::user()->customer->can('create', new Acelle\Model\SendingServer()))--}}
<div class="sub_section">
    <h2 class="text-semibold">{{ trans('messages.twilio_number') }}</h2>
    <h3 class="text-semibold">{{ trans('messages.phone_details') }}
    </h3>
    <div class="row">
        <div class="col-md-4">
                @include('helpers.form_control', [
                            'type' => 'text',
                            'name' => 'number',
                            'label' => trans('messages.phone_number'),
                            'value' => $twilio_number->number,
                            'rules' => Acelle\Model\TwilioNumber::$rules])
        </div>

        <div class="col-md-4">
            <label>
                {{ trans('messages.twilio.outbound') }}
            </label>
            <select class="form-control" name="outbound_recording">
                <option value="yes"> {{trans('messages.yes')}}</option>
                <option value="do-not-record"> {{trans('messages.no')}}</option>
            </select>
        </div>
        <div class="col-md-4">
            <label>
                {{ trans('messages.twilio.inbound') }}
            </label>
            <select class="form-control" name="inbound_recording">
                <option value="yes"> {{trans('messages.yes')}}</option>
                <option value="do-not-record"> {{trans('messages.no')}}</option>
            </select>
        </div>
    </div>
</div>

{{--@endif--}}

