$(function() {
    // Tinymce inline setup
    tinymce.init({
        selector: 'div.self-builder-content',
        theme: 'inlite',
        inline_styles : true,
        force_br_newlines : false,
        force_p_newlines : false,
        forced_root_block : '',
        valid_children : "body[style]",
        extended_valid_elements: '*[*]',        
        inline: true,
        
    });
});
