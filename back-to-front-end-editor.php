<?php
/*
  Plugin Name: Back to Front End Editor
  Plugin URI: http://trenvo.com
  Description: None yet.
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
define ( 'BFEE_VERSION', '0.1' );

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define ( 'BFEE_DIR', plugin_dir_path ( __FILE__ ) );
define ( 'BFEE_URL', plugin_dir_url ( __FILE__ ) );
define ( 'BFEE_INC_URL', BFEE_URL . '_inc/' );

// Load iFrame class early enough
if ( isset ( $_GET['bfee_preview'] ) && $_GET['bfee_preview'] == true )
    require_once( BFEE_DIR . 'lib/class-editor-iframe.php' );

if ( !class_exists ( 'BackToFrontEndEditor' ) ) :

    /**
     * The masterclass
     *
     * @todo Open boxes based on cookie
     * @todo Handle autosaves / revisions (autosave triggers content-to-parent)
     */
    class BackToFrontEndEditor
    {

        public $metabox_transports = array();
        public $active = null;
        public $templates;

        /**
         * Creates an instance of the BackToFrontEndEditor class
         *
         * @return BackToFrontEndEditor object
         * @since 0.1
         * @static
        */
        public static function &init() {
            global $bfee;

            if ( !$bfee ) {
                load_plugin_textdomain ( 'bfee', false, BFEE_DIR . '/languages/' );
                $bfee = new BackToFrontEndEditor;
            }

            return $bfee;
        }

        /**
         * Constructor
         *
         * @since 0.1
         */
        public function __construct() {
            global $pagenow;

            if ( ! is_user_logged_in() ) return;

            require_once ( BFEE_DIR . 'lib/class-templates.php' );
            $this->templates = new BackToFrontEndEditor_Templates;

            require_once ( BFEE_DIR . 'lib/class-settings.php' );
            $this->settings = new BackToFrontEndEditor_UserSettings;

            require_once ( BFEE_DIR . 'lib/class.wp-help-pointers.php' );

            $this->actions_and_filters();

            // Make sure we redirect to the right editing interface
            if ( isset ( $_POST['action'] ) && $_POST['action'] == "editpost" )
                $this->bfee_redirects();

            add_action ( 'admin_init', array ( &$this, 'setup' ), 11 );

        }

            /**
             * PHP4
             *
             * @since 0.1
             */
            public function backtofrontendeditor() {
                $this->__construct ();
            }

        public function is_active() {
            if ( ! empty ( $this->active ) )
                return $this->active;

            $is_default = $this->settings->is_default();
            $is_deactivated = ( isset($_REQUEST['no_bfee']) && $_REQUEST['no_bfee'] == true  );
            $is_activated = ( isset($_REQUEST['bfee']) && $_REQUEST['bfee'] == true  );

            if ( $is_default )
                $this->active = ( ! $is_deactivated );
            elseif ( ! $is_default )
                $this->active = ( $is_activated );

            return apply_filters ( 'bfee_is_active', &$this->active );
        }

        public function setup() {
            global $pagenow;

            $active = $this->is_active();

            if ( ! $active ) {
                if ( $pagenow == 'post.php' ) {
                    //add_action('submitpage_box', array ( &$this, 'switch_interface_button' ), 1 );
                    //add_action('submitpost_box', array ( &$this, 'switch_interface_button' ), 1 );
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
                        'id' => 'bfee_intor',
                        'screen' => 'post',
                        'target' => '#content-html',
                        'title' => __ ( 'Live Editing', 'bfee' ),
                        'content' => __( 'You have installed Back to Front End Editor, which enables Live editing of your WordPress pages and posts. Click the \'Live\' tab here, or go to your user profile to set the Live Editor as your default editor.', 'bfee' ),
                        'position' => array(
                                'edge' => 'top',
                                'align' => 'center'
                            )
                        )
                    );
            $pointers = apply_filters( 'bfee_pointers', $pointers );
            new WP_Help_Pointer($pointers);
        }

        /**
         * @todo Keep interface choice in a temp cookie
         *
         */
        public function switch_interface_button() {
            ?>
            <a class="button" href="<?php echo $this->add_bfee_query_arg(); ?>" style='margin-bottom: 12px'>
                <?php _e( 'Switch interface' ); ?>
            </a>
            <?php
        }

        public function add_live_mce_tab() {
            echo "
                <script>
                    jQuery('.wp-switch-editor#content-tmce').after(\"<a id='content-live' class='hide-if-no-js wp-switch-editor switch-live' href='" . $this->add_bfee_query_arg() . "' style='text-decoration:none'>Live</a>\");
                </script>
                ";

        }

        protected function actions_and_filters() {
            // We'll load our own scripts
            add_action ( "bfee_print_scripts", array ( &$this, "print_header_scripts" ) );
            add_action ( "bfee_print_footer_scripts", array ( &$this, "print_scripts" ) );

        }

        protected function bfee_redirects() {
            if ( ! isset ( $_POST['is_bfee'] ) || ! $_POST['is_bfee'] == 'true' ) {
                add_action ( 'redirect_post_location', array ( &$this, 'add_no_bfee_query_arg' ) );
            }
        }

        protected function no_bfee_redirects() {
            add_action ( 'redirect_post_location', array ( &$this, 'add_no_bfee_query_arg' ) );
        }

        public function add_bfee_query_arg( $url = '' ) {
            if ( empty ( $url ) ) {
                $url = $_SERVER["REQUEST_URI"];
            }

            return add_query_arg( 'bfee' , 1, remove_query_arg ('no_bfee', $url ) );
        }

        public function add_no_bfee_query_arg( $url = '' ) {
            if ( empty ( $url ) ) {
                $url = $_SERVER["REQUEST_URI"];
            }
            return add_query_arg( 'no_bfee' , 1, remove_query_arg ('bfee', $url ) );
        }

        public function live() {

            $this->enqueue_styles_and_scripts();
            $this->set_vars();

            require_once ( BFEE_DIR . 'lib/meta-box-transports.php' );
            do_action ( 'bfee_add_metabox_transports', &$this );

            $this->display();
            exit;
        }

        /**
         * @todo Post-type handling
         */
        public function new_post() {
            $post_type = 'post';
            $post = get_default_post_to_edit( $post_type, true );
            $postarr = get_post ( $post->ID, 'ARRAY_A' );
            $postarr['post_status'] = 'draft';
            wp_update_post( $postarr );

            $post_link = get_edit_post_link( $post->ID, '' );

            // If BFEE is not the default editor, make sure the next screen shows BFEE
            if ( ! $this->settings->is_default() )
                $post_link = $this->add_bfee_query_arg( $post_link );

            wp_redirect( $post_link );
        }

        /**
         * @todo is this the way to set global $post?
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
         * @todo Raptor doesn't include title editing.. shall we make our own?
         */
        protected function display() {
            $post_id = $post_ID = $this->post_id;
            require( BFEE_DIR . '/template.php' );
        }

        /**
         * Enqueue scripts and styles
         *
         * @since 0.1
         * @todo Choose Featured Image is no working
         */
        protected function enqueue_styles_and_scripts() {
            wp_enqueue_style("bfee", BFEE_INC_URL . 'css/bfee.css', array ("customize-controls"), "0.1" );
            wp_enqueue_script("bfee", BFEE_INC_URL . 'js/bfee.js', array ("jquery", "utils", "wp-lists", "suggest", "media-upload" ), "0.1" );
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
            $iframe_url = add_query_arg( "bfee_preview", "1", $post_url );

            $args = apply_filters ( 'bfee_js_vars', array (
                "blog_url"                 => get_bloginfo('url'),
                "post_url"                 => $iframe_url,
                "metabox_transports"       => $this->metabox_transports
            ) );

            wp_localize_script( "bfee", "bfee", $args);

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

            wp_localize_script( "bfee", "postL10n", $post_l10n);

            wp_print_scripts( array ('bfee') );
        }

        public function add_metabox_transport( $metabox, $transport ) {
            $this->metabox_transports[$metabox] = $transport;
        }

    }
    add_action ('init', array ( "BackToFrontEndEditor", "init" ) );
endif;