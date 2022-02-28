$(document).ready(function () {
    let saveButton = $('#save-button')
    let data = {}
    data['active'] = []
    data['deactive'] = []

    $(saveButton).on('click', function (e) {
        e.preventDefault()
        $(document).find('input[type=checkbox]').each(function (key, value) {
            let itemId = $(value).val();
            if ($(value).is(':checked')) {
                data['active'].push(itemId);
            } else {
                data['deactive'].push(itemId)
            }
        });
        sendAjaxRequest(data);
    })

    function sendAjaxRequest(data) {
        $.ajax({
            data: {'data': data},
            method: 'post',
            url: $(saveButton).data('url'),
            success: function (data) {
                window.location.replace(data.route_to_redirect);
            },
            error: function (data) {
                window.location.replace(data.route_to_redirect);
            }
        })
    }
})