$('#black_white_list_save').click(function (e) {
    e.preventDefault();
    
    if (validateRequiredFields()) {
        let form = $('#black-white-lists-form')
        let data = {
            'report_type': $('#black_white_list_report_type').val(),
            'data_type': $('#black_white_list_data_type').val(),
            'source': $('#black_white_list_sources').val(),
            'news': $('#black_white_list_news').val(),
            'campaign': $('#black_white_list_campaigns').val(),
            'format': $('#black_white_list_format').val()
        }
    
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: data,
            success: function (response) {
                $('#list').val(response)
            }
        });
    };
})

$('select[required]:visible').change(function() {
    $(this).removeClass('border-danger');
})

function validateRequiredFields() {
    let isValid = true;

    $('select[required]:visible').each(function(i, item) {
        if ($(item).find(':selected').val() == "") {
            isValid = false;
            $(item).addClass('border-danger');
            scrollTo(item);
            return false;
        } else {
            $(item).removeClass('border-danger');
        }
    })

    return isValid;
}

function scrollTo(item) {
    let id = '#' +  $(item).attr('id');
    document.querySelector(id).scrollIntoView({ behavior: 'smooth' });
}