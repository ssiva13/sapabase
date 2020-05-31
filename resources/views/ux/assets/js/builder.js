var store_callback;
$(function() {
    // Header save & close button
    $(document).on('click', '.editor-save-close', function(e) {
        e.preventDefault();

        var href = $(this).attr('href');

        // Save page
        editor.store();

        // redirect to funnel steps
        store_callback = function() {
            window.location = href;
        };
    });

    // Header save button
    $(document).on('click', '.editor-save', function(e) {
        e.preventDefault();

        var href = $(this).attr('href');
        var message = $(this).attr('data-message');

        // Save page
        editor.store();

        // redirect to funnel steps
        store_callback = function() {
            notify(message,'success')
        };
    });

    editor.on('storage:store', function(model) {
        store_callback();
    });
});
