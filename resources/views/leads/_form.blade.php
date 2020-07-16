@if($type == 'sms')
    <div class="col-md-6">
        @include('helpers.form_control', [
            'type' => 'select',
            'name' => 'template',
            'id' => 'sms_template',
            'include_blank' => trans('messages.choose_template'),
            'label' => trans('messages.sms_templates'),
            'value' => '',
            'options' => \Auth::user()->customer->getSmsTemplateSelectOptions(),
        ])
    </div>
@else
    <div class="col-md-6">
        @include('helpers.form_control', [
            'type' => 'select',
            'name' => 'template',
            'id' => 'sms_template',
            'include_blank' => trans('messages.choose_template'),
            'label' => trans('messages.call_templates'),
            'value' => '',
            'options' => Auth::user()->customer->getCallTemplateSelectOptions(),
        ])
    </div>
@endif

<div class="col-md-6">
    @include('helpers.form_control', [
        'type' => 'select',
        'name' => 'twilio_numbers',
        'id' => 'twilio_numbers',
        'include_blank' => trans('messages.choose'),
        'label' => trans('messages.from_number'),
        'value' => '',
        'options' => $numbers,
    ])
</div>

@if($type == 'sms')
    <div class="col-md-6">
        @include('helpers.form_control', [
            'type' => 'textarea',
            'name' => 'message',
            'label' => trans('messages.automation.sms.body'),
            'value' => $twiliomsg->message,
            'rules' => $twiliomsg->rules(),
        ])
    </div>
@else
    <div class="col-md-6">
        @include('helpers.form_control', [
            'type' => 'text',
            'name' => 'message',
            'label' => trans('messages.automation.call.recording.url'),
            'value' => $twiliomsg->message,
            'rules' => $twiliomsg->rules(),
        ])
    </div>

@endif

<div class="col-md-6">
    @include('helpers.form_control', [
        'type' => 'text',
        'name' => 'from',
        'id' => 'from',
        'label' => trans('messages.from_number'),
        'value' => $twiliomsg->from,
        'rules' => $twiliomsg->rules(),
        'placeholder' => '+1 343556...',
        'readonly' => 'readonly',
    ])

    <input type="text" value="{{$type}}" name="type" hidden readonly>
    <input type="text" value="{{$phone}}" name="phone" hidden readonly>

</div>
