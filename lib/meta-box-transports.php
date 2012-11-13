<?php
global $live_editor, $post;
$post_type = $post->post_type;


if ( post_type_supports($post_type, 'page-attributes') )
    $live_editor->add_metabox_transport( 'pageparentdiv', 'refresh' );

function live_editor_pageparentdiv_transport() {
    if ( isset ( $_GET[ 'page_template' ] ) && !empty ( $_GET[ 'page_template' ] ) ) {
        add_filter('get_post_metadata', 'live_editor_pageparentdiv_load_template', 10, 4);
    }
}
add_action ('live_editor_iframe_init', 'live_editor_pageparentdiv_transport');
//add_action ('setup_theme', 'live_editor_pageparentdiv_transport');

function live_editor_pageparentdiv_load_template( $meta, $post_id, $meta_key, $single ) {
    global $post;

    if ( $post_id == $post->ID && $meta_key == '_wp_page_template' && $single = true )
        return $_GET['page_template'];
}


if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post_type, 'post-formats' ) )
    $live_editor->add_metabox_transport( 'formatdiv', 'refresh' );