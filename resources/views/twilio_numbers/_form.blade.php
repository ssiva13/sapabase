@php
    $recordingOptions = [
        'do-not-record' => 'Do Not Record',
        'record-from-answer' => 'Record From Answer',
        'record-from-ringing' => 'Record From Ringing',
        'record-from-answer-dual' => 'Record From Answer Dual',
        'record-from-ringing-dual' => 'Record From Ringing Dual',
    ];
    $numberCapabilities = [
        'sms_enabled' => 'SMS Enabled',
        'mms_enabled' => 'MMS Enabled',
        'call_enabled' => 'Call Enabled',
        'fax_enabled' => 'Fax Enabled',
    ];

    $numberOptionsT = [
        [
            'text' => 'Toll Free',
            'value' => 'tollFree',
        ]
    ];

    $numberOptionsM = [
        [
            'text' => 'Mobile',
            'value' => 'mobile',
        ]
    ];
    $numberOptionsL = [
        [
            'text' => 'Local',
            'value' => 'local',
        ],
    ];
@endphp

<div class="sub_section">
    <h2 class="text-semibold">{{ trans('messages.twilio_number') }}</h2>
    <h3 class="text-semibold">{{ trans('messages.phone_details') }}
    </h3>
    <div class="row">
        <div class="col-md-12">
            <div class="checkbox-box-group" >
                @include('helpers.form_control', [
                    'type' => 'checkbox2',
                    'name' => 'extras',
                    'id' => 'extras',
                    'label' => 'Advanced Search Options',
                    'value' => false,
                    'options' => [false, true],
                    'help_class' => '',
                ])
            </div>
        </div>
    </div>
    <div class="row" id="advanced">
        <div class="col-md-6">
            <div class="col-md-4">
                    @include('helpers.form_control', [
                        'type' => 'radio',
                        'name' => 'number_type',
                        'value' => 'no',
                        'options' => ['no','yes'],
                        'help_class' => '',
                        'rules' => [],
                        'options' => $numberOptionsT,
                    ])
            </div>
            <div class="col-md-4">
                    @include('helpers.form_control', [
                        'type' => 'radio',
                        'name' => 'number_type',
                        'value' => 'no',
                        'options' => ['no','yes'],
                        'help_class' => '',
                        'rules' => [],
                        'options' => $numberOptionsM,
                    ])
            </div>
            <div class="col-md-4">
                    @include('helpers.form_control', [
                        'type' => 'radio',
                        'name' => 'number_type',
                        'value' => 'no',
                        'options' => ['no','yes'],
                        'help_class' => '',
                        'rules' => [],
                        'options' => $numberOptionsL,
                    ])
            </div>
        </div>

        <div class="col-md-6">
            @foreach($numberCapabilities as $numberCapabilityKey => $numberCapability)
                <div class="col-md-3">
                    <div class="checkbox-box-group" >
                        <input id="{{$numberCapabilityKey}}" value="1" readonly hidden>
                        @include('helpers.form_control', [
                            'type' => 'checkbox',
                            'name' => $numberCapabilityKey,
                            'label' => $numberCapability,
                            'value' => true,
                            'options' => [false, true],
                            'help_class' => '',
                        ])
                    </div>
                </div>
            @endforeach

        </div>

    </div>
    <div class="row">

        <div class="col-md-6">
            @include('helpers.form_control', [
                'type' => 'select',
                'name' => 'country',
                'value'=> '',
                'required'=> true,
                'rules' => ['country'=> 'required'],
                'id'=> 'countries',
                'options' => \Acelle\Model\Country::getCountryOptions(),
                'include_blank' => trans('messages.choose') . ' ' .trans('messages.country')

                ])
        </div>

        <div class="col-md-6">
            @include('helpers.form_control', [
                'type' => 'select',
                'name' => 'number',
                'value'=> '',
                'required'=> true,
                'rules' => ['number'=> 'required'],
                'id'=> 'phone_numbers',
                'options' => [],
                'include_blank' => trans('messages.choose') . ' ' .trans('messages.country')

            ])

        </div>


    </div>
    <div class="row">

        <div class="col-md-6">
            <label for="outbound_recording">
                {{ trans('messages.twilio.outbound') }}
            </label>
            <select id="outbound_recording" class="form-control select select-search required select2-hidden-accessible" name="outbound_recording">
                @foreach($recordingOptions as $recordingOptionKey => $recordingOptionValue)
                    <option value="{{ $recordingOptionKey }}">{{ $recordingOptionValue }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label for="inbound_recording">
                {{ trans('messages.twilio.inbound') }}
            </label>
            <select id="inbound_recording" class="form-control select select-search required select2-hidden-accessible" name="inbound_recording">
                @foreach($recordingOptions as $recordingOptionKey => $recordingOptionValue)
                    <option value="{{ $recordingOptionKey }}">{{ $recordingOptionValue }}</option>
                @endforeach
            </select>
        </div>


    </div>
</div>
