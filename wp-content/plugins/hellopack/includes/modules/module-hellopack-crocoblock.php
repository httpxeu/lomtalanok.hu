<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('check_rocket_plugin_status')) {
    function check_rocket_plugin_status()
    {
        if (class_exists('HPack_Set_API_Servers')) {
            $crocoblock = new HPack_Set_API_Servers();
            $crocoblock->set_api_servers('api.crocoblock.com', HP_PLUGIN_API_SERVER . '/crocoblock');
            $crocoblock->init();
        }
    }
    add_action('plugins_loaded', 'check_rocket_plugin_status');
}
