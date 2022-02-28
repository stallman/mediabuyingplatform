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
    workWithDateOnArticle();
});

function workWithDateOnArticle() {
    let current_article_day = $('#author__date').find('.date_day').text();
    let current_article_month = $('#author__date').find('.date_month').text();
    if (current_article_day !== '' && current_article_month !== '') {
        let compared_dates_result = compareDates(current_article_day, current_article_month)
        let text_to_set = compared_dates_result.day + ' ' + compared_dates_result.month;
        $('.result_date').text(
            text_to_set
        )
    }
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
            let teaserBlock
            let someSizeControlVal = $('#some-size-control-val').val()

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    [teaserBlock, someSizeControlVal] = sixthDesignParseObjectToHTML(array_with_teasers_objects[i], someSizeControlVal);
                    $('#load_main').append(teaserBlock);
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#some-size-control-val').val(someSizeControlVal)
            $('#loading').hide();
        }
    });
}


function sixthDesignParseObjectToHTML(item, someSizeControlVal) {
    const image_width = '492';
    const image_height = '328';
    const type = window.location.pathname.split('/')[2];
    const article_id = window.location.pathname.split('/')[3];
    const title = item['text'];
    const src = '/previews/' + item['file_path'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting/${item['id']}/${article_id}?pageType=` + type;
    let someSize;
    let teaserBlock;

    [someSize, someSizeControlVal] = getSomeSize(someSizeControlVal, type)

    let some_color = getColorFromObject();

    if (someSize == '-size-s') {
        teaserBlock = `<div class="item -size-s -theme-white">
                        <a href="${href}" style="text-decoration: none; color: black;" target="_blank">
                            <div class="item-container">
                                <div class="item-cover">
                                    <div class="item-cover-thumb" style="background: ${some_color[0]};">
                                        <img src="${src}"
                                             class="item-cover-img">
                                        <div class="item-cover-mask"
                                             style="background: linear-gradient(${some_color[1]} 0%, ${some_color[0]} 100%);"></div>
                                    </div>
                                </div>
                                <div class="item-content">
                                    <div class="item-category">

                                    </div>
                                    <div class="item-title" style="color: rgb(249, 249, 249);">
                                        <div class="item-link">
                                            <span class="item-link-text" style="font-size: 1.2rem;">${title}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>`
    } else {
        teaserBlock = `<div class="item -size-l -theme-white">
                        <a href="${href}" style="text-decoration: none; color: black;" target="_blank">
                            <div class="item-container-large-version">
                                <div class="card-img-out" style="background: ${some_color[0]};">
                                    <h5 class="card-title" style="color: #ffffff; z-index: 3; font-size: 1.2rem;">${title}</h5>
                                    <div class="item__gradient" style="background: radial-gradient(100% 500% at 100% center, ${some_color[1]} 55%, ${some_color[0]} 75%); z-index: 100;">
                                    </div>
                                </div>
                                <div class="card_col_1" style="z-index: 1; background: rgb(165, 153, 128);">
                                    <img class="card-img" src="${src}">
                                </div>
                            </div>
                        </a>
                    </div>`
    }

    return [teaserBlock, someSizeControlVal];
}

function getSomeSize(someSizeControlVal, type) {
    let someSize;
    someSizeControlVal = parseInt(someSizeControlVal)
    const includes = (x, [h, ...t]) => h && (x === h || includes(x, t))

    if (type == 'short') {
        if (someSizeControlVal < 5) {
            someSize = '-size-s'
            someSizeControlVal++
        } else if (someSizeControlVal == 5 || someSizeControlVal == 9) {
            someSize = '-size-l'
            someSizeControlVal++
        } else if (someSizeControlVal > 5 && someSizeControlVal < 9) {
            someSize = '-size-s'
            someSizeControlVal++
        } else if (someSizeControlVal == 10) {
            someSize = '-size-s'
            someSizeControlVal = 1
        }
    } else {
        if (includes(someSizeControlVal, [1, 2, 3, 5, 6, 9, 10, 11, 12, 13])) {
            someSize = '-size-s'
            someSizeControlVal++
        } else if (includes(someSizeControlVal, [4, 7, 8, 14, 15])) {
            someSize = '-size-l'
            someSizeControlVal++
        } else if (someSizeControlVal == 16) {
            someSize = '-size-s'
            someSizeControlVal = 10
        }
    }

    return [someSize, someSizeControlVal]
}