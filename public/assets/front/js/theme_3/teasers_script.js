$(document).ready(function () {
    $('#page_number').val(parseInt($('#page_number').val(), 10) + 1)
    while (!__getScroll('Height')) {
        if ($('#is_ajax_load').val() == 0) break
        ajaxLoad($('#page_number').val(), false)
    }

    $(window).scroll(function () {
        if (($(window).scrollTop() === $(document).height() - $(window).height())) {
            if ($('#is_ajax_load').val() != 0) {
                ajaxLoad($('#page_number').val())
            }
        }
    });
})

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
        url: '/ajax-teasers/' + page,
        async: isAsync,
        beforeSend: function () {
            page += 1;
            $('#page_number').val(page)
            $('#loading').show();
        },
        success: function (data) {
            const array_with_teasers_objects = JSON.parse(data);
            let someVal = $('#some-val').val()
            let blockCount = array_with_teasers_objects.length

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    let teaser_block;
                    [teaser_block, someVal] = thirdDesignParseObjectToHTML(array_with_teasers_objects[i], someVal);
                    $('#middleContent').append(teaser_block);
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#some-val').val(someVal)
            $('#loading').hide();
        }
    });
}

function thirdDesignParseObjectToHTML(item, someVal) {
    const image_width = '240';
    const image_height = '180';
    const title = item['text'];
    const src = '/previews/' + item['file_path'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting/${item['id']}?pageType=top`;

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