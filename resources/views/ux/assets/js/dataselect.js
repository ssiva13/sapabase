$.fn.dataselect = function(action, param) {
    var box = this;
    box.holder = box.find('.selected-holder');
    box.input = box.find('input');
    box.items = box.find('.dataselect-item');
    box.selected = box.find('.dataselect-item.selected');

    // Event
    // Click dataselect item
    box.find('.dropdown-item').click(function() {
        var link = $(this);
        var value = link.attr('data-value');
        var text = link.attr('data-text');

        box.holder.html(text);
        box.input.val(value).change();

        box.items.removeClass('selected');
        link.addClass('selected');
    });

    // Init selected item
    if (box.selected.length === 0) {
        box.selected = box.items.first();
    }
    box.selected.click();

    // action / param
    if (typeof(action) !== 'undefined') {
        switch(action) {
            // Update value for dataselect
            case 'val':
                box.input.val(param);

                // find box value
                var text = 'undefined';
                box.items.each(function() {
                    var l = $(this);
                    var v = l.attr('data-value');
                    var t = l.attr('data-text');

                    if (v === param) {
                        text = t;
                    }
                });

                box.holder.html(text);
                break;
        }

        return;
    }

    return box;
};
