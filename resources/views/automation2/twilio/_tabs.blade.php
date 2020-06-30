<div class="d-flex align-items-center mb-4">
    <div style="width: 70%" class="mr-auto">
        <h2 class="mb-2">{{ trans('messages.automation.automation_twilio', ['type' => ucfirst($type)]) }}</h2>
        <p>{{ trans('messages.automation.automation_twilio.intro', ['type' => ucfirst($type)]) }}</p>
    </div>    
    <div class="header-action">
        <button class="btn btn-secondary d-flex align-items-center" onclick="sidebar.load(); popup.hide()">
            <i class="material-icons-outlined mr-2">
                multiline_chart
            </i>
            {{ trans('messages.automation.back_to_workflow') }}
        </button>
    </div>  
</div>

<ul class="nav nav-tabs mb-4 twilio_tabs w-100">
    <li class="nav-item">
        <a class="nav-link setup" href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@twilioSetup', [
            'uid' => $automation->uid,
            'email_uid' => $twiliomsg->uid,
        ]) }}')">
            <i class="lnr lnr-cog mr-2"></i>
            {{ trans('messages.automation.twilio.setup', ['type' => ucfirst($type)]) }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link confirm {{ (!isset($twiliomsg->id)) ? 'disabled' : '' }}" href="javascript:;"
            @if (isset($twiliomsg->id))
                onclick="popup.load('{{ action('Automation2Controller@twilioConfirm', [
                    'uid' => $automation->uid,
                    'email_uid' => $twiliomsg->uid,
                ]) }}')"
            @endif
        >
            <i class="lnr lnr-cog mr-2"></i>
            {{ trans('messages.automation.twilio.confirm', ['type' => ucfirst($type)]) }}
        </a>
    </li>
</ul>
    
<script>
    @if (isset($tab))
        $('.twilio_tabs .nav-link.{{ $tab }}').addClass('active');
    @endif
    @if (isset($sub))
        $('.twilio_tabs .dropdown-item.{{ $sub }}').addClass('active');
    @endif
</script>