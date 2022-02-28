$(document).ready(function () {
    $('#page_number').val(parseInt($('#page_number').val(), 10) + 1);
    while (!__getScroll('Height')) {
        if ($('#is_ajax_load').val() == 0) break
        ajaxLoad($('#page_number').val(), false)
    }
    $(window).scroll(function () {
        if ((Math.ceil($(window).scrollTop()) === $(document).height() - $(window).height())) {
            if ($('#is_ajax_load').val() != 0) {
                ajaxLoad($('#page_number').val())
            }
        }
    });
    workWithDateOnFints();
});

function workWithDateOnFints() {
    let fints_from_page = $('.fint');
    for(let i = 0; i < fints_from_page.length; i++) {
        let current_fint = fints_from_page[i];
        let current_fint_day = $(current_fint).find('.date_day').text();
        let current_fint_month = $(current_fint).find('.date_month').text();
        if (current_fint_day !== '' && current_fint_month !== '') {
            let compared_dates_result = compareDates(current_fint_day, current_fint_month)
            let text_to_set = compared_dates_result.day + ' ' + compared_dates_result.month;
            $(current_fint).find('.s_date').text(text_to_set)
        }
    }
}

function workWithDateOnCard(day, month) {
    let month_number = convertMonthToNumber(month);
    return compareDates(day, month_number);
}

function __getScroll(a) {
    let d = document,
        b = d.body,
        e = d.documentElement,
        c = "client" + a;
    a = "scroll" + a;
    return /CSS/.test(d.compatMode) ? (e[c] < e[a]) : (b[c] < b[a])
}

