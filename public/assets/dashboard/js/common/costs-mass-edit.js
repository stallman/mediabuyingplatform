$(document).ready(function (e) {
    $('.costs-mass-edit').click(function(e) {
        e.preventDefault();
        if ($('.bulk-action-item:checked').length <= 0) {
            $('.costs-mass-edit-error-alert').show();
        } else {
            $('.costs-mass-edit-error-alert').hide();
            $('#costs-mass-edit-modal').modal();
        }
    });

    $('#costs-mass-edit-form').submit(function(e) {
        e.preventDefault();
        
        let formData = $(this).serializeArray();
        let ids = $('.bulk-action-item:checked').map((i, el) => $(el).data('item-id')).get();
        formData.push({'name':'ids', 'value': ids});
        
        $.ajax({
            data: formData,
            method: 'POST',
            url: '/mediabuyer/costs/mass-edit',
            success: function () {
                $('.costs-mass-edit-form-error-alert').text("").hide();
                $('#costs-mass-edit-modal').modal('hide');
                location.reload();
            },
            error: function (error) {
                $('.costs-mass-edit-form-error-alert').text(error.responseJSON.message).show();
            }
        })        
    });
})