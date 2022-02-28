if (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.toLowerCase().match(/firefox\/(\d+)/)) {
    let count = 0;
    window.onload = function () {
        if (typeof history.pushState === "function") {
            history.pushState("back", null, null);
            window.onpopstate = function () {
                history.pushState('back', null, null);
                if(count === 1) window.location.href = window.location.origin + '/teasers'
            };
        }
    }
    setTimeout(function(){count = 1;},200);
}