function ajaxLoad(page, isAsync = true) {
    page = parseInt(page)
    $.ajax({
        type: 'GET',
        url: '/ajax-news/' + page,
        async: isAsync,
        beforeSend: function () {
            page += 1;
            $('#page_number').val(page)
            $('#loading').show();
        },
        success: function (data) {
            const array_with_teasers_objects = JSON.parse(data);
            let blockCount = array_with_teasers_objects.length
            if (blockCount !== 0) {
                let place_object = findPlaceForBlocks();
                appendBlocks(blockCount, place_object, array_with_teasers_objects);
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#loading').hide();
        }
    });
}

function appendBlocks(blockCount, place_object, array_with_teasers_objects) {
    for (let i = 0; i < blockCount; i++) {
        if (place_object.hasOwnProperty('num_to_first_row')) {
            if (place_object['num_to_first_row'] > 0) {
                findPlaceInFirstRow(place_object['num_to_first_row'], array_with_teasers_objects, i);
                place_object['num_to_first_row'] -= 1;
            } else if (place_object['num_to_second_row'] > 0) {
                findPlaceInSecondRow(place_object['num_to_second_row'], array_with_teasers_objects, i);
                place_object['num_to_second_row'] -= 1;
            } else if (place_object['num_to_third_row'] > 0) {
                findPlaceInThirdRow(place_object['num_to_third_row'], array_with_teasers_objects, i);
                place_object['num_to_third_row'] -= 1;
            } else if (place_object['num_to_repeat_first_vertical'] > 0) {
                findPlaceInRepeatFirstRowVertical(place_object['num_to_repeat_first_vertical'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_vertical'] -= 1;
            } else if (place_object['num_to_repeat_first_horizontal'] > 0) {
                findPlaceInRepeatFirstRowHorizontal(place_object['num_to_repeat_first_horizontal'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_horizontal'] -= 1;
            }
        } else if (place_object.hasOwnProperty('repeat_variant') && place_object['repeat_variant'] === 'first_variant'
            || (place_object.hasOwnProperty('repeat_variant') && place_object['repeat_variant'] === 'third_variant')){
            if (place_object['num_to_repeat_first_vertical'] > 0) {
                findPlaceInRepeatFirstRowVertical(place_object['num_to_repeat_first_vertical'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_vertical'] -= 1;
            } else if (place_object['num_to_repeat_first_horizontal'] > 0) {
                findPlaceInRepeatFirstRowHorizontal(place_object['num_to_repeat_first_horizontal'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_horizontal'] -= 1;
            } else if (place_object['num_to_repeat_second']) {
                findPlaceInRepeatSecondRow(place_object['num_to_repeat_second'], array_with_teasers_objects, i);
                place_object['num_to_repeat_second'] -= 1;
            }
        } else if (place_object.hasOwnProperty('repeat_variant') && place_object['repeat_variant'] === 'second_variant') {
            if (place_object['num_to_repeat_second'] > 0) {
                findPlaceInRepeatSecondRow(place_object['num_to_repeat_second'], array_with_teasers_objects, i);
                place_object['num_to_repeat_second'] -= 1;
            } else if (place_object['num_to_repeat_first_vertical'] > 0) {
                findPlaceInRepeatFirstRowVertical(place_object['num_to_repeat_first_vertical'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_vertical'] -= 1;
            } else if (place_object['num_to_repeat_first_horizontal'] > 0) {
                findPlaceInRepeatFirstRowHorizontal(place_object['num_to_repeat_first_horizontal'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_horizontal'] -= 1;
            }
        } else if (place_object.hasOwnProperty('repeat_variant') && place_object['repeat_variant'] === 'fourth_variant') {
            if (place_object['num_to_repeat_first_horizontal'] > 0) {
                findPlaceInRepeatFirstRowHorizontal(place_object['num_to_repeat_first_horizontal'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_horizontal'] -= 1;
            } else if (place_object['num_to_repeat_second'] > 0) {
                findPlaceInRepeatSecondRow(place_object['num_to_repeat_second'], array_with_teasers_objects, i);
                place_object['num_to_repeat_second'] -= 1;
            } else if (place_object['num_to_repeat_first_vertical'] > 0) {
                createSectionForFirstRepeatableRow(false)
                findPlaceInRepeatFirstRowVertical(place_object['num_to_repeat_first_vertical'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_vertical'] -= 1;
            } else if (place_object['num_to_repeat_first_horizontal_new'] > 0) {
                findPlaceInRepeatFirstRowHorizontal(place_object['num_to_repeat_first_horizontal_new'], array_with_teasers_objects, i);
                place_object['num_to_repeat_first_horizontal_new'] -= 1;
            }
        }
    }
}

function findPlaceInFirstRow(num, array_with_teasers_objects, i) {
    let block_type = 'first_row_essential';
    const teaser_blocks = secondDesignParseObjectToHTML(block_type, array_with_teasers_objects[i]);
    $('#firstRowEssential').append(teaser_blocks)
}

function findPlaceInSecondRow(num, array_with_teasers_objects, i) {
    let block_type = 'second_row_essential';
    const teaser_blocks = secondDesignParseObjectToHTML(block_type, array_with_teasers_objects[i]);
    $('#secondRowEssential').append(teaser_blocks)
}

function findPlaceInThirdRow(num, array_with_teasers_objects, i) {
    let block_type = 'third_row_essential';
    const teaser_blocks = secondDesignParseObjectToHTML(block_type, array_with_teasers_objects[i]);
    let num_in_this_row = getElementsFromThirdEssentialRow();
    console.log(num_in_this_row)
    if (num_in_this_row >= 0 && num_in_this_row <= 1) {
        $($('#thirdEssentialRow').find('.fints_billet')[0]).append(teaser_blocks);
    } else if (num_in_this_row >= 2 && num_in_this_row <= 3) {
        console.log($('#thirdEssentialRow').find('.fints_billet')[1])
        $($('#thirdEssentialRow').find('.fints_billet')[1]).append(teaser_blocks);
    } else if (num_in_this_row >= 4 && num_in_this_row <= 5) {
        $($('#thirdEssentialRow').find('.fints_billet')[2]).append(teaser_blocks);
    }
}

function findPlaceInRepeatFirstRowVertical (num, array_with_teasers_objects, i) {
    let block_type = 'first_row_repeat_vertical';
    const teaser_blocks = secondDesignParseObjectToHTML(block_type, array_with_teasers_objects[i]);
    $($('body .first-repeat-row').last().find('.fints-line_of-3')[0]).append(teaser_blocks);
}

function findPlaceInRepeatFirstRowHorizontal(num, array_with_teasers_objects, i) {
    let block_type = 'first_row_repeat_horizontal';
    const teaser_blocks = secondDesignParseObjectToHTML(block_type, array_with_teasers_objects[i]);
    let num_in_this_row = getElementsFromFirstRepeatRow();
    if (num_in_this_row >=3 && num_in_this_row <=4) {
        $($('body .first-repeat-row').last().find('.fints_billet')[0]).append(teaser_blocks);
    } else if (num_in_this_row >=5 && num_in_this_row <=6) {
        $($('body .first-repeat-row').last().find('.fints_billet')[1]).append(teaser_blocks);
    } else if (num_in_this_row >=7 && num_in_this_row <=8) {
        $($('body .first-repeat-row').last().find('.fints_billet')[2]).append(teaser_blocks);
    }
}

function findPlaceInRepeatSecondRow(num, array_with_teasers_objects, i) {
    let block_type = 'second_row_repeat';
    const teaser_blocks = secondDesignParseObjectToHTML(block_type, array_with_teasers_objects[i]);
    $($('body .second-repeat-row').last().find('.fints-line_of-4')[0]).append(teaser_blocks);
}

function findPlaceForBlocks() {
    let result_object = {};
    let blocks_num = getNumberOfBlocks();
    let num_in_prev_repeat_first_row = getElementsFromFirstRepeatRow();
    let num_in_prev_repeat_second_row = getElementsFromSecondRepeatRow();

    if (blocks_num <= 6) {
        let first_row_num = getElementsFromFirstEssentialRow();
        let second_row_num = getElementsFromSecondEssentialRow();
        result_object['num_to_first_row'] = 3 - first_row_num;
        result_object['num_to_second_row'] = 4 - second_row_num;
        result_object['num_to_third_row'] = 10 - result_object['num_to_second_row'] - result_object['num_to_first_row'];
        if (result_object['num_to_third_row'] > 6) {
            result_object['num_to_third_row'] = 6;
            result_object['num_to_repeat_first_vertical'] = 10 - result_object['num_to_third_row'] - result_object['num_to_second_row'];
        }
    } else if (blocks_num <= 12) {
        let third_row_num = getElementsFromThirdEssentialRow();
        result_object['num_to_first_row'] = 0;
        result_object['num_to_second_row'] = 0;
        result_object['num_to_third_row'] = 6 - third_row_num;
        result_object['num_to_repeat_first_vertical'] = 3;
        result_object['num_to_repeat_first_horizontal'] = 7 - result_object['num_to_third_row']
    } else {
        if (num_in_prev_repeat_first_row === 9) {
            if (num_in_prev_repeat_second_row === 4) {
                result_object['num_to_repeat_second'] = 4;
                result_object['num_to_repeat_first_vertical'] = 3;
                result_object['num_to_repeat_first_horizontal'] = 3;
                result_object['repeat_variant'] = 'first_variant'
                let isSecondRepeatRow = isLastSectionClassSecondRepeatRow();
                if (isSecondRepeatRow) {
                    result_object['num_to_repeat_second'] = 1;
                    result_object['num_to_repeat_first_vertical'] = 3;
                    result_object['num_to_repeat_first_horizontal'] = 6;
                    createSectionForFirstRepeatableRow(false);
                    createSectionForSecondRepeatableRow(true);
                } else {
                    createSectionForSecondRepeatableRow(false);
                    createSectionForFirstRepeatableRow(false);
                }
            } else {
                result_object['num_to_repeat_second'] = 4 - num_in_prev_repeat_second_row;
                result_object['num_to_repeat_first_vertical'] = 3;
                result_object['num_to_repeat_first_horizontal'] = 10 - result_object['num_to_repeat_second'] - result_object['num_to_repeat_first_vertical'];
                result_object['repeat_variant'] = 'second_variant';
                createSectionForFirstRepeatableRow(false);
            }
        } else {
            if (num_in_prev_repeat_first_row <= 3) {
                result_object['num_to_repeat_first_vertical'] = 3 - num_in_prev_repeat_first_row;
                result_object['num_to_repeat_first_horizontal'] = 6;
                result_object['num_to_repeat_second'] = 10 - result_object['num_to_repeat_first_vertical'] - result_object['num_to_repeat_first_horizontal'];
                result_object['repeat_variant'] = 'third_variant'
            } else {
                result_object['num_to_repeat_first_horizontal'] = 9 - num_in_prev_repeat_first_row;
                result_object['num_to_repeat_second'] = 10 - result_object['num_to_repeat_first_horizontal'];
                if (result_object['num_to_repeat_second'] > 4) {
                    result_object['repeat_variant'] = 'fourth_variant'
                    result_object['num_to_repeat_second'] = 4;
                    result_object['num_to_repeat_first_vertical'] = 10 - result_object['num_to_repeat_first_horizontal'] - result_object['num_to_repeat_second'];
                    if (result_object['num_to_repeat_first_vertical'] > 3) {
                        result_object['num_to_repeat_first_vertical'] = 3;
                        result_object['num_to_repeat_first_horizontal_new'] = 10 - result_object['num_to_repeat_first_horizontal'] - result_object['num_to_repeat_second'] - result_object['num_to_repeat_first_vertical'];
                    }
                }
            }
            createSectionForSecondRepeatableRow(false);
        }
    }
    return result_object;
}

function isLastSectionClassSecondRepeatRow() {
    return $($('body section:last')[0]).hasClass('second-repeat-row');
}

function getNumberOfBlocks() {
    let fints_on_page = $('.fint').length;
    return fints_on_page;
}

function getElementsFromFirstEssentialRow() {
    let result_num = $('#firstRowEssential').find('.fint').length;
    return result_num
}

function getElementsFromSecondEssentialRow() {
    let result_num = $('#secondRowEssential').find('.fint').length;
    return result_num
}

function getElementsFromThirdEssentialRow() {
    let result_num = $('#thirdEssentialRow').find('.fint').length;
    return result_num
}

function getElementsFromFirstRepeatRow() {
    let result_num = $('body .first-repeat-row').last().find('.fint').length;
    return result_num
}

function getElementsFromSecondRepeatRow() {
    let result_num = $('body .second-repeat-row').last().find('.fint').length;
    return result_num
}

function createSectionForFirstRepeatableRow(flag) {
    let some_arr = $('body .first-repeat-row').last().find('.fint').filter(item => item[0] !== 'length' && item[0] !== 'prevObject');
    if (some_arr.length === 9 || some_arr.length === 0 || flag) {
        let section_to_append = `<section class="tail-fragment first-repeat-row">
                                    <div class="center-wrapper">
                                        <div class="fints-line fints-line_of-3">
                                        </div>
                                        <div class="fints fints_billet">
                                        </div>
                                        <div class="fints fints_billet">
                                        </div>
                                        <div class="fints fints_billet">
                                        </div>
                                    </div>
                                </section>`
        if ($('body .second-repeat-row').length !== 0) {
            $('body .second-repeat-row').last().after(section_to_append);
        } else {
            $('#place_for_teasers').last().after(section_to_append);
        }
    }
}

function createSectionForSecondRepeatableRow(flag) {
    let some_arr = $('body .second-repeat-row').last().find('.fint').filter(item => item[0] !== 'length' && item[0] !== 'prevObject');
    if (some_arr.length === 4 || flag) {
        let section_to_append = `<section class="tail-fragment tail-fragment_white decorated-bottom second-repeat-row">
                                    <div class="center-wrapper">
                                        <div class="fints-line fints-line_of-4">
                                        </div>
                                    </div>
                                </section>`
        $('body .first-repeat-row').last().after(section_to_append);
    }
}

function secondDesignParseObjectToHTML(block_type, item) {
    const image_width = '385';
    const image_height = '289';
    let title = item['title'];
    let date = item['createdAt'];
    let src = '/previews/' + item['filePath'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    let href = `/counting_news/${item['id']}?pageType=top`;
    console.log(date)
    if (block_type === 'first_row_essential') {
        return firstRowEssentialBlock(href, title, src, date);
    } else if (block_type === 'second_row_essential') {
        return secondRowEssentialBlock(href, title, src, date);
    } else if (block_type === 'third_row_essential') {
        return thirdRowEssentialBlock(href, title, src, date);
    } else if (block_type === 'first_row_repeat_vertical') {
        return firstRepeatableRowVertical(href, title, src, date);
    } else if (block_type === 'first_row_repeat_horizontal') {
        return firstRepeatableRowHorizontal(href, title, src, date);
    } else if (block_type === 'second_row_repeat') {
        return secondRepeatableRow(href, title, src, date);
    }
}

function firstRowEssentialBlock(href, title, src, date) {
    let row_num = getNumberOfBlocks();
    let compared_result = workWithDateOnCard(moment(date.timestamp * 1000).format("DD"), moment(date.timestamp * 1000).format("MMMM"))
    let text_to_set = compared_result.day + ' ' + compared_result.month
    if (row_num === 0) {
        return `<a href="${href}"
                   class="fint fint_vertical fint_bordered hide-on-560">
                    <div class="fint__image fint__image_vertical">
                        <img src="${src}">
                    </div>
                    <div class="fint__content fint__content_vertical">
                        <div class="fint__title fint__title_vertical">${title}</div>
                    </div>
                </a>`;
    }
    return `<a href="${href}"
               class="fint fint_vertical fint_bordered">
                <div class="fint__image fint__image_vertical">
                    <img src="${src}">
                </div>
                <div class="fint__content fint__content_vertical">
                    <div class="fint__title fint__title_vertical">${title}</div>
                </div>
            </a>`;
}

function secondRowEssentialBlock(href, title, src, date) {
    let row_num = getNumberOfBlocks();
    let compared_result = workWithDateOnCard(moment(date.timestamp * 1000).format("DD"), moment(date.timestamp * 1000).format("MMMM"))
    let text_to_set = compared_result.day + ' ' + compared_result.month
    if (row_num === 3) {
        return `<a href="${href}"
                   class="fint fint_vertical fint_bordered hide-on-1000">
                    <div class="fint__image fint__image_vertical">
                        <img src="${src}">
                    </div>
                    <div class="fint__content fint__content_vertical">
                        <div class="fint__title fint__title_vertical">${title}</div>
                    </div>
                </a>`;
    } else if (row_num === 4) {
        return `<a href="${href}"
                   class="fint fint_vertical fint_bordered hide-on-560">
                    <div class="fint__image fint__image_vertical">
                        <img src="${src}">
                    </div>
                    <div class="fint__content fint__content_vertical">
                        <div class="fint__title fint__title_vertical">${title}</div>
                    </div>
                </a>`;
    }
    return `<a href="${href}"
               class="fint fint_vertical fint_bordered">
                <div class="fint__image fint__image_vertical">
                    <img src="${src}">
                </div>
                <div class="fint__content fint__content_vertical">
                    <div class="fint__title fint__title_vertical">${title}</div>
                </div>
            </a>`;
}

function thirdRowEssentialBlock(href, title, src, date) {
    let num_block_in_row = getNumberOfBlocks();
    if ([7, 9, 11].includes(num_block_in_row)) {
        return `<a href="${href}"
                   class="fint fint_billet fint_bordered hide-on-1000">
                    <div class="fint__image fint__image_billet">
                        <img src="${src}">
                    </div>
                    <div class="fint__content fint__content_billet">
                        <div class="fint__title fint__title_billet">${title}</div>
                    </div>
                </a>`
    }
    return `<a href="${href}"
               class="fint fint_billet fint_bordered">
                <div class="fint__image fint__image_billet">
                    <img src="${src}">
                </div>
                <div class="fint__content fint__content_billet">
                    <div class="fint__title fint__title_billet">${title}</div>
                </div>
            </a>`
    }

function firstRepeatableRowVertical(href, title, src, date) {
    let num_block_in_row = getElementsFromFirstRepeatRow();
    let compared_result = workWithDateOnCard(moment(date.timestamp * 1000).format("DD"), moment(date.timestamp * 1000).format("MMMM"))
    console.log(compared_result)
    let text_to_set = compared_result.day + ' ' + compared_result.month
    if (num_block_in_row === 0) {
        return `<a href="${href}"
                   class="fint fint_vertical hide-on-560">
                    <div class="fint__image fint__image_vertical">
                        <img src="${src}"
                             alt="">
                    </div>
                    <div class="fint__content fint__content_vertical">
                        <div class="fint__title fint__title_vertical">${title}</div>
                    </div>
                </a>`
    }
    return `<a href="${href}"
               class="fint fint_vertical">
                <div class="fint__image fint__image_vertical">
                    <img src="${src}"
                         alt="">
                </div>
                <div class="fint__content fint__content_vertical">
                    <div class="fint__title fint__title_vertical">${title}</div>
                </div>
            </a>`
}

function firstRepeatableRowHorizontal(href, title, src, date) {
    let num_block_in_row = getElementsFromFirstRepeatRow();
    if ([3, 5, 7].includes(num_block_in_row)) {
        return `<a href="${href}"
                   class="fint fint_billet hide-on-1000">
                    <div class="fint__image fint__image_billet">
                        <img src="${src}"
                             alt="">
                    </div>
                    <div class="fint__content fint__content_billet">
                        <div class="fint__title fint__title_billet">${title}</div>
                    </div>
                </a>`
    }
    return `<a href="${href}"
               class="fint fint_billet">
                <div class="fint__image fint__image_billet">
                    <img src="${src}"
                         alt="">
                </div>
                <div class="fint__content fint__content_billet">
                    <div class="fint__title fint__title_billet">${title}</div>
                </div>
            </a>`
}

function secondRepeatableRow(href, title, src, date) {
    let num_block_in_row = getElementsFromSecondRepeatRow();
    let compared_result = workWithDateOnCard(moment(date.timestamp * 1000).format("DD"), moment(date.timestamp * 1000).format("MMMM"))
    let text_to_set = compared_result.day + ' ' + compared_result.month
    if (num_block_in_row === 0) {
        return `<a href="${href}"
                   class="fint fint_vertical fint_bordered hide-on-1000">
                    <div class="fint__image fint__image_vertical">
                        <img src="${src}"
                             alt="">
                    </div>
                    <div class="fint__content fint__content_vertical">
                        <div class="fint__title fint__title_vertical">${title}</div>
                    </div>
                </a>`
    } else if (num_block_in_row === 1) {
        return `<a href="${href}"
                   class="fint fint_vertical fint_bordered hide-on-560">
                    <div class="fint__image fint__image_vertical">
                        <img src="${src}"
                             alt="">
                    </div>
                    <div class="fint__content fint__content_vertical">
                        <div class="fint__title fint__title_vertical">${title}</div>
                    </div>
                </a>`
    }
    return `<a href="${href}"
               class="fint fint_vertical fint_bordered">
                <div class="fint__image fint__image_vertical">
                    <img src="${src}"
                         alt="">
                </div>
                <div class="fint__content fint__content_vertical">
                    <div class="fint__title fint__title_vertical">${title}</div>
                </div>
            </a>`
}