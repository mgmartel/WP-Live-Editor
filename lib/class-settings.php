<?php
if ( ! defined( 'ABSPATH' ) )
    exit(-1);

class WP_LiveEditor_UserSettings
{

    public $current_user_settings, $is_default;

    public function __construct() {
        $this->current_user_settings();
        $this->actions_and_filters();
    }

        public function wp_liveeditor_usersettings() {
            $this->_construct();
        }


    public function set_default_settings() {
        $user_id = get_current_user_id();
        $settings = array ( "live_editing" => false );
        update_user_meta ( $user_id, 'live-editor', $settings );
        return $settings;
    }

    protected function actions_and_filters() {
        add_action( 'personal_options', array ( &$this, 'add_user_settings' ) );
        add_action( 'personal_options', array ( &$this, 'add_user_settings' ) );
        add_action( 'personal_options_update', array ( &$this, 'save_user_settings' ) );
        add_action( 'edit_user_profile_update', array ( &$this, 'save_user_settings' ) );
    }

    public function current_user_settings() {
        $this->current_user_settings = get_user_meta( get_current_user_id(), 'live-editor', true );
        if ( empty ( $this->current_user_settings ) )
            return $this->set_default_settings();
    }

    public function is_default() {
        if ( ! empty ( $this->is_default ) )
            return $this->is_default;

        if ( empty ( $this->current_user_settings ) ) {
            _doing_it_wrong ( 'WP_LiveEditor_UserSettings::is_default', "Don't ask if Live Editor is default for the current user before set_current_user has run", "0.1" );
            return false;
        }

        return (bool) $this->current_user_settings['live_editing'];
    }

    public function add_user_settings( $profileuser ) {
        $displayed_user_settings = get_user_meta( $profileuser->id, 'live-editor', true );
        $live_editing = $displayed_user_settings['live_editing'];
        ?>
        <tr>
            <th scope="row"><?php _e('Live Editor', 'live-editor')?></th>
            <td><label for="live_editing"><input name="live_editing" type="checkbox" id="live_editing" value="true" <?php checked('true', $live_editing ); ?> /> <?php _e('Set Live Editor as the default editor for writing.', 'live-editor'); ?></label></td>
        </tr>
        <?php
    }

    public function save_user_settings() {
        $user_id = get_current_user_id();
        $settings = $this->current_user_settings;
        $settings['live_editing'] = $_POST['live_editing'];

        update_user_meta ( $user_id, 'live-editor', $settings );
    }
}