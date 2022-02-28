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

                        if (blockCount !== 0) {
                            for (let i = 0; i < blockCount; i++) {
                                let teaser_block = thirdDesignParseObjectToHTML(array_with_teasers_objects[i]);
                                $('#testBlockUnderText').append(teaser_block);
                            }
                        } else {
                            $('#is_ajax_load').val(0)
                        }
                        $('#loading').hide();
                    }
                });
            }
        }
    });
});

function thirdDesignParseObjectToHTML(item) {
    const image_width = '240';
    const image_height = '180';
    const type = window.location.pathname.split('/')[2];
    const article_id = window.location.pathname.split('/')[3];
    const title = item['text'];
    const src = '/previews/' + item['file_path'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting/${item['id']}/${article_id}?pageType=` + type;

    return `<a href="${href}" target="_blank" style="color: #353535;">
                                        <div id="testBlockUnderTextDiv" class="add-shows">
                                            <img src="${src}">
                                            <p class="testBlockUnderTextHeader">${title}</p>
                                            <p class="readMoreBtn">Подробнее</p>
                                        </div>
                                    </a>`
}