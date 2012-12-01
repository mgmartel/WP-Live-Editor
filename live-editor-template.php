<?php
// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

require_once ( LIVE_EDITOR_DIR . 'lib/live-admin/live-admin.php' );

if ( ! class_exists ( 'WP_LiveEditor_Template' ) ) :
    /**
     * @todo remember collapsed state
     */
    class WP_LiveEditor_Template extends WP_LiveAdmin
    {

        public function __construct() {
            global $post_id;

            $this->menu = true;
            $this->screen_options = true;

            $post_url = get_permalink ( $post_id );
            $this->iframe_url = add_query_arg( "live_editor_preview", "1", $post_url );

            add_action ( 'live_admin_buttons', array ( &$this, 'publish_button' ) );
            add_action ( 'live_admin_buttons', array ( &$this, 'save_button' ) );
            $this->add_button ( $this->cancel_button(), 20 );

            add_action ( 'live_admin_before_admin-controls', array ( &$this, 'before_admin_controls' ) );
            add_action ( 'live_admin_after_admin-controls', array ( &$this, 'after_admin_controls' ) );

            $this->enqueue_styles_and_scripts();

        }

            public function wp_livedashboard_template() {
                $this->_construct();
            }

        protected function cancel_button() {
            global $return;

            return '
            <a class="back button" href="' . esc_url( $return ? $return : admin_url( 'edit.php' ) ) . '">
                ' . __( 'Cancel' ) . '
            </a>';
        }

        public function publish_button( $post ) {

            $post_type = $post->post_type;
            $post_type_object = get_post_type_object($post_type);
            $can_publish = current_user_can($post_type_object->cap->publish_posts);

            if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
                if ( $can_publish ) :
                    if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>" />
                    <?php submit_button( __( 'Schedule' ), 'button-primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
            <?php	else : ?>
                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
                    <?php submit_button( __( 'Publish' ), 'button-primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
            <?php	endif;
                else : ?>
                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
                    <?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
            <?php
                endif;
            } else { ?>
                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
                    <input name="save" type="submit" class="button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update') ?>" />
            <?php
            }

        }

        public function save_button ( $post ) {
            if ( 'publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status ) { ?>
            <input <?php if ( 'private' == $post->post_status ) { ?>style="display:none"<?php } ?> type="submit" name="save" id="save-post-live-editor" value="<?php esc_attr_e('Save Draft'); ?>" class="button" />
            <?php } elseif ( 'pending' == $post->post_status && $can_publish ) { ?>
            <input type="submit" name="save" id="save-post-live-editor" value="<?php esc_attr_e('Save as Pending'); ?>" class="button" />
            <?php }
        }



        protected function enqueue_styles_and_scripts() {
            wp_enqueue_style( 'live-dashboard', LIVE_DASHBOARD_INC_URL .'css/live-dashboard.css', array ("customize-controls"), "0.1" );
            wp_enqueue_script( 'live-dashboard', LIVE_DASHBOARD_INC_URL .'js/live-dashboard.js', array ('jquery') );
        }


        public function do_start() {
            // Go ahead and load all admin globals
//            global $title, $hook_suffix, $current_screen, $wp_locale, $pagenow, $wp_version,
//                $current_site, $update_title, $total_update_count, $parent_file, $live_editor, $post_id, $post;
            global $title, $parent_file, $post_id, $post;

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

        }

        public function do_footer_actions() {
            global $live_editor;
            ?>
            <a class="button" href="<?php echo $live_editor->add_no_live_editor_query_arg(); ?>" style="
               position: absolute;
                bottom: 9px;
                right: 16px;
               ">
                <?php _e( 'Switch interface' ); ?>
            </a>
            <?php
        }

        public function before_admin_controls() {
            ?>
            <form name="post" action="post.php?live=1" method="post" id="post"<?php do_action('post_edit_form_tag'); ?>  class="wrap wp-full-overlay-sidebar">
            <?php wp_nonce_field($nonce_action); ?>
            <input type="hidden" id="is-live" name="live" value="true" />
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
            <?php
        }

        public function after_admin_controls() {
            ?>
            </form>
            <?
        }

        public function do_controls() {
            require ( LIVE_EDITOR_DIR . 'lib/meta-boxes.php' );
        }

        /**
         * Meta-Box template function
         *
         * @since 2.5.0
         *
         * @param string|object $screen Screen identifier
         * @param string $context box context
         * @param mixed $object gets passed to the box callback function as first parameter
         * @return int number of meta_boxes
         */
        public function do_meta_boxes( $screen, $context, $object ) {
            global $wp_meta_boxes;
            static $already_sorted = false;

            if ( empty( $screen ) )
                $screen = get_current_screen();
            elseif ( is_string( $screen ) )
                $screen = convert_to_screen( $screen );

            $page = $screen->id;

            $hidden = get_hidden_meta_boxes( $screen );

            printf('<ul id="%s-sortables" class="meta-box-sortables">', htmlspecialchars($context));

            $i = 0;
            do {
                // Grab the ones the user has manually sorted. Pull them out of their previous context/priority and into the one the user chose
                if ( !$already_sorted && $sorted = get_user_option( "meta-box-order_$page" ) ) {
                    foreach ( $sorted as $box_context => $ids ) {
                        foreach ( explode(',', $ids ) as $id ) {
                            if ( $id && 'dashboard_browser_nag' !== $id )
                                add_meta_box( $id, null, null, $screen, $box_context, 'sorted' );
                        }
                    }
                }
                $already_sorted = true;

                if ( !isset($wp_meta_boxes) || !isset($wp_meta_boxes[$page]) || !isset($wp_meta_boxes[$page][$context]) )
                    break;

                foreach ( array('high', 'sorted', 'core', 'default', 'low') as $priority ) {
                    if ( isset($wp_meta_boxes[$page][$context][$priority]) ) {
                        foreach ( (array) $wp_meta_boxes[$page][$context][$priority] as $box ) {
                            if ( false == $box || ! $box['title'] )
                                continue;
                            $i++;
                            $style = '';
                            $hidden_class = in_array($box['id'], $hidden) ? ' hide-if-js' : '';
                            $open_class = ( $box['id'] == 'submitdiv' ) ? ' open' : '';
                            //echo "<li class='control-section customize-section'>";
                            //echo '<li id="' . $box['id'] . '" class="control-section customize-section' . $open_class . postbox_classes($box['id'], $page) . $hidden_class . '" ' . '>' . "\n";
                            echo '<li id="' . $box['id'] . '" class="control-section customize-section' . $open_class . $hidden_class . '" ' . '>' . "\n";
                            //if ( 'dashboard_browser_nag' != $box['id'] )
                            //	echo '<div class="handlediv" title="' . esc_attr__('Click to toggle') . '"><br /></div>';
                            echo "<h3 class='customize-section-title'><span>{$box['title']}</span></h3>\n";
                            echo "<ul class='customize-section-content'>";
                                echo "<li>";
                                    echo '<div class="inside">' . "\n";
                                    call_user_func($box['callback'], $object, $box);
                                    echo "</div>\n";
                                echo "</li>";
                            echo "</ul>";
                            //echo "</div>\n";
                            echo "</li>";
                        }
                    }
                }
            } while(0);

            echo "</ul>";

            return $i;

        }
    }
    live_admin_register_extension ( 'WP_LiveEditor_Template' );
endif;