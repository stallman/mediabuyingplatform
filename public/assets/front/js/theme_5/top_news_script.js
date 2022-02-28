const top_blocks_num = 8;
const list_for_s_top = [0, 1, 2, 3, 4, 5];
const list_for_m_top = [6, 7];

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
    }
);

function fifthDesignParseObjectToHTML(item, someSize) {
    const image_width = '216';
    const image_height = '162';
    const title = item['title'];
    const src = '/previews/' + item['filePath'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting_news/${item['id']}?pageType=top`;

    return `<div class="item ${someSize} -theme-white">
                    <a href="${href}"
                       style="text-decoration: none;" target="_blank">
                        <div class="item-container">
                            <div class="item-cover">
                                <div class="item-cover-thumb">
                                    <img src="${src}"
                                         class="item-cover-img">
                                    <img src="${src}"
                                         class="item-cover-blur">
                                    <div class="item-cover-mask"
                                         style="background-image: linear-gradient(-180deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0) 55%, rgba(0, 0, 0, 0.91) 88%, rgb(0, 0, 0) 100%);"></div>
                                </div>
                            </div>
                            <div class="item-content">
                                <div class="item-category">
                                </div>
                                <div class="item-title" style="color: rgb(249, 249, 249);">
                                    <div class="item-link">
                                        <span class="item-link-text">${title}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>`;
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
            let blockCount = array_with_news_objects.length

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    let num_blocks = getNumbBlocksInEssentialCol();
                    let new_block_size = getNewBlockSize(num_blocks);
                    if (new_block_size !== void 0) {
                        const news_block = fifthDesignParseObjectToHTML(array_with_news_objects[i], new_block_size);
                        $('#load_main').append(news_block);
                    }
                }
            } else {
                $('#is_ajax_load').val(0)
            }
            $('#loading').hide();
        }
    });
}

function getNumbBlocksInEssentialCol() {
    let num_blocks = $('#load_main').find('.-theme-white').length;
    return num_blocks - 5;
}

function getNewBlockSize(num_blocks) {
    let remainder = num_blocks % top_blocks_num;
    if (list_for_s_top.includes(remainder)) {
        return '-size-s'
    } else if (list_for_m_top.includes(remainder)) {
        return '-size-m'
    }
    return void 0;
}