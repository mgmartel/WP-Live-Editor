// Customizer
jQuery(document).ready( function($) {
    // Sidebar collpase
    var body = $( document.body ),
        overlay = body.children('.wp-full-overlay');

    $('.collapse-sidebar').click( function( event ) {
        overlay.toggleClass( 'collapsed' ).toggleClass( 'expanded' );
        event.preventDefault();
    });

    $('.customize-section-title').click( function( event ) {
        var clicked = $( this ).parents( '.customize-section' );

        if ( clicked.hasClass('cannot-expand') )
            return;

    // Temporary accordeon
    $( '.customize-section' ).not( clicked ).removeClass( 'open' );
        clicked.toggleClass( 'open' );
        event.preventDefault();
    });

});

// iFrame
jQuery(document).ready(function($) {
    if ( ! overrideIFrameLoader ) {

        var reqUrl = iframeUrl,
                    iframe = $('.wp-full-overlay-main iframe');

        // Fade iFrame in onload
        iframe.load(function() {
            // Make sure admin links take over the window instead of the iFrame
            iframe.contents().find('a').click( function(e) {
                if ( disableNavigation ) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }

                if ( e.target.href.indexOf( 'wp-admin' ) != -1  ) {
                    e.stopPropagation; e.preventDefault;
                    window.location.href = e.target;
                }
            })

            iframe.fadeIn( function() {
                $(window).trigger('iframeLoaded');
            });
        });

        iframe.attr('src',reqUrl);

    }
});