function datalistAjaxLinkRun(link, box, params) {
    var url = link.attr('href');
    var method = link.attr('data-method');

    if (typeof(method) == 'undefined' || method.trim() == '') {
        method = 'get';
    }

    // data
    var data = {
        _token: CSRF_TOKEN,
    };

    // more data
    if (typeof(params) != 'undefined') {
        data = Object.assign({},data, params);
    }

    addMaskLoading();

    $.ajax({
        method: method,
        url: url,
        data: data,
    }).done(function( data ) {
        removeMaskLoading();

        notify(data.message, data.type);
        datalistLoadList(box);
    });
}

function datalistAfterLoadedEvents(box) {
    box.check_items = box.find('.check-item');
    box.form = box.find(':input');

    // Check item click
    box.check_items.change(function() {
        var checked = $(this).is(':checked');

        if (checked) {
            if (datalistIsAllChecked(box)) {
                box.check_all.prop('checked', true);
            }
        } else {
            box.check_all.prop('checked', false);
        }

        // Check some
        if (datalistIsSomeChecked(box)) {
            box.check_all.addClass('checked-some');
        } else {
            box.check_all.removeClass('checked-some');
        }

        // show action
        if (datalistIsSomeChecked(box) || datalistIsAllChecked(box)) {
            box.actions_box.show();
        } else {
            box.actions_box.hide();
        }
    });

    // row ajax action
    box.find('.datalist-row-action-ajax').click(function(e) {
        e.preventDefault();

        // confirm
        var link = $(this);
        var confirm = link.attr('data-confirm');
        var confirm_url = link.attr('data-confirm-url');

        // confirm message
        if (typeof(confirm) != 'undefined' && confirm.trim() != '') {
            confirmModal(confirm, function() {
                datalistAjaxLinkRun(link, box);
            });
            return;
        }

        // confirm url
        if (typeof(confirm_url) != 'undefined' && confirm_url.trim() != '') {
            confirmModal(confirm_url, function() {
                datalistAjaxLinkRun(link, box);
            }, true);
            return;
        }

        datalistAjaxLinkRun(link, box);
    });

    // change per page
    box.find('select[name=per_page]').change(function() {
        datalistLoadList(box);
    });

    // click pagination event
    box.find('.pagination li a').click(function(e) {
        e.preventDefault();

        var url = $(this).attr('href');
        // box.find('input[name=page]').val();

        datalistLoadList(box, url);
    });
}

function datalistIsSomeChecked(box) {
    var checked_some = false;
    var unchecked_some = false;
    box.check_items.each(function() {
        var checked = $(this).is(':checked');

        if (checked) {
            checked_some = true;
            if (unchecked_some) {
                return;
            }
        } else {
            unchecked_some = true;
            if (checked_some) {
                return;
            }
        }
    });

    return checked_some && unchecked_some;
}

function datalistIsAllChecked(box) {
    var checked_all = true;
    box.check_items.each(function() {
        var checked = $(this).is(':checked');

        if (!checked) {
            checked_all = false;
            return;
        }
    });
    return checked_all;
}

function datalistLoadList(box, customUrl) {
    var url = box.url;
    var data = box.form.serialize();

    if (typeof(customUrl) !== 'undefined') {
        url = customUrl;
    }

    box.container.css('opacity', '0.6');
    if (box.find('.loader').length === 0) {
        box.container.prepend(htmlLoader());
    }

    // ajax update custom sort
	if(datalists[url] && datalists[url].readyState != 4){
		datalists[url].abort();
	}
    datalists[url] = $.ajax({
        method: "GET",
        url: url,
        data: data
    }).done(function( data ) {
        box.container.html( data );
        box.container.css('opacity', '1');

        datalistAfterLoadedEvents(box);

        // uncheck check all and hide action
        box.check_all.prop('checked', false);
        box.actions_box.hide();
    });
}

var datalists = {};
$.fn.datalist = function(action, param) {
    var box = this;
    box.check_all = box.find('.check-all');
    box.check_items = box.find('.check-item');
    box.actions_box = box.find('.actions-box');
    box.container = box.find('.datalist-container');
    box.url = box.attr('data-url');
    box.form = box.find(':input');

    // Load datalist first time
    datalistLoadList(box);

    // Check all click
    box.check_all.change(function() {
        var checked = $(this).is(':checked');

        if (checked) {
            box.check_items.prop('checked', true);

            // Show action box
            box.actions_box.show();
        } else {
            box.check_items.prop('checked', false);

            // Hide action box
            box.actions_box.hide();
        }

        box.check_all.removeClass('checked-some');
    });

    // Action list, Events when first init
    // row ajax action
    box.find('.datalist-list-action-ajax').click(function(e) {
        e.preventDefault();

        // confirm
        var link = $(this);
        var confirm = link.attr('data-confirm');
        var confirm_url = link.attr('data-confirm-url');
        var form = box.find(':input');

        if (typeof(confirm) != 'undefined' && confirm.trim() != '') {
            confirmModal(confirm, function() {
                datalistAjaxLinkRun(link, box, form.serializeHash());
            });
            return;
        }

        // confirm url
        if (typeof(confirm_url) != 'undefined' && confirm_url.trim() != '') {
            confirmModal(confirm_url, function() {
                datalistAjaxLinkRun(link, box, form.serializeHash());
            }, true, form.serializeHash());
            return;
        }

        datalistAjaxLinkRun(link, box);
    });

    // Load list when filtering
    box.find('.datalist-filter input:not(.check-all), .datalist-filter select').change(function() {
        datalistLoadList(box);
    });
    box.find('.datalist-filter .keyword-input').keyup(function() {
        datalistLoadList(box);
    });

    // Change sort direction
    box.find('.sort-direction-but').click(function() {
        var input = $(this).prev();

        if (input.val() != 'desc') {
            input.val('desc').change();
            $(this).find('i').attr('class', 'ion-chevron-down');
        } else {
            input.val('asc').change();
            $(this).find('i').attr('class', 'ion-chevron-up');
        }
    });

    return box;
};
