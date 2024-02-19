<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * This file contains definition of the core of the plugin
 *
 * @since 1.0.0
 * @package HPack_Updater
 * @subpackage HPack_Updater/includes
 */

/**
 * Class HPack_Updater
 */
final class HPack_Updater
{
    protected static $singleton = null;

    protected $version;

    protected $plugin_name;

    protected $plugin_basename;

    /**
     * @var HPack_Updater_Loader
     */
    protected $emitter;

    public static function instance()
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    public function __construct()
    {
        $this->version = HP_UPDATER_VERSION;
        $this->plugin_name = HP_UPDATER_NAME;
        $this->plugin_basename = HP_UPDATER_BASENAME;

        $this->base_includes();
        $this->set_locale();
        $this->dismiss_third_party_notices();
        $this->initial_hooks();
        $this->admin_panel_hooks();

        do_action('hp_instantiated');
    }

    /**
     * Sets the internationalization for the plugin
     */
    private function set_locale()
    {
        $this->emitter->add_action('plugins_loaded', $this, 'load_text_domain');
    }

    public function load_text_domain()
    {
        load_plugin_textdomain(
            'hellopack',
            false,
            HP_UPDATER_PATH . 'languages'
        );
    }

    private function base_includes()
    {
        require_once HP_UPDATER_INC . 'hellopack-helpers.php';
        require_once HP_UPDATER_INC . 'class-hellopack-loader.php';
        require_once HP_UPDATER_INC . 'settings/class-hellopack-settings-manager.php';
        require_once HP_UPDATER_INC . 'class-hellopack-util.php';
        require_once HP_UPDATER_INC . 'logger/load.php';
        //TODO: Need to review this logger functionality
        require_once HP_UPDATER_INC . 'hellopack-functions.php';
        require_once HP_UPDATER_INC . 'api/class-hellopack-api-manager.php';
        require_once HP_UPDATER_INC . 'settings/class-hellopack-admin.php';
        require_once HP_UPDATER_INC . 'class-hellopack-items.php';


        $this->emitter = HPack_Updater_Loader::instance();
        HPack_Items::instance()->init();
    }

    public function run()
    {
        $this->emitter->run();
    }

    private function admin_panel_hooks()
    {
        /** @var HPack_Admin $hp_admin */
        $hp_admin = HPack_Admin::instance();

        /** @var HPack_Util $hp_util */
        $hp_util = HPack_Util::instance();

        $this->emitter->add_filter('cron_schedules', $this, 'cron_schedules');

        $this->emitter->add_action('admin_menu', $hp_admin, 'init_menu');
        $this->emitter->add_action('admin_init', $hp_admin, 'register_settings');
        $this->emitter->add_action('admin_enqueue_scripts', $hp_admin, 'admin_scripts');
        $this->emitter->add_action('in_admin_footer', $hp_util, 'in_admin_footer');

        $this->emitter->add_filter('plugin_row_meta', $hp_util, 'plugin_row_meta', 10, 2);
        $this->emitter->add_filter('plugin_action_links_' . HP_UPDATER_BASENAME, $hp_util, 'actions_links');

        $this->emitter->add_action('admin_print_styles', $this, 'initial_notices');
        $this->emitter->add_action('init', $hp_util, 'disable_woothemes_notice');

        if (defined('HPACKS_RENAME_PLUGINS') && HPACKS_RENAME_PLUGINS) {
            $this->emitter->add_filter('all_plugins', $hp_util, 'rename_plugins');
        }

        $this->emitter->add_action('wp_ajax_hp_activate_api', $hp_admin, 'ajax_activate_api_key');
        $this->emitter->add_action('wp_ajax_hp_check_api', $hp_admin, 'ajax_hp_check_api');
        $this->emitter->add_action('wp_ajax_hp_deactivate_api', $hp_admin, 'ajax_deactivate_license');
        $this->emitter->add_action('wp_ajax_hp_deactivate_api', $hp_admin, 'ajax_clear_local_settings');
        $this->emitter->add_action('wp_ajax_hp_cleanup_settings', $hp_admin, 'ajax_clear_local_settings');
        $this->emitter->add_filter('upgrader_package_options', $hp_admin, 'maybe_deferred_package', 11);

        // action hooks for logging
        //   $this->emitter->add_action('hellopack_log_schema_update', $hp_util, 'log_schema_update', 10, 3);
    }

