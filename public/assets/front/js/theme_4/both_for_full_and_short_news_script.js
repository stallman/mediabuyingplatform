$(document).ready(function () {
    replacePreloadedBlocks();
    replaceBlocksWhenResize();
    workWithDateOnArticle();
    $(window).resize(function() {
        replaceBlocksWhenResize();
    });
    $('#page_number').val(parseInt($('#page_number').val(), 10) + 1);
    loadAdditionalBlocks();
    $(window).scroll(function () {
        if ((Math.ceil($(window).scrollTop()) + 3*($(window).height()) >= $(document).height())) {
            if ($('#is_ajax_load').val() != 0) {
                ajaxLoad($('#page_number').val())
            }
        }
    });
});

function workWithDateOnArticle() {
    let current_article_day = $('.news-info').find('.date_day').text();
    let current_article_month = $('.news-info').find('.date_month').text();
    if (current_article_day !== '' && current_article_month !== '') {
        let compared_dates_result = compareDates(current_article_day, current_article_month, true)
        let text_to_set = compared_dates_result.day + ':' + compared_dates_result.month;
        $('.news-date').text(
            text_to_set
        )
    }
}

function loadAdditionalBlocks() {
    for(let i = 0; i < 3; i++) {
        if ($('#is_ajax_load').val() != 0) {
            ajaxLoad($('#page_number').val())
        }
    }
}

function ajaxLoad(page, isAsync = true) {
    page = parseInt(page)
    let news = parseInt($('#article_id').val());
    let type = window.location.pathname.split('/')[2];
    $.ajax({
        type: 'GET',
        url: '/ajax-news-teasers/' + news + '/' + type + '/' + page,
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
                for (let i = 0; i < blockCount; i++) {
                    const teaser_block = firstDesignParseObjectToHTML(array_with_teasers_objects[i]);
                    let bool_teasers = notNewsCol();
                    if (bool_teasers) {
                        if (i % 2 === 0) {
                            $('.teasers-second-col').append(teaser_block);
                        } else if ($(window).width >= 992){
                            $('.teasers-third-col').append(teaser_block);
                        }
                    } else {
                        let col_def = findPlaceForTeaser();
                        switch(col_def) {
                            case 1:
                                $('.additional-first-col').append(teaser_block);
                                break;
                            case 2:
                                if ($(window).width() >= 768) {
                                    $('.additional-second-col').append(teaser_block);
                                } else {
                                    $('.additional-first-col').append(teaser_block);
                                }
                                break;
                            case 3:
                                if ($(window).width() >= 576){
                                    let mobile_col_def = mobileFindPlaceForTeasers();
                                    switch(mobile_col_def) {
                                        case 1:
                                            $('.additional-first-col').append(teaser_block);
                                            break;
                                        case 3:
                                            $('.teasers-second-col').append(teaser_block);
                                            break;
                                    }
                                } else {
                                    $('.additional-first-col').append(teaser_block);
                                }
                                break;
                            case 4:
                                if ($(window).width() >= 992) {
                                    $('.teasers-third-col').append(teaser_block);
                                } else if ($(window).width() >= 768) {
                                    let additional_col_def = additionalFindPlaceForTeaser();
                                    switch(additional_col_def) {
                                        case 1:
                                            $('.additional-first-col').append(teaser_block);
                                            break;
                                        case 2:
                                            $('.additional-second-col').append(teaser_block);
                                            break;
                                        case 3:
                                            $('.teasers-second-col').append(teaser_block);
                                            break;
                                    }
                                } else if ($(window).width() >= 576){
                                    let mobile_col_def = mobileFindPlaceForTeasers();
                                    switch(mobile_col_def) {
                                        case 1:
                                            $('.additional-first-col').append(teaser_block);
                                            break;
                                        case 3:
                                            $('.teasers-second-col').append(teaser_block);
                                            break;
                                    }
                                } else {
                                    $('.additional-first-col').append(teaser_block);
                                }
                                break;
                        }
                    }
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#loading').hide();
        }
    });
}

