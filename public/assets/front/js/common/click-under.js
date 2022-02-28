if (typeof navigator !== 'undefined' && navigator.userAgent && navigator.userAgent.toLowerCase().match(/firefox\/(\d+)/)) {
    $('#header-my-links').on('click', function () {
        window.location.href = window.location.origin
    })

    $('.menu').on('click', function () {
        window.location.href = window.location.origin
    })

    $('.some-header-nav').on('click', function () {
        window.location.href = window.location.origin
    })

    $('.fourth-design-navbar').on('click', function () {
        window.location.href = window.location.origin
    })
}