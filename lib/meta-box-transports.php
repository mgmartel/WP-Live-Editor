<?php
global $bfee, $post;
$post_type = $post->post_type;


if ( post_type_supports($post_type, 'page-attributes') )
    $bfee->add_metabox_transport( 'pageparentdiv', 'refresh' );

function bfee_pageparentdiv_transport() {
    if ( isset ( $_GET[ 'page_template' ] ) && !empty ( $_GET[ 'page_template' ] ) ) {
        add_filter('get_post_metadata', 'bfee_pageparentdiv_load_template', 10, 4);
    }
}
add_action ('bfee_iframe_init', 'bfee_pageparentdiv_transport');
//add_action ('setup_theme', 'bfee_pageparentdiv_transport');

function bfee_pageparentdiv_load_template( $meta, $post_id, $meta_key, $single ) {
    global $post;

    if ( $post_id == $post->ID && $meta_key == '_wp_page_template' && $single = true )
        return $_GET['page_template'];
}


if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post_type, 'post-formats' ) )
    $bfee->add_metabox_transport( 'formatdiv', 'refresh' );