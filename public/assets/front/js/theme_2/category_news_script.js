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

function secondDesignParseObjectToHTML(items) {
    const image_width = '385';
    const image_height = '289';
    let html = '';

    for (let item of items) {
        let title = item['title'];
        let date = item['createdAt'];
        let src = '/previews/' + item['filePath'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
        let href = `/counting_news/${item['id']}?pageType=category`;

        let compared_result = workWithDateOnCard(moment(date.timestamp * 1000).format("DD"), moment(date.timestamp * 1000).format("MMMM"))
        let text_to_set = compared_result.day + ' ' + compared_result.month

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
            let array_with_news_objects = JSON.parse(data);
            let lastRow = $('#load_main .fints-line_of-4')[$('#load_main  .fints-line_of-4').length - 2]
            let lastRowElementsCount = lastRow.children.length
            if (lastRowElementsCount < 4 && lastRowElementsCount !== 0) {
                let splice_news_arr = array_with_news_objects.splice(0, (4 - lastRowElementsCount))
                const news_blocks = secondDesignParseObjectToHTML(splice_news_arr);
                $(lastRow).append(news_blocks);
            }

            let news_array = arraySplit(array_with_news_objects, 4)
            let blockCount = news_array.length

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    const news_blocks = secondDesignParseObjectToHTML(news_array[i]);
                    $('#load_main').append(`<div class="fints-line fints-line_of-4">${news_blocks}</div>`);
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#loading').hide();
        }
    });
}

function arraySplit(array, size) {
    let subarray = [];
    for (let i = 0; i < Math.ceil(array.length / size); i++) {
        subarray[i] = array.slice((i * size), (i * size) + size);
    }

    return subarray
}