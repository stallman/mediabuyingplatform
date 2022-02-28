$('#bulk-actions-selector').change(function (event, ui) {
    let teaserGroupSelect = $('#teasers-group-select')
    let id = $("#bulk-actions-selector option:selected").attr('id')

    $(teaserGroupSelect).hide();
    $(teaserGroupSelect).html('')

    if (id === 'subgroup-option') {
        $.ajax({
            method: 'get',
            url: '/mediabuyer/teaser_group/json',
            success: function (data) {
                if (data.length) {
                    $(teaserGroupSelect).append(addSelectGroup(data))
                    $(teaserGroupSelect).show();
                }
            },
            error: function (data) {
                alert('Ошибка получения подгрупп')
            }
        })
    }
});

function addSelectGroup(data) {
    return ' <select class="custom-select" id="bulk-change-teasers-group">\n' +
        getSelectData(data) +
        '    </select>\n'
}

function getSelectData(data) {
    let selectData = ''

    for (let i = 0; i < data.length; i++) {
        selectData += '<optgroup label="' + data[i].name + '">\n' +
            getSelectOptions(data[i]) +
            '    </optgroup>\n'
    }

    return selectData
}

function getSelectOptions(data) {
    let selectOption = ''

    for (let j = 0; j < data.teasersSubGroup.length; j++) {
        selectOption += '<option value="' + data.teasersSubGroup[j].id + '">' + data.teasersSubGroup[j].name + '</option>\n'
    }

    return selectOption
}