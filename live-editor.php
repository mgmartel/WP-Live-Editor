<?php
/*
  Plugin Name: Live Editor
  Plugin URI: http://trenvo.com
  Description: Front-end editing with back-end flexibility
  Version: 0.1
  Author: Mike Martel
  Author URI: http://trenvo.com
 */

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

/**
 * Version number
 *
 * @since 0.1
 */
define ( 'LIVE_EDITOR_VERSION', '0.1' );

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define ( 'LIVE_EDITOR_DIR', plugin_dir_path ( __FILE__ ) );
define ( 'LIVE_EDITOR_URL', plugin_dir_url ( __FILE__ ) );
define ( 'LIVE_EDITOR_INC_URL', LIVE_EDITOR_URL . '_inc/' );

// Load iFrame class early enough
if ( isset ( $_GET['live_editor_preview'] ) && $_GET['live_editor_preview'] == true )
    require_once( LIVE_EDITOR_DIR . 'lib/class-editor-iframe.php' );

if ( !class_exists ( 'WP_LiveEditor' ) ) :

    /**
     * The masterclass
     *
     * @todo Open boxes based on cookie
     * @todo Handle autosaves / revisions (autosave triggers content-to-parent)
     */
    class WP_LiveEditor
    {

        public $metabox_transports = array();
        public $active = null;
        public $templates;

        /**
         * Creates an instance of the WP_LiveEditor class
         *
         * @return WP_LiveEditor object
         * @since 0.1
         * @static
        */
        public static function &init() {
            global $live_editor;

            if ( !$live_editor ) {
                load_plugin_textdomain ( 'live-editor', false, LIVE_EDITOR_DIR . '/languages/' );
                $live_editor = new WP_LiveEditor;
            }

            return $live_editor;
        }

        /**
         * Constructor
         *
         * @since 0.1
         */
        public function __construct() {
            global $pagenow;

            if ( ! is_user_logged_in() ) return;

            require_once ( LIVE_EDITOR_DIR . 'lib/class-templates.php' );
            $this->templates = new WP_LiveEditor_Templates;

            require_once ( LIVE_EDITOR_DIR . 'lib/class-settings.php' );
            $this->settings = new WP_LiveEditor_UserSettings;

            require_once ( LIVE_EDITOR_DIR . 'lib/class.wp-help-pointers.php' );

            $this->actions_and_filters();

            // Make sure we redirect to the right editing interface
            if ( isset ( $_POST['action'] ) && $_POST['action'] == "editpost" )
                $this->live_editor_redirects();

            add_action ( 'admin_init', array ( &$this, 'setup' ), 11 );

        }

            /**
             * PHP4
             *
             * @since 0.1
             */
            public function wpliveeditor() {
                $this->__construct ();
            }

        public function is_active() {
            if ( ! empty ( $this->active ) )
                return $this->active;

            $is_default = $this->settings->is_default();
            $is_deactivated = ( isset($_REQUEST['live_off']) && $_REQUEST['live_off'] == true  );
            $is_activated = ( isset($_REQUEST['live']) && $_REQUEST['live'] == true  );

            if ( $is_default )
                $this->active = ( ! $is_deactivated );
            elseif ( ! $is_default )
                $this->active = ( $is_activated );

            return apply_filters ( 'live_editor_is_active', &$this->active );
        }

        public function setup() {
            global $pagenow;

            $active = $this->is_active();

            if ( ! $active ) {
                if ( $pagenow == 'post.php' ) {
                    if ( get_user_meta ( get_current_user_id(), 'rich_editing', true ) == "false" ) {
                        add_action('submitpage_box', array ( &$this, 'switch_interface_button' ), 1 );
                        add_action('submitpost_box', array ( &$this, 'switch_interface_button' ), 1 );
                    }

                    add_action('admin_print_footer_scripts', array ( &$this, 'add_live_mce_tab' ) );
                    if ( ! $this->settings->is_default() )
                        add_action ( 'admin_enqueue_scripts', array ( &$this, 'set_pointers' ) );
                }
            } else {
                if ( $pagenow == 'post.php' )
                    add_action ('admin_action_edit', array ( &$this, 'live' ) );
                elseif ( $pagenow == 'post-new.php' )
                    $this->new_post();
            }
        }

        public function set_pointers() {
            $pointers = array(
                    array(
                        'id' => 'live_editor_intro',
                        'screen' => 'post',
                        'target' => '#content-html',
                        'title' => __ ( 'Live Editing', 'live-editor' ),
                        'content' => __( 'You have installed Live Editor. Click the \'Live\' tab here, or go to your user profile to set the Live Editor as your default editor.', 'live-editor' ),
                        'position' => array(
                                'edge' => 'top',
                                'align' => 'center'
                            )
                        )
                    );
            $pointers = apply_filters( 'live_editor_pointers', $pointers );
            new WP_Help_Pointer($pointers);
        }

        /**
         * @todo Keep interface choice in a temp cookie
         *
         */
        public function switch_interface_button() {
            ?>
            <a class="button" href="<?php echo $this->add_live_editor_query_arg(); ?>" style='margin-bottom: 12px'>
                <?php _e( 'Use Live Editor', 'bfee' ); ?>
            </a>
            <?php
        }

        public function add_live_mce_tab() {
            echo "
                <script>
                    jQuery('.wp-switch-editor#content-tmce').after(\"<a id='content-live' class='hide-if-no-js wp-switch-editor switch-live' href='" . $this->add_live_editor_query_arg() . "' style='text-decoration:none'>Live</a>\");
                </script>
                ";

        }

        protected function actions_and_filters() {
            // We'll load our own scripts
            add_action ( "live_editor_print_scripts", array ( &$this, "print_header_scripts" ) );
            add_action ( "live_editor_print_footer_scripts", array ( &$this, "print_scripts" ) );

        }

        protected function live_editor_redirects() {
            if ( ! isset ( $_POST['live'] ) || ! $_POST['live'] == 'true' ) {
                add_action ( 'redirect_post_location', array ( &$this, 'add_no_live_editor_query_arg' ) );
            }

            if ( $this->settings->is_default() && ( ! isset ( $_POST['live'] ) || ! $_POST['live'] == 'true' ) )
                add_action ( 'redirect_post_location', array ( &$this, 'add_no_live_editor_query_arg' ) );
            elseif ( ! $this->settings->is_default() && isset ( $_POST['live'] ) && $_POST['live'] == 'true' )
                add_action ( 'redirect_post_location', array ( &$this, 'add_live_editor_query_arg' ) );
        }

        protected function no_live_editor_redirects() {
            add_action ( 'redirect_post_location', array ( &$this, 'add_no_live_editor_query_arg' ) );
        }

        public function add_live_editor_query_arg( $url = '' ) {
            if ( empty ( $url ) ) {
                $url = $_SERVER["REQUEST_URI"];
            }

            return add_query_arg( 'live' , 1, remove_query_arg ('live_off', $url ) );
        }

        public function add_no_live_editor_query_arg( $url = '' ) {
            if ( empty ( $url ) ) {
                $url = $_SERVER["REQUEST_URI"];
            }
            return add_query_arg( 'live_off' , 1, remove_query_arg ('live', $url ) );
        }

        public function live() {

            $this->enqueue_styles_and_scripts();
            $this->set_vars();

            require_once ( LIVE_EDITOR_DIR . 'lib/meta-box-transports.php' );
            do_action ( 'live_editor_add_metabox_transports', &$this );

            $this->display();
            exit;
        }

        public function new_post() {
            $post_type = ( isset ( $_GET['post_type'] ) && ! empty ( $_GET['post_type'] ) ) ? $_GET['post_type'] : 'post';
            $post_type_object = get_post_type_object( $post_type );

            $post = get_default_post_to_edit( $post_type, true );
            $postarr = get_post ( $post->ID, 'ARRAY_A' );
            $postarr['post_title'] = $post_type_object->labels->new_item;
            $postarr['post_status'] = 'draft';
            wp_update_post( $postarr );

            $post_link = get_edit_post_link( $post->ID, '' );

            // If Live Editor is not the default editor, make sure the next screen shows Live Editor
            if ( ! $this->settings->is_default() )
                $post_link = $this->add_live_editor_query_arg( $post_link );

            $post_link = apply_filters ( 'live_editor_new_post_redirect', $post_link, $post->ID );

            wp_redirect( $post_link );
        }

        /**
         * @global type $post
         * @return type
         */
        protected function set_vars() {
            global $post, $post_ID;

            if ( isset( $_GET['post'] ) ) {
                $this->post_id = (int) $_GET['post'];
                $post = get_post ( $this->post_id );
            } elseif ( isset( $_POST['post_ID'] ) ) {
                $this->post_id = (int) $_POST['post_ID'];
            } else {
                $this->post_id = 0;
            }

            $post_ID = $this->post_id;
            $post = get_post ( $this->post_id );
            return $this->post_id;
        }

        /**
         * Load the template
         *
         * @since 0.1
         */
        protected function display() {
            $post_id = $post_ID = $this->post_id;
            require( LIVE_EDITOR_DIR . '/template.php' );
        }

        /**
         * Enqueue scripts and styles
         *
         * @since 0.1
         * @todo Choose Featured Image is no working @ WP 3.5beta
         */
        protected function enqueue_styles_and_scripts() {
            wp_enqueue_style("live-editor", LIVE_EDITOR_INC_URL . 'css/live-editor.css', array ("customize-controls"), "0.1" );
            wp_enqueue_script("live-editor", LIVE_EDITOR_INC_URL . 'js/live-editor.js', array ("jquery", "utils", "wp-lists", "suggest", "media-upload" ), "0.1" );
        }

        public function print_header_scripts() {
            wp_print_scripts( array ('jquery') );
        }

        /**
         * Set the js vars and print the scripts
         *
         * @since 0.1
         */
        public function print_scripts() {
            $post_url = get_permalink ( $this->post_id );
            $iframe_url = add_query_arg( "live_editor_preview", "1", $post_url );

            $args = apply_filters ( 'live_editor_js_vars', array (
                "blog_url"                 => get_bloginfo('url'),
                "post_url"                 => $iframe_url,
                "metabox_transports"       => $this->metabox_transports
            ) );

            wp_localize_script( "live-editor", "liveEditor", $args);

            $post_l10n = array(
                'publishOn' => __('Publish on:'),
                'publishOnFuture' =>  __('Schedule for:'),
                'publishOnPast' => __('Published on:'),
                'showcomm' => __('Show more comments'),
                'endcomm' => __('No more comments found.'),
                'publish' => __('Publish'),
                'schedule' => __('Schedule'),
                'update' => __('Update'),
                'savePending' => __('Save as Pending'),
                'saveDraft' => __('Save Draft'),
                'private' => __('Private'),
                'public' => __('Public'),
                'publicSticky' => __('Public, Sticky'),
                'password' => __('Password Protected'),
                'privatelyPublished' => __('Privately Published'),
                'published' => __('Published'),
                'comma' => _x( ',', 'tag delimiter' ),
            );

            wp_localize_script( "live-editor", "postL10n", $post_l10n);

            wp_print_scripts( array ('live-editor') );
        }

        public function add_metabox_transport( $metabox, $transport ) {
            $this->metabox_transports[$metabox] = $transport;
        }

    }
    add_action ('init', array ( "WP_LiveEditor", "init" ) );
endif;