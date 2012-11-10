<?php

/**
 * @class Handles bootstrapping basic actions
 */
class RaptorInitialiser {

    /**
     * @var Raptor The raptor object handles inclusion of raptor editor scripts
     */
    public $raptor = null;

    /**
     * @var RaptorSave The raptor save object handles post saving
     */
    public $save = null;
    public $options = null;

    /**
     * Add plugins_loaded action
     */
    public function __construct() {
        add_action('plugins_loaded', array(&$this, 'initialise'));
    }

    /**
     * Add admin actions if applicable, otherwise add in place editing actions
     */
    public function initialise() {

        $this->options = new RaptorOptions();
        $this->raptor = new Raptor($this->options);

        if(RaptorStates::admin()){

            // Admin page
            $this->admin = new RaptorAdmin($this->options);
            add_action('admin_menu', array(&$this->admin, 'setupMenu'));

            // Post editing
            if ($this->options->raptorizeQuickpress() ||
                $this->options->raptorizeAdminEditing() ||
                $this->options->allowAdditionalEditorSelectors()){

                add_action('admin_print_scripts', array(&$this->raptor, 'removeNativeEditors'));

                if ($this->options->raptorizeQuickpress()) {
                    add_action('admin_print_scripts', array(&$this->raptor, 'addAdminQuickPressJs'));
                }

                if ($this->options->raptorizeAdminEditing()) {
                    add_action('admin_print_scripts', array(&$this->raptor, 'addAdminPostJs'));
                }

                if ($this->options->allowAdditionalEditorSelectors()) {
                    add_action('admin_print_scripts', array(&$this->raptor, 'addAdminAdditionalEditorSelectorsJs'));
                }
            }
        }

        if (RaptorStates::adminViewingPosts() && $this->options->allowInPlaceEditing()) {
            add_filter('the_content', array(&$this->raptor, 'encloseEditablePosts'));
            add_action('wp_print_scripts', array(&$this->raptor, 'addInPlacePostJs'));

            $this->save = new RaptorSave();
            add_action('wp_ajax_'.RaptorSave::SAVE_POSTS, array(&$this->save, 'savePosts'));
        }
    }
}