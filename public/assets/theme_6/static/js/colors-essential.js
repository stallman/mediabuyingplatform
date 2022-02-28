const colors_object = {
    1: ['rgb(138, 95, 137)', 'rgba(138, 95, 137, 0)'],
    2: ['rgb(185, 208, 246)', 'rgba(185, 208, 246, 0)'],
    3: ['rgb(181, 133, 130)', 'rgba(181, 133, 130, 0)'],
    4: ['rgb(161, 176, 183)', 'rgba(161, 176, 183, 0)'],
    5: ['rgb(179, 129, 101)', 'rgba(179, 129, 101, 0)'],
    6: ['rgb(195, 197, 198)', 'rgba(195, 197, 198, 0)'],
    7: ['rgb(239, 194, 164)', 'rgba(239, 194, 164, 0)']
}

function getRandomNumber() {
    let rand = 1 + Math.random() * (7 + 1 - 1);
    return Math.floor(rand);
}

function getColorFromObject() {
    let random_number = getRandomNumber();
    if (colors_object.hasOwnProperty(random_number)) {
        return colors_object[random_number];
    }
    return !1
}