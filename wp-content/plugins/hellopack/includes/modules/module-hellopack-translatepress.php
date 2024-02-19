<?php
/**
 * @since 1.1.9
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('check_TRP_Handle_Included_Addons_plugin_status')) {
    function check_TRP_Handle_Included_Addons_plugin_status()
    {
        if (get_option('my_multi_options') != 'valid' or get_option('my_multi_options') != 'valid') {
            HP_check_options('trp_license_status', 'valid');
            HP_check_options('trp_license_details', 'valid');
        }
    }
}

if (hp_is_plugin_activated('translatepress-business', 'index.php')) {
    add_action('plugins_loaded', 'check_TRP_Handle_Included_Addons_plugin_status');
}
