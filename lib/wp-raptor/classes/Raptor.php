<?php
/**
 * Prepare Raptor CSS & JavaScript.
 */
class Raptor {

    /**
     * @var boolean True if Raptor editor JavaScript has been queued.
     */
    public $raptorQueued = false;
    /**
     * @var boolean True if admin CSS has been queued.
     */
    public $adminCss = false;
    public $options = null;

    public function __construct($options) {
        $this->options = $options;
    }

    public function addRaptor() {
        if (!$this->raptorQueued) {

            // JavaScript
            wp_enqueue_script('jquery');
            wp_enqueue_script('raptor', plugins_url('javascript/raptor.0deps.nc.min.js', dirname(__FILE__)), 'jquery-ui', '1.0.19', true);

            // Extra plugins
            wp_register_style('raptor-wordpress-media-library-css', plugins_url('javascript/plugins/wordpress-media-library/wordpress-media-library.css', dirname(__FILE__)), false, '1.0.0');
            wp_enqueue_script('raptor-wordpress-media-library', plugins_url('javascript/plugins/wordpress-media-library/wordpress-media-library.js', dirname(__FILE__)), 'raptor', '1.0.0', true);
            wp_localize_script('raptor-wordpress-media-library', 'raptorMediaLibrary',
                array(
                    'url' => admin_url('media-upload.php'),
                ));


            // Theme
            wp_enqueue_style('raptor-wordpress-media-library-css');
            wp_register_style('jquery-raptor-theme', plugins_url('css/raptor-theme.css', dirname(__FILE__)), false, '1.0.19');
            wp_enqueue_style('jquery-raptor-theme');
        }
        $this->raptorQueued = true;
    }

    /**
     * Prepare & enqueue Raptor admin CSS.
     */
    private function addAdminCss() {
        if (!$this->adminCss) {
            wp_register_style('raptor-admin-css', plugins_url('css/raptor-admin.css', dirname(__FILE__)), false, '1.0.0');
            wp_enqueue_style('raptor-admin-css');
            $this->adminCss = true;
        }
    }

    public function addAdminPostJs() {
        $this->addRaptor();
        $this->addAdminCss();
        wp_enqueue_script('raptor-admin-init', plugins_url('javascript/raptor-admin-init.js', dirname(__FILE__)), 'raptor', '1.0.0', true);
        wp_localize_script('raptor-admin-init', 'raptorAdmin',
                array(
                    'allowOversizeImages' => $this->options->resizeImagesAutomatically(),
                    'selector' => '#post-body #content'
                ));
    }

    /**
     * Add JavaScript required for additional editor initialisation.
     */
    public function addAdminAdditionalEditorSelectorsJs() {
        $this->addRaptor();
        $this->addAdminCss();
        wp_enqueue_script('raptor-admin-additional-editor-selector-init', plugins_url('javascript/raptor-admin-additional-editor-selector-init.js', dirname(__FILE__)), 'raptor', '1.0.0', true);
        wp_localize_script('raptor-admin-additional-editor-selector-init', 'raptorAdminAdditionalEditorSelector',
                array(
                    'allowOversizeImages' => $this->options->resizeImagesAutomatically(),
                    'selector' => $this->options->additionalEditorSelectors()
                ));
    }

    /**
     * Add JavaScript required for quickpress initialisation.
     */
    public function addAdminQuickPressJs() {
        $this->addRaptor();
        wp_enqueue_script('raptor-admin-quickpress-init', plugins_url('javascript/raptor-quickpress-init.js', dirname(__FILE__)), 'raptor', '1.0.0', true);
        wp_enqueue_script('raptor-admin-quickpress-init', plugins_url('javascript/raptor-in-place-init.js', dirname(__FILE__)), 'raptor', '1.0.0', true);
        wp_localize_script('raptor-admin-quickpress-init', 'raptorQuickpress',
                array(
                    'allowOversizeImages' => $this->options->resizeImagesAutomatically()
                ));
        wp_register_style('raptor-quickpress-css', plugins_url('css/raptor-quickpress.css', dirname(__FILE__)), false, '1.0.0');
        wp_enqueue_style('raptor-quickpress-css');
    }

    /**
     * Add JavaScript required for in place post initialisation.
     */
    public function addInPlacePostJs() {
        $this->addRaptor();
        wp_enqueue_script('raptor-in-place-init', plugins_url('javascript/raptor-in-place-init.js', dirname(__FILE__)), 'raptor', '1.0.0', true);
        wp_localize_script('raptor-in-place-init', 'raptorInPlace',
                array(
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce(RaptorSave::SAVE_POSTS_NONCE),
                    'action' => RaptorSave::SAVE_POSTS,
                    'allowOversizeImages' => $this->options->resizeImagesAutomatically()
                ));

        wp_register_style('raptor-in-place-css', plugins_url('css/raptor-in-place.css', dirname(__FILE__)), false, '1.0.0');
        wp_enqueue_style('raptor-in-place-css');
    }

    /**
     * Add JavaScript required for comment initialisation.
     */
    public function addCommentsJs() {
        $this->addRaptor();
        wp_enqueue_script('raptor-comments', plugins_url('javascript/raptor-comments-init.js', dirname(__FILE__)), false, '1.0.0', true);
        wp_localize_script('raptor-comments', 'raptorCommentsSave',
                array(
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('myajax_nonce_val'),
                    'action' => RaptorSave::SAVE_COMMENTS,
                ));
    }

    /**
     * Remove tiny mce
     */
    public function removeNativeEditors() {
        wp_deregister_script('tiny_mce');
        // Include CSS to display:none the quicktag toolbar
        wp_register_style('raptor-remove-native-editors', plugins_url('css/raptor-remove-native-editors.css', dirname(__FILE__)), false, '1.0.15');
        wp_enqueue_style('raptor-remove-native-editors');
    }

    /**
     * Enclose posts that the author is allowed to edit with a div targeted by the editor
     * @param  {String} $value The post content to be wrapped
     * @return {String} The wrapped content, or the unmodified content if the author isn't allowed to edit this post
     */
    public function encloseEditablePosts($content) {
        global $post;

        if(current_user_can('edit_post', $post->ID)) {
            $content = "<div class='raptor-editable-post' data-post_id='{$post->ID}'>{$content}</div>";
        }

        return $content;
    }

}
