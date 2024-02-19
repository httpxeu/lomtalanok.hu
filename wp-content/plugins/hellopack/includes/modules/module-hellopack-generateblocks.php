<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('check_generateblocks_pro_licensing_status')) {
    function check_generateblocks_pro_licensing_status()
    {
        $generate = array('key' => HP_GLOBAL_SERIAL,
        'status' => 'valid',
        'beta' => false,);
        HP_check_options('generateblocks_pro_licensing', $generate);
    }
}

if (hp_is_plugin_activated('generateblocks-pro', 'plugin.php')) {
    add_action('plugins_loaded', 'check_generateblocks_pro_licensing_status');
}
