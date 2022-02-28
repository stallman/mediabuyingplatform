$(document).ready(function () {
    $('#filter-teasers-group-subgroup').on('change', function (e) {
        let table = $('#data-table').DataTable();
        table.groupSubGroupId = $( this ).val();
        table.draw();
    });

    $('#clear-filter-teasers-group-subgroup').on('click', function (e) {
        let table = $('#data-table').DataTable();
        $('#filter-teasers-group-subgroup option').prop('selected', false);
        table.draw();
    });

});