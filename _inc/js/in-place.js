jQuery(document).ready ( function ($) {
    // Block the following of links
    $('a').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });
});