<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('check_hellopack_cfw_plugin_status')) {
    //checkout-for-woocommerce
    function check_hellopack_cfw_plugin_status()
    {
        update_option('_cfw_licensing__key_status', 'valid', 'yes');
        update_option('_cfw_licensing__license_key', HP_GLOBAL_SERIAL, 'yes');
        update_option('cfw_license_activation_limit', '500', 'yes');
        update_option('cfw_license_price_id', '9');
    }
    if (hp_is_plugin_activated('checkout-for-woocommerce', 'checkout-for-woocommerce.php')) {
        add_action('plugins_loaded', 'check_hellopack_cfw_plugin_status');
    }
}
