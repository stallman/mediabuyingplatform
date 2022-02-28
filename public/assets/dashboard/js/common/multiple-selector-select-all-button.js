$(document).ready(function () {
    $('.multiple-selector-help').append('<button class="multiple-selector-select-all btn btn-info">Выбрать все</button>');
    $('.multiple-selector-select-all').click(function (e) {
        e.preventDefault()
        $(this).closest('.form-group').find('.multiple-selector option').prop('selected', true);
    });
    $('.selected-all option').prop('selected', true);
});