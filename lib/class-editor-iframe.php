<?php
if ( ! defined('ABSPATH' ) )
    exit(-1);

require_once ( LIVE_EDITOR_DIR . 'lib/meta-box-transports.php' );

if ( !class_exists ( 'WP_LiveEditor_iFrame' ) ) :

    class WP_LiveEditor_iFrame
    {

        public $transport_params = array ();

        /**
         * Creates an instance of the WP_LiveEditor_iFrame class
         *
         * @return WP_LiveEditor_iFrame object
         * @since 0.1
         * @static
        */
        public static function &init() {
            global $live_editor;

            if ( !$live_editor ) {
                $live_editor = new WP_LiveEditor_iFrame;
            }

            return $live_editor;
        }

        public function __construct() {
            if ( ! $this->raptor_is_active () ) {
                require_once ( LIVE_EDITOR_DIR . 'lib/wp-raptor/index.php');
            }

            if ( isset ( $_GET['live-editor-transport'] ) && ! empty ( $_GET['live-editor-transport'] ) )
                $this->transport_params = json_decode ( $_GET['live-editor-transport'], true );

            foreach ( $this->transport_params as $key => $param ) {
                do_action ('live_editor_transports-' . $key, $param );
            }

            add_action('setup_theme', array ( &$this, 'hide_admin_bar' ) );
            add_action('plugins_loaded', array ( &$this, 'setup_editor' ) );
            add_action('wp_print_styles', array ( &$this, 'try_hide_edit_links' ) );
            add_action('wp_print_footer_scripts', array ( &$this, 'live_editor_preview_iframe_styles' ) );

            add_filter ('the_title', array ( &$this, 'enclose_titles' ), 10, 2 );

            $this->do_iframe_actions();
        }

            function wp_liveeditor_iframe() {
                $this->_construct();
            }

        protected function do_iframe_actions() {
            add_action ( 'init', array ( &$this, 'iframe_init' ) );
        }

        public function iframe_init() {
            do_action('live_editor_iframe_init');
        }

        public function enclose_titles($title, $id) {
            global $post;

            if ( ! in_the_loop() || $id != $post->ID ) return $title;

            if(current_user_can('edit_post', $post->ID)) {
                $title = "<div class='live-editor-editable-title' contenteditable=true data-post_id='{$post->ID}'>{$title}</div>";
            }

            return $title;
        }

        protected function raptor_is_active() {
            $plugin = 'wp-raptor/index.php';

            if ( in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) )
                return true;

            if ( !is_multisite() )
                return false;

            $plugins = get_site_option( 'active_sitewide_plugins');
            if ( isset($plugins[$plugin]) )
                return true;

            return false;
        }

        /**
         * @todo Add FEE?
         */
        public function setup_editor() {
            wp_enqueue_script('live-editor-in-place-init', LIVE_EDITOR_INC_URL . 'js/in-place.js', array ( 'jquery' ), '1.0.0', true);

            if ( defined ( 'RAPTOR_ROOT' ) ) {
                add_filter('_pre_option_raptor-settings', array ( &$this, 'bundled_raptor_settings' ) );
                add_action ( 'wp_print_scripts', array ( &$this, 'raptor_in_place_scripts' ), 11 );

                add_filter('front_end_editor_allow_post', '__return_false', 10, 0);
            }
        }

        /**
         * @todo get allowOversizeImages from Raptor
         */
        public function raptor_in_place_scripts() {
            wp_dequeue_script( 'raptor-in-place-init' );
            wp_enqueue_script('live-editor-raptor-in-place-init', LIVE_EDITOR_INC_URL . 'js/raptor.js', array ( 'raptor', 'jquery' ), '1.0.0', true);
            wp_localize_script('live-editor-raptor-in-place-init', 'raptorInPlace',
                    array(
                        'url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce(RaptorSave::SAVE_POSTS_NONCE),
                        'action' => RaptorSave::SAVE_POSTS,
                        'allowOversizeImages' => true
                        //'allowOversizeImages' => $raptor->options->resizeImagesAutomatically()
                    ));
        }

        public function hide_admin_bar() {
            show_admin_bar( false );
        }


        public function try_hide_edit_links() {
            echo "<style>.edit-link, .post-edit-link, .comment-edit-link {display:none;}</style>";
        }

        public function live_editor_preview_iframe_styles() {
            echo "<style>
                .raptor-editable-post,
                /*.live-editor-editable-title */
                    {outline:1px dashed rgba(0, 0, 0, 0.5);}
                .raptor-editable-post:focus,
                /*.live-editor-editable-title:focus */
                    {outline:none;}
                .ui-editor-dock-docked .ui-editor-toolbar-wrapper { min-height: 44px; background-color: #f5f5f5 !important;
            </style>";
        }

        /**
         *
         * @param array $options
         * @return string
         * @todo properly test this
         */
        public function bundled_raptor_settings( $options) {
            $options = array(
                'index-allow-in-place-editing' => '1',
                'index-raptorize-quickpress' => '0',
                'index-raptorize-admin-editing' => '1',
                'resize-images-automatically' => '1',
                'allow-additional-raptor-classes' => '0',
                'additional-raptor-classes' => ''
            );
            return $options;
        }


    }
    WP_LiveEditor_iFrame::init();
endif;