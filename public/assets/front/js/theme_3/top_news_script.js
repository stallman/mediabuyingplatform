$(document).ready(function () {
        $('#page_number').val(parseInt($('#page_number').val(), 10) + 1)
        while (!__getScroll('Height')) {
            if ($('#is_ajax_load').val() == 0) break
            ajaxLoad($('#page_number').val(), false)
        }

        $(window).scroll(function () {
            if ((parseInt($(window).scrollTop()) === $(document).height() - $(window).height())) {
                if ($('#is_ajax_load').val() != 0) {
                    ajaxLoad($('#page_number').val())
                }
            }
        });
    }
);

function thirdDesignParseObjectToHTML(item, someVal) {
    const image_width = '240';
    const image_height = '180';
    const title = item['title'];
    const src = '/previews/' + item['filePath'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting_news/${item['id']}?pageType=top`;

    let blockHorizontal = `<a href="${href}"
                           target="_blank" style="color: #353535;">
                            <div id="horizontalTizBlock" class="add-shows">
                                <div id="horizontalTizBlockImg">
                                    <img src="${src}">
                                </div>
                                <p class="horizontalTizBlockHeader">${title}</p>
                                <p class="readMoreBtn">Подробнее</p>
                            </div>
                        </a>`

    let blockVertical = `<a href="${href}"
                           target="_blank" style="color: #353535;">
                            <div id="verticalTizBlock" class="add-shows">
                                <div id="verticalTizBlockImg">
                                     <img src="${src}">
                                </div>
                                <p class="verticalTizBlockHeader">${title}</p>
                                <p class="readMoreBtn">Подробнее</p>
                            </div>
                        </a>`

    if (someVal < 2) {
        return [blockHorizontal, ++someVal]
    } else if (someVal == 2) {
        return [blockVertical, ++someVal]
    } else {
        return [blockVertical, someVal - 3]
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
            const array_with_news_objects = JSON.parse(data);
            let someVal = $('#some-val').val()
            let blockCount = array_with_news_objects.length

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    let news_block;
                    [news_block, someVal] = thirdDesignParseObjectToHTML(array_with_news_objects[i], someVal);
                    $('#middleContent').append(news_block);
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#some-val').val(someVal)
            $('#loading').hide();
        }
    });
}