<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// WP All Export Pro & WP All Import Pro

if (hp_is_plugin_activated('wp-all-export-pro', 'wp-all-export-pro.php') or hp_is_plugin_activated('wp-all-import-pro', 'wp-all-import-pro.php')) {
    $options = get_option('PMXE_Plugin_Options');
    if ($options) {
        $options['license'] = HP_GLOBAL_SERIAL;
        $options['license_status'] = 'valid';
        update_option('PMXE_Plugin_Options', $options);
    }

    $options = get_option('PMXI_Plugin_Options');
    if ($options) {
        $options['licenses']['PMXI_Plugin'] = HP_GLOBAL_SERIAL;
        $options['statuses']['PMXI_Plugin'] = 'valid';
        update_option('PMXI_Plugin_Options', $options);
    }
}
