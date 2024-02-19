<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/* Fix PRO widgets */
if (!empty(get_option('elementor_pro_license_key'))) {
    delete_option('_elementor_pro_api_requests_lock');
}

if (!empty(get_option('elementor_pro_license_key'))) {
    delete_option('elementor_pro_license_key');
}

if (!empty(get_option('_elementor_pro_license_data_fallback'))) {
    delete_option('_elementor_pro_license_data_fallback');
}

if (!empty(get_option('_elementor_pro_license_data'))) {
    delete_option('_elementor_pro_license_data');
}
