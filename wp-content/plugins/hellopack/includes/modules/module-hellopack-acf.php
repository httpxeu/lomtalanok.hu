<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (hp_is_plugin_activated('advanced-custom-fields-pro', 'acf.php')) {
     $data = array('status' => 'active');
     HP_check_options('acf_pro_license_status', $data);
}