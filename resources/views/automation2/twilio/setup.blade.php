@extends('layouts.popup.medium')

@section('content')
        
    @include('automation2.twilio._tabs', ['tab' => 'setup'])
        
    <h5 class="mb-3"> Sms/Call Setup</h5>
    <p>Please fill-up sms/call information below. They will be used to apply to all calls/sms to customers.</p>
    
    <form id="twilioSetup" action="{{ action('Automation2Controller@twilioSetup', $automation->uid) }}" method="POST">
        {{ csrf_field() }}
        
        <input type="hidden" name="twilio_uid" value="{{ $twiliomsg->uid }}" />
        <input type="hidden" name="action_id" value="{{ $twiliomsg->action_id }}" />
        <input type="hidden" name="type" value="{{ $type }}" />

        <div class="row">
            <div class="col-md-6">
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'subject',
                    'label' => trans('messages.subject'),
                    'value' => $twiliomsg->subject,
                    'rules' => $twiliomsg->rules(),
                ])
            </div>
            <div class="col-md-6">
                @include('helpers.form_control', [
                    'type' => 'select',
                    'name' => 'template',
                    'id' => 'sms_template',
                    'include_blank' => trans('messages.automation.choose_list'),
                    'label' => trans('messages.sms_templates'),
                    'value' => '',
                    'options' => Auth::user()->customer->getTemplateSelectOptions(),
                ])
            </div>
            <div class="col-md-6">
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'from_name',
                    'label' => trans('messages.from_name'),
                    'value' => $twiliomsg->from_name,
                    'rules' => $twiliomsg->rules(),
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
                ])
            </div>
            <div class="col-md-6">
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'reply_to',
                    'label' => trans('messages.reply_to'),
                    'value' => $twiliomsg->reply_to,
                    'rules' => $twiliomsg->rules(),
                    'placeholder' => '+1 343556...',
                ])
            </div>
        </div>
        
        <div class="text-right mt-5">
            <button class="btn btn-secondary">
                <span class="d-flex align-items-center">
                    <span>{{ trans('messages.email.setup.save_next') }}</span> <i class="material-icons">keyboard_arrow_right</i>
                </span>
            </button>
        </div>
    </form>
    
    <script>
        $('#twilioSetup').submit(function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');

            console.log(url);

            // loading effect
            popup.loading();

            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                statusCode: {
                    // validate error
                    400: function (res) {
                       popup.loadHtml(res.responseText);
                    }
                 },
                 success: function (response) {
                    popup.load(response.url);

                    // set node title
                    tree.getSelected().setTitle(response.title);
                    // merge options with reponse options
                    tree.getSelected().setOptions($.extend(tree.getSelected().getOptions(), {init: "true", type: "twilio"}));
                    tree.getSelected().setOptions($.extend(tree.getSelected().getOptions(), response.options));

                    doSelect(tree.getSelected());

                    // validate
					tree.getSelected().validate();

                    // save tree
					saveData();

                    notify('success', '{{ trans('messages.notify.success') }}', response.message);
                 }
            });
        });

        $('[name="from"]').keyup(function() {
            $('[name="reply_to"]').val($(this).val());
        });

        $('#sms_template').change(function() {
            var uri = '{{ action('SmsTemplateController@get') }}';
            $.ajax({
                url: uri,
                method: 'GET',
                data: {template_uid : $(this).val()},
                statusCode: {
                    // validate error
                    400: function (res) {
                    }
                },
                success: function (response) {
                    $('[name="message"]').val(response);
                }
            });
        });
    </script>
@endsection