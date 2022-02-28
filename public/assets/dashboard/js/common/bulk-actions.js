$(document).ready(function (e) {
    let bulkCheckbox = $('.bulk-actions');
    let bulkActionsSubmit = $('#bulk-actions-submit');
    let bulkActionsFormSubmit = $('#bulk-actions-form-submit');
    let bulkActionsSelector = $('#bulk-actions-selector');
    let reloadTable = $('input[name="_do_table_reload"]').length > 0;
    let reportSettingsForm = $('#report_settings');

    $(bulkCheckbox).on('click', function () {

        if ($(bulkCheckbox).is(':checked')) {
            $(document).find('.bulk-action-item').each(function (key, val) {
                $(val).parent().parent().addClass('checked-row');
                $(val).prop('checked', true)
            })
        } else {
            $(document).find('.bulk-action-item').each(function (key, val) {
                $(val).parent().parent().removeClass('checked-row');
                $(val).prop('checked', false)
            })
        }
    })

    $(document).find('.bulk-action-item:checked').each(function (key, val) {
        $(val).parent().parent().addClass('checked-row');
    })

    $(bulkActionsSubmit).on('click', function () {
        if (!$(bulkActionsSelector).val()) {
            return;
        }

        let selectedOption = $(bulkActionsSelector).children("option:selected");
        let selectedOptionValue = $(selectedOption).val();
        let selectedOptionUrl = $(selectedOption).data('url');
        let reportSettingGroups = $('.report_setting_groups');
        let confirmMessage = confirm('Подтверждаете массовую операцию?')
        let checkedItems
        let choosenGroups = []
        let checkedGroups

        reportSettingGroups.each(function (i, element) {
            let choosenGroupName = $(element).children('option:selected').val()
            if (choosenGroupName.length !== 0) {
                choosenGroups.push(choosenGroupName)
            }
        });

        if (confirmMessage) {
            if ($(this).hasClass('is-black-list-submit')) {
                hasForbiddenCN(choosenGroups).then(function (result) {
                    if (false === result) {
                        bulkChooser()
                    }
                })
            } else {
                bulkChooser()
            }
        }

        function bulkChooser() {
            if (selectedOptionValue === 'change_teaser_subgroup') {
                checkedItems = {}
                checkedItems['checked_items'] = []
                $(document).find('.bulk-action-item').each(function (key, value) {
                    if ($(value).is(':checked')) {
                        let itemId = $(value).data('item-id');
                        checkedItems['checked_items'].push(itemId);
                    }
                });
                checkedItems['sub_group'] = $('#bulk-change-teasers-group').val()
            } else if (selectedOptionUrl.search('teasers-groups') != -1) {
                let form = $('#bulk-actions-form')
                form.attr('action', selectedOptionUrl);
                if (confirmMessage) {
                    form.submit()
                }
                return;
            } else {
                checkedItems = [];
                checkedGroups = [];
                $(document).find('.bulk-action-item').each(function (key, value) {
                    if ($(value).is(':checked')) {
                        let itemGroup = $(value).data('item-group');
                        let itemId = $(value).data('item-id');
                        let blGroup = $(value).data('item-bl-group');
                        let blId = $(value).data('item-bl-id');
                        checkedItems.push(itemId);
                        if (!checkedGroups.includes(itemGroup)) {
                            checkedGroups.push(itemGroup);
                        }
                    }
                });
            }

            if (checkedItems.length === 0) {
                return;
            }

            if (typeof checkedItems.checked_items !== 'undefined' && checkedItems.checked_items.length === 0) {
                return;
            }

            if (confirmMessage) {
                sendAjaxRequest(checkedItems, checkedGroups, selectedOptionUrl);
            }
        }
    })

    $(bulkActionsFormSubmit).on('click', function (e) {
        e.preventDefault()
        let confirmMessage = confirm('Подтверждаете массовую операцию?')
        let selectedOption = $(bulkActionsSelector).children("option:selected");
        let selectedOptionUrl = $(selectedOption).data('url');
        let form = $('#bulk-actions-form')
        form.attr('action', selectedOptionUrl);
        if (confirmMessage) {
            form.submit()
        }
    })

    async function hasForbiddenCN(choosenGroups) {
        return new Promise((resolve, reject) => {
            let hasForbidden = false;

             $.ajax({
                url: '/mediabuyer/statistic/traffic-analysis/column-params',
                method: 'get',
                success: async function (json) {
                    let canBlackedColumnLabels = []
                    let canBlackedColumnNames = []
                    json.forEach(function (row) {
                        if (row.canBlacked) {
                            canBlackedColumnLabels.push(row.label)
                            canBlackedColumnNames.push(row.columnName)
                        }
                    })

                    await choosenGroups.forEach(function (columnName) {
                        if (!canBlackedColumnNames.includes(columnName)) {
                            alert(`Запрещено! Список доступных групп для добавления в блеклист: ${canBlackedColumnLabels.join(', ')}`)
                            hasForbidden = true;
                        }
                    })
                    resolve(hasForbidden)
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    alert(err.Message);
                    hasForbidden = true;
                    resolve(hasForbidden)
                },
            })

        })
    }

    function sendAjaxRequest(checkedItems, checkedGroups, selectedOptionUrl) {
        $.ajax({
            data: {checkedItems,checkedGroups},
            method: 'post',
            url: selectedOptionUrl,
            success: function (data) {
                if(reloadTable){
                    $('#data-table').DataTable().ajax.reload();
                }else {
                    window.location.replace(data.route_to_redirect);
                }
            },
            error: function (data) {
                window.location.reload();
            }
        })
    }

    $('[name="data-table_length"], #filter-teasers-group-subgroup, #filter-news').on('change', function() {
        releaseBulkActionsCheckbox();
    });

    $('.dataTables_filter>label>input').on('input', function() {
        releaseBulkActionsCheckbox();
    });

    $('body').on('click', '.paginate_button', function() {
        if (!$(this).hasClass('disabled')) {
            releaseBulkActionsCheckbox();
        }
    });

    $('.sorting, #clear-filter-teasers-group-subgroup, #clear-filter-news').on('click', function() {
        releaseBulkActionsCheckbox();
    });

    $(document).on('change', '.bulk-action-item', function(e) {
        let input = e.target
        if ($('.bulk-action-item:checked').length == 0) {
            releaseBulkActionsCheckbox();
        }
        if($(input).is(':checked')) {
            $(input).parent().parent().addClass('checked-row')
        } else {
            $(input).parent().parent().removeClass('checked-row')
        }
    });

    function releaseBulkActionsCheckbox() {
        if ($('.bulk-actions').is(':checked')) {
            $('.bulk-actions').trigger('click');
        }
    }

})
