<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('check_DCE_PLUGIN_BASE_plugin_status')) {
    function check_DCE_PLUGIN_BASE_plugin_status()
    {
        HP_check_options('dce_license_status', 'active');
    }

    if (hp_is_plugin_activated('dynamic-content-for-elementor', 'dynamic-content-for-elementor.php')) {
        add_action('plugins_loaded', 'check_DCE_PLUGIN_BASE_plugin_status');
    }
}
