$(document).ready(function () {
    $("#date-filter").on("click", function (e) {
        e.preventDefault()
        $('#period').val(null)
        tableReload()
    });

    $("#period-link a").on("click", function (e) {
        e.preventDefault()
        let period = $(this).data('period')
        addFromToDates(period)
        $('#period').val(period)
        tableReload()
    });

    $("#news-finance-period").on("change", function (e) {
        e.preventDefault()
        let period = $(this).find(":selected").data('period')
        addFromToDates(period)
        $('#period').val(period)
        tableReload()
    });

    $("#reload").on("change", function (e) {
        e.preventDefault()
        let period = $(this).find(":selected").data('period')
        addFromToDates(period)
        $('#period').val(period)
    });

    function addFromToDates(period) {

        let from, to;
        let format = "DD.MM.YYYY";
        let serverTime = $('#server-time')

        switch (period) {
            case 'today':
                from = moment(serverTime).format(format);
                to = moment(serverTime).format(format);
                break;
            case 'yesterday':
                from = moment(serverTime).add(-1, 'day').format(format);
                to = moment(serverTime).add(-1, 'day').format(format);
                break;
            case 'week':
                from = moment(serverTime).add(-6, 'day').format(format);
                to = moment(serverTime).format(format);
                break;
            case 'two-week':
                from = moment(serverTime).add(-13, 'day').format(format);
                to = moment(serverTime).format(format);
                break;
            case 'current-month':
                from = moment(serverTime).startOf('month').format(format);
                to = moment(serverTime).endOf('month').format(format);
                break;
            case 'last-month':
                from = moment(serverTime).add(-1, 'month').startOf('month').format(format);
                to = moment(serverTime).add(-1, 'month').endOf('month').format(format);
                break;
            case 'current-year':
                from = moment(serverTime).startOf('year').format(format);
                to = moment(serverTime).endOf('year').format(format);
                break;
            case 'last-year':
                from = moment(serverTime).add(-1, 'year').startOf('year').format(format);
                to = moment(serverTime).add(-1, 'year').endOf('year').format(format);
                break;

            case 'day-before-yesterday':
                from = moment(serverTime).add(-2, 'day').format(format);
                to = moment(serverTime).add(-2, 'day').format(format);
                break;
            case 'current-week':
                from = moment(serverTime).startOf('isoWeek').format(format);
                to = moment(serverTime).endOf('isoWeek').format(format);
                break;
            case 'last-week':
                from = moment(serverTime).add(-1, 'week').startOf('isoWeek').format(format);
                to = moment(serverTime).add(-1, 'week').endOf('isoWeek').format(format);
                break;
            case 'month':
                from = moment(serverTime).add(-29, 'day').format(format);
                to = moment(serverTime).format(format);
                break;
            case 'two-month':
                from = moment(serverTime).add(-59, 'day').format(format);
                to = moment(serverTime).format(format);
                break;
            case 'three-month':
                from = moment(serverTime).add(-89, 'day').format(format);
                to = moment(serverTime).format(format);
                break;
        }
        $('#from').val(from);
        $('#to').val(to);
    }


    function tableReload() {
        let table = $('#data-table').DataTable();
        table.ajax.reload();
    }
});