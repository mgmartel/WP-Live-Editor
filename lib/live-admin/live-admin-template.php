<?php
define('IFRAME_REQUEST', true);

do_action( 'live_admin_start');

add_action( 'live_admin_print_scripts',        'print_head_scripts', 20 );
add_action( 'live_admin_print_footer_scripts', '_wp_footer_scripts'     );
add_action( 'live_admin_print_styles',         'print_admin_styles', 20 );

do_action( 'live_admin_init' );

//wp_enqueue_script( 'customize-controls' );
wp_enqueue_style( 'customize-controls' );

do_action( 'live_admin_enqueue_scripts' );
do_action( 'admin_enqueue_scripts' );

// Let's roll.
@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

wp_user_settings();
_wp_admin_html_begin();

$body_class = '';

if ( wp_is_mobile() ) :
	$body_class .= ' mobile';

	?><meta name="viewport" id="viewport-meta" content="width=device-width, initial-scale=0.8, minimum-scale=0.5, maximum-scale=1.2"><?php
endif;

$is_ios = wp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] );

if ( $is_ios )
	$body_class .= ' ios';

if ( is_rtl() )
	$body_class .=  ' rtl';
$body_class .= ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_locale() ) ) );

global $title, $current_screen, $current_user, $wp_locale, $handle;

if ( empty( $current_screen ) )
	set_current_screen();

get_admin_page_title();
$title = esc_html( strip_tags( $title ) );
$admin_title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), $title, get_bloginfo('name') );

?><title><?php echo $admin_title; ?></title>
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
    iframeUrl = "<?php echo esc_attr( $this->iframe_url ); ?>";
    overrideIFrameLoader = <?php echo ( $this->override_iframe_loader ) ? 'true' : 'false'; ?>;
    disableNavigation = <?php echo ( $this->disable_nav ) ? 'true' : 'false'; ?>;
</script><?php

do_action( "admin_print_styles-$handle" );
do_action( "admin_print_scripts-$handle" );
do_action( "admin_print_styles" );
do_action( "admin_print_scripts" );
do_action( 'live_admin_print_styles' );
do_action( 'live_admin_print_scripts' );
do_action( 'admin_head' );

$overlay_class = ( $this->collapsed ) ? ' collapsed' : ' expanded';

?>

</head>
<body class="no-js <?php echo esc_attr( $body_class ); ?>">
<script type="text/javascript">
	document.body.className = document.body.className.replace('no-js','js');
</script>

<?php do_action('live_admin_before_wpwrap'); ?>

<div class="wp-full-overlay<?php echo $overlay_class; ?>" id="wpwrap">

    <?php do_action('live_admin_before_admin-controls'); ?>

    <div id="live-admin-controls" class="wrap wp-full-overlay-sidebar">

		<div id="live-admin-header-actions" class="wp-full-overlay-header">

            <?php do_action('live_admin_buttons'); ?>

		</div>

		<div class="wp-full-overlay-sidebar-content" tabindex="-1">

            <?php do_action ('live_admin_info'); ?>

			<div id="live-admin-theme-controls">

                <?php do_action ('live_admin_controls'); ?>

            </div>
		</div>

		<div id="live-admin-footer-actions" class="wp-full-overlay-footer">
			<a href="#" class="collapse-sidebar button-secondary" title="<?php esc_attr_e('Collapse Sidebar'); ?>">
				<span class="collapse-sidebar-arrow"></span>
				<span class="collapse-sidebar-label"><?php _e('Collapse'); ?></span>

                <?php do_action ( 'live_admin_after_collapse_sidebar' ); ?>

			</a>

            <?php do_action ( 'live_admin_footer_actions' ); ?>

		</div>

	</div>

    <?php do_action ( 'live_admin_after_admin-controls' ); ?>
    
	<div id="live-admin-preview" class="wp-full-overlay-main">

        <?php do_action ( 'before_live_admin_preview' ); ?>

        <iframe width="100%" height="100%" frameborder="0" scrolling="auto" src="" name="live-admin-iframe" id="live-admin-iframe"></iframe>

        <?php do_action ( 'after_live_admin_preview' ); ?>

    </div>

    <?php
    do_action( "admin_print_footer_scripts-$handle" );
    do_action( 'live_admin_print_footer_scripts' );
    ?>

    </body>
</html>