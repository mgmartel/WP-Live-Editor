jQuery(document).ready( function($) {
    // Hacky way of hiding the menu
    $("#adminmenuback, #adminmenuwrap, .wp-full-overlay-sidebar").addClass( 'collapsed' ).removeClass( 'expanded' );

    /*
     * @todo L10n
     */
    var hideMenuButton = '<li id="hide-menu" class="hide-if-no-js toggle-menu"><div id="collapse-button"><div></div></div><span>Hide menu</span></li>';
    $('#adminmenuwrap > ul').prepend(hideMenuButton);

    // Menu
    $('.toggle-menu').click( function( e ) {
        toggleLiveMenu(e);
    });

    function toggleLiveMenu(e) {
        $('.wp-full-overlay-sidebar').toggleClass( 'collapsed' ).toggleClass( 'expanded' );
        $("#adminmenuback, #adminmenuwrap").toggleClass( 'collapsed' ).toggleClass( 'expanded' );
        e.preventDefault();
    }
});