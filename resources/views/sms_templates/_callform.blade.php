<div class="row">
    <div class="col-md-12">
        @include('helpers.form_control',[
            'required' => true,
            'type' => 'text',
            'label' => trans('messages.template_name'),
            'name' => 'name',
            'value' => $template->name,
            'rules' => ['name' => 'required']]
        )
    </div>
    <div class="col-md-6">
        @include('helpers.form_control',[
            'type' => 'text',
            'label' => trans('messages.template_url'),
            'name' => 'template_url',
            'value' => $template->content ? $template->content : 'http://',
            'readonly' => true,
        ])
    </div>
    <div class="col-md-6">
        @include('helpers.form_control', [
            'required' => true,
            'type' => 'file',
            'name' => 'content',
            'label' => trans('messages.content'),
            'value' => '',
            'rules' => ['content' => 'required']]
        )
    </div>
    <div class="col-md-12">
        <div class="checkbox-box-group" >
            @include('helpers.form_control', [
                'type' => 'checkbox2',
                'name' => 'extras',
                'id' => 'extras',
                'label' => 'Set As AutoRespond Audio',
                'value' => false,
                'options' => [false, true],
                'help_class' => '',
            ])
        </div>
    </div>
</div>
