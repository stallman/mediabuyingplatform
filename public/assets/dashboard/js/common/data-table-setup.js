$(function () {
    const COST_TD_NUMBER = 5;

    $('#data-table').DataTable(getDataTablesOpts());

    function getPaging() {
        return $('[data-paging]').data('paging') === 0 ? false : true
    }
    function getPagingServerSide() {
        return $('[data-paging-server-side]').data('paging-server-side') === 1 ? true : false
    }

    function getSortedTargets() {
        let sortedTargets = [];
        $('[data-sortable-column="active"]').each(function (i, item) {
            sortedTargets.push($(item).index());
        })

        return sortedTargets;
    }

    function getUnsortedTargets() {
        let allColumnNumbers = generateArrayRange(getTableColumnsCount());
        return removeArrayFromArray(allColumnNumbers, getSortedTargets())
    }

    function generateArrayRange(to) {
        return Array.from(Array(to).keys())
    }

    function removeArrayFromArray(array, toRemove) {
       return array.filter( ( el ) => !toRemove.includes( el ) );
    }

    function getTableColumnsCount() {
        return $("table > tbody > tr:first > td").length
    }

    function setOrder() {
        if (getOrderColumnIndex() >= 0) {
            return [[getOrderColumnIndex(), getOrderColumnDirection()]]
        } else {
            return [[0, "asc"]]
        }
    }

    function getOrderColumnIndex() {
        return $('[data-sortable-order]').index();
    }

    function getOrderColumnDirection() {
        return $('[data-sortable-order]').data('sortable-order')
    }

    function getOrderColumnName() {
        let index = $('#data-table').DataTable().order()[0]
        return  $("th:eq("+index[0]+")").data('column-name')

    }

    function hideSortIcon(columnNumber) {
       $($('th')[columnNumber])
           .removeClass('sorting')
           .removeClass('sorting_asc')
           .removeClass('sorting_desc')
           .addClass('sorting_disabled')
           .attr('id', 'sorting_disabled');
    }

    function hideUnsortedColumnsIcons() {
        for (let columnNumber of getUnsortedTargets()) {
            hideSortIcon(columnNumber);
        }
    }

    function getDataTablesOpts() {
        let opts = {
            "fixedHeader": {
                header: true,
                headerOffset: -10
            },
            "paging": getPaging(),
            "retrieve": true,
            "lengthChange": true,
            "searching": getSearching(),
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "pageLength": 20,
            "bInfo" : getBinfo(),
            "lengthMenu": [[20, 50, 100, 200, -1], [20, 50, 100, 200, "All"]],
            "language": {
                "lengthMenu": "Показать _MENU_ записей",
                "zeroRecords": "Извините, ничего не найдено",
                "info": "Показаны с _START_ по _END_ запись из _MAX_ записей",
                "infoEmpty": "Нет записей",
                "infoFiltered": "(отфильтровано из _MAX_ записей)",
                "search": 'Поиск',
                "paginate": {
                    "previous": "Предыдущая",
                    "next": "Следующая"
                }
            },
            "columnDefs": [
                {
                    "targets": getSortedTargets(),
                    "sortable": true,
                }, {
                    "targets": getUnsortedTargets(),
                    "sortable": false
                }
            ],
            "order": setOrder(),
        }

        if(getPagingServerSide()) {
            opts['processing'] = true;
            opts['serverSide'] = true;
            opts['ajax'] = {
                "url": getAjaxUrl(),
                "type": getAjaxMethod(),
                "data": function (d) {
                    d.report_settings = getReportSettingsFormData();
                    d.other_settings = getOtherSettingsFormData();
                    d.from = $('#from').val()
                    d.to = $('#to').val()
                    d.period = $('#period').val()
                    d.order[0]['column'] = getOrderColumnName()
                    d.groupSubGroupId = $('#filter-teasers-group-subgroup').val();
                    d.reportSettingsSources = $('#report_settings_sources').val()
                    d.reportSettingsNews = $('#report_settings_news').val()
                    d.news_categories = $("#filter-news :selected").map(function (i, el) {
                        return $(el).val();
                    }).get();
                }
            }
            opts["columns"] = []
            for (let i = 0; i < getTableColumnsCount(); i++) {
                opts["columns"].push({"data": i})
            }

            opts["fnRowCallback"] = function (nRow, aData, iDisplayIndex) {
                let roiColumn = $('tr th').filter(
                    function(){
                        let columnName = $(this).text().replace(/\s+/g, '');
                        return columnName === 'РеальныйROI';
                    }).index();

                if (aData.includes('отклонен')) {
                    $(nRow).css("background-color", "red");
                }
                if(aData.includes('подтвержден')){
                    $(nRow).css("background-color", "green");
                }
                if(aData.includes('inactive')){
                    $(nRow).css("background-color", "#a7a3a3");
                }
                if(aData.includes('isNotFinal')){
                    $(nRow).children(":nth-child("+COST_TD_NUMBER+")").css("background-color", "red");
                }
                if(aData.includes('in_black_list')){
                    $($('td', nRow)[1]).css("color", "red");
                    $($('td', nRow)[2]).css("color", "red");
                }
                if(aData.includes('in_white_list')){
                    $($('td', nRow)[1]).css("color", "green");
                    $($('td', nRow)[2]).css("color", "green");
                }
                if(roiColumn !== -1){
                    if(aData[roiColumn] > 0){
                        changeCellColorByNum(nRow, roiColumn, 'bg-success');
                    }else{
                        changeCellColorByNum(nRow, roiColumn, 'bg-danger');
                    }
                }
                addColorToCells(aData, nRow);
            }
        }

        return opts
    }


    function addColorToCells(aData, nRow)
    {
        let coloredFidelds = ['Прибыль реальная', 'Прибыль прогнозируемая', 'ROI реальный', 'ROI прогнозируемое'];
        for (let i in coloredFidelds) {
            let index = getColumnIndexByTitle(coloredFidelds[i]);
            if (aData[index] > 0) {
                changeCellColorByNum(nRow, index, 'bg-success');
            } else if (aData[index] < 0) {
                changeCellColorByNum(nRow, index, 'bg-danger');
            }
        }
    }

    function getColumnIndexByTitle(title)
    {
        return $('th').index($('th:contains("' + title + '")'));
    }

    function changeCellColorByNum(nRow, num, colorClass)
    {
        $(nRow).find('td:eq(' + num +')').addClass(colorClass);
    }

    function getAjaxUrl() {
        return $('[data-ajax-url]').data('ajax-url')
    }

    function getAjaxMethod() {
        return $('[data-ajax-method]').data('ajax-method') ? $('[data-ajax-method]').data('ajax-method') : 'GET'
    }

    function getSearching() {
        let isSeareching = typeof $('[data-searching]').data('searching') !== 'undefined' ? $('[data-searching]').data('searching') : true

        if(isSeareching) {
            $('.update-cron-date-info').removeClass('update-cron-date-info')
        }

        return isSeareching
    }

    function getBinfo() {
        return typeof $('[data-binfo]').data('binfo') !== 'undefined' ? $('[data-binfo]').data('binfo') : true
    }

    function getOtherSettingsFormData() {
        let formData = []
        formData['other_settings'] = {
            'otherFilterParams1': $("#other_settings_otherFilterParams1").val(),
            'otherFilterParams2': $("#other_settings_otherFilterParams2").val(),
            'otherFilterParams3': $("#other_settings_otherFilterParams3").val(),
            'otherFilterValues1': $("#other_settings_otherFilterValues1").val(),
            'otherFilterValues2': $("#other_settings_otherFilterValues2").val(),
            'otherFilterValues3': $("#other_settings_otherFilterValues3").val(),
            'blackListParams': $("#other_settings_blackListParams").val(),
            'dropTrafficByBl': $("#other_settings_dropTrafficByBl").val(),
        }
        return formData['other_settings']
    }

    function getReportSettingsFormData() {
        let formData = []
        formData['report_settings'] = {
            'from': $('#from').val(),
            'to': $('#to').val(),
            'source': $("#report_settings_sources").val(),
            'news': $("#report_settings_news").val(),
            'level1': $("#report_settings_level1").val(),
            'level2': $("#report_settings_level2").val(),
            'level3': $("#report_settings_level3").val()
        }
        return formData['report_settings']
    }

    hideUnsortedColumnsIcons();

});