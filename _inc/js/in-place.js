jQuery(document).ready ( function ($) {
    // Block the following of links
    $('a').bind('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });
});