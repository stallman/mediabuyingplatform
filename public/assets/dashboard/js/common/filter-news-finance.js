$(document).ready(function () {
    $('#report_settings_sources').on('change', function (e) {
        let table = $('#data-table').DataTable();
        table.reportSettingsSources = $( this ).val();
    });

    $('#report_settings_news').on('change', function (e) {
        let table = $('#data-table').DataTable();
        table.reportSettingsNews = $( this ).val();
    });

    $('#report_settings_save').on('click', function (e) {
        let table = $('#data-table').DataTable();
        table.draw();
    });

});