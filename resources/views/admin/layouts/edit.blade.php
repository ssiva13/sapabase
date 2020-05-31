@extends('layouts.black')

@section('title', trans('messages.edit_template'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('content')
    <header>
		<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" style="height: 56px;">
			<a class="navbar-brand left-logo mr-0" href="#">
				@if (\Acelle\Model\Setting::get('site_logo_small'))
					<img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="">
				@else
					<img height="22" src="{{ URL::asset('images/logo_light.png') }}" alt="">
				@endif
			</a>
			<div class="d-inline-block d-flex mr-auto align-items-center">
                <a style="" href="{{ action('Admin\LayoutController@index') }}" class="action black-back-button mr-3">
					<i class="material-icons-outlined">arrow_back</i>
				</a>
                <h1 class="">{{ $layout->subject }}</h1>
				<i class="material-icons-outlined automation-head-icon ml-2">web</i>
			</div>
			<div class="automation-top-menu">
				<button class="btn btn-primary" onclick="$('#classic-builder-form').submit()">{{ trans('messages.save') }}</button>
			</div>
            <a style="" href="{{ action('Admin\LayoutController@index') }}"
                class="action black-close-button ml-2" style="margin-right: -15px">
                <i class="material-icons-outlined">close</i>
            </a>
		</nav>
	</header>
    <form id="classic-builder-form" style="margin-top: 56px !important;" action="{{ action('Admin\LayoutController@update', $layout->uid) }}" method="POST" class="ajax_upload_form form-validate-jquery">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PATCH">

        <div class="row mr-0 ml-0">
            <div class="col-md-9 pl-0 pb-0 pr-0">
                @include('helpers.form_control', [
                    'class' => 'template-editor',
                    'label' => '',
                    'required' => true,
                    'type' => 'textarea',
                    'name' => 'content',
                    'value' => $layout->content,
                    'rules' => ['content' => 'required']
                ])                
            </div>
            <div class="col-md-3 pr-0 pb-0 sidebar pr-4 pt-4 pl-4" style="overflow:auto;background:#f5f5f5">
				@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'subject',
					'value' => $layout->subject,
					'rules' => ['subject' => 'subject']
				])
				<hr>
				@if (count($layout->tags()) > 0)
					<div class="tags_list">
						<label class="text-semibold text-teal">{{ trans('messages.available_tags') }}:</label>
						<br />
						@foreach($layout->tags() as $tag)
							@if (!$tag["required"])
								<a style="padding: 3px 7px !important;
    								font-weight: normal;" draggable="false" data-popup="tooltip" title='{{ trans('messages.click_to_insert_tag') }}' href="javascript:;" class="btn btn-secondary mb-2 mr-1 text-semibold btn-xs insert_tag_button" data-tag-name="{{ $tag["name"] }}">
									{{ $tag["name"] }}
								</a>
							@endif
						@endforeach
					</div>
				@endif
            </div>            
        </div>   
    </form> 

    <script>
        $('.sidebar').css('height', $(window).height()-56);

        var editor;
        $(document).ready(function() {
            editor = tinymce.init({
                selector: '.template-editor',
                height: $(window).height()-56,
                convert_urls: false,
                remove_script_host: false,
                skin: "oxide-dark",
                forced_root_block: "",
                plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
                imagetools_cors_hosts: ['picsum.photos'],
                menubar: 'file edit view insert format tools table help',
                toolbar: 'acelletags | undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
                toolbar_sticky: true,
                valid_elements : '*[*],meta[*]',
                extended_valid_elements : "meta[*]",
                valid_children : "+body[style],+body[meta],+div[h2|span|meta|object],+object[param|embed]",
                content_css: [
                    APP_URL.replace('/index.php','')+'/tinymce/skins/lightgray/content.fixed.css',
                ],
                external_filemanager_path:APP_URL.replace('/index.php','')+"/filemanager2/",
                filemanager_title:"Responsive Filemanager" ,
                external_plugins: { "filemanager" : APP_URL.replace('/index.php','')+"/filemanager2/plugin.min.js"},
                setup: function (editor) {
                    
                    /* Menu button that has a simple "insert date" menu item, and a submenu containing other formats. */
                    /* Clicking the first menu item or one of the submenu items inserts the date in the selected format. */
                    editor.ui.registry.addMenuButton('acelletags', {
                        text: '{{ trans('messages.editor.insert_tag') }}',
                        fetch: function (callback) {
                        var items = [];

                        @foreach(Acelle\Model\Template::tags() as $tag)
                            items.push({
                                type: 'menuitem',
                                text: '{{ "{".$tag["name"]."}" }}',
                                onAction: function (_) {
                                    editor.insertContent('{{ "{".$tag["name"]."}" }}');
                                }
                            });
                        @endforeach

                        callback(items);
                        }
                    });
                }
            });
        });
    </script>
@endsection
