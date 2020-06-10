{{--@if (Auth::user()->customer->can('create', new Acelle\Model\SendingServer()))--}}
<div class="sub_section">
    <h2 class="text-semibold">{{ trans('messages.twilio_number') }}</h2>
    <h3 class="text-semibold">{{ trans('messages.phone_details') }}
    </h3>
    <div class="row">
        <div class="col-md-6">
            @include('helpers.form_control', [
                'type' => 'select',
                'name' => 'country',
                'value'=> '',
                'id'=> 'countries',
                'options' => \Acelle\Model\Country::getCountryOptions(),
                'include_blank' => trans('messages.choose') . ' ' .trans('messages.country')

                ])
        </div>

        <div class="col-md-6">
            <label for="phone_numbers">
                {{ trans('messages.phone_number') }}
            </label>
            <select class="form-control select select-search required select2-hidden-accessible"
                    id="phone_numbers" name="number">
                <option value="">{{ trans('messages.choose') }} {{ trans('messages.country') }}</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label>
                {{ trans('messages.twilio.outbound') }}
            </label>
            <select class="form-control select select-search required select2-hidden-accessible" name="outbound_recording">
                <option value="yes"> {{trans('messages.yes')}}</option>
                <option value="do-not-record"> {{trans('messages.no')}}</option>
            </select>
        </div>
        <div class="col-md-6">
            <label>
                {{ trans('messages.twilio.inbound') }}
            </label>
            <select class="form-control select select-search required select2-hidden-accessible" name="inbound_recording">
                <option value="yes"> {{trans('messages.yes')}}</option>
                <option value="do-not-record"> {{trans('messages.no')}}</option>
            </select>
        </div>
    </div>
</div>

{{--@endif--}}

