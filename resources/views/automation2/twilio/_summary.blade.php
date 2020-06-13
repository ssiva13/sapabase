<ul class="key-value-list mt-2">
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-muted">textsms</i>
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.automation.email.subject') }}
            </label>
            <div class="value">
                {{ $twiliomsg->subject }}
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@twilioSetup', [
                'uid' => $automation->uid,
                'email_uid' => $twiliomsg->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.setup') }}
            </a>
        </div>
    </li>
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-muted">my_location</i>
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.automation.email.from') }}
            </label>
            <div class="value">
                {{ $twiliomsg->from }}
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@twilioSetup', [
                'uid' => $automation->uid,
                'email_uid' => $twiliomsg->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.twilio.setup') }}
            </a>
        </div>
    </li>
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-muted">reply</i>
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.reply_to') }}
            </label>
            <div class="value">
                @if($twiliomsg->reply_to)
                    {{ $twiliomsg->reply_to }}
                @else
                    <span class="text-warning small">
                        <i class="material-icons-outlined">warning</i>
                        {{ trans('messages.email.no_reply_to') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@twilioSetup', [
                'uid' => $automation->uid,
                'email_uid' => $twiliomsg->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.setup') }}
            </a>
        </div>
    </li>
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            @if($twiliomsg->message)
                <i class="material-icons-outlined text-muted">vertical_split</i>
            @else
                <i class="material-icons-outlined text-muted">vertical_split</i>
            @endif
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.automation.email.summary.content') }}
            </label>
            <div class="value">
                @if($twiliomsg->message)
                    {{ trans('messages.automation.email.content.last_edit', [
                        'time' => $twiliomsg->updated_at->diffForHumans(),
                    ]) }}
                @else
                    <span class="text-danger small">
                        <i class="material-icons-outlined">error_outline</i>
                        {{ trans('messages.automation.email.no_content') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@emailTemplate', [
                'uid' => $automation->uid,
                'email_uid' => $twiliomsg->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.summary.content.update') }}
            </a>
        </div>
    </li>
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-muted">track_changes</i>
        </div>

        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@twilioSetup', [
                'uid' => $automation->uid,
                'email_uid' => $twiliomsg->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.setup') }}
            </a>
        </div>
    </li>
</ul> 