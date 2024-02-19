<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


if (!function_exists('check_GP_PREMIUM_VERSION_plugin_status')) {
    function check_GP_PREMIUM_VERSION_plugin_status()
    {
        HP_check_options('gen_premium_license_key', '61f1be33598b9644de31e3214c9d15fb');
        HP_check_options('gen_premium_license_key_status', 'valid');
    }
    if (hp_is_plugin_activated('gp-premium', 'gp-premium.php')) {
        add_action('plugins_loaded', 'check_GP_PREMIUM_VERSION_plugin_status');
    }
}
