<?php
/**
 * Customize Controls
 *
 * @package WordPress
 * @subpackage Customize
 * @since 3.4.0
 */

define( 'IFRAME_REQUEST', true );

global $bfee;

/**
 * START EDIT POST HEADER
 */
$parent_file = 'edit.php';
$submenu_file = 'edit.php';

wp_reset_vars(array('action', 'safe_mode', 'withcomments', 'posts', 'content', 'edited_post_title', 'comment_error', 'profile', 'trackback_url', 'excerpt', 'showcomments', 'commentstart', 'commentend', 'commentorder'));

$post = $post_type = $post_type_object = null;

if ( $post_id )
	$post = get_post( $post_id );

if ( $post ) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
}

$sendback = wp_get_referer();
if ( ! $sendback ||
     strpos( $sendback, 'post.php' ) !== false ||
     strpos( $sendback, 'post-new.php' ) !== false ) {
	if ( 'attachment' == $post_type ) {
		$sendback = admin_url( 'upload.php' );
	} else {
		$sendback = admin_url( 'edit.php' );
		$sendback .= ( ! empty( $post_type ) ) ? '?post_type=' . $post_type : '';
	}
} else {
	$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), $sendback );
}

// We're always editing in this screen
$action = 'edit';
$editing = true;

if ( empty( $post_id ) ) {
    wp_redirect( admin_url('post.php') );
    exit();
}

$p = $post_id;

if ( empty($post->ID) )
    wp_die( __('You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?') );

if ( null == $post_type_object )
    wp_die( __('Unknown post type.') );

if ( !current_user_can($post_type_object->cap->edit_post, $post_id) )
    wp_die( __('You are not allowed to edit this item.') );

if ( 'trash' == $post->post_status )
    wp_die( __('You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.') );

$post_type = $post->post_type;
if ( 'post' == $post_type ) {
    $parent_file = "edit.php";
    $submenu_file = "edit.php";
    $post_new_file = "post-new.php";
} elseif ( 'attachment' == $post_type ) {
    $parent_file = 'upload.php';
    $submenu_file = 'upload.php';
    $post_new_file = 'media-new.php';
} else {
    if ( isset( $post_type_object ) && $post_type_object->show_in_menu && $post_type_object->show_in_menu !== true )
        $parent_file = $post_type_object->show_in_menu;
    else
        $parent_file = "edit.php?post_type=$post_type";
    $submenu_file = "edit.php?post_type=$post_type";
    $post_new_file = "post-new.php?post_type=$post_type";
}

if ( $last = wp_check_post_lock( $post->ID ) ) {
    add_action('admin_notices', '_admin_notice_post_locked' );
} else {
    $active_post_lock = wp_set_post_lock( $post->ID );
    wp_enqueue_script('autosave');
}

$title = $post_type_object->labels->edit_item;
$post = get_post($post_id, OBJECT, 'edit');

if ( post_type_supports($post_type, 'comments') ) {
    wp_enqueue_script('admin-comments');
    enqueue_comment_hotkeys_js();
}

/**
 * START EDIT FORM ADVANCED
 */
/**
 * Post ID global
 * @name $post_ID
 * @var int
 */
global $current_user;
if ( ! isset($current_user) ) $current_user = wp_get_current_user();
//$post_ID = isset($post_ID) ? (int) $post_ID : 0;
//$user_ID = isset($user_ID) ? (int) $user_ID : 0;
$post_ID = $post_id;
$user_ID = $current_user;

