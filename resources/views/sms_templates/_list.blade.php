@if ($templates->count() > 0)
    <table class="table table-box pml-table"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}">
        @foreach ($templates as $key => $template)
            <tr>
                <td width="15%">
                    <span class="text-muted">
                        {!! is_object($template->customer) ? '<i class="icon-user"></i>' . $template->customer->displayName() : '' !!}
                    </span>
                    <br>
                    <span class="text-muted">{{ trans('messages.updated_at') }}: {{ Tool::formatDateTime($template->created_at) }}</span>
                </td>

                <td width="10%" class="ml-2">
                    <div class="single-stat-box pull-left">
                        <span class="no-margin stat-num">{{$template->name}}</span>
                        <br>
                        <span class="text-muted text-nowrap">{{ trans('messages.name') }}</span>
                    </div>
                </td>
                <td>
                    <div class="single-stat-box pull-left">
                        <span class="text-black-50 text-capitalize">{{ substr($template->content, 0, 100) }} ... </span>
                        <br>
                        <span class="text-muted text-nowrap">{{ trans('messages.content') }}</span>
                    </div>
                </td>

                <td class="text-right">
                    <span onclick="SmsTemplateEdit('{{ action('SmsTemplateController@edit', $template->uid) }}')" type="button" class="btn bg-grey btn-icon sms-edit">
                        {{ trans('messages.edit') }}
                    </span>
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', ["items" => $templates])
    {{ $templates->links() }}


@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-magazine"></i>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-magazine"></i>
        <span class="line-1">
            {{ trans('messages.template_empty_line_1') }}
        </span>
    </div>
@endif
