let mediaBuyer = $('#conversion_mediabuyer');
mediaBuyer.change(function() {
    let form = $(this).closest('form');
    let data = {};
    data[mediaBuyer.attr('name')] = mediaBuyer.val();
    $.ajax({
        url : form.attr('action'),
        type: form.attr('method'),
        data : data,
        success: function(html) {
            $('#conversion_affilate').replaceWith(
                $(html).find('#conversion_affilate')
            );
        }
    });
});