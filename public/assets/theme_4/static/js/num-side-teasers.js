$(document).ready(function() {
    const article_element_height = $('.custom-news').outerHeight(true);
    if (article_element_height >= 830) {
        $('.custom-grid').css({'grid-template-rows': '1fr 1fr 1fr'});
        moveBlocks();
    }
});

function moveBlocks() {
    $('.custom-grid').append($('.teasers-custom-grid').find('.grid-item').get(0))
    $('.custom-grid').append($('.teasers-custom-grid').find('.grid-item').get(1))
}