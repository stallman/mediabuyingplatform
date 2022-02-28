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