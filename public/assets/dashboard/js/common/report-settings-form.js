$(".multiple-selector").select2();

let groupIds = [
    'report_settings_level1',
    'report_settings_level2',
    'report_settings_level3',
];

$(`#${ groupIds.join(',#') }`).on('change', function (e) {
    disableSelectedGroups()
});

$('.multiple-selector-help').append('<button class="multiple-selector-select-all">+</button>');
$('.multiple-selector-help').append('<button class="multiple-selector-un-select-all">-</button>');

$('.multiple-selector-help').click(function (e) {
    changeSelected(e, $(this))
    e.preventDefault()
});

function changeSelected(e, select) {
    if($(e.target).attr('class') == 'multiple-selector-select-all'){
        $(select).closest('.form-group').find('.multiple-selector option').prop('selected', true);
        $(select).closest('.form-group').find(".multiple-selector").trigger("change");
    } else if($(e.target).attr('class') == 'multiple-selector-un-select-all'){
        $(select).closest('.form-group').find('.multiple-selector option').prop('selected', false);
        $(select).closest('.form-group').find(".multiple-selector").trigger("change");
    }
}

//Не должно быть возможности выбрать одну и ту же группу в нескольких селектбоксах.
function disableSelectedGroups() {
    new Promise((res, rej) => {
        let groupsSelectedOptionsVal = [];

        $(`#${groupIds.join(',#')}`).each(function (i, element) {
            let curSelectedVal = $(element).find(`option:selected`).val();

            if (curSelectedVal !== "" && !groupsSelectedOptionsVal.includes(curSelectedVal)) {
                groupsSelectedOptionsVal.push(curSelectedVal)
            }

            $(element).find(`option`).prop('disabled', false);
        });

        res(groupsSelectedOptionsVal);
    }).then((groupsSelectedOptionsVal) => {
        $(`#${groupIds.join(',#')}`).each(function (i, element) {
            groupsSelectedOptionsVal.forEach(function (value) {
                $(element).find(`option[value="${value}"]`).prop('disabled', true);
            })
        });
    });
}

$(document).ready(function () {
    setTimeout(function () {
        $(`#${groupIds[0]}`).trigger('change');
    }, 1000)
});
