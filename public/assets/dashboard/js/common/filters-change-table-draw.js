$(document).ready(function () {
    let allFilterEls = [
        'input.hasDatepicker',
        '#reload',
        '#report_settings_level1',
        '#report_settings_level2',
        '#report_settings_level3',
        '#report_settings_sources',
        '#report_settings_campaigns',
        '#settings-fields-form input',
        '#other_settings_otherFilterParams1',
        '#other_settings_otherFilterValues1',
        '#other_settings_otherFilterParams2',
        '#other_settings_otherFilterValues2',
        '#other_settings_otherFilterParams3',
        '#other_settings_otherFilterValues3',
        '#other_settings_blackListParams',
    ];

    $(".dataTables_wrapper").delegate(allFilterEls.join(','), "change", function(e) {
        e.preventDefault()
        // $('#data-table').DataTable().ajax.reload();
        let dataTable = $('#data-table').DataTable();
        dataTable.clear().draw();
        $('#data-table tfoot').empty();
    })

});