$messages = array();
$messages['post'] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => sprintf( __('Post updated. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 2 => __('Custom field updated.'),
	 3 => __('Custom field deleted.'),
	 4 => __('Post updated.'),
	/* translators: %s: date and time of the revision */
	 5 => isset($_GET['revision']) ? sprintf( __('Post restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => sprintf( __('Post published. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 7 => __('Post saved.'),
	 8 => sprintf( __('Post submitted. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	 9 => sprintf( __('Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Post draft updated. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);
$messages['page'] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => sprintf( __('Page updated. <a href="%s">View page</a>'), esc_url( get_permalink($post_ID) ) ),
	 2 => __('Custom field updated.'),
	 3 => __('Custom field deleted.'),
	 4 => __('Page updated.'),
	 5 => isset($_GET['revision']) ? sprintf( __('Page restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => sprintf( __('Page published. <a href="%s">View page</a>'), esc_url( get_permalink($post_ID) ) ),
	 7 => __('Page saved.'),
	 8 => sprintf( __('Page submitted. <a target="_blank" href="%s">Preview page</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	 9 => sprintf( __('Page scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview page</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Page draft updated. <a target="_blank" href="%s">Preview page</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);
$messages['attachment'] = array_fill( 1, 10, __( 'Media attachment updated' ) ); // Hack, for now.

$messages = apply_filters( 'post_updated_messages', $messages );

$message = false;
if ( isset($_GET['message']) ) {
	$_GET['message'] = absint( $_GET['message'] );
	if ( isset($messages[$post_type][$_GET['message']]) )
		$message = $messages[$post_type][$_GET['message']];
	elseif ( !isset($messages[$post_type]) && isset($messages['post'][$_GET['message']]) )
		$message = $messages['post'][$_GET['message']];
}

$notice = false;
$form_extra = '';
if ( 'auto-draft' == get_post_status( $post ) ) {
	if ( 'edit' == $action )
		$post->post_title = '';
	$autosave = false;
	$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
} else {
	$autosave = wp_get_post_autosave( $post_ID );
}

$form_action = 'editpost';
$nonce_action = 'update-post_' . $post_ID;
$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr($post_ID) . "' />";

// Detect if there exists an autosave newer than the post and if that autosave is different than the post
if ( $autosave && mysql2date( 'U', $autosave->post_modified_gmt, false ) > mysql2date( 'U', $post->post_modified_gmt, false ) ) {
	foreach ( _wp_post_revision_fields() as $autosave_field => $_autosave_field ) {
		if ( normalize_whitespace( $autosave->$autosave_field ) != normalize_whitespace( $post->$autosave_field ) ) {
			$notice = sprintf( __( 'There is an autosave of this post that is more recent than the version below. <a href="%s">View the autosave</a>' ), get_edit_post_link( $autosave->ID ) );
			break;
		}
	}
	unset($autosave_field, $_autosave_field);
}

/**
 * START CUSTOMZE.PHP
 */
wp_reset_vars( array( 'url', 'return' ) );
$url = urldecode( $url );
$url = wp_validate_redirect( $url, home_url( '/' ) );
$return = $sendback;

/*if ( $return )
	$return = wp_validate_redirect( urldecode( $return ) );
if ( ! $return )
	$return = $url;*/

global $wp_scripts;

$registered = $wp_scripts->registered;
$wp_scripts = new WP_Scripts;
$wp_scripts->registered = $registered;

add_action( 'bfee_print_scripts',        'print_head_scripts', 20 );
add_action( 'bfee_print_footer_scripts', '_wp_footer_scripts'     );
add_action( 'bfee_print_footer_scripts', 'print_footer_scripts'     );
add_action( 'bfee_print_styles',         'print_admin_styles', 20 );

do_action( 'bfee_init' );

wp_enqueue_style( 'customize-controls' );

global $current_user, $current_screen, $wp_locale;

do_action( 'bfee_enqueue_scripts' );

// Let's roll.
@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

wp_user_settings();
_wp_admin_html_begin();

do_action( 'admin_head' );

$body_class = '';

if ( wp_is_mobile() ) :
	$body_class .= ' mobile';

	?><meta name="viewport" id="viewport-meta" content="width=device-width, initial-scale=0.8, minimum-scale=0.5, maximum-scale=1.2"><?php
endif;

$is_ios = wp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] );

if ( $is_ios )
	$body_class .= ' ios';

//$admin_title = sprintf( __( '%1$s &#8212; WordPress' ), strip_tags( sprintf( __( 'Customize %s' ), $wp_customize->theme()->display('Name') ) ) );
/**
 * @todo Set a nice title
 */
$admin_title = "Edit Post";
?><title><?php echo $admin_title; ?></title><?php
?>
<script type="text/javascript">
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var userSettings = {
		'url': '<?php echo SITECOOKIEPATH; ?>',
		'uid': '<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>',
		'time':'<?php echo time() ?>'
	},
	ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
	pagenow = '<?php echo $current_screen->id; ?>',
	typenow = '<?php echo $current_screen->post_type; ?>',
	thousandsSeparator = '<?php echo addslashes( $wp_locale->number_format['thousands_sep'] ); ?>',
	decimalPoint = '<?php echo addslashes( $wp_locale->number_format['decimal_point'] ); ?>',
	isRtl = <?php echo (int) is_rtl(); ?>;
</script>
<?php

do_action( 'bfee_print_styles' );
do_action( 'bfee_print_scripts' );


?>
</head>
<body class="no-js <?php echo esc_attr( $body_class ); ?>">
<script type="text/javascript">
	document.body.className = document.body.className.replace('no-js','js');
</script>

<div class="wp-full-overlay expanded">

	<!--<form id="bfee-controls" class="wrap wp-full-overlay-sidebar">-->

    <form name="post" action="post.php?bfee=1" method="post" id="post"<?php do_action('post_edit_form_tag'); ?>  class="wrap wp-full-overlay-sidebar">
    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" id="is-bfee" name="is_bfee" value="true" />
    <input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
    <input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ) ?>" />
    <input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ) ?>" />
    <input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
    <input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $post_type ) ?>" />
    <input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status) ?>" />
    <input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(stripslashes(wp_get_referer())); ?>" />
    <?php if ( ! empty( $active_post_lock ) ) { ?>
    <input type="hidden" id="active_post_lock" value="<?php echo esc_attr( implode( ':', $active_post_lock ) ); ?>" />
    <?php
    }
    if ( 'draft' != get_post_status( $post ) )
        wp_original_referer_field(true, 'previous');

    echo $form_extra;

    wp_nonce_field( 'autosave', 'autosavenonce', false );
    wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
    ?>

    <?php // Pretend we've got the textarea and title ?>
    <input type="hidden" name="post_title" id="hidden_title">
    <input type="hidden" name="content" id="hidden_content">

		<div id="bfee-header-actions" class="wp-full-overlay-header">

            <?php $bfee->templates->publish_button ( $post ); ?>
            <span class="spinner"></span>

            <a class="back button" href="<?php echo esc_url( $return ? $return : admin_url( 'edit.php' ) ); ?>">
                <?php _e( 'Cancel' ); ?>
            </a>

            <?php $bfee->templates->save_button( $post ); ?>

		</div>

		<div class="wp-full-overlay-sidebar-content">

            <?php if ( $notice ) : ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
            <?php endif; ?>
            <?php if ( $message ) : ?>
            <div id="message" class="updated"><p><?php echo $message; ?></p></div>
            <?php endif; ?>

			<div id="bfee-theme-controls" ><ul>
                    <?php require ( BFEE_DIR . 'lib/meta-boxes.php' ); ?>
			</ul></div>
		</div>

		<div id="bfee-footer-actions" class="wp-full-overlay-footer">
			<a href="#" class="collapse-sidebar button-secondary" title="<?php esc_attr_e('Collapse Sidebar'); ?>">
				<span class="collapse-sidebar-arrow"></span>
				<span class="collapse-sidebar-label"><?php _e('Collapse'); ?></span>
			</a>

            <a class="button" href="<?php echo $bfee->add_no_bfee_query_arg(); ?>" style="
               position: absolute;
                bottom: 9px;
                right: 16px;
               ">
                <?php _e( 'Switch interface' ); ?>
            </a>
		</div>
	</form>
	<div id="bfee-preview" class="wp-full-overlay-main">
        <iframe width="100%" height="100%" frameborder="0" scrolling="auto" src="" name="bfee-iframe" id="bfee-iframe"></iframe>
    </div>
	<?php

	do_action( 'bfee_print_footer_scripts' );
	do_action( 'admin_footer' );

    ?>
</div>
</body>
</html>
