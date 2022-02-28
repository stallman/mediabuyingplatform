$(document).ready(function () {
    let slug = $('#slug').val();
    $('#page_number').val(parseInt($('#page_number').val(), 10) + 1)
    while (!__getScroll('Height')) {
        if ($('#is_ajax_load').val() == 0) break
        ajaxLoad($('#page_number').val(), slug, false)
    }

    $(window).scroll(function () {
        if ((Math.ceil($(window).scrollTop()) === $(document).height() - $(window).height())) {
            if ($('#is_ajax_load').val() != 0) {
                ajaxLoad($('#page_number').val(), slug)
            }
        }
    });
})

function sixthDesignParseObjectToHTML(item, someSizeControlVal) {
    const image_width = '492';
    const image_height = '328';
    const title = item['title'];
    const src = '/previews/' + item['filePath'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting_news/${item['id']}?pageType=category`;
    let someSize;
    let newsBlock;

    if (someSizeControlVal < 4) {
        someSize = '-size-s'
        someSizeControlVal++
    } else if (someSizeControlVal == 4) {
        someSize = '-size-l'
        someSizeControlVal++
    } else if (someSizeControlVal > 4 && someSizeControlVal < 7) {
        someSize = '-size-s'
        someSizeControlVal++
    } else if (someSizeControlVal == 7) {
        someSize = '-size-l'
        someSizeControlVal = 1
    }

    let some_color = getColorFromObject();

    if (someSize == '-size-s') {
        newsBlock = `<div class="item -size-s -theme-white">
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
        newsBlock = `<div class="item -size-l -theme-white">
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

    return [newsBlock, someSizeControlVal];
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
            let news_block;
            let someSizeControlVal = $('#some-size-control-val').val()

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    [news_block, someSizeControlVal] = sixthDesignParseObjectToHTML(array_with_news_objects[i], someSizeControlVal);
                    $('#load_main').append(news_block);
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#some-size-control-val').val(someSizeControlVal)
            $('#loading').hide();
        }
    });
}