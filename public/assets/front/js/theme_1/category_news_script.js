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

function firstDesignParseObjectToHTML(item) {
    const image_width = '240';
    const image_height = '180';
    const title = item['title'];
    const src = '/previews/' + item['filePath'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting_news/${item['id']}?pageType=category`;

    return `<li><a href="${href}" target="_blank">
                             <span class="visual">
                                <img src="${src}" alt="${title}">
                             </span>
                             <h2>${title}</h2>
                         </a>
                     </li>`;
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
                    const news_li_block = firstDesignParseObjectToHTML(array_with_news_objects[i]);
                    $('#load_main').append(news_li_block);
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#loading').hide();
        }
    });
}