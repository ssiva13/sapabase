function selectElement(box, ele) {
    var tag = ele.prop("tagName");
    box.sidebar_element_title_box.html(tag);

    // add selected class
    box.content.find('*').removeClass('self-builder-selected');
    ele.addClass('self-builder-selected');
}

$.fn.selfBuilder = function(action, param) {
    var box = this;
    box.sidebar_element_title_box = box.find('.element-options .element-title');
    box.frame = $('.self-builder-frame');

    // Atfer iframe loaded
    box.frame.on('load', function() {
        box.content = box.frame.contents().find('.self-builder-content');

        // Stop all link event
        box.content.find('a').click(function(e) {
            e.preventDefault();
        });

        // element click event
        box.content.click(function(e) {
            var ele = $(e.target);
            selectElement(box, ele);
        });
    });

    return box;
};

$('.self_builder').selfBuilder();
