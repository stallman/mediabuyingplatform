$(document).ready(function() {

    function workWithAllSmallCards() {
        let some_array = Object.entries($('.-size-s ')).filter(item => item[0] !== 'length' && item[0] !== 'prevObject');
        if (some_array && some_array.length > 0) {
            some_array.map(item => {
                let some_color = getColorFromObject();
                $(item[1]).find('.item-cover-thumb').css({'background': `${some_color[0]}`});
                $(item[1]).find('.item-cover-mask').css({'background': `linear-gradient(${some_color[1]} 0%, ${some_color[0]} 100%)`});
            })
        }
    }

    function workWithAllBigCards() {
        let some_array = Object.entries($('.-size-l')).filter(item => item[0] !== 'length' && item[0] !== 'prevObject')
        if (some_array && some_array.length > 0) {
            some_array.map(item =>  {
                let some_color = getColorFromObject();
                $(item[1]).find('.card-img-out').css({'background': `${some_color[0]}`});
                $(item[1]).find('.item__gradient').css({'background': `radial-gradient(100% 500% at 100% center, ${some_color[1]} 55%, ${some_color[0]} 75%)`});
            })
        }
    }

    function workWithCards() {
        workWithAllSmallCards();
        workWithAllBigCards();
    }

    workWithCards();

});