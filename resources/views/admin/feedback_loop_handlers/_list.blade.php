                        @if ($items->count() > 0)
                            <table class="table table-box pml-table"
                                current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
                            >
                                @foreach ($items as $key => $item)
                                    <tr>
                                        <td width="1%">
                                            <div class="text-nowrap">
                                                <div class="checkbox inline">
                                                    <label>
                                                        <input type="checkbox" class="node styled" custom-order="{{ $item->custom_order }}"
                                                            name="ids[]" value="{{ $item->uid }}"/>
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <h5 class="no-margin text-bold">
                                                <a class="kq_search" href="{{ action('Admin\FeedbackLoopHandlerController@edit', $item->uid) }}">{{ $item->name }}</a>
                                            </h5>
                                            @if (Auth::user()->can('readAll', $item))
                                                @include ('admin.modules.admin_line', ['admin' => $item->admin])
                                                <br />
                                            @endif
                                            <span class="text-muted">{{ trans('messages.created_at') }}: {{ Tool::formatDateTime($item->created_at) }}</span>
                                        </td>
                                        <td>
                                            <span class="no-margin stat-num kq_search">{{ $item->host }}</span>
                                            <br />
                                            <span class="text-muted">{{ trans('messages.host') }}</span>
                                        </td>
                                        <td>
                                            <span class="no-margin stat-num kq_search">{{ $item->username }}</span>
                                            <br />
                                            <span class="text-muted">{{ trans('messages.username') }}</span>
                                        </td>
                                        <td class="text-right">
                                            @can('update', $item)
                                                <a href="{{ action('Admin\FeedbackLoopHandlerController@edit', $item->uid) }}" data-popup="tooltip" title="{{ trans('messages.edit') }}" type="button" class="btn btn-info btn-icon"><i class="icon-pencil"></i> {{ trans('messages.edit') }}</a>
                                            @endcan
                                            @if(Auth::user()->admin->can('delete', $item) || Auth::user()->admin->can('test', $item))
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
                                                    <div data-simplebar style="max-height: 230px;" class="dropdown-menu dropdown-menu-right" >
                                                        <div class="media-body">
                                                            <ol class="activity-feed mb-0">
                                                                @can('test', $item)
                                                                    <li class="dropdown-item">
                                                                        <a href="{{ action('Admin\FeedbackLoopHandlerController@test', $item->uid) }}" data-method="POST" type="button" class="ajax_link">
                                                                            <div class="text-muted">
                                                                                <p class="mb-1">
                                                                                    <i class="icon-rotate-cw3"></i> {{ trans('messages.feedback_loop_handler.test') }}
                                                                                </p>
                                                                            </div>
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                                @can('delete', $item)
                                                                    <li class="dropdown-item">
                                                                        <a delete-confirm="{{ trans('messages.delete_feedback_loop_handlers_confirm') }}" href="{{ action('Admin\FeedbackLoopHandlerController@delete', ["uids" => $item->uid]) }}">
                                                                            <div class="text-muted">
                                                                                <p class="mb-1">
                                                                                    <i class="icon-trash"></i> {{ trans('messages.delete') }}
                                                                                </p>
                                                                            </div>
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            @include('elements/_per_page_select')
                            {{ $items->links() }}
                        @elseif (!empty(request()->keyword) || !empty(request()->filters["type"]))
                            <div class="empty-list">
                                <i class="glyphicon glyphicon-transfer"></i>
                                <span class="line-1">
                                    {{ trans('messages.no_search_result') }}
                                </span>
                            </div>
                        @else
                            <div class="empty-list">
                                <i class="glyphicon glyphicon-transfer"></i>
                                <span class="line-1">
                                    {{ trans('messages.feedback_loop_handler_empty_line_1') }}
                                </span>
                            </div>
                        @endif
