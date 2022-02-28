$(document).ready(function() {

    let num_items = 0;

    function getSidebarItems() {
        let sidebar_items = Object.entries($('.sidebar-item').find('.item'))
            .filter(item => item[0] !== 'length' && item[0] !== 'prevObject');
        return sidebar_items;
    }

    function moveItemsFromSidebar() {
        let sidebar_items = getSidebarItems();
        if (sidebar_items && sidebar_items.length > 0) {
            sidebar_items.map(item => {
                $('#load_main').prepend($(item[1]));
                num_items += 1;
            })
            $('.sidebar-item').css({'display': 'none'});
        }
    }

    function moveItemsToSidebar() {
        if (num_items > 0) {
            $('.sidebar-item').css({'display': 'block'});
            for(let i = 0; i < num_items; i++) {
                $('.sidebar-item').append($('#load_main').find('.item')[0])
                console.log($('#load_main').find('.item'))
            }
            num_items = 0
        }
    }

    function workWithScreenWidth() {
        $(window).width() <= 727 ? moveItemsFromSidebar() : moveItemsToSidebar();
    }

    workWithScreenWidth();

    $(window).resize(function() {
        workWithScreenWidth();
    })

})