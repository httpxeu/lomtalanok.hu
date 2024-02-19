<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class HPack_Util
{
    protected static $singleton = null;
    /**
     * @var HPack_Settings_Manager $settings
     */
    protected $settings;

    public static function instance()
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    private function __construct()
    {
        $this->settings = HPack_Settings_Manager::instance();
    }

    public function plugin_row_meta($links, $file)
    {
        if (HP_UPDATER_BASENAME === $file and !defined('HELLOPACK_WHITELABEL')) {
            $row_meta = array(
                'docs' => '<a href="' . esc_url(apply_filters('hellopack_docs_url', 'https://hub.hellowp.io/docs/dokumentacio/hellopack/')) . '" aria-label="' . esc_attr__('View HelloPack documentation', 'hellopack') . '">' . esc_html__('Docs', 'hellopack') . '</a>',
                'support' => '<a href="' . esc_url(apply_filters('hellopack_support_url', 'https://hellowp.io/hu/tamogatas/')) . '" aria-label="' . esc_attr__('Visit customer support', 'hellopack') . '">' . esc_html__('Support', 'hellopack') . '</a>',
            );

            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }

    public function actions_links($links)
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=hellopack_settings_manager') . '">' . __('Settings', 'hellopack') . '</a>';

        array_unshift($links, $settings_link);

        return $links;
    }

    public function in_admin_footer()
    {
        /*
        if (static::is_hellopack_area()) { ?>
            <div class="hp_admin_note">
                <p><strong><?php _e('Help &amp; Support', 'hellopack') ?></strong>: <?php printf(__('If you have any question, issue with HelloPack or feedback, please send an email to: <a href="%1$s">support@hellowp.io</a> | <a href="%2$s" target="_blank">Terms &amp; Conditions</a> | <a href="%3$s" target="_blank">Privacy Policy</a>', 'hellopack'), 'mailto:support@hellowp.io', 'https://hellowp.io/hu/aszf/', 'https://hellowp.io/hu/adatvedelem/') ?></p>
            </div>

<?php	}
*/
    }

    public function rename_plugins($plugins)
    {
        $hp_plugins = (array) $this->settings->get_available_plugins();

        if (!empty($hp_plugins)) {
            foreach ($plugins as $key => $plugin) {
                if (array_key_exists($key, $hp_plugins) && !empty($hp_plugins[$key]['short_name'])) {
                    $plugins[$key]['Name'] = $hp_plugins[$key]['short_name'];
                }
            }
        }
        return $plugins;
    }

    public function disable_woothemes_notice()
    {
        if ($this->settings->disable_woo()) {
            add_filter('woocommerce_helper_suppress_admin_notices', '__return_true');
        }
    }

    public static function is_hellopack_area()
    {
        $pages = array(
            'hellopack_plugins_manager',
            'hellopack_themes_manager',
            'hellopack_settings_manager',
        );
        $p_now = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';

        return in_array($p_now, $pages, true);
    }

    public function check_status()
    {
        $apiObj = HPApi()->set_initials()->setGetFailurePayload();
        $status = $apiObj->status();

        if (!$apiObj->has_error()) {
            HPOM()->remove_subscription_status(true);
            return;
        }

        if (!$apiObj->is_remote_error()) {
            HPOM()->remove_subscription_status(true);
            return;
        }

        HPOM()->store_subscription_status($status);
    }

    public function cleanup()
    {
        $main = HPMain();

        remove_action('init', array($this, 'disable_woothemes_notice'));

        remove_action('init', array($main, 'update_schema'));
        remove_action('init', array($main, 'hellopack_six_hours_cron'));
        remove_action('init', array($main, 'load_initial_schema'));

        remove_action('hellopack_six_hours_cron', array($main, 'update_schema'));

        remove_filter('rename_plugins', array($this, 'rename_plugins'));

        HPack_Items::instance()->remove_hooks();

        HPack_Settings_Manager::instance()->remove_all_schema();
    }

    public function inactive_status_notice()
    {
        include HP_UPDATER_STATIC_PATH . 'notices/notice-inactive.php';
    }

    public function log_schema_update($payload, $type, $context)
    {
        /* translators: 1: schema update operation for either Theme or Plugins */
        $heading = sprintf(__('Schema update operation for: %1$s', 'hellopack'), strtoupper($context));

        /* translators: 1: status of fetched schema Error or Success */
        $status = sprintf(__('STATUS: %1$s', 'hellopack'), ucwords($type));
        $data = array();
        $message_for_saved = '';
        $saved_schema = array();

        if ('error' === trim($type)) {
            $data = $payload;
        } elseif ('theme' === trim($context)) {
            $theme_count = is_array($payload) ? count($payload) : 0;
            $data = array('payload' => 'theme-schema', 'total_themes' => $theme_count);
        } else {
            $plugin_count = is_array($payload) ? count($payload) : 0;
            $data = array('payload' => 'plugin-schema', 'total_plugins' => $plugin_count);
        }

        $body = __('Schema Information: ', 'hellopack') . PHP_EOL;
        $body .= hp_print_r($data, true);

        $message = $heading . PHP_EOL . $status . PHP_EOL . $body;

        hp_schema_debug($message);

        if ('success' === trim($type)) {
            $saved_schema = 'theme' === trim($context) ?
                HPOM()->get_available_themes() : HPOM()->get_available_plugins();

            $item_count = is_array($saved_schema) ? count($saved_schema) : 0;

            $message_for_saved .= sprintf(__('%1$s Schema found in the database.', 'hellopack'), strtoupper($context)) . PHP_EOL;
            $message_for_saved .= sprintf(__('Saved %2$s: %1$d', 'hellopack'), $item_count, ucwords($context));

            hp_schema_debug($message_for_saved);
        }
    }
}
