$("#settings-fields-form input:checkbox").on('change', function (e) {
    e.preventDefault()
    let data = {}
    data.traffic = $('#fields_settings_traffic :input').serializeArray();
    data.leads = $('#fields_settings_leads :input').serializeArray();
    data.finances = $('#fields_settings_finances :input').serializeArray();

    $('#settings-fields-form input:checkbox').each(function () {
        let columnName = $(this).val();
        let $th = $('#data-table thead').find('[data-column-name="' + columnName + '"]');
        if ($(this).prop('checked') === false) {
            $th.hide().attr('data-hidden', 'true');
        } else {
            $th.show().attr('data-hidden', 'false');
        }
    });

    $.ajax({
        data: {'settings-fields': data},
        method: 'post',
        url: '/mediabuyer/statistic/traffic-analysis/settings-fields/update',
        beforeSend: function( xhr ) {
            $("#settings-fields-form input:checkbox").prop('disabled', true);
        },
        success: function (data) {
            $("#settings-fields-form input:checkbox").prop('disabled', false);
        },
        error: function (data) {
            alert('Произошла ошибка. Перезагрузите страницу и попробуйте снова.')
        }
    })
})