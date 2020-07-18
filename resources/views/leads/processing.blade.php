@extends('layouts.popup.medium')

@section('title', trans('messages.twilio.api.response.data', [ 'type' => ucfirst($type), 'from' => $from, 'to' => $phone]))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="empty-list">
                <i class="icon-phone"></i>
                <span class="line-1">
                    {{ trans('messages.twilio.api.response', ['type' => ucfirst($type)]) }}
                </span>
                <hr/>

                    @if($code == 200)
                        <span class="line-2">
                                @if($type == 'sms')
                                    {{ trans('messages.sms.on') }}
                                @else
                                    {{ trans('messages.call.on') }}<span id="call_progress"></span>
                                @endif
                            <br/>
                            <span class="label label-flat" id="call_status"></span>
                        </span>
                    @else
                        <span class="line-2">
                            <span class="label label-flat bg-danger">
                                {{ trans('messages.failed') }}
                            </span>
                        </span>
                        <br>
                        <span class="text-danger text-muted">
                            {{ $message }}
                        </span>
                    @endif
            </div>
        </div>
    </div>
    <script>
        let type = '{{ $type }}', message = '{{ $message }}', dots = 0;
        let statusOptions = [
            'no-answer', 'completed', 'busy', 'canceled', 'failed'
        ]

        setInterval(progress,5000);

        function progress() {
            if(type != 'sms'){
                let uri = '{{ action('TwilioController@callStatus') }}' + '?message=' + message;
                let callStatus = $('#call_status');
                let callProgress = $('#call_progress');
                if($.inArray(callStatus.html(), statusOptions) == -1){
                    $.ajax({
                        url: uri,
                        method: 'GET',
                        statusCode: {
                            // validate error
                            400: function (res) {
                            }
                        },
                        success: function (response) {
                            if(dots < 3) {
                                callProgress.append('.');
                                dots++;
                            } else {
                                callProgress.html('');
                                dots = 0;
                            }
                            if(response == 'completed' || response == 'ringing' || response == 'in-progress'){
                                callStatus.addClass('bg-success');
                            }else{
                                callStatus.addClass('bg-danger')
                            }
                            callStatus.html(response);
                        }
                    });
                }

            }
        }
    </script>
@endsection