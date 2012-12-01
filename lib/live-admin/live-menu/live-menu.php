<?php
/**
 * WP_LiveAdmin extension: Live Menu
 */

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

/**
 * PATHs and URLs
 */
define ( 'LIVE_MENU_DIR', plugin_dir_path ( __FILE__ ) );
define ( 'LIVE_MENU_URL', plugin_dir_url ( __FILE__ ) );
define ( 'LIVE_MENU_INC_URL', LIVE_MENU_URL . '_inc/' );

/**
 * Live Admin menu is pluggable
 */
if ( !class_exists ( 'WP_LiveAdmin_LiveMenu' ) ) :

    class WP_LiveAdmin_LiveMenu
    {

        /**
         * Creates an instance of the LiveAdmin_Menu class
         *
         * @return LiveMenu object
         * @since 0.1
         * @static
        */
        public static function &init() {
            static $instance = false;

            if ( !$instance ) {
                $instance = new WP_LiveAdmin_LiveMenu;
            }

            return $instance;
        }

        /**
         * Constructor
         *
         * @since 0.1
         */
        public function __construct() {
            global $live_admin;

            wp_enqueue_script('live-menu', LIVE_MENU_INC_URL . 'js/live-menu.js', array ('jquery', 'common'), 0.1 );
            wp_enqueue_style('live-menu', LIVE_MENU_INC_URL . 'css/live-menu.css', array('customize-controls'), 0.1 );

            add_action ( 'live_admin_before_wpwrap', array ( &$this, 'load_admin_menu_header' ) );

            $live_admin->add_button( $this->menu_button(), 1 );
        }

            /**
             * PHP4
             *
             * @since 0.1
             */
            public function liveadmin_menu() {
                $this->__construct ();
            }

        public function load_admin_menu_header() {
            require_once(ABSPATH . 'wp-admin/menu-header.php');
        }

        private function menu_button() {
            //return '<a href="" class="button toggle-menu">' . __("Menu") . '</a>';
            return '<a href="" class="button toggle-menu">' . __("&#9776;") . '</a>';
        }


    }

    add_action ( 'live_admin_start', array ( 'WP_LiveAdmin_LiveMenu', 'init' ) );
endif;