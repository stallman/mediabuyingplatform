$(document).ready(function () {
    $('#page_number').val(parseInt($('#page_number').val(), 10) + 1)
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
            let array_with_teasers_objects = JSON.parse(data);

            let lastRow = $('.fints-line_of-4')[$('.fints-line_of-4').length - 1]
            let lastRowElementsCount = lastRow.children.length

            if (lastRowElementsCount < 4 && lastRowElementsCount !== 0) {
                let splice_news_arr = array_with_teasers_objects.splice(0, (4 - lastRowElementsCount))
                const teasers_blocks = secondDesignParseObjectToHTML(splice_news_arr);
                $(lastRow).append(teasers_blocks);
            }

            let teasers_array = arraySplit(array_with_teasers_objects, 4)
            let blockCount = teasers_array.length

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    const teaser_blocks = secondDesignParseObjectToHTML(teasers_array[i]);
                    $('#load_main').append(`<div class="fints-line fints-line_of-4">${teaser_blocks}</div>`);
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#loading').hide();
        }
    });
}

function secondDesignParseObjectToHTML(items) {
    const image_width = '385';
    const image_height = '289';
    let html = '';
    for (let item of items) {
        let title = item['text'];
        let src = '/previews/' + item['file_path'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
        let href = `/counting/${item['id']}?pageType=top`;
        html += `<a href="${href}"
               class="fint fint_vertical fint_bordered hide-on-560">
                <div class="fint__image fint__image_vertical">
                    <img src="${src}">
                </div>
                <div class="fint__content fint__content_vertical">
                    <div class="fint__title fint__title_vertical">${title}</div>
                </div>
            </a>`;
    }

    return html;
}

function arraySplit(array, size) {
    let subarray = [];
    for (let i = 0; i < Math.ceil(array.length / size); i++) {
        subarray[i] = array.slice((i * size), (i * size) + size);
    }

    return subarray
}