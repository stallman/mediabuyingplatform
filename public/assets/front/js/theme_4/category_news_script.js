$(document).ready(function () {
    replacePreloadedBlocks();
    replaceBlocksWhenResize();
    $(window).resize(function() {
        replaceBlocksWhenResize();
    });
    let slug = $('#slug').val();
    $('#page_number').val(parseInt($('#page_number').val(), 10) + 1)
    loadAdditionalBlocks(slug);
    $(window).scroll(function () {
        if ((Math.ceil($(window).scrollTop()) + 3*($(window).height()) >= $(document).height())) {
            if ($('#is_ajax_load').val() != 0) {
                ajaxLoad($('#page_number').val(), slug)
            }
        }
    });
});

function loadAdditionalBlocks(data) {
    for(let i = 0; i < 3; i++) {
        if ($('#is_ajax_load').val() != 0) {
            ajaxLoad($('#page_number').val(), data)
        }
    }
}

function fourthDesignParseObjectToHTML(item) {
    const image_width = '275';
    const image_height = '183';
    const title = item['title'];
    const src = '/previews/' + item['filePath'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting_news/${item['id']}?pageType=category`;

    return `<div class="grid-item item item-news">
                    <a class="teaser teaser-news" href="${href}"
                       target="_blank" style="background: rgb(190, 148, 192);">
                        <div class="teaser-image">
                            <div class="background" style="background: linear-gradient(0deg, rgb(190, 148, 192) 0px, transparent 100%);"></div>
                            <img src="${src}">
                        </div>
                        <div class="teaser-title" style="color: #212121;">
                            ${title}
                        </div>
                        <div class="category" style="color: #212121; background: rgb(190, 148, 192);">
                            Тип новости
                        </div>
                        <div class="button" style="display: none;">
                            <div class="read-more">
                                Подробнее
                            </div>
                        </div>
                    </a>
                </div>`;
}

function __getScroll(a) {
    let d = document,
        b = d.body,
        e = d.documentElement,
        c = "client" + a;
    a = "scroll" + a;
    return /CSS/.test(d.compatMode) ? (e[c] < e[a]) : (b[c] < b[a])
}

function ajaxLoad(page, slug, isAsync = true) {
    page = parseInt(page)
    $.ajax({
        type: 'GET',
        url: '/ajax-news-categories/' + slug + '/' + page,
        async: isAsync,
        beforeSend: function () {
            page += 1;
            $('#page_number').val(page)
            $('#loading').show();
        },
        success: function (data) {
            const array_with_news_objects = JSON.parse(data);
            let blockCount = array_with_news_objects.length

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    const news_block = fourthDesignParseObjectToHTML(array_with_news_objects[i]);
                    setTimeout(function() {
                        let col_def = findMinNewsInCol();
                        let window_width = $(window).width();
                        switch(col_def) {
                            case 1:
                                $('.first_col').append(news_block);
                                break;
                            case 2:
                                if (window_width >= 576) {
                                    $('.second_col').append(news_block);
                                } else {
                                    $('.first_col').append(news_block);
                                }
                                break;
                            case 3:
                                if (window_width >= 768) {
                                    $('.third_col').append(news_block);
                                } else if (window_width >= 576) {
                                    let min_for_tablet = Math.min(colHeight(1), colHeight(2))
                                    let num_col = getNumColByHeight(min_for_tablet);
                                    switch(num_col) {
                                        case 1:
                                            $('.first_col').append(news_block);
                                            break;
                                        case 2:
                                            $('.second_col').append(news_block);
                                            break;
                                    }
                                } else {
                                    $('.first_col').append(news_block);
                                }
                                break;
                            case 4:
                                if (window_width >= 992) {
                                    $('.fourth_col').append(news_block);
                                } else if (window_width >= 768) {
                                    let min_for_tablet = Math.min(colHeight(1), colHeight(2), colHeight(3))
                                    let num_col = getNumColByHeight(min_for_tablet);
                                    switch(num_col) {
                                        case 1:
                                            $('.first_col').append(news_block);
                                            break;
                                        case 2:
                                            $('.second_col').append(news_block);
                                            break;
                                        case 3:
                                            $('.third_col').append(news_block);
                                            break;
                                    }
                                } else if (window_width >= 576) {
                                    let min_for_tablet = Math.min(colHeight(1), colHeight(2))
                                    let num_col = getNumColByHeight(min_for_tablet);
                                    switch(num_col) {
                                        case 1:
                                            $('.first_col').append(news_block);
                                            break;
                                        case 2:
                                            $('.second_col').append(news_block);
                                            break;
                                    }
                                } else {
                                    $('.first_col').append(news_block);
                                }
                                break;
                        }
                    }, 200)
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#loading').hide();
        }
    });
}

function replaceBlocksWhenResize() {
    let window_width = $(window).width();
    if (window_width <= 991 && window_width >= 768) {
        workWithFourthCol();
    } else if (window_width <= 767 && window_width >= 576) {
        additionalWorkerWithFourthCol();
        workWithThirdCol();
    } else if (window_width <= 575) {
        additionalWorkerWithFourthCol();
        additionalWorkerWithThirdCol();
        workWithSecondCol();
    }
}

function workWithFourthCol() {
    while($('.fourth_col').find('.grid-item').length !== 0) {
        let min_col_extra = Math.min(colHeight(1), colHeight(2), colHeight(3));
        let current_col = getNumColByHeight(min_col_extra);
        switch(current_col) {
            case 1:
                $('.first_col').append($($('.fourth_col').find('.grid-item')).last());
                break;
            case 2:
                $('.second_col').append($($('.fourth_col').find('.grid-item')).last());
                break;
            case 3:
                $('.third_col').append($($('.fourth_col').find('.grid-item')).last());
                break;
        }
    }
    $('.all-news-block').css({'grid-template-columns': '1fr 1fr 1fr'})
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

function additionalWorkerWithFourthCol() {
    let fourth_teasers_col = $('.fourth_col').find('.grid-item').length;
    if (fourth_teasers_col > 0) {
        workWithFourthCol();
    }
}

function workWithThirdCol() {
    while($('.third_col').find('.grid-item').length !== 0) {
        let min_col_extra = Math.min(colHeight(1), colHeight(3));
        let current_col = getNumColByHeight(min_col_extra);
        switch(current_col) {
            case 1:
                $('.first_col').append($($('.third_col').find('.grid-item')).last());
                break;
            case 3:
                $('.second_col').append($($('.third_col').find('.grid-item')).last());
                break;
        }
    }
    $('.all-news-block').css({'grid-template-columns': '1fr 1fr'})
}

function additionalWorkerWithThirdCol() {
    let third_teasers_col = $('.third_col').find('.grid-item').length;
    if (third_teasers_col > 0) {
        workWithThirdCol();
    }
}

function workWithSecondCol() {
    while($('.second_col').find('.grid-item').length !== 0) {
        $('.first_col').append($($('.second_col').find('.grid-item')).last());
    }
    $('.all-news-block').css({'grid-template-columns': '1fr'})
}

function replacePreloadedBlocks() {
    let max_col = findMaxNewsInCol();
    let min_col = findMinNewsInCol();

    let last_block_height_max = lastBlockHeight(max_col);

    let max_col_height = colHeight(max_col);
    let min_col_height = colHeight(min_col);

    let first_var = max_col_height - last_block_height_max;
    let second_var = min_col_height + last_block_height_max;

    let min_col_block = getBlockCol(min_col);
    let max_col_block = getBlockCol(max_col);

    while (first_var > second_var) {

        $(min_col_block).append($($(max_col_block).find('.grid-item')).last());

        max_col = findMaxNewsInCol();
        min_col = findMinNewsInCol();
        last_block_height_max = lastBlockHeight(max_col);
        max_col_height = colHeight(max_col);
        min_col_height = colHeight(min_col);
        first_var = max_col_height - last_block_height_max;
        second_var = min_col_height + last_block_height_max;
        min_col_block = getBlockCol(min_col);
        max_col_block = getBlockCol(max_col);
    }

}

function getBlockCol(data) {
    let block;
    if (data === 1) {
        block = $('.first_col');
    } else if (data === 2) {
        block = $('.second_col');
    } else if (data === 3) {
        block = $('.third_col');
    } else if (data === 4) {
        block = $('.fourth_col');
    }
    return block;
}

function findMinNewsInCol() {
    let first_col = colHeight(1);
    let second_col = colHeight(2);
    let third_col = colHeight(3);
    let fourth_col = colHeight(4);

    let min_col = Math.min(first_col, second_col, third_col, fourth_col);

    return getNumColByHeight(min_col);
}

function findMaxNewsInCol() {
    let first_col = colHeight(1);
    let second_col = colHeight(2);
    let third_col = colHeight(3);
    let fourth_col = colHeight(4);

    let max_col = Math.max(first_col, second_col, third_col, fourth_col);

    return getNumColByHeight(max_col)
}

function colHeight(col_type) {
    let col_height;
    if (col_type === 1) {
        col_height = $('.first_col').find('.grid-item');
    } else if (col_type === 2) {
        col_height = $('.second_col').find('.grid-item');
    } else if (col_type === 3) {
        col_height = $('.third_col').find('.grid-item');
    } else {
        col_height = $('.fourth_col').find('.grid-item');
    }
    let sum_h = 0;
    for (let i = 0; i < col_height.length; i++) {
        sum_h += $(col_height[i]).height();
    }
    return sum_h
}

function lastBlockHeight(col_type) {
    let last_block_height;

    if (col_type === 1) {
        last_block_height = $($($('.first_col').find('.grid-item')).last()).height();
    } else if (col_type === 2) {
        last_block_height = $($($('.second_col').find('.grid-item')).last()).height();
    } else if (col_type === 3) {
        last_block_height = $($($('.third_col').find('.grid-item')).last()).height();
    } else if (col_type === 4) {
        last_block_height = $($($('.fourth_col').find('.grid-item')).last()).height();
    }

    return last_block_height
}