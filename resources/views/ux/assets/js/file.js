$(document).on('click', '#close-preview', function(){
    $('.file-upload').popover('hide');
    // Hover befor close the preview
    $('.file-upload').hover(
        function () {
           $('.file-upload').popover('show');
        },
         function () {
           $('.file-upload').popover('hide');
        }
    );
});

$(function() {
    // Create the close button
    var closebtn = $('<button/>', {
        type:"button",
        text: 'x',
        id: 'close-preview',
        style: 'font-size: initial;',
    });
    closebtn.attr("class","close pull-right");
    // Set the popover default content
    $('.file-upload').popover({
        trigger:'manual',
        html:true,
        title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
        content: "There's no image",
        placement:'bottom'
    });
    // Clear event
    $('.file-upload-clear').click(function(){
        $('.file-upload').attr("data-content","").popover('hide');
        $('.file-upload-filename').val("");
        $('.file-upload-clear').hide();
        $('.file-upload-input input:file').val("");
        $(".file-upload-input-title").text("Browse");
    });
    // Create the preview image
    $(".file-upload-input input:file").change(function (){
        var img = $('<img/>', {
            id: 'dynamic',
            width:250,
            height:200
        });
        var file = this.files[0];
        var reader = new FileReader();
        // Set preview image into the popover data-content
        reader.onload = function (e) {
            $(".file-upload-input-title").text($(".file-upload-input-title").attr('change-label'));
            $(".file-upload-clear").show();
            $(".file-upload-filename").val(file.name);
            img.attr('src', e.target.result);
            //$(".file-upload").attr("data-content",$(img)[0].outerHTML).popover("show");
        }
        reader.readAsDataURL(file);
    });
});