    private function initial_hooks()
    {
        //   $this->emitter->add_action('plugins_loaded', $this, 'create_log_files');
        $this->emitter->add_action('hp_api_license_activated', $this, 'load_initial_schema');

        $this->emitter->add_action('init', $this, 'hellopack_six_hours_cron');

        $this->emitter->add_action('hellopack_six_hours_cron', $this, 'update_schema');

        $hp_util = HPack_Util::instance();
        $this->emitter->add_action('wp_version_check', $hp_util, 'check_status');

        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            add_action('init', array($this, 'update_schema'));
        }

        //  $this->emitter->add_filter('hellopack_register_log_handlers', $this, 'default_log_handlers');
    }

    public function default_log_handlers($handlers)
    {
        if (defined('HP_UPDATER_LOG_HANDLER') && class_exists(HP_UPDATER_LOG_HANDLER)) {
            $handler_class = HP_UPDATER_LOG_HANDLER;
            $default_handler = new $handler_class();
        } else {
            $default_handler = new HPack_Log_Handler_File();
        }

        array_push($handlers, $default_handler);

        return $handlers;
    }

    public function create_log_files()
    {
        if (wp_is_writable(HP_UPDATER_LOG_DIR)) {
            return;
        }

        //  @hp_create_files();
    }

    public function update_schema()
    {
        $activated_status = get_option('hellopack_updater_activated', 'Deactivated');

        if ('Activated' === trim($activated_status)) {
            $current_time = current_time('timestamp');
            if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
                $next_run = HPOM()->get('hellopack_next_fetch_schema', 0);

                if ($next_run > $current_time) {
                    return;
                }
            }

            $schema = HPApi()->set_initials()->setGetFailurePayload()->schema();

            $this->update_plugins($schema);

            $this->update_themes($schema);

            if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
                $next_run = $current_time + MINUTE_IN_SECONDS;
                HPOM()->update('hellopack_next_fetch_schema', $next_run);
            }
        }
    }

    private function update_plugins($schema)
    {
        $decoded_plugins = $schema;
        $log_type = 'success';

        if (!isset($schema['code'])) {
            $decoded_plugins = $schema['plugins'];
            HPOM()->update_plugins_catalog($decoded_plugins);
        } else {
            $log_type = 'error';
        }

        do_action('hellopack_log_schema_update', $decoded_plugins, $log_type, 'plugin');
    }

    private function update_themes($schema)
    {
        $decoded_themes = $schema;
        $log_type = 'success';

        if (!isset($schema['code'])) {
            $decoded_themes = $schema['themes'];
            HPOM()->update_themes_catalog($decoded_themes);
        } else {
            $log_type = 'error';
        }

        do_action('hellopack_log_schema_update', $decoded_themes, $log_type, 'theme');
    }

    public function initial_notices()
    {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $show_on_screens = array(
            'dashboard',
            'plugins',
            'themes',
            'toplevel_page_hellopack_settings_manager',
            'hello_page_hellopack_themes_manager',
            'hello_page_hellopack_settings_manager',
        );

        $updater_space = array(
            'hello_page_hellopack_settings_manager',
        );

        // Notices should only show on WooCommerce screens, the main dashboard, and on the plugins screen.
        if (!in_array($screen_id, $show_on_screens, true)) {
            return;
        }

        if (!HPOM()->api_key_exists() && !HPOM()->license_is_activated()) {
            add_action('admin_notices', array($this, 'activation_notice'));
        }

        if (!empty(HPOM()->subscription_status())) {
            add_action('admin_notices', array($this, 'status_notice'));
        }

        if (defined('WP_HTTP_BLOCK_EXTERNAL') && WP_HTTP_BLOCK_EXTERNAL) {
            $host = parse_url(HP_UPDATER_API_URL, PHP_URL_HOST);

            if (!defined('WP_ACCESSIBLE_HOSTS') || stristr(WP_ACCESSIBLE_HOSTS, $host) === false) {
                add_action('admin_notices', array($this, 'external_block_notice'));
            }
        }
    }

    public function load_initial_schema()
    {
        $activated_status = get_option('hellopack_updater_activated', 'Deactivated');

        if ('Activated' === trim($activated_status)) {
            $plugins_schema = HPOM()->get_available_plugins();
            $themes_schema = HPOM()->get_available_themes();

            if (empty($plugins_schema) || empty($themes_schema)) {
                $schema = HPApi()->set_initials()->setGetFailurePayload()->schema();

                if (empty($plugins_schema)) {
                    $this->update_plugins($schema);
                }

                if (empty($themes_schema)) {
                    $this->update_themes($schema);
                }
            }
        }
    }

    public function cron_schedules($schedules)
    {
        $schedules['hp_thricedaily'] = array(
            'interval' => HOUR_IN_SECONDS,
            'display' => __('Thrice daily', 'hellopack'),
        );

        $schedules['hp_fourtimes'] = array(
            'interval' => 10 * MINUTE_IN_SECONDS, // 10mins check
            'display' => __('Four times daily', 'hellopack'),
        );

        $schedules['hp_two_hours'] = array(
            'interval' => HOUR_IN_SECONDS,
            'display' => __('Every two hours', 'hellopack'),
        );

        /* Updater cron every sec
        $schedules['hp_thricedaily'] = array(
            'interval' => 8 * HOUR_IN_SECONDS,
            'display' => __('Thrice daily', 'hellopack'),
        );

        $schedules['hp_fourtimes'] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display' => __('Four times daily', 'hellopack'),
        );

        $schedules['hp_two_hours'] = array(
            'interval' => 2 * HOUR_IN_SECONDS,
            'display' => __('Every two hours', 'hellopack'),
        );
*/
        return $schedules;
    }

    public function hellopack_six_hours_cron()
    {
        if (!wp_next_scheduled('hellopack_six_hours_cron')) {
            wp_schedule_event(time(), 'hp_fourtimes', 'hellopack_six_hours_cron');
        }
    }

    public function activation_notice()
    {
        include HP_UPDATER_STATIC_PATH . 'notices/html-notice-activate.php';
    }

    public function status_notice()
    {
        include HP_UPDATER_STATIC_PATH . 'notices/html-notice-status.php';
    }

    public function cron_notice()
    {
        include HP_UPDATER_STATIC_PATH . 'notices/html-notice-cron.php';
    }

    public function external_block_notice()
    {
        include HP_UPDATER_STATIC_PATH . 'notices/html-notice-external-block.php';
    }

    public function user_subs_pending_notice()
    {
        include HP_UPDATER_STATIC_PATH . 'notices/notice-pending-subscription.php';
    }

    private function dismiss_third_party_notices()
    {
        // Disable Brainstorm Force license notices
        add_action('admin_notices', array($this, 'hp_discard_bsf_update_notices'), -10);

        // Disable Elementor Pro license notices
        add_action('after_setup_theme', array($this, 'hp_discard_elementor_update_notices'), PHP_INT_MAX);
    }

    public function hp_discard_bsf_update_notices()
    {
        define('BSF_PRODUCTS_NOTICES', false);
    }

    public function hp_discard_elementor_update_notices()
    {
        if (class_exists('\ElementorPro\Plugin', false)) {
            remove_action('admin_notices', array(\ElementorPro\Plugin::instance()->license_admin, 'admin_license_details'), 20);
        }
    }
}
