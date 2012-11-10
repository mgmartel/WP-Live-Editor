<?php
if ( ! defined( 'ABSPATH' ) )
    exit(-1);

class BackToFrontEndEditor_Templates
{

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

        printf('<div id="%s-sortables" class="meta-box-sortables">', htmlspecialchars($context));

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

        echo "</div>";

        return $i;

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
        <input <?php if ( 'private' == $post->post_status ) { ?>style="display:none"<?php } ?> type="submit" name="save" id="save-post-bfee" value="<?php esc_attr_e('Save Draft'); ?>" class="button" />
        <?php } elseif ( 'pending' == $post->post_status && $can_publish ) { ?>
        <input type="submit" name="save" id="save-post-bfee" value="<?php esc_attr_e('Save as Pending'); ?>" class="button" />
        <?php }
    }
}