<?php
class RaptorOptions {

    const OPTIONS = 'raptor-settings';
    const RAPTOR_USAGE_AREAS = 'raptor-usage-areas';
    const RAPTOR_SETTINGS_INDEX = 'raptor-settings-index';

    const INDEX_ALLOW_IN_PLACE_EDITING = 'index-allow-in-place-editing';
    const INDEX_RAPTORIZE_QUICKPRESS = 'index-raptorize-quickpress';
    const INDEX_RAPTORIZE_ADMIN_EDITING = 'index-raptorize-admin-editing';
    const RESIZE_IMAGES_AUTOMATICALLY = 'resize-images-automatically';
    const ALLOW_ADDITIONAL_EDITOR_SELECTORS = 'allow-additional-raptor-classes';
    const ADDITIONAL_EDITOR_SELECTORS = 'additional-raptor-classes';

    private $options = null;

    public function __construct() {
        $this->load();
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            update_option(self::OPTIONS, $_POST);
            $this->load();
        }
    }

    public function load() {
        $this->options = get_option(self::OPTIONS);
        if(!is_array($this->options)) {
            $this->options = array(
                self::INDEX_ALLOW_IN_PLACE_EDITING => '1',
                self::INDEX_RAPTORIZE_QUICKPRESS => '0',
                self::INDEX_RAPTORIZE_ADMIN_EDITING => '1',
                self::RESIZE_IMAGES_AUTOMATICALLY => '1',
                self::ALLOW_ADDITIONAL_EDITOR_SELECTORS => '0',
                self::ADDITIONAL_EDITOR_SELECTORS => ''
            );
            update_option(self::OPTIONS, $this->options);
        }
    }

    public function getOption($key) {
        if (!isset($this->options[$key])) {
            return null;
        }
        return $this->options[$key];
    }

    public function allowInPlaceEditing() {
        return $this->getOption(self::INDEX_ALLOW_IN_PLACE_EDITING);
    }

    public function raptorizeQuickpress() {
        return $this->getOption(self::INDEX_RAPTORIZE_QUICKPRESS);
    }

    public function raptorizeAdminEditing() {
        return $this->getOption(self::INDEX_RAPTORIZE_ADMIN_EDITING);
    }

    public function resizeImagesAutomatically() {
        return $this->getOption(self::RESIZE_IMAGES_AUTOMATICALLY);
    }

    public function allowAdditionalEditorSelectors() {
        return $this->getOption(self::ALLOW_ADDITIONAL_EDITOR_SELECTORS);
    }

    public function additionalEditorSelectors() {
        return $this->getOption(self::ADDITIONAL_EDITOR_SELECTORS);
    }
}
