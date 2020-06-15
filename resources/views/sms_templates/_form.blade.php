<div class="row">
    <div class="col-md-6">
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
        @include('helpers.form_control', [
            'required' => true,
            'type' => 'textarea',
            'name' => 'content',
            'label' => trans('messages.content'),
            'value' => $template->content,
            'rules' => ['content' => 'required']]
        )
    </div>
</div>