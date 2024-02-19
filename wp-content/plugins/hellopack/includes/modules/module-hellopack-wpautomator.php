<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (hp_is_plugin_activated('automatorwp-pro', 'automatorwp-pro.php')) {
    function remove_automatorwp_add_ons_menu()
    {
        remove_submenu_page('automatorwp', 'automatorwp_add_ons');
        remove_submenu_page('automatorwp', 'automatorwp_licenses');
    }
    add_action('admin_menu', 'remove_automatorwp_add_ons_menu', 999);
}
