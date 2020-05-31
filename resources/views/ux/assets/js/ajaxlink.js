$.fn.ajaxlink = function(action, param) {
    var link = this;
    link.url = link.attr('href');

    link.click(function(e) {
        e.preventDefault();
    });

    return link;
};
