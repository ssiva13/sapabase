@include('automation2._info')

@include('automation2._tabs', ['tab' => 'settings'])
    
<h5 class="mb-20 mt-3"h5>
    {{ trans('messages.automation.action.twilio') }}
</h5>
<p class="mb-10">
    {{ trans('messages.automation.action.twilio.intro') }}
</p>

<form action="{{ action('Automation2Controller@twilioSetup', $automation->uid) }}" method="POST" class="form-validate-jqueryz">
    {{ csrf_field() }}
    
    @include('automation2.twilio._summary')
    
    <div class="trigger-action mt-4">    
        <span class="btn btn-secondary twilio-settings-change mr-1"
        >
            {{ trans('messages.automation.twilio.settings') }}
        </span>
    </div>
    
<form>

<h6 class="mb-2 mt-5 text-danger">
    {{ trans('messages.automation.dangerous_zone') }}
</h6>
<p class="">
    {{ trans('messages.automation.action.delete.confirm') }}        
</p>
<div class="mt-3">
    <a href="{{ action('Automation2Controller@twilioDelete', [
        'uid' => $automation->uid,
        'twilio_uid' => $twiliomsg->uid,
    ]) }}" data-confirm="{{ trans('messages.automation.twilio.delete.confirm') }}" class="btn btn-danger twilio-action-delete">
        <i class='lnr lnr-trash mr-2'></i> {{ trans('messages.automation.delete_this_action') }}
    </a>
</div>

<script>
    // Click on exist action
    $('.twilio-settings-change').click(function(e) {
        e.preventDefault();
        var url = '{{ action('Automation2Controller@twilioSetup', ['uid' => $automation->uid, 'twilio_uid' => $twiliomsg->uid]) }}';

        popup.load(url);
    });
    
    $('.twilio-action-delete').click(function(e) {
        e.preventDefault();

        var confirm = $(this).attr('data-confirm');
        var url = $(this).attr('href');

        var dialog = new Dialog('confirm', {
            message: confirm,
            ok: function(dialog) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: CSRF_TOKEN
                    },
                    statusCode: {
                        // validate error
                        400: function (res) {
                            response = JSON.parse(res.responseText);
                            // notify
                            notify('notice', '{{ trans('messages.notify.warning') }}', response.message);
                        }
                    },
                    success: function (response) {
                        // remove current node
                        tree.getSelected().remove();

                        // save tree
                        saveData(function() {
                            // notify
                            notify('success', '{{ trans('messages.notify.success') }}', response.message);

                            // load default sidebar
                            sidebar.load('{{ action('Automation2Controller@settings', $automation->uid) }}');
                        });
                    }
                });
            },
        });
    });
</script>