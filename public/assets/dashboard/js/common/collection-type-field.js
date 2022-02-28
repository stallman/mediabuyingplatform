$(document).ready(function (e) {
    hideDefaultGeoField();
    renameToDefaultLink();
    if (isAutoCalculate() == 1) {
        $('.approve-average-percentage').prop("readonly", true)
    }

    let addButton = $('<button type="button" class="btn btn-primary add-collection-button">Добавить ГЕО</button>');
    let collectionHolder = $('.collection-field');
    let buttonHolder = $('<div class="add-button-wrapper"></div>').append(addButton);

    $(collectionHolder).append(buttonHolder);
    collectionHolder.data('index', collectionHolder.find('input').length);

    $(addButton).click(function (e) {
        addForm(collectionHolder, buttonHolder)
    })

    $('#teasers_sub_group_save').on('click', function (e) {
        if (isAutoCalculate() && isAutoCalculate() != 0) {
            setCalculatedPercent()
        }
    })
})

$(document).on('click', '.delete-collection-button', function (e) {
    let settingsId = $(this).data('settings-id');
    let parentDiv = $(this).closest('div');
    $(parentDiv).remove();
});

function addForm(collectionHolder, buttonHolder) {
    let index = $(collectionHolder).data('index');
    let newRow = $(collectionHolder).data('prototype');

    let newRowIndex = index + 1;
    let newRowWrapperId = "container-" + index;

    newRow = newRow.replace(/__name__/g, index);
    collectionHolder.data('index', newRowIndex);
    let newRowWrapper = $('<div id=' + newRowWrapperId + '></div>');

    $(newRowWrapper).append(newRow);

    let newRowDiv = $('<div></div>')
        .append(newRowWrapper)
        .append(buttonHolder)
    ;

    collectionHolder.append(newRowDiv);
    setAutoCalculate()
}

function hideDefaultGeoField() {
    $('.geo-code-input').each(function (key, value) {
        if ($(value).find('option:selected').val().length === 0) {
            $('label[for=' + $(this).attr('id') + ']').remove();
            $(this).remove();
        }
    })
}

function renameToDefaultLink() {
    $('.tsg-link').labels().html('Ссылка по умолчанию*');
}

function isAutoCalculate() {
    return $('.is-auto-calculate').val();
}

function setAutoCalculate() {
    if (isAutoCalculate() == 1) {
        let hiddenVal = $('.is-auto-calculate').val();
        $('.is-auto-calculate').val(hiddenVal)
    } else {
        $('.is-auto-calculate').val(0)
    }
}

function setCalculatedPercent() {
    let percent = $('.approve-average-percentage').val();
    $('.approve-average-percentage').val(percent)
}