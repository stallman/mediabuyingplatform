$(function(){
    if($(window).width() > 920) {
        var topPos = $('.floating').offset().top;
        $(window).scroll(function() {
            var top = $(document).scrollTop(),
                pip = $('.footer').offset().top,
                height = $('.floating').outerHeight();
            if (top + 20 > topPos && top < pip - height - 265) {$('.floating').addClass('fixed').removeAttr("style");}
            else if (top > pip - height - 265) {$('.floating').removeClass('fixed').css({'position':'absolute','bottom':'265px'});}
            else {$('.floating').removeClass('fixed');}
        });
    }
});