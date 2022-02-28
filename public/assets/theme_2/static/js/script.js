function calcPositioning(floatingBlock, newsBlock) {
    const newsPos = newsBlock.offset();

    if ($(window).scrollTop() >= (newsPos['top'] - 10)) {

        if ((newsBlock.height() + newsPos['top']) > ($(window).scrollTop() + floatingBlock.height())) {

            if (floatingBlock.css('position') !== 'fixed'){
                floatingBlock.css({
                    'position': 'fixed',
                    'top': '10px'
                });
            }

        } else {

            floatingBlock.css({
                'position': 'absolute',
                'top': parseInt(newsBlock.height() - floatingBlock.height()) + newsPos['top']
            });

        }

    } else if (floatingBlock.css('position') !== 'top'){

        floatingBlock.css({
            'position': '',
            'top': ''
        });

    }
}

function rightBlockPositioning() {
    const floatingBlock = $("#rightBlock");
    const newsBlock = $("#newsBlock");

    this.calcPositioning(floatingBlock, newsBlock);

    $(window).scroll(function() {
        this.calcPositioning(floatingBlock, newsBlock);
    });

    $(window).resize(function () {
        this.calcPositioning(floatingBlock, newsBlock);
    });
}

function defineElementInContainer() {
    let some_str = $($('#articleContainer')[0]).html();
    if (some_str.includes('<br')) {
        return 'first_type'
    } else if (some_str.includes('<p')) {
        return 'second_type'
    }
    return false;
}

$(window).ready(function() {
    let current_type = defineElementInContainer();
    if (current_type === 'first_type') {
        let length_for_elements = $('#articleContainer').find('br').length;
        let first_val_from_length = parseInt(length_for_elements / 3) - 1;
        let first_place_for_teaser = $($('#articleContainer').find('br')).get(first_val_from_length);
        $('#firstTeaser').insertAfter(first_place_for_teaser)
        let second_place_for_teaser = $($('#articleContainer').find('br')).get(2*first_val_from_length);
        $('#focused-effect').insertAfter(second_place_for_teaser)
    } else if (current_type === 'second_type') {
        let length_for_elements = $('#articleContainer').find('p').length;
        let first_val_from_length = parseInt(length_for_elements / 3) - 1;
        let first_place_for_teaser = $($('#articleContainer').find('p')).get(first_val_from_length);
        $('#firstTeaser').insertAfter(first_place_for_teaser)
        let second_place_for_teaser = $($('#articleContainer').find('p')).get(2*first_val_from_length);
        $('#focused-effect').insertAfter(second_place_for_teaser)
    }
})
