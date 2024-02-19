<?php

defined('WP_UNINSTALL_PLUGIN') || exit;

require_once 'hellopack-updater.php';

/**
 * @var HPack_API_Manager $apiManager
 */
try {
    if (HPack_Settings_Manager::instance()->has_api_settings()) {
        HPApi()->set_initials()->deactivate();
    }
} catch (Exception $e) {
}

HPUtil()->cleanup();
HPOM()->remove_api_key();
HPOM()->remove_initial();
//HPOM()->remove_plugins_catalog();
//HPOM()->remove_themes_catalog();
