@extends('layouts.popup.medium')

@section('content')
        
    @include('automation2.twilio._tabs', ['tab' => 'setup'])
        
    <h5 class="mb-3">Twilo Sms/Call Setup</h5>
    <p>Please fill-up twilio sms/call information below. They will be used to apply to all calls/sms to customers.</p>
    
    <form id="twilioSetup" action="{{ action('Automation2Controller@twilioSetup', $automation->uid) }}" method="POST">
        {{ csrf_field() }}
        
        <input type="hidden" name="email_uid" value="{{ $twiliomsg->uid }}" />
        <input type="hidden" name="action_id" value="{{ $twiliomsg->action_id }}" />
    
        <div class="row">
            <div class="col-md-6">
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'from_name',
                    'label' => trans('messages.from_name'),
                    'value' => $twiliomsg->from_name,
                    'rules' => $twiliomsg->rules(),
                ])
            </div>
            <div class="col-md-6">
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'message',
                    'label' => trans('messages.twilio_message'),
                    'value' => $twiliomsg->message,
                    'rules' => $twiliomsg->rules(),
                ])
            </div>
            <div class="col-md-6">
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'from',
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

        $('[name="from"]').blur(function() {
            $('[name="reply_to"]').val($(this).val());
        });
    </script>
@endsection