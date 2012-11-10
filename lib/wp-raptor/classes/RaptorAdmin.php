<?php
class RaptorAdmin {

    public $options = null;

    public function __construct($options) {
        $this->options = $options;
    }

    public function setupMenu() {
        add_options_page('Raptor', 'Raptor Editor', 1, 'Raptor', array(&$this, 'adminIndex'));
    }

    public function adminIndex() {

        // Include jQuery UI
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-cookie', plugins_url('javascript/jquery.cookie.js', dirname(__FILE__)), 'jquery', '1.0.0');

        // CSS
        wp_register_style('jquery-ui-smoothness', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/smoothness/jquery-ui.css', false, '1.8.16');
        wp_enqueue_style('jquery-ui-smoothness');

        wp_register_style('raptor-admin-styles', plugins_url('css/admin/style.css', dirname(__FILE__)), false, '1.0.20');
        wp_enqueue_style('raptor-admin-styles');

        include RAPTOR_ROOT.'/views/admin/index.php';
    }
}
