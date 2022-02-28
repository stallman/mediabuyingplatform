$(document).ready(function () {
    $('#conversion_teaserClick').keyup(debounce(function(e) {
        selectBuyerByClckId($(this).val());
    }, 500));
});

function selectBuyer(buyerId) {
    $('#conversion_mediabuyer').val(buyerId);
    $('#conversion_mediabuyer').trigger('change');
}

function selectBuyerByClckId(clickId) {
    if (clickId == '') {
        selectEmptyOption();
        return;
    }

    $.ajax({
        method: 'POST',
        url: '/admin/conversion/buyer-by-click/' + clickId,
        success: function (data) {
            if (data.buyerId) {
                selectBuyer(data.buyerId);
            } else {
                selectEmptyOption();
            }
        },
        error: function (data) {
            alert('Ошибка получения партнерок по id клика')
        }
    })
}

function selectEmptyOption() {
    $("#conversion_mediabuyer").val($("#conversion_mediabuyer option:first").val());
    $('#conversion_mediabuyer').trigger('change');
}

//ожидание окончания ввода прежде чем отправить аякс запрос
function debounce(callback, ms) {
    var timer = 0;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function() {
            callback.apply(context, args);
        }, ms || 0);
    };
}