function replacePreloadedBlocks() {
    let news_height = newsColHeight();
    let second_height = secondTeasersCol();
    let third_height = thirdTeasersCol();
    let max_teasers_height = Math.max(second_height, third_height);
    while(news_height <= max_teasers_height) {
        let target_col = findAdditionalColWithMinHeight();
        switch(target_col) {
            case 1:
                if (max_teasers_height === second_height) {
                    let teaser = $($('.teasers-second-col').find('.grid-item')).last();
                    $($($('.teasers-second-col').find('.grid-item')).last()).remove();
                    $('.additional-first-col').append(teaser);
                } else if (max_teasers_height === third_height) {
                    let teaser = $($('.teasers-third-col').find('.grid-item')).last();
                    $($($('.teasers-second-col').find('.grid-item')).last()).remove();
                    $('.additional-first-col').append(teaser);
                }
                break;
            case 2:
                if (max_teasers_height === second_height) {
                    let teaser = $($('.teasers-second-col').find('.grid-item')).last();
                    $($($('.teasers-second-col').find('.grid-item')).last()).remove();
                    $('.additional-second-col').append(teaser);
                } else if (max_teasers_height === third_height) {
                    let teaser = $($('.teasers-third-col').find('.grid-item')).last();
                    $($($('.teasers-second-col').find('.grid-item')).last()).remove();
                    $('.additional-second-col').append(teaser);
                }
                break;
        }
        second_height = secondTeasersCol();
        third_height = thirdTeasersCol();
        max_teasers_height = Math.max(second_height, third_height);
        news_height = newsColHeightWithTeasers();
    }
}
function findAdditionalColWithMinHeight() {
    let first_col_height = getHeightFirstCol();
    let second_col_height = getHeightSecondCol();
    let min_height = Math.min(first_col_height, second_col_height);
    switch(min_height) {
        case first_col_height:
            return 1;
        case second_col_height:
            return 2;
    }
}

function newsColHeightWithTeasers() {
    let news_height = $('.news-for-full').height();
    let result_height = news_height + $('.additional-teasers').height();
    return result_height;
}

