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
    console.log(current_type)
    if (current_type === 'first_type') {
        let length_for_elements = $('#articleContainer').find('br').length;
        let first_val_from_length = parseInt(length_for_elements / 3) - 1;
        let first_place_for_teaser = $($('#articleContainer').find('br')).get(first_val_from_length);
        $('#load_content0').insertAfter(first_place_for_teaser)
        let second_place_for_teaser = $($('#articleContainer').find('br')).get(2*first_val_from_length);
        $('#load_content1').insertAfter(second_place_for_teaser)
    } else if (current_type === 'second_type') {
        let length_for_elements = $('#articleContainer').find('p').length;
        let first_val_from_length = parseInt(length_for_elements / 3) - 1;
        let first_place_for_teaser = $($('#articleContainer').find('p')).get(first_val_from_length);
        $('#load_content0').insertAfter(first_place_for_teaser)
        let second_place_for_teaser = $($('#articleContainer').find('p')).get(2*first_val_from_length);
        $('#load_content1').insertAfter(second_place_for_teaser)
    }
})