<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('HPOM')) {
    require_once HP_UPDATER_INC . 'hellopack-functions.php';
}

if (get_option('hellopack_updater_activated') == 'Activated') {
    foreach (glob(HP_UPDATER_INC . '/modules/*.php') as $file) {
        try {
            include_once $file;
        } catch (Exception $e) {
            $error = new WP_Error("file_load_error", "Error loading HelloPack module: " . $file . " " . $e->getMessage());
        }
    }
}

if (!defined('HP_TESTER')) {
    include_once HP_UPDATER_INC . '/modules/module-hellopack-plugin-tester.php';
}
