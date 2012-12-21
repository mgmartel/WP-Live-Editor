jQuery(document).ready( function($) {
    var transport_refresh_buffer,
        transport_options = {},
        iframeScrollTop;

    // Get some vars from our template
    var post_url = iframeUrl,
        iframe = $('.wp-full-overlay-main iframe'),
        hidden_content = $("input#hidden_content"),
        hidden_title = $("input#hidden_title");

    function addQueryParam(key, value, url) {
        if (!url) url = window.location.href;
        var re = new RegExp("([?|&])" + key + "=.*?(&|#|$)", "gi");

        if (url.match(re)) {
            if (value)
                return url.replace(re, '$1' + key + "=" + value + '$2');
            else
                return url.replace(re, '$2');
        }
        else {
            if (value) {
                var separator = url.indexOf('?') !== -1 ? '&' : '?',
                    hash = url.split('#');
                url = hash[0] + separator + key + '=' + value;
                if (hash[1]) url += '#' + hash[1];
                return url;
            }
            else
                return url;
        }
    }

    function transport_refresh() {

        iframe.fadeOut();
        no_beforeonunload();
        contents_to_parent();
        iframeScrollTop = document.getElementById('live-admin-iframe').contentWindow.document.body.scrollTop;

        if ( transport_options ) var new_url = addQueryParam( 'live-editor-transport', JSON.stringify ( transport_options ), post_url );
        else new_url = post_url;

        $(iframe).attr( "src", new_url );
    }

    function contents_to_parent() {
        var contents = iframe.contents().find(".raptor-editable-post").html();
        hidden_content.val( contents );
        var title = iframe.contents().find(".live-editor-editable-title").first().text();
        hidden_title.val( title );
    }

    function no_beforeonunload() {
        frames['live-admin-iframe'].onbeforeunload = null;
    }

    // Get the data and make sure WP Raptor doesn't nag for unsaved changes when we're saving
    $('form#post').bind('submit', function() {
        no_beforeonunload();
        contents_to_parent();
    });

    // Show spinner on publish
    $('form#post #publish').click( function () { $('body').addClass('saving'); });

    // Fade iFrame in onload
    iframe.load(function() {
        if ( hidden_content.val().length ) iframe.contents().find(".raptor-editable-post").html ( hidden_content.val() );

        /**
         * @todo This iframe scrolltop business is not working properly yet (because Raptor also tries to set the scrolltop?)
         */
        if ( iframeScrollTop )
            document.getElementById('live-admin-iframe').contentWindow.document.body.scrollTop = iframeScrollTop;
        else if ( iframe.contents().find('.live-editor-editable-title').first().length ) {
            document.getElementById('live-admin-iframe').contentWindow.document.body.scrollTop =
                iframe.contents().find('.live-editor-editable-title').first().offset().top - 100;
        }
    });

    // Transport listeners
    if ( typeof( liveEditor.metabox_transports ) != 'undefined' && liveEditor.metabox_transports ) {
        $.each( liveEditor.metabox_transports, function( metabox, transport ) {
            $( "#" + metabox + " *").on("change", function() {
                if ( ! $(this).val() ) return;
                var transport_key = $(this).attr('name');
                transport_options[$(this).attr('name')] = $(this).val();

                if ( transport == 'refresh') {
                    if ( transport_refresh_buffer )
                        clearTimeout ( transport_refresh_buffer );
                    transport_refresh_buffer = setTimeout ( function() { transport_refresh() }, 1000 );
                }
            });
        });
    }

});