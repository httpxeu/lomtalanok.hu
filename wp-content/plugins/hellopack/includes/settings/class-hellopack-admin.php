<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class HPack_Admin
{
    public const SLUG_SETTINGS = 'hellopack_settings_manager';
    public const SLUG_PLUGINS = 'hellopack_plugins_manager';
    public const SLUG_THEME = 'hellopack_themes_manager';
    public const TAB_ACTIVATION = 'hellopack_settings_activation';
    public const TAB_DEACTIVATION = 'hellopack_settings_deactivation';
    public const TAB_OTHERS = 'hellopack_settings_others';

    protected static $singleton = null;

    /**
     * @var HPack_Settings_Manager
     */
    protected $settings;

    /**
     * @var HPack_Updater_Loader
     */
    protected $emitter;

    protected $tab_settings = array();
    protected $instance_id;

    /**
     * @var HPack_API_Manager $api
     */
    protected $api;

    public static function instance()
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }

    public function __construct()
    {
        $this->load_dep();
        $this->set_tab_settings();
    }

    private function load_dep()
    {
        if (!class_exists('HPack_API_Manager')) {
            require_once HP_UPDATER_INC . 'api/class-hellopack-api-manager.php';
        }

        $this->emitter = HPEmitter();
        $this->settings = HPOM();
        $this->api = HPack_API_Manager::instance();

        if (hp_clean($this->settings->silent_mode()) === 'yes' && get_option('hellopack_updater_activated') == 'Activated') {
            add_action("plugins_loaded", 'hellopack_silent_mode_load', 101);
        }

        if (hp_clean($this->settings->disable_plugins()) === 'yes' && get_option('hellopack_updater_activated') == 'Activated') {
            add_action("plugins_loaded", 'HelloPack_Disable_Plugin_Menu', 101);
        }
    }

    public function admin_scripts($hook)
    {
        //  wp_register_style('hp-admin-css', HP_UPDATER_STATIC_URL . 'styles/hp-admin-helper-min.css', array(), HP_UPDATER_VERSION);
        wp_register_style('hp-admin-helper-css', HP_UPDATER_STATIC_URL . 'styles/hp-admin-helper.css', array(), HP_UPDATER_VERSION);
        //  wp_register_script('hp-settings-js', HP_UPDATER_STATIC_URL . 'scripts/hp-settings.js', array('jquery'), HP_UPDATER_VERSION);

        wp_register_script('hp-settings-js', HP_UPDATER_STATIC_URL . 'scripts/hp-settings.js', array( 'wp-i18n' ));

        wp_register_script('hp-settings-helper-js', HP_UPDATER_STATIC_URL . 'scripts/helper-settings.js', array('jquery'), HP_UPDATER_VERSION);

        if ($hook === "toplevel_page_hellopack_settings_manager") {
            wp_enqueue_style('hp-admin-css');
            wp_enqueue_script('hp-settings-js');
            wp_enqueue_script('hp-settings-helper-js');
        }

        global $pagenow;
        global $post_type;

        $screen = get_current_screen();

        if (in_array($screen->base, array('plugins_page_hellopack_settings_manager'))) {
            wp_enqueue_style('hp-admin-css');
            wp_enqueue_script('hp-settings-js');
            wp_enqueue_script('hp-settings-helper-js');
        }
    }

    public function init_menu()
    {
        if (hp_clean($this->settings->plugin_space()) === 'yes') {
            add_submenu_page(
                'plugins.php',
                __('HelloPack', 'hellopack'),
                __('HelloPack', 'hellopack'),
                'manage_options',
                static::SLUG_SETTINGS,
                array($this, 'page_settings'),
                58,
                1
            );
        } else {
            add_menu_page(
                __('HelloPack', 'hellopack'),
                __('HelloPack', 'hellopack'),
                'manage_options',
                static::SLUG_SETTINGS,
                array($this, 'page_settings'),
                'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="hellopack-icon"><!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path class="hellopack-icon-path" fill="currentColor" d="M383.5 192c.3-5.3 .5-10.6 .5-16c0-51-15.9-96-40.2-127.6C319.5 16.9 288.2 0 256 0s-63.5 16.9-87.8 48.4C143.9 80 128 125 128 176c0 5.4 .2 10.7 .5 16H240V320H208c-7 0-13.7 1.5-19.7 4.2L68.2 192H96.5c-.3-5.3-.5-10.6-.5-16c0-64 22.2-121.2 57.1-159.3C62 49.3 18.6 122.6 4.2 173.6C1.5 183.1 9 192 18.9 192h6L165.2 346.3c-3.3 6.5-5.2 13.9-5.2 21.7v96c0 26.5 21.5 48 48 48h96c26.5 0 48-21.5 48-48V368c0-7.8-1.9-15.2-5.2-21.7L487.1 192h6c9.9 0 17.4-8.9 14.7-18.4C493.4 122.6 450 49.3 358.9 16.7C393.8 54.8 416 112.1 416 176c0 5.4-.2 10.7-.5 16h28.3L323.7 324.2c-6-2.7-12.7-4.2-19.7-4.2H272V192H383.5z"/></svg>

                '),
                58
            );
        }
    }



    public function register_settings()
    {
        register_setting(
            HPack_Settings_Manager::API_SETTINGS_MAIN,
            HPack_Settings_Manager::API_SETTINGS_MAIN,
            null
        );

        // HelloPack Key Activation Settings
        add_settings_section(
            HPack_Settings_Manager::API_SETTINGS_SECTION,
            '',
            array($this, 'api_key_text'),
            HPack_Admin::TAB_ACTIVATION
        );

        add_settings_field(
            HPack_Settings_Manager::API_DOMAIN,
            __('Domain', 'hellopack'),
            array($this, 'api_domain'),
            HPack_Admin::TAB_ACTIVATION,
            HPack_Settings_Manager::API_SETTINGS_SECTION
        );

        add_settings_field(
            HPack_Settings_Manager::API_KEY,
            __('API Key', 'hellopack'),
            array($this, 'api_key_field'),
            HPack_Admin::TAB_ACTIVATION,
            HPack_Settings_Manager::API_SETTINGS_SECTION
        );

        add_settings_field(
            HPack_Settings_Manager::PRODUCT_KEY,
            __('', 'hellopack'),
            array($this, 'product_id_field'),
            HPack_Admin::TAB_ACTIVATION,
            HPack_Settings_Manager::API_SETTINGS_SECTION
        );

        add_settings_field(
            'status',
            __('License Status', 'hellopack'),
            array($this, 'api_key_status'),
            HPack_Admin::TAB_ACTIVATION,
            HPack_Settings_Manager::API_SETTINGS_SECTION
        );

        add_settings_field(
            'api_deactivation',
            __('Deactivate License', 'hellopack'),
            array($this, 'force_deactivate_api'),
            HPack_Admin::TAB_ACTIVATION,
            HPack_Settings_Manager::API_SETTINGS_SECTION
        );

        add_settings_field(
            'check_api_status',
            __('Check License Status', 'hellopack'),
            array($this, 'check_api_status'),
            HPack_Admin::TAB_ACTIVATION,
            HPack_Settings_Manager::API_SETTINGS_SECTION
        );

        add_settings_field(
            'cleanup_api_settings',
            __('Cleanup Local Settings', 'hellopack'),
            array($this, 'cleanup_api_settings'),
            HPack_Admin::TAB_ACTIVATION,
            HPack_Settings_Manager::API_SETTINGS_SECTION
        );

        // Other HelloPack Settings
        register_setting(
            HPack_Settings_Manager::EXTRA_SETTINGS_KEY,
            HPack_Settings_Manager::EXTRA_SETTINGS_KEY,
            array($this, 'hellopack_settings_extra')
        );

        add_settings_section(
            HPack_Settings_Manager::EXTRA_SECTION,
            // __('Settings', 'hellopack'),
            __('', 'hellopack'),
            array($this, 'extra_settings_section'),
            HPack_Admin::TAB_OTHERS
        );

        /*    add_settings_field(
                HPack_Settings_Manager::EXTRA_WOO_NOTICE,
                __('Disable Wootheme Notice', 'hellopack'),
                array($this, 'disable_woo_field'),
                HPack_Admin::TAB_OTHERS,
                HPack_Settings_Manager::EXTRA_SECTION
            );

            */

        add_settings_field(
            HPack_Settings_Manager::EXTRA_LOG,
            __('Disable Logging', 'hellopack'),
            array($this, 'disable_log_field'),
            HPack_Admin::TAB_OTHERS,
            HPack_Settings_Manager::EXTRA_SECTION
        );

        add_settings_field(
            HPack_Settings_Manager::EXTRA_PLUGIN_SPACE,
            __('HelloPack menu', 'hellopack'),
            array($this, 'plugin_space_field'),
            HPack_Admin::TAB_OTHERS,
            HPack_Settings_Manager::EXTRA_SECTION
        );

        add_settings_field(
            HPack_Settings_Manager::EXTRA_SILENT_MODE,
            __('Silent mode', 'hellopack'),
            array($this, 'silent_mode_field'),
            HPack_Admin::TAB_OTHERS,
            HPack_Settings_Manager::EXTRA_SECTION
        );

        add_settings_field(
            HPack_Settings_Manager::EXTRA_DISABLE_PLUGINS,
            __('Hide plugins menu', 'hellopack'),
            array($this, 'disable_plugins_field'),
            HPack_Admin::TAB_OTHERS,
            HPack_Settings_Manager::EXTRA_SECTION
        );
    }

    //
    //  Callbacks for Settings API starts
    //

    public function cleanup_api_settings()
    {
        //TODO
        
         $api_settings = $this->settings->get_api_settings();

         $disabled = !empty($api_settings) ? '' : ' disabled';

         $tip = sprintf(
             __('If you are having troubles with cleaning up activations or activations on both main server and your site are not in sync, please delete entries on main server from hellowp.io against this domain, then clean up local settings and try to activate your license once again.', 'hellopack'),
         );

         echo '<button id="hp_cleanup_settings" type="button" class="button button-primary hellopack-button hellopack-button-small hellopack-blue" data-action="hp_cleanup_settings" data-nonce="' . wp_create_nonce('hp_cleanup_settings') . '"' . $disabled . '> <svg class="hellopack-delete-icon"><use xlink:href="'.HP_UPDATER_STATIC_URL.'/svg/sprite.svg?v='.HP_UPDATER_VERSION.'#hellopack-delete-icon"></use></svg>' . __('Clean up local settings', 'hellopack') . '</button>';
          echo '<div class="description">' . $tip . '</div>';
         
    }

    public function check_api_status()
    {
        $api_settings = $this->settings->get_api_settings();

        $disabled = !empty($api_settings) ? '' : ' disabled';

        $tip = __('Check API Status from the server.', 'hellopack');
        echo '<button id="hp_check_api" type="button" class="button button-primary hellopack-button hellopack-button-small hellopack-green" data-action="hp_check_api" data-method="refresh_status" data-nonce="' . wp_create_nonce('hp_check_api') . '"' . $disabled . '><svg class="hellopack-reset-icon"><use xlink:href="'.HP_UPDATER_STATIC_URL.'/svg/sprite.svg?v='.HP_UPDATER_VERSION.'#hellopack-reset-icon"></use></svg>' . __('Check License', 'hellopack') . '</button>';
        echo '<div class="description">' . $tip . '</div>';
    }

    public function force_deactivate_api()
    {
        $api_settings = $this->settings->get_api_settings();

        $disabled = !empty($api_settings) ? '' : ' disabled';

        $tip = __('Deactivate your current API activation on the main server <code>www.hellowp.io</code>', 'hellopack');
        echo '<button id="hp_deactivate_api" type="button" class="button button-danger hellopack-button hellopack-button-small hellopack-button-icon hellopack-red" data-action="hp_deactivate_api" data-nonce="' . wp_create_nonce('hp_deactivate_api') . '"' . $disabled . '><svg class="hellopack-padlock-close-icon"><use xlink:href="'.HP_UPDATER_STATIC_URL.'/svg/sprite.svg?v='.HP_UPDATER_VERSION.'#hellopack-padlock-close-icon"></use></svg>' . __('Deactivate License', 'hellopack') . '</button>';
        echo '<div class="description">' . $tip . '</div>';
    }

    public function api_key_text()
    {
        echo '<div id="hp_reports"></div>';
    }

    public function api_key_status()
    {
        $api_key_status = trim($this->settings->get(HPack_Settings_Manager::DEACTIVATION_KEY));
        $status_check_text = empty($api_key_status) ? 'Deactivated' : $api_key_status;


        if ($status_check_text == 'Deactivated') {
            $status_check_text = '<span class="hp_api_status_text_deactivated hellopack-license-status  hellopack-t-red"><strong>'.__("Deactivated", "hellopack").'</strong></span>';
        } else {
            $status_check_text = '<span class="hp_api_status_text_activated hellopack-license-status  hellopack-t-green"><strong><svg class="hellopack-grid-icon"><use xlink:href="'.HP_UPDATER_STATIC_URL.'/svg/sprite.svg?v='.HP_UPDATER_VERSION.'#hellopack-check-icon"></use></svg>'.__("Activated", "hellopack").'</strong></span>';
        }



        echo '<div id="hp_api_status_text">' . $status_check_text . '</div>';
    }

    public function api_key_field()
    {
        $api_key_option = $this->settings->get_api_key();
        $api_key = empty($api_key_option) ? '' : $api_key_option;
        $report_class = $api_key_option ? 'api-has-activation' : 'api-no-activation';


        echo "<input class='hp_api_field " . $report_class . "' id='" . HPack_Settings_Manager::API_KEY . "' name='" . HPack_Settings_Manager::API_SETTINGS_MAIN . "[" . HPack_Settings_Manager::API_KEY . "]' type='password' value='" . $api_key . "' required />";
        

        if (defined('HELLOPACK_WHITELABEL') && HELLOPACK_WHITELABEL === true) {
            echo '<div class="description"><p>' . __('Please, enter the API key from HelloPack center page.', 'hellopack') . '</p></div>';
        } else {
            if (strlen($api_key) == 42){
                echo '<div class="description"><span style="background-color: #ffdd57; padding:5px;border-radius: 10px;"><strong>' . __('You are using the new API system!', 'hellopack') . '</strong></span></div>';
            }elseif  (strlen($api_key) == 40){
                echo '<div class="description"><span style="background-color: #ffdd57; padding:5px;border-radius: 10px;"><strong>' . __('You are using an old API key!', 'hellopack') . '</strong></span> <br><br> 

                <span style="color:red">' . __('Change to the new key by 2023-07-01! You can find it here:', 'hellopack') . ' <a href="https://hellowp.io/hu/helloconsole/hellopack-kozpont/api-creator/" target="_blank">' . __('API Key Creator', 'hellopack') . '</a></span>
                
                </div>';
            }else{
                echo '<div class="description"><p>' . __('Please, enter the API key from HelloPack center page.', 'hellopack') . ' <a href="https://hellowp.io/hu/helloconsole/hellopack-kozpont/api-creator/" target="_blank">HelloPack console</a></p></div>';
            }
       
        }
    }

    public function api_domain()
    {
     
        $api_domain =  str_ireplace(array('http://', 'https://'), '', home_url());

        $tip = sprintf(
            __('You can use this domain for creating the API key.', 'hellopack'),
        );

        echo "<input class='hp_api_domain' type='text' value='" . $api_domain . "' disabled />";
        echo '<div class="description">' . $tip . '</div>';
    }

    public function product_id_field()
    {
        $product_key_option = $this->settings->get_product_id();
        $product_id = 51507;
        $report_class = $product_key_option ? 'api-has-activation' : 'api-no-activation';

        echo "
        <input type='hidden' id='" . HPack_Settings_Manager::PRODUCT_KEY . "' name='" . HPack_Settings_Manager::API_SETTINGS_MAIN . "[" . HPack_Settings_Manager::PRODUCT_KEY . "]' value='51507'>";

        //	echo "<input class='hp_api_field " . $report_class . "' id='" . HPack_Settings_Manager::PRODUCT_KEY . "' name='" . HPack_Settings_Manager::API_SETTINGS_MAIN . "[" . HPack_Settings_Manager::PRODUCT_KEY . "]' type='select' value='" . $product_id . "' required />";

        /*  if ($product_key_option) {
              echo "<span class='hp-ff-mark logo' style='color: #23d160; margin-left: 10px;'><svg aria-hidden='true'  width='29' focusable='false' data-prefix='fas' data-icon='check-circle' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' class='svg-inline--fa fa-check-circle fa-w-16 fa-3x'><path fill='currentColor' d='M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z' class=''></path></svg></span>";
          } else {
              echo "<span class='hp-ff-mark logo' style='color: #ff3960; margin-left: 10px;'><svg aria-hidden='true' focusable='false' width='29' data-prefix='fas' data-icon='times-circle' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' class='svg-inline--fa fa-times-circle fa-w-16 fa-3x'><path fill='currentColor' d='M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z' class=''></path></svg></span>";
          }
          echo '<div class="description">' . __('Válaszd ki, hogy milyen fizetési gyariságot választottál', 'hellopack') . '</div>';
          /*ó*/
    }

    public function ajax_activate_api_key()
    {
        if (!function_exists('HPOM')) {
            require_once HP_UPDATER_INC . 'hellopack-functions.php';
        }

        // check request validity
        $verified = check_ajax_referer('hp_cleanup_settings', 'security', false);
        if (!$verified) {
            wp_send_json_error(array(
                'error' => __('Request is not authorized and recognized as insecure or malicious request to the system.', 'hellopack'),
            ));
        }

        $api_key = !empty($_POST['api_key']) ? hp_clean($_POST['api_key']) : null;
        $product_id = !empty($_POST['product_id']) ? hp_clean($_POST['product_id']) : null;

        if (empty($api_key) && empty($product_id)) {
            wp_send_json_error(array(
                'error' => __('Both Product ID and API Key field is required   sss', 'hellopack'),
            ));
        }

        $apiObj = HPApi()->set_api_key($api_key)->set_product_id($product_id)->setGetFailurePayload();
        // check api status
        @hp_settings_debug(esc_html__('Checking License status...', 'hellopack'));
        $status = $apiObj->status();

        if (isset($status['code'])) {
            $message = __("API Activation did not take place, because unknown error occurred.", 'hellopack'). $status['code'];
            if ($status['code'] = '24'){
                $message = __("This API key is inactive or does not belong to the domain. Check the API key here: ", 'hellopack') . ' <a href="https://hellowp.io/hu/helloconsole/hellopack-kozpont/api-creator/" target="_blank">HelloPack console</a>';
                
            }
            if (isset($status['error'])) {
                $message = $status['error'];
            } elseif (isset($status['message'])) {
                $message = $status['message'];
            }

            unset($apiObj);
            wp_send_json_error(array('message' => $message));
        }

        // TODO: heavily dependant on WooCommerce API Manager
        if (isset($status['status_check']) && $status['status_check'] === 'inactive') {
            @hp_settings_debug(esc_html__('License status checked successfully!', 'hellopack') . PHP_EOL);

            // API status is inactive and going to activate the Key
            @hp_settings_debug(esc_html__('Activating License on the server...', 'hellopack'));
            $activation_response = $apiObj->activate();

            // activation failed
            if (isset($activation_response['code'])) {
                $message = __('Unknown error occurred during activation process', 'hellopack');
                if (isset($activation_response['error'])) {
                    $message = $activation_response['error'];
                } elseif (isset($activation_response['message'])) {
                    $message = $activation_response['message'];
                }

                @hp_settings_debug(esc_html__('License activation failed: ', 'hellopack') . PHP_EOL);
                @hp_settings_debug(hp_print_r($activation_response) . PHP_EOL);

                unset($apiObj);
                wp_send_json_error(array('message' => $message));
            }
            HPOM()->save_api_settings(array(
                'api_key' => $api_key,
                'product_id' => $product_id,
            ));

            HPOM()->enable_activation_status();

            do_action('hp_api_license_activated');

            @hp_settings_debug(__('License activated successfully! Please, check your Dashboard under "API Keys" section on hellowp.io.', 'hellopack') . PHP_EOL);
            unset($apiObj);
            wp_send_json_success(array('message' => $activation_response['message']));
        }

        HPOM()->save_api_settings(array(
            'api_key' => $api_key,
            'product_id' => $product_id,
        ));

        HPOM()->enable_activation_status();

        @hp_settings_debug(esc_html__('License key already activated for this site.', 'hellopack') . PHP_EOL);
        unset($apiObj);
        wp_send_json_success(array('message' => __('Your license is already activated!', 'hellopack')));
    }

    public function hellopack_settings_extra()
    {
        $settings_extra = (array) $this->settings->extra_settings(array());
        $woo_param = 0;
        $disable_log = 'no';
        if ($_REQUEST['option_page'] === HPack_Settings_Manager::EXTRA_SETTINGS_KEY) {
            @hp_settings_debug(esc_html__('Processing extra settings...', 'hellopack'));

            if (isset($_POST[HPack_Settings_Manager::EXTRA_SETTINGS_KEY])) {
                $form_data = $_POST[HPack_Settings_Manager::EXTRA_SETTINGS_KEY];
                $woo_param = isset($form_data[HPack_Settings_Manager::EXTRA_WOO_NOTICE]) ? intval($form_data[HPack_Settings_Manager::EXTRA_WOO_NOTICE]) : 0;
                $disable_log = isset($form_data[HPack_Settings_Manager::EXTRA_LOG]) ? hp_clean($form_data[HPack_Settings_Manager::EXTRA_LOG]) : 'no';
                $plugin_space = isset($form_data[HPack_Settings_Manager::EXTRA_PLUGIN_SPACE]) ? hp_clean($form_data[HPack_Settings_Manager::EXTRA_PLUGIN_SPACE]) : 'no';
                $silent_mode = isset($form_data[HPack_Settings_Manager::EXTRA_SILENT_MODE]) ? hp_clean($form_data[HPack_Settings_Manager::EXTRA_SILENT_MODE]) : 'no';
                $disable_plugins = isset($form_data[HPack_Settings_Manager::EXTRA_DISABLE_PLUGINS]) ? hp_clean($form_data[HPack_Settings_Manager::EXTRA_DISABLE_PLUGINS]) : 'no';
            }

            $settings_extra[HPack_Settings_Manager::EXTRA_WOO_NOTICE] = $woo_param;
            $settings_extra[HPack_Settings_Manager::EXTRA_LOG] = $disable_log;
            $settings_extra[HPack_Settings_Manager::EXTRA_PLUGIN_SPACE] = $plugin_space;
            $settings_extra[HPack_Settings_Manager::EXTRA_SILENT_MODE] = $silent_mode;
            $settings_extra[HPack_Settings_Manager::EXTRA_DISABLE_PLUGINS] = $disable_plugins;

            @hp_settings_debug(esc_html__('Saving extra settings:', 'hellopack') . PHP_EOL);
            @hp_settings_debug(hp_print_r($settings_extra, true) . PHP_EOL);

            return $settings_extra;
        }
    }

    public function extra_settings_section()
    {
    }

    public function disable_woo_field()
    {
        echo '<label for="' . esc_attr(HPack_Settings_Manager::EXTRA_WOO_NOTICE) . '"><input type="checkbox" id="' . esc_attr(HPack_Settings_Manager::EXTRA_WOO_NOTICE) . '" name="' . esc_attr(HPack_Settings_Manager::EXTRA_SETTINGS_KEY) . '[' . esc_attr(HPack_Settings_Manager::EXTRA_WOO_NOTICE) . ']" value="1"';
        echo checked(intval($this->settings->disable_woo()), 1);
        echo '/>'; ?><span class="description"><?php esc_html_e('Disable WooCommerce Updater notice', 'hellopack'); ?></span></label><div class="description"><?php esc_html_e('If you enable it, the built-in plugin installer will be hidden.', 'hellopack'); ?></div>
	<?php
    }

    public function disable_log_field()
    {
        echo '<div class="hellopack-toggle"><input class="hellopack-input hellopack-checkbox" type="checkbox" id="' . esc_attr(HPack_Settings_Manager::EXTRA_LOG) . '" name="' . esc_attr(HPack_Settings_Manager::EXTRA_SETTINGS_KEY) . '[' . esc_attr(HPack_Settings_Manager::EXTRA_LOG) . ']" value="yes"';
        echo checked(hp_clean($this->settings->disable_log()), 'yes');
        echo '/>'; ?><span></span></div><div class="description"><?php esc_html_e('Diable the HelloPack logging.', 'hellopack'); ?></div>
	<?php
    }

    public function plugin_space_field()
    {
        echo '<div class="hellopack-toggle"><label for="' . esc_attr(HPack_Settings_Manager::EXTRA_PLUGIN_SPACE) . '"><input type="checkbox" id="' . esc_attr(HPack_Settings_Manager::EXTRA_PLUGIN_SPACE) . '" name="' . esc_attr(HPack_Settings_Manager::EXTRA_SETTINGS_KEY) . '[' . esc_attr(HPack_Settings_Manager::EXTRA_PLUGIN_SPACE) . ']" value="yes"';
        echo checked(hp_clean($this->settings->plugin_space()), 'yes');
        echo '/>'; ?><span></span></label></div><div class="description"><?php esc_html_e('Move the HelloPack menu item under the Plugins menu.', 'hellopack'); ?></div>
<?php
    }

    public function silent_mode_field()
    {
        echo '
        <div class="hellopack-toggle">
        <label for="' . esc_attr(HPack_Settings_Manager::EXTRA_SILENT_MODE) . '"><input type="checkbox" id="' . esc_attr(HPack_Settings_Manager::EXTRA_SILENT_MODE) . '" name="' . esc_attr(HPack_Settings_Manager::EXTRA_SETTINGS_KEY) . '[' . esc_attr(HPack_Settings_Manager::EXTRA_SILENT_MODE) . ']" value="yes"';
        echo checked(hp_clean($this->settings->silent_mode()), 'yes');
        echo '/>'; ?><span></span></label></div><div class="description"><?php esc_html_e('Silent mode disables notifications in WordPress. Attention! Certain plugins may not function properly as a result.', 'hellopack'); ?></div>
        
<?php
    }

