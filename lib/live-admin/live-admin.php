<?php
// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

/**
 * Version number
 *
 * @since 0.1
 */
define ( 'LIVE_ADMIN_VERSION', '0.1' );

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define ( 'LIVE_ADMIN_DIR', plugin_dir_path ( __FILE__ ) );
define ( 'LIVE_ADMIN_URL', plugin_dir_url ( __FILE__ ) );
define ( 'LIVE_ADMIN_INC_URL', LIVE_ADMIN_URL . '_inc/' );

class WP_LiveAdmin
{
        var $info_notice    = '',
            $info_content   = '',
            /**
             * First url for the iframe
             */
            $iframe_url     = '',

            $buttons        = array(),

            /**
             * Enable admin menu
             */
            $menu           = false,

            /**
             * Enable screen options menu
             */
            $screen_options = false,

            /**
             * Start with sidebar collapsed
             */
            $collapsed      = false,

            /**
             * Disable bundled iFrame loading code
             */
            $override_iframe_loader
                            = false,

            /**
             * Disables following of links within the iFrame
             */
            $disable_nav    = false,

            /**
             * Custom JS vars
             */
            $custom_js_vars = array(),

            /**
             * Minimum user capability
             */
            $capability = 'edit_posts',

            $postbox_class  = 'postbox-live';

        public function _register() {
            $this->check_permissions();

            $this->enqueue_live_admin_styles_and_scripts();
            $this->enqueue_styles_and_scripts();
            $this->actions_and_filters();

            if ( empty ( $this->iframe_url ) )
                $this->iframe_url = get_bloginfo('url');

            if ( $this->menu )
                require_once ( LIVE_ADMIN_DIR . 'live-menu/live-menu.php' );

            if ( $this->screen_options ) {
                require_once ( LIVE_ADMIN_DIR . 'live-screen-options/live-screen-options.php' );
            }

            require_once ( LIVE_ADMIN_DIR . 'live-admin-template.php' );
            exit;
        }

        private function check_permissions() {
            if ( ! current_user_can( $this->capability ) )
                wp_die( __( 'Cheatin&#8217; uh?' ) );
        }

        private function enqueue_live_admin_styles_and_scripts() {
            wp_enqueue_script( 'live-admin', LIVE_ADMIN_INC_URL . 'js/live-admin.js', array ( 'jquery' ), 0.1 );
            wp_enqueue_style( 'live-admin', LIVE_ADMIN_INC_URL . 'css/live-admin.css', array ( 'customize-controls'), 0.1 );

            if ( ! empty ( $this->custom_js_vars ) )
                wp_localize_script ( 'live-admin', 'liveAdmin', $this->custom_js_vars );
        }

        protected function actions_and_filters() {
            add_action ( 'live_admin_start', array ( &$this, 'do_start' ) );

            add_action ( 'live_admin_buttons', array ( &$this, 'do_buttons' ), 15 );

            if ( ! empty ( $this->info_notice ) )
                add_action ( 'live_admin_info', array ( &$this, 'do_info' ) );

            if ( ! empty ( $this->pointers ) )
                add_action('live_admin_init', array (&$this, 'pointers' ) );

            add_action ( 'live_admin_controls', array ( &$this, 'do_controls' ) );

            add_action ( 'live_admin_footer_actions', array ( &$this, 'do_footer_actions' ) );

        }

        /**
         * Methods to be overwritten by child classes
         */
        public function do_start() {}

        public function do_controls() {}

        public function do_footer_actions() {}

        protected function enqueue_styles_and_scripts() {}

        public function do_buttons() {
            asort ( $this->buttons );
            foreach ( $this->buttons as $button ) {
                echo $button;
            }
            unset ( $this->buttons );
        }

        public function add_button( $button, $priority = 10 ) {
            while ( isset ( $this->buttons[$priority] ) ) {
                $priority++;
            }

            $this->buttons[$priority] = $button;
        }

        public function do_info() {
            ?>
            <div id="live-admin-info" class="customize-section open">
				<div class="customize-section-title" aria-label="<?php esc_attr_e( 'Live Admin', 'live-admin' ); ?>" tabindex="0">
					<span class="preview-notice">

                        <?php echo $this->info_notice; ?>

                    </span>
				</div>

                <?php if ( ! empty ( $this->info_content ) ) : ?>

                    <div class="customize-section-content">
                        <div class="theme-description">

                            <?php echo $this->info_content; ?>

                        </div>

                    </div>

                <?php endif; ?>

			</div>
            <?php
        }

        public function pointers() {
            require_once ( LIVE_ADMIN_DIR . 'lib/class.wp-help-pointers.php' );
            new WP_Help_Pointer($this->pointers);
        }



        /**
         * A couple re-usable template items
         */
        public function logout_button() {
            return '<a href="' . wp_logout_url() . '" class="button" id="log-out">' . __("Log out") . '</a>';
        }

        public function my_account_button() {
            $user_id      = get_current_user_id();
            $current_user = wp_get_current_user();
            $profile_url  = get_edit_profile_url( $user_id );

            if ( ! $user_id )
                return;

            $avatar = get_avatar( $user_id, 16 );
            $howdy  = sprintf( __('Howdy, %1$s'), $current_user->display_name );
            $class  = empty( $avatar ) ? '' : ' with-avatar';

            $link = sprintf ('<a href="%1$s" title="%2$s" class="howdy %3$s">%4$s%5$s</a>',
                        $profile_url,
                        __('My Account'),
                        $class,
                        $howdy,
                        $avatar
                    );

            return $link;

        }

}

function live_admin_register_extension( $live_admin_extension_class ) {
    global $live_admin;

	if ( !class_exists( $live_admin_extension_class ) )
		return false;

	$live_admin = new $live_admin_extension_class;
    $live_admin->_register();
}