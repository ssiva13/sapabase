<div class="mc_section boxing">
    <div class="row">
        <div class="col-md-6">
            <h3 class="mt-0">{{ trans('messages.sending_servers.sending_identity') }}</h3>
            <p>
                {!! trans('messages.sending_servers.sending_identity.sendgrid.intro', ['link' => '']) !!}
            </p>
            @if (is_null($identities))
                @include('elements._notification', [
                    'level' => 'warning',
                    'title' => 'Error fetching identities list',
                    'message' => 'Please check your connection',
                ])
            @else
                <table class="table table-box table-box-head field-list">
                    <thead>
                        <tr>
                            <td>{{ trans('messages.domain') }}</td>
                            <td>{{ trans('messages.status') }}</td>
                            <td>{{ trans('messages.action') }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($identities as $domain)
                            <tr class="odd">
                                <td>
                                    {{ $domain }}
                                </td>
                                <td>
                                    <span class="badge badge-success badge-lg">{{ trans('messages.sending_identity.status.active') }}</span>
                                </td>
                                <td>
                                    @if (checkEmail($domain))
                                        <input type="checkbox" name="options[emails][]" value="{{ $domain }}" class="switchery"
                                            {{ $server->isEmailEnabled($domain) ? " checked" : "" }}
                                        />
                                    @else
                                        <input type="checkbox" name="options[domains][]" value="{{ $domain }}" class="switchery"
                                            {{ $server->isDomainEnabled($domain) ? " checked" : "" }}
                                        />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <br>
            <a href="https://app.sendgrid.com" type="button" target="_blank"
              class="btn btn-mc_default mr-10">
                {{ trans('messages.sending_serbers.go_to_sendgrid_dashboard') }}
            </a>

            <p class="mt-40">
                {{ trans('messages.sending_serbers.sendgrid.allow_verify.intro') }}
            </p>

            @include('helpers.form_control', [
                'type' => 'checkbox2',
                'label' => trans('messages.allow_verify_domain_against_acelle'),
                'name' => 'options[allow_verify_domain_against_acelle]',
                'value' => $server->getOption('allow_verify_domain_against_acelle'),
                'help_class' => 'sending_server',
                'options' => ['no', 'yes'],
            ])

            <hr>
            <div class="mt-20">
                <button class="btn btn-mc_primary mr-10">{{ trans('messages.save') }}</button>
                <a href="{{ action('Admin\SendingServerController@index') }}" type="button" class="btn btn-mc_inline">
                    {{ trans('messages.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>
