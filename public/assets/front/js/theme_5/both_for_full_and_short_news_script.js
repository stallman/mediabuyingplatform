const full_block_num = 8;
const short_blocks_num = 12;
const list_for_s_full = [0, 1, 2, 3, 4, 5];
const list_for_xs_short = [0, 1, 2, 3, 5, 6, 7, 8];
const list_for_m_short = [4, 9, 10, 11];
const list_for_m_full = [6, 7]

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
});

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
            const array_with_teasers_objects = JSON.parse(data);
            let blockCount = array_with_teasers_objects.length

            if (blockCount !== 0) {
                for (let i = 0; i < blockCount; i++) {
                    let num_blocks = getNumbBlocksInEssentialCol();
                    let new_block_size = getNewBlockSize(num_blocks, type);
                    if (new_block_size !== void 0) {
                        const teaser_block = fifthDesignParseObjectToHTML(array_with_teasers_objects[i], new_block_size);
                        $('#load_main').append(teaser_block);
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
    return num_blocks;
}

function fifthDesignParseObjectToHTML(item, someSize) {
    const image_width = '216';
    const image_height = '162';
    const type = window.location.pathname.split('/')[2];
    const article_id = window.location.pathname.split('/')[3];
    const title = item['text'];
    const src = '/previews/' + item['file_path'].slice(16) + '/' + image_width + 'x' + image_height + '_' + item['fileName'];
    const href = `/counting/${item['id']}/${article_id}?pageType=` + type;

    return `<div class="item ${someSize} -theme-white">
                    <a href="${href}" style="text-decoration: none;" target="_blank">
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

function getNewBlockSize(num_blocks, type) {
    let remainder = type === 'full' ? num_blocks % full_block_num : num_blocks % short_blocks_num;
    if (list_for_s_full.includes(remainder) && type === 'full') {
        return '-size-s'
    } else if (list_for_m_full.includes(remainder) && type === 'full') {
        return '-size-m'
    } else if (list_for_xs_short.includes(remainder) && type === 'short') {
        return '-size-xs'
    } else if (list_for_m_short.includes(remainder) && type === 'short') {
        return '-size-m'
    }
    return void 0;
}