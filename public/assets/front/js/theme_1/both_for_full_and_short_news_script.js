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

    workWithLink();
});

function workWithLink() {
    let source_link = $('.source_link').text();
    if (source_link === '') {
        $('.info-text__link').hide();
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
            const array_with_teasers_objects = JSON.parse(data)
            let blockCount = array_with_teasers_objects.length

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    const news_li_block = firstDesignParseObjectToHTML(array_with_teasers_objects[i]);
                    $('#place_for_teasers').append(news_li_block);
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#loading').hide();
        }
    });
}

function firstDesignParseObjectToHTML(item) {
    const image_width = '240';
    const image_height = '180';
    const type = window.location.pathname.split('/')[2];
    const article_id = window.location.pathname.split('/')[3];
    const title = item['text'];
    const src = '/previews/' + item['file_path'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting/${item['id']}/${article_id}?pageType=` + type;

    return `<div class="item-fourth__unit">
                <a href="${href}" class="item-fourth__link" target="_blank">
                <span class="item-fourth__img">
                    <img src="${src}" alt="${title}">
                </span>
                    <span class="item-fourth__title">${title}</span>
                </a>
            </div>`;

}