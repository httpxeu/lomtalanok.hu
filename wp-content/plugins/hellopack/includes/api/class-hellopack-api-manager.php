<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class HPack_API_Manager
{
    protected static $singleton;

    protected $api_url = '';

    protected $instance;
    protected $api_key;
    protected $product_id;
    protected $item_name;
    protected $item_version;
    protected $object;
    protected $slug;

    protected $get_failure_payload = false;

    /**
     * @var array $errors
     *         type: string     system|remote
     *         error: array
     */
    protected $errors = array();

    /**
     * @var HPack_Updater_Loader
     */
    protected $emitter;

    /**
     * @var HPack_Settings_Manager
     */
    protected $settings_manager;

    /**
     * @var string $api_namespace
     */
    protected $api_namespace;

    protected $api_args = array();

    protected $request_action = '';

    protected $response_data = array();

    public static function instance($singleton = false)
    {
        if ($singleton) {
            if (is_null(self::$singleton)) {
                self::$singleton = new self();
            }

            return self::$singleton;
        }

        return new self();
    }

    protected function __construct()
    {
        $this->api_url = defined('HP_UPDATER_API_URL') ? trailingslashit(HP_UPDATER_API_URL) : 'https://hellopack.wp-json.app/';

        if (empty($this->api_url)) {
            do_action('hp_no_api_url', $this);
        }
        $this->api_namespace = 'wp-json/helloup/v2/';

        $this->load_dependencies();
        $this->set_initials();
    }

    public static function make()
    {
        return new static();
    }

    protected function build_url($args = array(), $endpoint = '')
    {
        if (!$this->api_url) {
            return '';
        }

        $defaults = array(
            'api_key' => $this->api_key,
            'instance' => $this->instance,
        );

        $base_url = $this->api_url;

        if ($endpoint) {
            $defaults['subscription'] = $this->product_id;
            $base_url = $this->get_endpoint($endpoint);

            $main_api_args = wp_parse_args($args, $defaults);

            // storing request args for debug log
            $this->api_args = wp_parse_args($main_api_args, $this->api_args);
            $this->request_action = $endpoint;

            return $base_url . '?' . http_build_query($main_api_args, '', '&');
        }

        $defaults['wc-api'] = 'wc-am-api';
        $defaults['object'] = $this->object;
        $defaults['product_id'] = $this->product_id;

        $wcam_args = wp_parse_args($args, $defaults);

        // storing request args for debug log
        $this->api_args = wp_parse_args($wcam_args, $this->api_args);
        $this->request_action = isset($wcam_args['wc_am_action']) ? sanitize_text_field($wcam_args['wc_am_action']) : '';

        if ($wcam_args['wc_am_action']) {
            $base_url = "https://helloapi.wp-json.app/";
        }

        return $base_url . 'wp-json/helloup/v2/api/?' . http_build_query($wcam_args, '', '&');
    }

    protected function get_endpoint($endpoint)
    {
        $url = $this->api_url ? $this->api_url . $this->api_namespace . $endpoint : '';

        if ($url) {
            return trailingslashit($url);
        }

        return $url;
    }

    public function set_api_url($url)
    {
        $this->api_url = $url;

        return $this;
    }

    public function set_api_key($key)
    {
        $this->api_key = $key;

        return $this;
    }

    public function set_product_id($product_id)
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function status($args = array())
    {
        $defaults = array(
            'wc_am_action' => 'status',
        );

        $args = wp_parse_args($args, $defaults);

        $request_url = $this->build_url($args);

        return $this->make_call($request_url, array(
            'method' => 'GET',
        ));
    }

    public function can_activate($args = array())
    {
        $status = $this->status($args);

        if ($status) {
            //			$status = json_decode($response, true);

            if (isset($status['data']) && isset($status['success']) && $status['success']) {
                $data = $status['data'];
                return ($data['activated'] === false) && ($data['activations_remaining'] > 0);
            }
        }

        return false;
    }

    public function activate($args = array())
    {
        $defaults = array(
            'wc_am_action' => 'activate',
            'instance' => $this->instance ? $this->instance : $this->settings_manager->refresh_instance_id(),
        );

        $args = wp_parse_args($args, $defaults);

        $request_url = $this->build_url($args);

        return $this->make_call($request_url, array(
            'method' => 'GET',
        ));
    }

    public function deactivate($args = array())
    {
        $defaults = array(
            'wc_am_action' => 'deactivate',
        );

        $args = wp_parse_args($args, $defaults);

        $request_url = $this->build_url($args);

        return $this->make_call($request_url, array(
            'method' => 'GET',
        ));
    }

    public function plugin_schema()
    {
        $url = $this->build_url(array(), 'plugin-schema');

        return $this->make_call($url);
    }

    public function theme_schema()
    {
        $url = $this->build_url(array(), 'theme-schema');

        return $this->make_call($url);
    }

    public function schema()
    {
        $items = array(
            'plugins' => hp_installed_plugins(),
            //'themes' => hp_installed_themes(),
        );

        $url = $this->build_url(array(
            'domain' => $this->domain(),
            'items' => json_encode($items),
        ), 'schema');

        return $this->make_call($url, array('method' => 'GET'));
    }

    public function api_status($args = array())
    {
        $url = $this->build_url($args, 'status');

        return $this->make_call($url);
    }

    public function deferred_download($product_id)
    {
        if (empty($product_id)) {
            return '';
        }

        $args = array(
            'hp_delayed_download' => true,
            'hp_item_id' => $product_id,
        );

        $admin_url = admin_url('admin.php?page=hellopack_settings_manager');

        return add_query_arg($args, esc_url($admin_url));
    }

    public function download($args)
    {
        $defaults = array(
            'subscription' => $this->product_id,
            'domain' => $this->object,
            'instance' => $this->instance,
        );

        $args = wp_parse_args($args, $defaults);

        $url = $this->build_url($args, 'download');

        $response = $this->make_call($url);

        if ($response) {
            //			$response = json_decode($response, true);
            if (isset($response['package']) && !empty($response['package'])) {
                return $response['package'];
            }

            return false;
        }

        return $response;
    }

    protected function make_call($url, $args = array())
    {
        $defaults = array(
            'timeout' => 25,
            'method' => 'GET',
        );

        $args = wp_parse_args($args, $defaults);

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            if (is_wp_error($response)) {
                $this->parse_response($response);
                $this->log();
                $error_payload = array(
                    'code' => $response->get_error_code(),
                    'message' => $response->get_error_message($response->get_error_code()),
                    'data' => $response->get_error_data(),
                );

                $this->errors['type'] = 'system';
                $this->errors['error'] = $error_payload;

                if ($this->get_failure_payload) {
                    /*				    return array(
                        'code' => $response->get_error_code(),
                        'message' => $response->get_error_message($response->get_error_code()),
                        'data' => $response->get_error_data()
                    */
                    return $error_payload;
                }
            } else {
                $response_body = json_decode(wp_remote_retrieve_body($response), true);
                $this->parse_response($response_body);
                $this->log();

                $this->errors['type'] = 'remote';
                $this->errors['error'] = $response_body;

                if ($this->get_failure_payload) {
                    return $response_body;
                }
            }

            return false;
        }

        $body = wp_remote_retrieve_body($response);

        $body = json_decode($body, true);

        $this->parse_response($body);

        $this->log();

        return $body;
    }

    public function api_key_exists()
    {
        $this->api_key = $this->settings_manager->get_api_key();

        return !empty($this->api_key);
    }

    protected function load_dependencies()
    {
        if (!function_exists('HPOM')) {
            require_once HP_UPDATER_INC . 'hellopack-functions.php';
        }

        $this->emitter = HPEmitter();
        $this->settings_manager = HPOM();
    }

    public function set_initials()
    {
        $this->object = $this->domain();
        $this->api_key = $this->settings_manager->get_api_key();
        $this->product_id = $this->settings_manager->get_product_id();
        $this->instance = $this->settings_manager->get_instance_id(null);
        $this->errors = array();
        $this->api_namespace = 'wp-json/helloup/v2/'; //TODO: V3
        $this->api_url = defined('HP_UPDATER_API_URL') ? trailingslashit(HP_UPDATER_API_URL) : 'https://hellopack.wp-json.app/';

        $this->api_args = array();

        return $this;
    }

    public function set_instance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    public function domain()
    {
        return str_ireplace(array('http://', 'https://'), '', home_url());
    }

    public function get_errors()
    {
        return $this->errors;
    }

    public function has_error()
    {
        return !empty($this->errors);
    }

    public function get_error_type()
    {
        if ($this->has_error() && isset($this->errors['type'])) {
            return $this->errors['type'];
        }

        return null;
    }

    public function is_system_error()
    {
        return 'system' === $this->get_error_type();
    }

    public function is_remote_error()
    {
        return 'remote' === $this->get_error_type();
    }

    public function getResponseData()
    {
        return $this->response_data;
    }

    /**
     * @param WP_Error|array $response
     *
     * @return HPack_API_Manager
     */
    private function parse_response($response)
    {
        if (is_wp_error($response)) {
            $error_payload = array(
                'code' => $response->get_error_code(),
                'message' => $response->get_error_message($response->get_error_code()),
                'data' => $response->get_error_data(),
            );

            $this->errors['type'] = 'system';
            $this->errors['error'] = $error_payload;

            $error = array();
            $error['code'] = $response->get_error_code();
            $error['message'] = $response->get_error_messages();
            $error['data'] = $response->get_error_data();
            $this->response_data = $error;
            return $this;
        }

        if (isset($response['code']) || isset($response['error'])) {
            $this->errors['type'] = 'remote';
            $this->errors['error'] = $response;

            $this->response_data = $response;
            return $this;
        }

        switch ($this->request_action) {
            case 'download':
                if (is_array($response) && isset($response['package'])) {
                    $this->response_data = array('package' => __('Valid download URL returned.', 'hellopack'));
                } else {
                    $this->response_data = is_array($response) ? $response : (array) $response;
                }
                return $this;
            case 'theme-schema':
                $this->response_data = array('payload' => 'theme-schema', 'total_themes' => count($response));
                return $this;
            case 'plugin-schema':
                $this->response_data = array('payload' => 'plugin-schema', 'total_plugins' => count($response));
                return $this;
            default:
                $this->response_data = is_array($response) ? $response : (array) $response;
                break;
        }

        return $this;
    }

    protected function log()
    {
        $message = '';
        $message .= __('Requesting for: ', 'hellopack');
        $message .= !empty($this->request_action) ? $this->request_action : __('Unknown', 'hellopack');
        $message .= PHP_EOL;
        $message .= __('Request data:', 'hellopack') . PHP_EOL;
        $message .= hp_print_r($this->api_args, true);
        $message .= PHP_EOL;
        $message .= __('Response Information:', 'hellopack') . PHP_EOL;
        $message .= hp_print_r($this->response_data, true);

        hp_api_debug($message);
    }

    /**
     * @param bool $get_failure_payload
     * @return HPack_API_Manager
     */
    public function setGetFailurePayload($get_failure_payload = true)
    {
        $this->get_failure_payload = $get_failure_payload;
        return $this;
    }
}