function firstDesignParseObjectToHTML(item) {
    const image_width = '275';
    const image_height = '183';
    const type = window.location.pathname.split('/')[2];
    const article_id = window.location.pathname.split('/')[3];
    const title = item['text'];
    const src = '/previews/' + item['file_path'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting/${item['id']}/${article_id}?pageType=` + type;

    return `<div class="grid-item item item-product">
                    <a class="teaser teaser-product" href="${href}" target="_blank" style="background: rgb(141, 96, 73);">
                        <div class="teaser-image">
                            <div class="background" style="background: linear-gradient(0deg, rgb(141, 96, 73) 0px, transparent 100%);"></div>
                            <img src="${src}" width="492" height="328">
                            <div class="category" style="color: #fafafa; background: rgb(141, 96, 73);">
                                Лучшее
                            </div>
                        </div>
                        <div class="teaser-title" style="color: #fafafa;">${title}</div>
                        <div class="button" style="display: none;">
                            <div class="read-more">
                                Подробнее
                            </div>
                        </div>
                    </a>
                </div>`;
}

function replaceBlocksWhenResize() {
    let window_width = $(window).width();
    if (window_width <= 991 && window_width >= 768) {
        // перемещаем блоки из четвертого тизерного ряда в третий, второй и первый
        workWithFourthCol();
    } else if (window_width <= 767 && window_width >= 576) {
        // перемещаем блоки из второго тизерного ряда в первый и третий
        additionalWorkerWithFourthCol();
        workWithThirdCol();
    } else if (window_width <= 575) {
        additionalWorkerWithFourthCol();
        additionalWorkerWithThirdCol();
        workWithSecondCol();
    }
}

function getNumColByHeight(data) {
    switch (data) {
        case colHeight(1):
            return 1;
        case colHeight(2):
            return 2;
        case colHeight(3):
            return 3;
        case colHeight(4):
            return 4;
    }
}

function colHeight(col_type) {
    if (col_type === 1) {
        return getHeightFirstCol();
    } else if (col_type === 2) {
        return getHeightSecondCol();
    } else if (col_type === 3) {
        return secondTeasersCol();
    } else {
        return thirdTeasersCol();
    }
}

function workWithFourthCol() {
    while($('.teasers-third-col').find('.grid-item').length !== 0) {
        let min_col_extra = Math.min(colHeight(1), colHeight(2), colHeight(3));
        let current_col = getNumColByHeight(min_col_extra);
        switch(current_col) {
            case 1:
                $('.additional-first-col').append($($('.teasers-third-col').find('.grid-item')).last());
                break;
            case 2:
                $('.additional-second-col').append($($('.teasers-third-col').find('.grid-item')).last());
                break;
            case 3:
                $('.teasers-second-col').append($($('.teasers-third-col').find('.grid-item')).last());
                break;
        }
    }
    $('.short-essential-block').css({'grid-template-columns': '2fr 1fr'})
}

function additionalWorkerWithFourthCol() {
    let fourth_teasers_col = $('.teasers-third-col').find('.grid-item').length;
    if (fourth_teasers_col > 0) {
        workWithFourthCol();
    }
}

function workWithThirdCol() {
    while($('.teasers-second-col').find('.grid-item').length !== 0) {
        let min_col_extra = Math.min(colHeight(1), colHeight(2));
        let current_col = getNumColByHeight(min_col_extra);
        switch(current_col) {
            case 1:
                $('.additional-first-col').append($($('.teasers-second-col').find('.grid-item')).last());
                break;
            case 2:
                $('.additional-second-col').append($($('.teasers-second-col').find('.grid-item')).last());
                break;
        }
    }
    $('.short-essential-block').css({'grid-template-columns': '1fr'})
}

function additionalWorkerWithThirdCol() {
    let third_teasers_col = $('.teasers-second-col').find('.grid-item').length;
    if (third_teasers_col > 0) {
        workWithThirdCol();
    }
}

function workWithSecondCol() {
    while($('.additional-second-col').find('.grid-item').length !== 0) {
        $('.additional-first-col').append($($('.additional-second-col').find('.grid-item')).last());
    }
}

function newsColHeight() {
    return $($('.news-wrapper').get(0)).height();
}

function secondTeasersCol() {
    let teasers_in_second_col = $('.teasers-second-col').find('.grid-item');
    let sum_h = 0;
    for (let i = 0; i < teasers_in_second_col.length; i++) {
        sum_h += $(teasers_in_second_col[i]).height();
    }
    return sum_h;
}

function thirdTeasersCol() {
    let teasers_in_third_col = $('.teasers-third-col').find('.grid-item');
    let sum_h = 0;
    for (let i = 0; i < teasers_in_third_col.length; i++) {
        sum_h += $(teasers_in_third_col[i]).height();
    }
    return sum_h;
}

function hasNewsColTeasers() {
    let teasers = $('.additional-teasers').find('.grid-item').length;
    if (teasers > 0) {
        return true;
    }
    return false;
}

function notNewsCol() {
    let second_col_height = secondTeasersCol();
    let third_col_height = thirdTeasersCol();
    let news_col_height = newsColHeight();
    let max_height = Math.max(second_col_height, third_col_height);
    let bool_teasers_news = hasNewsColTeasers();
    if (max_height < news_col_height && !bool_teasers_news) {
        return true
    }
    return false;
}

function getHeightFirstCol() {
    let news_height = newsColHeight();
    let teasers_in_first_col = $('.additional-first-col').find('.grid-item');
    let sum_h = 0;
    for (let i = 0; i < teasers_in_first_col.length; i++) {
        sum_h += $(teasers_in_first_col[i]).height();
    }
    return sum_h + news_height;
}

function getHeightSecondCol() {
    let news_height = newsColHeight();
    let teasers_in_second_col = $('.additional-second-col').find('.grid-item');
    let sum_h = 0;
    for (let i = 0; i < teasers_in_second_col.length; i++) {
        sum_h += $(teasers_in_second_col[i]).height();
    }
    return sum_h + news_height;
}

function findPlaceForTeaser() {
    let first_col_height = getHeightFirstCol();
    let second_col_height = getHeightSecondCol();
    let third_col_height = secondTeasersCol();
    let fourth_col_height = thirdTeasersCol();
    let min_height = Math.min(first_col_height, second_col_height, third_col_height, fourth_col_height);
    switch(min_height) {
        case first_col_height:
            return 1
        case second_col_height:
            return 2
        case third_col_height:
            return 3
        case fourth_col_height:
            return 4
    }
}

function additionalFindPlaceForTeaser() {
    let first_col_height = getHeightFirstCol();
    let second_col_height = getHeightSecondCol();
    let third_col_height = secondTeasersCol();
    let min_height = Math.min(first_col_height, second_col_height, third_col_height);
    switch(min_height) {
        case first_col_height:
            return 1
        case second_col_height:
            return 2
        case third_col_height:
            return 3
    }
}

function mobileFindPlaceForTeasers() {
    let first_col_height = getHeightFirstCol();
    let third_col_height = secondTeasersCol();
    let min_height = Math.min(first_col_height, third_col_height);
    switch(min_height) {
        case first_col_height:
            return 1
        case third_col_height:
            return 3
    }
}