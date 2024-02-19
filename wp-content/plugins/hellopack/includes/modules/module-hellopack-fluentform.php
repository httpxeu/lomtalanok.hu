<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('check_FLUENTFORMPRO_VERSION_plugin_status')) {
    function check_FLUENTFORMPRO_VERSION_plugin_status()
    {
        HP_check_options('_ff_fluentform_pro_license_key', HP_GLOBAL_SERIAL);
        HP_check_options('_ff_fluentform_pro_license_status', 'valid');
    }

    if (hp_is_plugin_activated('fluentformpro', 'fluentformpro.php')) {
        add_action('plugins_loaded', 'check_FLUENTFORMPRO_VERSION_plugin_status');
    }
}
