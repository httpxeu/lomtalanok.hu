<?php


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


// QuadMenu PRO
if (hp_is_plugin_activated('quadmenu-pro', 'quadmenu-pro.php')) {
    $qlwlm_quadmenu_pro_user_data = array(
        'license_key' => HP_GLOBAL_SERIAL,
        'license_email' => 'support@hellowp.io',
        'license_market' => '',
    );

    $qlwlm_quadmenu_pro_activation = array(
        "message" => null,
        "order_id" => 42,
        "license_key" => HP_GLOBAL_SERIAL,
        "license_email" => 1,
        "license_limit" => 0,
        "license_updates" => 0,
        "license_support" => 1,
        "license_expiration" => "2028-04-21 10:40:23",
        "license_created" => "2000-10-21 10:40:23",
        "activation_limit" => "Unlimited",
        "activation_count" => 42,
        "activation_remaining" => "Unlimited",
        "activation_instance" => 42,
        "activation_status" => 1,
        "activation_site" => get_home_url(),
        "activation_created" => "2020-02-05 17:04:58"
    );

    HP_check_options('qlwlm_quadmenu-pro_user_data', $qlwlm_quadmenu_pro_user_data);
    HP_check_options('qlwlm_quadmenu-pro_activation', $qlwlm_quadmenu_pro_activation);
}
