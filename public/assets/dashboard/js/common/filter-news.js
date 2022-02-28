$(document).ready(function () {
    $('#filter-news').on('change', function (e) {
        let table = $('#data-table').DataTable();
        table.ajax.reload();
    });

    $('#clear-filter-news').on('click', function (e) {
        $('#filter-news option').prop('selected', false);
        let table = $('#data-table').DataTable();
        table.draw();
    });

    let table = $('#data-table').DataTable();
    table.on( 'xhr', function () {
        let json = table.ajax.json();
        if(json) {
            $('.badge-primary').text(`Всего: ${json.recordsTotal}`)
            $('.badge-success').text(`Активные: ${json.countActiveData}`)
            $('.badge-danger').text(`Неактивные: ${json.countInActiveData}`)
            $('.badge-info').text(`Лиды с топа тизеров: ${json.countTopConversions}`)
            if (json.countTopConversions === undefined) {
                $('.badge-info').hide()
            }
        }
    } );
});