$(document).ready(function () {

    $( '#burger-menu-for-sidebar' ).on( 'click', function () {

        if (!$.cookie('sidebar')) {

            $.cookie('sidebar', 'closed', { path: '/' });

        } else{

            actionsWithCookies();

        }

    });

    function actionsWithCookies(){
        if ($.cookie('sidebar') === 'opened') {

            $.removeCookie('sidebar', { path: '/' });
            $.cookie('sidebar', 'closed', { path: '/' });

        } else {

            $.removeCookie('sidebar', { path: '/' });
            $.cookie('sidebar', 'opened', { path: '/' });

        }
    }

    $( '.nav-treeview' ).find( 'li' ).on( 'click', function () {

        localStorage.setItem('page', $(this).find('a').attr('href'));

    });

    $( '#sidebar-navigation' ).find( 'li' ).each( function() {

        if (!$(this).hasClass('has-treeview')) {

            $(this).on('click', function () {

                localStorage.setItem('page', $(this).find('a').attr('href'));

            });

        }

    })

    function findMenuItemUsingURL() {

        let result_version_of_url = window.location.href;
        const base_url = window.location.origin;

        return result_version_of_url.replace(base_url, '');

    }

    localStorage.setItem('page', findMenuItemUsingURL());

    if ( localStorage.getItem( 'page' ) !== null ) {

        $('.nav-treeview').each(function () {

            $(this).find('a').each(function () {

                if ($(this).attr('href') === localStorage.getItem('page')) {

                    $( this ).find( 'i' ).toggleClass( 'far' );
                    $( this ).find( 'i' ).toggleClass( 'fa' );

                    $( this ).parent().parent().css({ 'display': 'block' });

                }

            });

        });

    }

})