@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h3 class="mb-3">
                {{ trans('messages.automation.trigger.' . $key) }}
            </h3>
            <p class="mb-10">
                {{ trans('messages.automation.trigger.' . $key . '.intro') }}
            </p>
				
			<form id="trigger-select" action="{{ action("Automation2Controller@triggerSelect", $automation->uid) }}" method="POST" class="form-validate-jqueryz">
				{{ csrf_field() }}
				
				<input type="hidden" name="options[key]" value="{{ $key }}" />
				
				@if(View::exists('automation2.trigger.' . $key))
					@include('automation2.trigger.' . $key)
				@endif
				
				<button class="btn btn-secondary select-trigger-confirm mt-2"
					data-url="{{ action('Automation2Controller@triggerSelect', ['uid' => $automation->uid]) }}"
				>
						{{ trans('messages.automation.trigger.select_confirm') }}
				</button>
			</form>
        </div>
    </div>

	<script>
		// when click confirm select trigger type
		$('.select-trigger-confirm').click(function(e) {
			e.preventDefault();
		
			var url = $(this).attr('data-url');
			var data = $(this).closest('form').serialize();
			
			// show loading effect
			popup.loading();

			$.ajax({
				url: url,
				method: 'POST',
				data: data,
				statusCode: {
					// validate error
					400: function (res) {
						popup.loadHtml(res.responseText);
					}
				},
				success: function (response) {
					
					// todo: when trigger selected
					// console.log('Trigger was selected');
					
					// set node title
					tree.setTitle(response.title);
					// merge options with reponse options
					tree.setOptions(response.options);
					tree.setOptions($.extend(tree.getOptions(), {init: "true"}));

					// validate
					tree.validate();
					
					// save tree
					saveData(function() {
						// select trigger
						doSelect(tree);

						// hide popup
						popup.hide();
						
						// notify success message
						notify('success', '{{ trans('messages.notify.success') }}', response.message);
						
						// Edit Trigger
						EditTrigger('{{ action('Automation2Controller@triggerEdit', $automation->uid) }}' + '?key=' + tree.getOptions().key);
					});
				}
			});
		});
	</script>
@endsection
