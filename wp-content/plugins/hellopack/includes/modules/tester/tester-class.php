<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class HP_TESTER
{
    private static $_instance = null;
    public const AJAX_ACTION = 'hp_tester';

    private function __construct()
    {
    }
    private function __clone()
    {
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
            self::$_instance->init_actions();
        }

        return self::$_instance;
    }

    public function init_actions()
    {
        add_action('wp_ajax_' . self::AJAX_ACTION . '_healthcheck', array($this, 'ajax_healthcheck'));
    }

    public function ajax_healthcheck()
    {
        if (!check_ajax_referer(self::AJAX_ACTION, 'nonce', false)) {
            status_header(400);
            wp_send_json_error('bad_nonce');
        } elseif ('POST' !== $_SERVER['REQUEST_METHOD']) {
            status_header(405);
            wp_send_json_error('bad_method');
        }

        $limits = $this->get_server_limits();

        wp_send_json_success(array(
            'limits' => $limits,
        ));
    }

    public function get_server_limits()
    {
        $limits = [];

        // Check memory limit is > 256 M
        try {
            $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));

            if (is_plugin_active('elementor-pro/elementor-pro.php')) {
                $memory_limit_desired = 1024;
            } else {
                $memory_limit_desired = 256;
            }

            $memory_limit_ok = $memory_limit < 0 || $memory_limit >= $memory_limit_desired * 1024 * 1024;
            $memory_limit_in_mb = $memory_limit < 0 ? __('Unlimited', 'hellopack') : floor($memory_limit / (1024 * 1024)) . 'M';

            if (is_plugin_active('elementor-pro/elementor-pro.php')) {
                $limits['memory_limit'] = [
                    'title' => __('PHP Memory Limit', 'hellopack'),
                    'ok' => $memory_limit_ok,
                    'message' => $memory_limit_ok ? __("is ok at ", 'hellopack') . $memory_limit_in_mb : $memory_limit_in_mb . __(" may be enough, however, for proper functioning of Elementor Pro, a minimum of 1024 M memory limit is recommended. If you are having issues please set your PHP memory limit to at least 1024M - or ask your hosting provider to do this if you're unsure. <a href='https://elementor.com/help/requirements/' target='_blank'>Elementor’s system requirements</a>", 'hellopack'),
                ];
            } else {
                $limits['memory_limit'] = [
                    'title' => __('PHP Memory Limit', 'hellopack'),
                    'ok' => $memory_limit_ok,
                    'message' => $memory_limit_ok ? __("is ok at ", 'hellopack') . $memory_limit_in_mb : $memory_limit_in_mb . __(" may be too small. If you are having issues please set your PHP memory limit to at least 256M - or ask your hosting provider to do this if you're unsure.", 'hellopack'),
                ];
            }
        } catch (\Exception$e) {
            $limits['memory_limit'] = [
                'title' => __('PHP Memory Limit', 'hellopack'),
                'ok' => false,
                'message' => __('Failed to check memory limit. If you are having issues please ask hosting provider to raise the memory limit for you.', 'hellopack'),
            ];
        }

        // Check upload size.
        try {
            $upload_size_desired = 48;

            $upload_max_filesize = wp_max_upload_size();
            $upload_max_filesize_ok = $upload_max_filesize < 0 || $upload_max_filesize >= $upload_size_desired * 1024 * 1024;
            $upload_max_filesize_in_mb = $upload_max_filesize < 0 ? __('Unlimited', 'hellopack') : floor($upload_max_filesize / (1024 * 1024)) . 'M';

            $limits['upload'] = [
                'ok' => $upload_max_filesize_ok,
                'title' => __('PHP Upload Limits', 'hellopack'),
                'message' => $upload_max_filesize_ok ? __("is ok at ", 'hellopack') . $upload_max_filesize_in_mb : $upload_max_filesize_in_mb . __(' may be too small. If you are having issues please set your PHP upload limits to at least ', 'hellopack') . $upload_size_desired . 'M' . __(' - or ask your hosting provider to do this if you are unsure.', 'hellopack'),
            ];
        } catch (\Exception$e) {
            $limits['upload'] = [
                'title' => __('PHP Upload Limits', 'hellopack'),
                'ok' => false,
                'message' => __('Failed to check upload limit. If you are having issues please ask hosting provider to raise the upload limit for you.', 'hellopack'),
            ];
        }

        // Check max_input_vars.
        try {
            $max_input_vars = ini_get('max_input_vars');
            $max_input_vars_desired = 10000;
            $max_input_vars_ok = $max_input_vars < 0 || $max_input_vars >= $max_input_vars_desired;

            $limits['max_input_vars'] = [
                'ok' => $max_input_vars_ok,
                'title' => __('PHP Max Input Vars', 'hellopack'),
                'message' => $max_input_vars_ok ? __("is ok at ", 'hellopack') . $max_input_vars : $max_input_vars . __(" may be too small. If you are having issues please set your PHP max input vars to at least ", 'hellopack') . $max_input_vars_desired . __("- or ask your hosting provider to do this if you're unsure.", 'hellopack'),
            ];
        } catch (\Exception$e) {
            $limits['max_input_vars'] = [
                'title' => __('PHP Max Input Vars', 'hellopack'),
                'ok' => false,
                'message' => __('Failed to check input vars limit. If you are having issues please ask hosting provider to raise the input vars limit for you.', 'hellopack'),
            ];
        }

        // Check max_execution_time.
        try {
            $max_execution_time = ini_get('max_execution_time');
            $max_execution_time_desired = 120;
            $max_execution_time_ok = $max_execution_time <= 0 || $max_execution_time >= $max_execution_time_desired;

            $limits['max_execution_time'] = [
                'ok' => $max_execution_time_ok,
                'title' => __('PHP Execution Time', 'hellopack'),
                'message' => $max_execution_time_ok ? __('PHP execution time limit is ok at ', 'hellopack') . $max_execution_time : $max_execution_time . __(' is too small. Please set your PHP max execution time to at least ', 'hellopack') . $max_execution_time_desired . __(' - or ask your hosting provider to do this if you are unsure.', 'hellopack'),
            ];
        } catch (\Exception$e) {
            $limits['max_execution_time'] = [
                'title' => __('PHP Execution Time', 'hellopack'),
                'ok' => false,
                'message' => __('Failed to check PHP execution time limit. Please ask hosting provider to raise this limit for you.', 'hellopack'),
            ];
        }

        // Check various hostname connectivity.
        $hosts_to_check = array(
            array(
                'hostname' => 'api.wp-json.app',
                'url' => 'https://api.wp-json.app',
                'title' => __('HelloPack API server', 'hellopack'),
            ),
            array(
                'hostname' => 'hellowp.io',
                'url' => 'https://hellowp.io/hu/robots.txt',
                'title' => __('HelloPack Client API server', 'hellopack'),
            ),
            array(
                'hostname' => 'hellopack.wp-json.app',
                'url' => 'https://hellopack.wp-json.app/wp-json/helloup/v2/schema/',
                'title' => __('HelloPack Download server', 'hellopack'),
            ),
        );

        foreach ($hosts_to_check as $host) {
            try {
                $response = wp_remote_get($host['url'], [
                    'timeout' => 5,
                ]);
                $response_code = wp_remote_retrieve_response_code($response);
                if ($response && !is_wp_error($response) && 200 === $response_code) {
                    $limits[$host['hostname']] = [
                        'ok' => true,
                        'title' => $host['title'],
                        'message' => __('Connected ok.', 'hellopack'),
                    ];
                } else {
                    $limits[$host['hostname']] = [
                        'ok' => false,
                        'title' => $host['title'],
                        'message' => __('Connection failed. Status ', 'hellopack') . $response_code . __(' Please ensure PHP is allowed to connect to the host ', 'hellopack') . $host['hostname'] . __(' - or ask your hosting provider to do this if you’re unsure. ', 'hellopack') . (is_wp_error($response) ? $response->get_error_message() : ''),
                    ];
                }
            } catch (\Exception$e) {
                $limits[$host['hostname']] = [
                    'ok' => true,
                    'title' => $host['title'],
                    'message' => __('Connection failed. Please contact the hosting provider and ensure PHP is allowed to connect to the host ', 'hellopack') . $host['hostname'] . "'. " . $e->getMessage(),
                ];
            }
        }
        if (get_option('hellopack_updater_api_settings')) {
            $response_api_api_settings = get_option('hellopack_updater_api_settings');
            $response_api_api_key = $response_api_api_settings['api_key'];
            $response_api_product_id = $response_api_api_settings['product_id'];
        }

        if (get_option('hellopack_updater_instance')) {
            $response_api_updater_instance = get_option('hellopack_updater_instance');
        }

        if (get_option('hellopack_updater_activated') == 'Activated') {
            $response_api_url_site_url = get_site_url();
            $response_api_url_parsed_url = parse_url($response_api_url_site_url);
            $response_api_url_host = $response_api_url_parsed_url['host'];

            // Check authenticated API request
            $response_api_url = 'https://helloapi.wp-json.app/wp-json/helloup/v2/api/?api_key=' . $response_api_api_key . '&instance=' . $response_api_updater_instance . '&wc-api=wc-am-api&object=' . $response_api_url_host . '&product_id=' . $response_api_product_id . '&wc_am_action=status';
            $response = wp_remote_get($response_api_url);
            if (!is_wp_error($response)) {
                $response = json_decode($response['body']);
                // $response->status_check = 'deactive';
            }
            if (is_wp_error($response) or 'active' != $response->status_check) {
                $error_msg = '';
                if (is_wp_error($response)) {
                    $error_msg = __('Error message: ', 'hellopack') . $response->get_error_message();
                } else {
                    $error_msg = __('Please add your API key and activate the HelloPack plugin again.', 'hellopack');
                }
                $limits['authentication'] = [
                    'ok' => false,
                    'title' => __('HelloPack API status', 'hellopack'),
                    'message' => __('Not currently authenticated with the HelloPack API. ', 'hellopack') . $error_msg,
                ];
            } else {
                $limits['authentication'] = [
                    'ok' => true,
                    'title' => __('HelloPack API status', 'hellopack'),
                    'message' => __('Authenticated with the HelloPack API. All ok.', 'hellopack'),
                ];
            }
        } else {
            $limits['authentication'] = [
                'ok' => false,
                'title' => __('HelloPack API status', 'hellopack'),
                'message' => __('Please add your API key and activate the HelloPack plugin again.', 'hellopack'),
            ];
        }

        $debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
        $limits['wp_debug'] = [
            'ok' => !$debug_enabled,
            'title' => 'WP Debug',
            'message' => $debug_enabled ? __('If you are on a production website, it is best to set WP_DEBUG to false, please ask your hosting provider to do this if you are unsure. <a href="https://codex.wordpress.org/Debugging_in_WordPress" target="_blank">Learn more</a>', 'hellopack') : __('WP Debug is disabled, all ok.', 'hellopack'),
        ];

        $zip_archive_installed = class_exists('\ZipArchive');
        $limits['zip_archive'] = [
            'ok' => $zip_archive_installed,
            'title' => __('ZipArchive Support', 'hellopack'),
            'message' => $zip_archive_installed ? __('ZipArchive is available.', 'hellopack') : __('ZipArchive is not available. If you have issues installing or updating items please ask your hosting provider to enable ZipArchive.', 'hellopack'),
        ];

        $php_version_ok = version_compare(PHP_VERSION, '7.0', '>=');
        $limits['php_version'] = [
            'ok' => $php_version_ok,
            'title' => __('PHP Version', 'hellopack'),
            'message' => $php_version_ok ? __('PHP version is ok at ', 'hellopack') . PHP_VERSION . '.' : __('Please ask the hosting provider to upgrade your PHP version to at least 7.0 or above.', 'hellopack'),
        ];

        $php_version_ok_81 = version_compare(PHP_VERSION, '8.1', '>=');
        if ($php_version_ok_81 == true) {
            $limits['php_version_81'] = [
                'ok' => false,
                'title' => __('PHP Version', 'hellopack'),
                'message' => __('Many plugins (such as Elementor Pro) and WordPress itself are not compatible with PHP 8.1. Please use at most PHP 8.0.', 'hellopack'),
            ];
        }


        require_once ABSPATH . 'wp-admin/includes/file.php';
        $current_filesystem_method = get_filesystem_method();
        if ('direct' !== $current_filesystem_method) {
            $limits['filesystem_method'] = [
                'ok' => false,
                'title' => __('WordPress Filesystem', 'hellopack'),
                'message' => __('Please enable WordPress FS_METHOD direct - or ask your hosting provider to do this if you’re unsure.', 'hellopack'),
            ];
        }

        $wp_upload_dir = wp_upload_dir();
        $upload_base_dir = $wp_upload_dir['basedir'];
        $upload_base_dir_writable = is_writable($upload_base_dir);
        $limits['wp_content_writable'] = [
            'ok' => $upload_base_dir_writable,
            'title' => __('WordPress File Permissions', 'hellopack'),
            'message' => $upload_base_dir_writable ? __('is ok.', 'hellopack') : __('Please set correct WordPress PHP write permissions for the wp-content directory - or ask your hosting provider to do this if you’re unsure.', 'hellopack'),
        ];

        $active_plugins = get_option('active_plugins');
        $active_plugins_ok = count($active_plugins) < 25;
        if (!$active_plugins_ok) {
            $limits['active_plugins'] = [
                'ok' => false,
                'title' => __('Active Plugins', 'hellopack'),
                'message' => __('Please try to reduce the number of active plugins on your WordPress site, as this will slow things down.', 'hellopack'),
            ];
        }

        return $limits;
    }
}
