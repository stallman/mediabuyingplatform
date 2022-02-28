if (!$.cookie('setScreenSize')) {
    $.ajax({
        data: {
            'screenSize': screen.width + 'x' + screen.height
        },
        method: 'post',
        url: '/visit/' + $.cookie('unique_index'),
        success: function (data) {
            $.cookie('setScreenSize', 1, {expires: 365 * 10, path: '/'})
        },
        error: function (data) {
            $.cookie('setScreenSize', 0, {expires: 365 * 10, path: '/'})
        }
    })
}