// TODO: remove this function
    public function disable_plugins_field()
    {
        echo '
        <div class="hellopack-toggle">
        <label for="' . esc_attr(HPack_Settings_Manager::EXTRA_DISABLE_PLUGINS) . '"><input type="checkbox" id="' . esc_attr(HPack_Settings_Manager::EXTRA_DISABLE_PLUGINS) . '" name="' . esc_attr(HPack_Settings_Manager::EXTRA_SETTINGS_KEY) . '[' . esc_attr(HPack_Settings_Manager::EXTRA_DISABLE_PLUGINS) . ']" value="yes"';
        echo checked(hp_clean($this->settings->disable_plugins()), 'yes');
        echo '/>'; ?><span></span></label></div>  <div class="description"><?php esc_html_e('If you enable it, the built-in plugin installer will be hidden.', 'hellopack'); ?></div>
        
<?php
    }

    //
    //  Callbacks for Settings API ends
    //

    public function page_settings()
    {
        include HP_UPDATER_STATIC_PATH . 'partials/settings.php';
    }

    public function set_tab_settings()
    {
        $this->tab_settings = array(
            static::TAB_ACTIVATION => __('License', 'hellopack'),
            static::TAB_OTHERS => sprintf(__('Extra', 'hellopack')),
        );

        return $this;
    }

    public function get_tab_settings()
    {
        if (empty($this->tab_settings)) {
            $this->set_tab_settings();
        }
        return $this->tab_settings;
    }

    /**
     * Ajax hook to deactivate API License
     */
    public function ajax_deactivate_license()
    {
        if (!function_exists('HPApi')) {
            require_once HP_UPDATER_INC . 'hellopack-functions.php';
        }

        $verified = check_ajax_referer('hp_deactivate_api', false, false);

        if (!$verified) {
            wp_send_json_error(array(
                'error' => __('Ajax request was initiated from unauthorised source.', 'hellopack'),
            ));
        }

        $response = HPApi()->set_initials()->setGetFailurePayload()->deactivate();

        if (isset($response['code'])) {
            $message = __('Unknown error occurred during deactivation process', 'hellopack');
            if (isset($response['error'])) {
                $message = $response['error'];
            } elseif (isset($response['message'])) {
                $message = $response['message'];
            }

            wp_send_json_error(array('error' => $message));
        }

        if (isset($response['success']) && $response['success']) {
            $payload = array(
                'status' => $response['deactivated'] ? 'deactivated' : 'not deactivated',
                'activations' => $response['data']['total_activations_purchased'],
                'used' => $response['data']['total_activations'],
                'remaining' => $response['data']['activations_remaining'],
            );

            HPOM()->disable_activation_status();
            HPOM()->remove_api_key();
            HPOM()->remove_all_schema();

            do_action('hp_api_license_deactivated');

            wp_send_json_success($payload);
        } else {
            $error_response = array(
                'error' => __('API Call could not be made for unknown reason', 'hellopack'),
            );
            wp_send_json_error($error_response);
        }
    }

    /*
         * Ajax handler to check API status
    */
    public function ajax_hp_check_api()
    {
        if (!function_exists('HPApi')) {
            require_once HP_UPDATER_INC . 'hellopack-functions.php';
        }

        $verified = check_ajax_referer('hp_check_api', false, false);

        if (!$verified) {
            wp_send_json_error(array(
                'error' => __('Request is not authorized and recognized as insecure or malicious request to the system.', 'hellopack'),
            ));
        }

        $apiManager = HPApi()->set_initials()->setGetFailurePayload();

        $response = $apiManager->status();

        if ($apiManager->has_error()) {
            if ($apiManager->is_system_error()) {
                HPOM()->remove_subscription_status(true);
            } else {
                HPOM()->subscription_status($response);
            }

            $message = __('Unknown error occurred during deactivation process', 'hellopack');
            if (isset($response['error'])) {
                $message = $response['error'];
            } elseif (isset($response['message'])) {
                $message = $response['message'];
            }

            wp_send_json_error(array('error' => $message));
        }

        if (isset($response['success']) && $response['success']) {
            $payload = array(
                'status' => $response['status_check'],
                'activations' => $response['data']['total_activations_purchased'],
                'used' => $response['data']['total_activations'],
                'remaining' => $response['data']['activations_remaining'],
                'activated' => $response['data']['activated'],
            );

            HPOM()->remove_subscription_status(true);

            if ($response['data']['activated']) {
                if (!HPOM()->license_is_activated()) {
                    HPOM()->enable_activation_status();
                }
            } else {
                HPOM()->disable_activation_status();
            }

            wp_send_json_success($payload);
        } else {
            $error_response = array(
                'error' => __('API Call could not be made for unknown reason', 'hellopack'),
            );
            wp_send_json_error($error_response);
        }
    }

    // Ajax handler for local settings clean up
    public function ajax_clear_local_settings()
    {
        if (!function_exists('HPOM')) {
            require_once HP_UPDATER_INC . 'hellopack-functions.php';
        }

        $verified = check_ajax_referer('hp_cleanup_settings', false, false);

        if (!$verified) {
            wp_send_json_error(array(
                'error' => __('Request is not authorized and recognized as insecure or malicious request to the system.', 'hellopack'),
            ));
        }

        HPOM()->remove_all_schema();
        HPOM()->disable_activation_status();
        HPOM()->remove_api_key();

        wp_send_json_success(array(
            'message' => __('Local settings is cleared!', 'hellopack'),
        ));
    }

    public function maybe_deferred_package($options)
    {
        $package = $options['package'];
        if (false !== strrpos($package, 'hp_delayed_download') && false !== strrpos($package, 'hp_item_id')) {
            parse_str(parse_url($package, PHP_URL_QUERY), $vars);
            if ($vars['hp_item_id']) {
                $options['package'] = $this->api->set_initials()->download(array('product_id' => $vars['hp_item_id']));
            }
        }

        return $options;
    }
}
