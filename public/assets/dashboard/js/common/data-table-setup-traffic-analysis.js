$(function () {
    const COST_TD_NUMBER = 5;

    let loadBtn = $('#other_settings_save');
    let mainTable = $('#data-table').DataTable(getDataTablesOpts());

    toggleLoader(true);
    toggleVisiblityColumns();

    $(loadBtn).on('click', function (e) {
        e.preventDefault();

        toggleLoader(true);

        if ($("#report_settings_sources").val().length == 0) {
            $(".validate-submit-forms").empty()
            $(".validate-submit-forms").append("<p class=\"validate-message\">Не выбраны источники</p>")
            $(".validate-submit-forms").show()

            toggleLoader();
        } else if ($("#report_settings_campaigns").val().length == 0) {
            $(".validate-submit-forms").empty()
            $(".validate-submit-forms").append("<p class=\"validate-message\">Не выбраны кампании</p>")
            $(".validate-submit-forms").show()

            toggleLoader();
        } else {

            $(".validate-submit-forms").hide();
            mainTable.clear().draw();
            $('#data-table tfoot').empty();

            mainTable.ajax.reload();

        }
    });


    $('#other_settings_blackListParams').on('change', function (e) {
        $(loadBtn).trigger('click');
    });

    function visits_percent(cn, row, total) {
        let res = 100 * dz(row.visits, total.visits);
        row[cn] = pfy(rnd(res));
        total[cn] += res;
    }

    function uniq_visits_percent(cn, row, total) {
        row[cn] = pfy(rnd(prcnt(row.visits, row.uniq_visits)));
    }

    function percent_of_total_click_count(cn, row, total) {
        let res = 100 * dz(row.click_count, total.click_count);
        row[cn] = pfy(rnd(res));
        total[cn] += res;
    }

    function percent_probiv(cn, row, total) {
        let res = dz(row.click_count, row.uniq_visits) * 100;
        row[cn] = pfy(rnd(res));
        total[cn] += res;
    }

    function percent_leads_pending(cn, row, total) {
        let res = prcnt(row.total_leads, row.leads_pending_count);
        row[cn] = pfy(rnd(res));
        total[cn] += res;
    }

    function percent_leads_declined(cn, row, total) {
        let res = prcnt(row.total_leads, row.leads_declined_count);
        row[cn] = pfy(rnd(res));
        total[cn] += res;
    }

    function percent_leads_approve(cn, row, total) {
        let res = prcnt(row.total_leads, row.leads_approve_count);
        row[cn] = pfy(rnd(res));
        total[cn] += res;
    }

    function cr_conversion(cn, row, total) {
        let res = (row.total_leads / row.uniq_visits) * 100;
        row[cn] = pfy(rnd(res));
        total[cn] += res;
    }

    function middle_lead(cn, row, total, countML) {
        let res = dz(row.middle_lead, row.total_leads);
        row[cn] = rnd(res);
        total[cn] += res;
        countML += res > 0 ? 1 : 0;

        return countML;
    }

    function real_income(cn, row, total) {
        row[cn] = rnd(row.real_income);
    }

    function real_epc(cn, row, total) {
        let res = dz(row.real_income, row.uniq_visits);
        row[cn] = rnd(res);
        total[cn] += res;
    }

    function lead_price(cn, row, total) {
        let res = dz(row.consumption, row.total_leads);
        row[cn] = rnd(res);
        total[cn] += res;
    }

    function real_roi(cn, row, total) {
        let res = dz(100 * (row.real_income - row.consumption), row.consumption);
        row[cn] = pfy(rnd(res).toFixed(0));
        total[cn] += res;
    }

    function income_projected(cn, row, total) {
        row[cn] = rnd(row.income_projected);
    }

    function epc_projected(cn, row, total) {
        let res = dz(row.income_projected, row.uniq_visits);
        row[cn] = rnd(res);
        total[cn] += res;
    }

    function roi_projected(cn, row, total) {
        let res = dz(100 * (row.income_projected - row.consumption), row.consumption);
        row[cn] = pfy(rnd(res).toFixed(0));
        total[cn] += res;
    }

    function consumption(cn, row, total) {
        row[cn] = rnd(row.consumption);
        total[cn] += row.consumption;
    }

    function replaceToTitles(row, columnNames, groups) {
        groups.forEach(function (name) {
            if (row[name] === undefined) return;
            let nullEmptyMark = 'Не указан';

            if (row[name].length === 0 || row[name] === 'null') {
                row[name] = nullEmptyMark;
            }

            if (name === 'source') {
                let sourceId = parseInt(row.source, 10);
                row.source = TA.source_names[sourceId] ?? nullEmptyMark;
            }

            if (name === 'news_category') {
                let newsCategoryId = parseInt(row.news_category, 10);
                row.news_category = TA.news_categories[newsCategoryId] ?? nullEmptyMark;
            }

            if (name === 'trafficType') {
                row.trafficType = TA.traffic_types[row.trafficType] ?? nullEmptyMark;
            }

            if (name === 'dayOfWeek') {
                row.dayOfWeek = TA.days_of_week[row.dayOfWeek] ?? nullEmptyMark;
            }

        });

    }

    function initTotalRow(groups, numericColumns) {
        let total = {};

        groups.forEach(function (name) {
            total[name] = '';
        });

        numericColumns.forEach(function (name) {
            total[name] = 0;
        });

        return total;
    }

    function initTotalFooter(total) {
        let tr = '<tr>';
        tr += '<td><b>ИТОГО</b></td>';

        $('#data-table thead th').each(function (i, col) {
            let colName = $(col).attr('data-column-name');
            let totalColVal = total[colName];

            if (totalColVal === undefined) {
                return;
            }

            let td = '<td data-column-name="' + colName + '">' + totalColVal + '</td>';

            tr += td;
        });

        tr += '</tr>';

        $('#data-table tfoot').html(tr);
    }

    function markRow(type, row) {
        for (let [group,ids] of Object.entries(TA[type])) {
            if (row[group] !== undefined && ids.includes(row[group])) {
                if (row['in_' + type] === undefined) {
                    row['in_' + type] = [];
                }
                row['in_' + type].push(group);
            }
        }
    }

    const pick = (obj, ...args) => ({
        ...args.reduce((res, key) => ({...res, [key]: obj[key]}), {})
    });

    function mergePicks(left, right) {
        for(let index in left) {
            if (typeof left[index] === 'number') {
                left[index] += right[index]
            }
        }
        return left;
    }

    function dtLoadEvent(dt, json) {
        let columnNames = getAjaxColumnNames();
        let choosenGroups = choosenGroupNames();
        let groups = choosenGroups;
        let costGroups = ['c_source', 'c_campaign', 'c_date'];
        let strGroups = choosenGroups.join(',');

        if (json !== null && json.data.rows.length !== 0) {
            let rows = json.data.rows;
            let dateFrom = json.dateFrom.split('.').reverse().join('-');
            let dateTo = json.dateTo.split('.').reverse().join('-');

            let leads_amount_rub = json.data.leads_amount_rub;
            let leads_approve_avg_percentages = json.data.leads_approve_avg_percentages;
            let numericColumns = getNumericColumns();
            let skipTotalCols = ['middle_lead'];
            let total = initTotalRow(groups, numericColumns);

            // берём только нужные даты с настроек отчёта
            let costs = TA.costs.filter((cost) => {
                if (cost.date >= dateFrom && cost.date <= dateTo) {
                    cost['indexes'] = [];
                    return true;
                }
            })

            // новый массив расходов для сравнения с результатами отчёта
            let rawCosts = [];
            costs.forEach(function (cost) {
                rawCosts.push({c_source: cost.source, c_campaign: cost.campaign, c_date: cost.date })
            });

            // создаём и нумеруем все колонки с типом int|float
            rows.forEach(function (row) {
                numericColumns.forEach(function (col) {
                    row[col] = Number(row[col] ?? 0);
                });
            });

            let commons = [];
            rows.forEach(function (row) {
                let inCommon = false;
                let costRow = pick(row, ...costGroups);
                let left = pick(row, ...groups);
                let commonKey = commons.length;

                // ищем общую строку
                commons.forEach(function (common, cKey) { // todo test ip group
                    let right = pick(common, ...groups);

                    if (JSON.stringify(left) === JSON.stringify(right)) {
                        commonKey = cKey;
                        inCommon = true;
                        return;
                    }
                });

                // сравниваем строку результатов со строкой расходов, если совпало - сохраняем индекс результата в расходах
                rawCosts.forEach(function (cost, rawCostKey) {
                    if (JSON.stringify(cost) === JSON.stringify(costRow)) {
                        costs[rawCostKey]['indexes'].push(commonKey);
                    }
                });

                // если уже есть общая строка с такими группировками - мержим его с новой строкой
                if (inCommon) {
                    let appendIp = 0;
                    if (commons[commonKey].c_ip !== row.c_ip) { // выделяем уникальные айпи
                        appendIp++;
                    }
                    let merged = mergePicks(commons[commonKey], row);
                    merged.uniq_visits += appendIp;
                    commons[commonKey] = merged;
                } else {
                    row.uniq_visits += 1;
                    commons[commonKey] = row;
                }
            });
            commons = commons.filter(function (item) {
                return item !== undefined;
            });
            rows = commons;

            // создаём и нумеруем все колонки с типом int|float для total строки
            rows.forEach(function (row) {
                numericColumns.forEach(function (col) {
                    if (!skipTotalCols.includes(col)) {
                        total[col] += Number(row[col] ?? 0);
                    }
                });
            });

            // распределяем расходы для помеченных строк
            costs.forEach(function (cost) {
                let count = cost.indexes.length;
                cost.indexes.forEach(function (rowIndex) {
                    let amount = Number(cost.cost ?? 0);
                    rows[rowIndex].consumption += dz(amount, count);
                });
            });

            rows.forEach(function (row) {
                // checkbox
                let valGroups = [];
                groups.forEach(function (group) {
                    if (row[group] === null) {
                        row[group] = 'null';
                    }

                    valGroups.push(row[group]);
                });
                let strValGroups = valGroups.join(',');

                row['id'] = '<input type="checkbox" class="bulk-action-item" data-item-group="'+strGroups+'" data-item-id="'+strValGroups+'">';

                // raw_payout
                if (row.lead_ids !== null) {
                    let raw_payout = 0;
                    let lead_ids = row.lead_ids.split(',');

                    lead_ids.forEach(function (lead_id) {
                        let percentage = 0;
                        let tsgs = leads_approve_avg_percentages.find(x => x.id === lead_id);
                        if (tsgs !== undefined) {
                            percentage = parseFloat(tsgs.lead_percentage ?? 0);
                        }
                        let leadAmountRub = leads_amount_rub.find(x => x.id === lead_id);

                        leadAmountRub = parseFloat(leadAmountRub.amount_rub ?? 0);
                        raw_payout += leadAmountRub * percentage / 100;
                    });

                    row.income_projected = raw_payout;
                }

                // titles
                replaceToTitles(row, columnNames, groups);

                // black/white lists
                markRow('black_list', row);
                markRow('white_list', row);
            });

            let countML = 0;

            // recalc rows
            rows.forEach(function (row) {
                visits_percent('visits_percent', row, total);
                uniq_visits_percent('uniq_visits_percent', row, total);
                percent_of_total_click_count('percent_of_total_click_count', row, total);
                percent_probiv('percent_probiv', row, total);
                percent_leads_pending('percent_leads_pending', row, total);
                percent_leads_declined('percent_leads_declined', row, total);
                percent_leads_approve('percent_leads_approve', row, total);
                cr_conversion('cr_conversion', row, total);
                countML = middle_lead('middle_lead', row, total, countML);
                real_income('real_income', row, total);
                real_epc('real_epc', row, total);
                lead_price('lead_price', row, total);
                real_roi('real_roi', row, total);
                income_projected('income_projected', row, total);
                epc_projected('epc_projected', row, total);
                roi_projected('roi_projected', row, total);
                consumption('consumption', row, total); // should be always last
            });

            total.visits_percent = pfy(rnd(total.visits_percent));
            total.uniq_visits_percent = pfy(rnd(prcnt(total.visits, total.uniq_visits)));
            total.percent_of_total_click_count = pfy(rnd(total.percent_of_total_click_count));
            total.percent_probiv = pfy(rnd(dz(total.click_count, total.uniq_visits) * 100));
            total.percent_leads_pending = pfy(rnd(prcnt(total.total_leads, total.leads_pending_count)));
            total.percent_leads_declined = pfy(rnd(prcnt(total.total_leads, total.leads_declined_count)));
            total.percent_leads_approve = pfy(rnd(prcnt(total.total_leads, total.leads_approve_count)));
            total.cr_conversion = pfy(rnd(dz(total.total_leads, total.uniq_visits) * 100));
            total.middle_lead = rnd(total.middle_lead / countML);
            total.real_income = rnd(total.real_income);
            total.real_epc = rnd(dz(total.real_income, total.uniq_visits));
            total.lead_price = rnd(dz(total.consumption, total.total_leads));
            total.real_roi = pfy(rnd(dz(100 * (total.real_income - total.consumption), total.consumption)).toFixed(0));
            total.income_projected = rnd(total.income_projected);
            total.epc_projected = rnd(dz(total.income_projected, total.uniq_visits));
            total.roi_projected = pfy(rnd(dz(100 * (total.income_projected - total.consumption), total.consumption)).toFixed(0));
            total.consumption = rnd(total.consumption); // should be always last

            // console.log(total)

            initTotalFooter(total);

            // default order column
            let defaultColumnOrder = [[0, "asc"]];
            let activeOrderColumnName = 'click_count';
            let currentColumnNames = getColumnNames();
            let choosenGroups = choosenGroupNames();
            let hasClickCount = currentColumnNames.filter((cn) => {
                return cn.name === activeOrderColumnName
            })
            let disabledOrderGroupsForClickCount = ['createdAt', 'timesOfDay', 'dayOfWeek']
            let foundDisabledGroup = disabledOrderGroupsForClickCount.some(element => choosenGroups.includes(element))

            if (!foundDisabledGroup && hasClickCount.length) {
                let $activeOrderColumn = $('[data-column-name="' + activeOrderColumnName +'"]')
                defaultColumnOrder = [[$activeOrderColumn.attr('data-column-index'), "desc"]];
            }

            json.data.rows = rows;

            dt.order(defaultColumnOrder)
        }

        // hide unused cols
        columnNames.forEach(function (columnName) {
            let tableColumn = $('#data-table thead th[data-column-name="' + columnName + '"]')
            let columnIndex = $(tableColumn).index()
            if (choosenGroups.includes(columnName)) {
                $(tableColumn).attr('data-hidden', 'false');
            } else {
                $(tableColumn).attr('data-hidden', 'true');
            }
        });

        // move group cols to begin
        choosenGroups.forEach(function (columnName, i) {
            dt.colReorder.move(dt.column(columnName + ':name').index(), i + 1)
        });

        dt.colReorder.disable()

        toggleLoader();
        toggleVisiblityColumns();
        // console.log( 'Ajax event occurred. Returned data: ', json );
    }

    function toggleLoader(isLoad = false) {
        let loadBtn = $('#other_settings_save');
        let loader = $('#other_settings_save i');

        if (isLoad) {
            $(loadBtn).removeClass('btn-danger').addClass('btn-success');
            $(loader).addClass('fa-spin');
            $(loadBtn).prop('disabled', true);
        } else {
            $(loadBtn).removeClass('btn-success').addClass('btn-danger');
            $(loader).removeClass('fa-spin');
            $(loadBtn).prop('disabled', false);
        }
    }

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
        return  $('#data-table thead tr:first-child th:nth-child(2)').data('column-name')
    }

    function getCurSortedOrderColumn() {
        let index = $('#data-table').DataTable().order()[0]
        let column = $('[data-column-index="' + index[0] +'"]').data('column-name')
        return {
            column: column,
            dir: index[1],
            columnNumber: index[0],
        }
    }

    function getColumnNames() {
        let columnNames = [];
        let numericColumns = getNumericColumns();

        $('#data-table thead th').each(function (index) {
            let columnName = $(this).data('column-name');
            let columnHidden = ($(this).attr('data-hidden') === 'true');
            let defaultContent = '';

            if (numericColumns.includes(columnName)) {
                defaultContent = 0;
            }

            let column = {data: columnName, name: columnName, defaultContent, isHidden: columnHidden};

            if (index === 0) {
                column = {data: 'id', name: 'id', defaultContent: '', isHidden: false}
            }
            columnNames.push(column);
        })
        return columnNames;
    }

    function toggleVisiblityColumns() {
        $('#data-table thead th').each(function () {
            let colName = $(this).attr('data-column-name');
            let $footCol = $('#data-table tfoot td[data-column-name="' + colName + '"]');

            if ($(this).attr('data-hidden') === 'true') {
                $(this).hide();
                $footCol.hide();
            } else {
                $(this).show();
                $footCol.show();
            }
        })
    }

    function getGroups() {
        let formData = {}
        $.each($('#report_settings_level1, #report_settings_level2, #report_settings_level3'), function (index, el) {
            if($(this).find('option:selected').val()){
                formData[index] = $(this).find("option[value=" + $(this).find('option:selected').val() + "]").text()
            }
        })
        return formData
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
            "colReorder": true
        }

        if(getPagingServerSide()) {
            opts['processing'] = false;
            opts['serverSide'] = false;
            opts['ajax'] = {
                "url": getAjaxUrl(),
                "dataSrc": function (res){
                    if(typeof res.blackList !== 'undefined'){
                        TA.black_list = res.blackList;
                    }
                    if(typeof res.whiteList !== 'undefined'){
                        TA.white_list = res.whiteList;
                    }
                    dtLoadEvent(mainTable, res)
                    return res.data.rows;
                },
                "type": getAjaxMethod(),
                "data": function (d) {
                    d.report_settings = getReportSettingsFormData();
                    d.other_settings = getOtherSettingsFormData();
                    d.from = $('#from').val()
                    d.to = $('#to').val()
                    d.period = $('#period').val()
                    d.order = getOrderColumnName()
                    d.orders = getCurSortedOrderColumn()
                    d.groupSubGroupId = $('#filter-teasers-group-subgroup').val();
                    d.reportSettingsSources = $('#report_settings_sources').val()
                    d.reportSettingsCampaigns = $('#report_settings_campaigns').val()
                    d.news_categories = $("#filter-news :selected").map(function (i, el) {
                        return $(el).val();
                    }).get();
                    d.groups = getGroups()
                }
            }

            opts["columns"] = getColumnNames()

            opts["fnRowCallback"] = function (nRow, aData, iDisplayIndex) {
                let columnNames = getAjaxColumnNames();
                let columns = getColumnNames();
                let excludedCols = choosenGroupNames();

                /**
                 * iterate over rows
                 */
                columns.forEach(function (column) {
                    let tableColumn = $('#data-table thead th[data-column-name="' + column.name + '"]')
                    let columnIndex = $(tableColumn).index()

                    if (column.isHidden) {
                        let $row = $(nRow).children().eq(columnIndex).hide();
                    }

                    if ((column.name === 'real_roi' && !column.isHidden) ||
                        (column.name === 'roi_projected' && !column.isHidden)
                    ) {
                        let $row = $(nRow).children().eq(columnIndex);
                        let rowVal = $row.text();
                        let rowNum = parseInt(rowVal.replace(/%/, ''), 10);
                        if (rowNum < 0) $row.addClass('roi_danger');
                        if (rowNum > 0) $row.addClass('roi_success');
                        if (rowNum === 0) $row.addClass('roi_warning');
                    }
                })

                let roiColumn = $('tr th').filter(
                    function(){
                        let columnName = $(this).text().replace(/\s+/g, '');
                        return columnName === 'РеальныйROI';
                    }).index();

                if(Object.values(aData).includes('отклонен')){
                    $(nRow).css("background-color", "red");
                }
                if(Object.values(aData).includes('подтвержден')){
                    $(nRow).css("background-color", "green");
                }

                if(Object.values(aData).includes('inactive')){
                    $(nRow).css("background-color", "#a7a3a3");
                }
                if(Object.values(aData).includes('isNotFinal')){
                    $(nRow).children(":nth-child("+COST_TD_NUMBER+")").css("background-color", "red");
                }

                if('in_black_list' in aData){
                    //console.log(aData)
                    aData.in_black_list.forEach(function (groupName) {
                        let tableColumn = $('#data-table thead th[data-column-name="' + groupName + '"]')
                        let columnIndex = $(tableColumn).index()
                        $(nRow).children().eq(columnIndex).css("color", "red");
                    });
                }

                if('in_white_list' in aData){
                    //console.log(aData)
                    aData.in_white_list.forEach(function (groupName) {
                        let tableColumn = $('#data-table thead th[data-column-name="' + groupName + '"]')
                        let columnIndex = $(tableColumn).index()
                        $(nRow).children().eq(columnIndex).css("color", "green");
                    });
                }
                // if(Object.values(aData).includes('in_white_list')){
                //     $($('td', nRow)[1]).css("color", "green");
                //     $($('td', nRow)[2]).css("color", "green");
                // }
            }

            opts["fnPreDrawCallback"] = function(settings) {
                let table = this.api();
            }
        }

        return opts
    }

    function choosenGroupNames() {
        let groupNames = [];

        $('.report_setting_groups').each(function (i,element) {
            let choosenGroupName = $(element).children('option:selected').val()
            if (choosenGroupName.length !== 0) {
                groupNames.push(choosenGroupName)
            }
        })

        return groupNames
    }

    function getAjaxUrl() {
        return $('[data-ajax-url]').data('ajax-url')
    }

    function getAjaxColumnNames() {
        return $('[data-column-names]').data('column-names').split(',')
    }

    function getNumericColumns() {
        return $('[data-numeric-columns]').data('numeric-columns').split(',')
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
            'campaign': $("#report_settings_campaigns").val(),
            'level1': $("#report_settings_level1 option:selected").val(),
            'level2': $("#report_settings_level2 option:selected").val(),
            'level3': $("#report_settings_level3 option:selected").val()
        }
        return formData['report_settings']
    }

    hideUnsortedColumnsIcons();

    /** division by zero */
    function dz(left, right) {
        return right ? left / right : 0;
    }

    function prcnt(totalCount, statusCount)
    {
        return totalCount ? statusCount / (totalCount / 100) : 0;
    }

    function rnd(num) {
        return Math.round(num * 100) / 100;
    }

    /** percentify */
    function pfy(num) {
        return num + '%';
    }
});
