$(document).ready(function () {
    $('#page_number').val(parseInt($('#page_number').val(), 10) + 1)
    $(window).scroll(function () {
        let news = parseInt($('#article_id').val());
        let page = parseInt($('#page_number').val());
        let type = window.location.pathname.split('/')[2];

        if (($(window).scrollTop() === $(document).height() - $(window).height())) {
            if ($('#is_ajax_load').val() != 0) {
                $.ajax({
                    type: 'GET',
                    url: '/ajax-news-teasers/' + news + '/' + type + '/' + page,
                    beforeSend: function () {
                        page += 1;
                        $('#page_number').val(page)
                        $('#loading').show();
                    },
                    success: function (data) {
                        const array_with_teasers_objects = JSON.parse(data);
                        let blockCount = array_with_teasers_objects.length
                        let someVal = $('#some-val').val()

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
        }
    });
});

function thirdDesignParseObjectToHTML(item, someVal) {
    const image_width = '240';
    const image_height = '180';
    const type = window.location.pathname.split('/')[2];
    const article_id = window.location.pathname.split('/')[3];
    const title = item['text'];
    const src = '/previews/' + item['file_path'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting/${item['id']}/${article_id}?pageType=` + type;

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