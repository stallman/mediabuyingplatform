$(document).ready(function(){
    if (window.matchMedia("(min-width: 992px)").matches) {

        var static =  $('.secondary-main');
        var fixed =  $('.fixed-box');
        var wrapper = $('.secondary__aside');

        if (static.outerHeight(true) > fixed.outerHeight(true)) {
            $(window).scroll(function(){
                var nsc = $(document).scrollTop();
                var bp1 = wrapper.offset().top;
                var bp2 = bp1 + wrapper.outerHeight()-fixed.height();

                if (nsc>bp1) {
                    fixed.css('position','fixed');
                } else {
                    fixed.css('position','relative');
                }
                if (nsc>bp2) {
                    fixed.css('top', bp2-nsc);
                } else {
                    fixed.css('top', '0');
                }
            });
        }
    }

});

function openMenu() {

    var elem = document.getElementById("header-my-links");

    if (elem.style.display === "flex") {

        elem.style.display = "none";

    } else {

        elem.style.display = "flex";

    }

}

window.onresize = function () {

    if ( window.innerWidth > 1200 ) {

        document.getElementById("header-my-links").style.display = "none";

    }

}