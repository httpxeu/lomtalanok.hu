<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class HPack_Settings_Manager
{
    public const API_SETTINGS_MAIN = 'hellopack_updater_api_settings';
    public const API_SETTINGS_SECTION = 'hellopack_updater_api_settings';

    public const API_DOMAIN = 'api_domain';
    public const API_KEY = 'api_key';

    public const PRODUCT_KEY = 'product_id';

    public const DEACTIVATION_CHECKBOX_KEY = 'hellopack_updater_deactivate_checkbox';
    public const DEACTIVATION_KEY = 'hellopack_updater_activated';
    public const INSTANCE_KEY = 'hellopack_updater_instance';

    public const EXTRA_SETTINGS_KEY = 'hellopack_updater_extras';
    public const EXTRA_SETTINGS_PLUGINS = 'hellopack_updater_extras_plugins';
    public const EXTRA_SECTION = 'hellopack_extra_section';
    public const EXTRA_WOO_NOTICE = 'disable_woo_notice';
    public const EXTRA_LOG = 'disable_log';
    public const EXTRA_PLUGIN_SPACE = 'plugin_space';
    public const EXTRA_SILENT_MODE = 'silent_mode';
    public const EXTRA_DISABLE_PLUGINS = 'disable_plugins';

    public const PLUGINS_ALL = 'hellopack_available_plugins';
    public const THEMES_ALL = 'hellopack_available_themes';

    public const USER_SUBS_STATUS = 'hellopack_subscription_status';

    public const ADMIN_NOTICES_KEY = 'hellopack_admin_notices';
    public const NOTICE_CUSTOM = 'hellopack_admin_notice_';

    protected static $singleton = null;

    public static function instance()
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    public function __construct()
    {
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }

    /**
     * Get the data of all available plugins on HPack server
     *
     * @return array
     */
    public function get_available_plugins()
    {
        return $this->get(static::PLUGINS_ALL, array());
    }

    public function get_by_slug($slug, $type = null, $data = array())
    {
        if (is_null($type)) {
            $plugins = $data;
            if (empty($data)) {
                $plugins = $this->get_available_plugins();
            }

            $result = array_filter($plugins, function ($plugin) use ($slug) {
                return $plugin['slug'] === $slug;
            });

            return current($result);
        }

        $themes = $data;

        if (empty($data)) {
            $themes = $this->get_available_themes();
        }

        $result = array_filter($themes, function ($theme) use ($slug) {
            return $theme['slug'] === $slug;
        });

        return current($result);
    }

    /**
     * Get the data of all available themes on HPack server
     *
     * @return array
     */
    public function get_available_themes()
    {
        return $this->get(static::THEMES_ALL, array());
    }

    /**
     * Get option value for provided option key.
     * @param string $key
     * @param mixed $default
     *
     * @return mixed|false
     */
    public function get($key, $default = false)
    {
        if (is_multisite()) {
            return get_site_option($key, $default);
        }

        return get_option($key, $default);
    }

    /**
     * Add a new option
     *
     * Works both for multi-site and single site WordPress
     *
     * @param $key
     * @param string $value
     * @param string|bool $autoload
     *
     * @return bool
     */
    public function add($key, $value = '', $autoload = 'no')
    {
        if (is_multisite()) {
            $exists = get_site_option($key, false);

            if (false !== $exists) {
                return add_site_option($key, $value);
            } else {
                return update_site_option($key, $value);
            }
        }

        return update_option($key, $value, $autoload);
    }

    /**
     * Updates options for both multi-site and single site
     *
     * @param string $key
     * @param mixed $value
     * @param string|bool $autoload
     *
     * @return bool
     */
    public function update($key, $value, $autoload = 'no')
    {
        if (is_multisite()) {
            return update_site_option($key, $value);
        }

        return update_option($key, $value, $autoload);
    }

    /**
     * Delete an option from WordPress Options on both Multi-site and single site WordPress
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete($key)
    {
        if (is_multisite()) {
            return delete_site_option($key);
        }

        return delete_option($key);
    }

    /**
     * Updates option for total list of available plugins
     *
     * @param array $plugin_lists
     */
    public function update_plugins_catalog($plugin_lists)
    {
        $this->update(static::PLUGINS_ALL, $plugin_lists);
    }

    public function remove_plugins_catalog()
    {
        return $this->delete(static::PLUGINS_ALL);
    }

    public static function clean($input)
    {
        if (is_array($input)) {
            return array_map(array(__CLASS__, 'clean'), $input);
        } else {
            return is_scalar($input) ? sanitize_text_field($input) : $input;
        }
    }

    /**
     * Updates option for total list of available themes
     *
     * @param array $theme_lists
     *
     * @return bool
     */
    public function update_themes_catalog($theme_lists)
    {
        return $this->update(static::THEMES_ALL, $theme_lists);
    }

    public function remove_themes_catalog()
    {
        return $this->delete(static::THEMES_ALL);
    }

    public function remove_api_key()
    {
        return $this->delete(static::API_SETTINGS_MAIN);
    }

    public function remove_all_schema()
    {
        $this->remove_themes_catalog();
        $this->remove_plugins_catalog();
    }

    public function clear_api_settings()
    {
        return $this->update(static::API_SETTINGS_MAIN, array());
    }

    public function has_api_settings()
    {
        return $this->api_key_exists() && $this->product_id_exists();
    }

    public function deactivate_api_settings()
    {
        $this->update(static::API_SETTINGS_MAIN, array());
        $this->update(static::DEACTIVATION_KEY, 'Deactivated');
        $this->update(static::DEACTIVATION_CHECKBOX_KEY, 'off');
    }

    public function set_initial()
    {
        if (empty($this->get(static::INSTANCE_KEY))) {
            $this->update(static::INSTANCE_KEY, hp_generate_password(12, false));
        }

        if (empty($this->get(static::API_SETTINGS_MAIN, array()))) {
            $this->update(static::DEACTIVATION_CHECKBOX_KEY, 'off');
            $this->update(static::DEACTIVATION_KEY, 'Deactivated');
        }
    }

    public function remove_initial()
    {
        foreach (array(
            static::API_SETTINGS_MAIN,
            static::INSTANCE_KEY,
            static::DEACTIVATION_CHECKBOX_KEY,
            static::DEACTIVATION_KEY,
            static::EXTRA_SETTINGS_KEY,
            static::EXTRA_SETTINGS_PLUGINS,
        ) as $option) {
            $this->delete($option);
        }
    }

    public function deactivation()
    {
        foreach (array(
            static::API_SETTINGS_MAIN,
            static::DEACTIVATION_CHECKBOX_KEY,
            static::DEACTIVATION_KEY,
        ) as $option) {
            $this->delete($option);
        }
    }

    public function api_key_exists()
    {
        $api_key = $this->get_api_key();
        return !empty($api_key);
    }

    public function get_api_key($default = false)
    {
        return $this->get_api_settings(static::API_KEY, $default);
    }

    public function get_product_id($default = false)
    {
        return $this->get_api_settings(static::PRODUCT_KEY, $default);
    }

    public function product_id_exists()
    {
        $product_id = $this->get_product_id();

        return !empty($product_id);
    }

    public function update_api_key($key)
    {
        $args = array(
            static::API_KEY => $key,
        );

        return $this->update(static::API_SETTINGS_MAIN, $args);
    }

    public function get_instance_id($default = false)
    {
        return $this->get(static::INSTANCE_KEY, $default);
    }

    public function refresh_instance_id()
    {
        $instance_id = hp_generate_password(12, false);
        $this->update(static::INSTANCE_KEY, $instance_id);

        return $instance_id;
    }

    public function save_api_settings($value)
    {
        return $this->update(static::API_SETTINGS_MAIN, $value);
    }

    public function get_api_settings($key = null, $default = false)
    {
        $settings = $this->get(static::API_SETTINGS_MAIN, array());

        if (empty($settings)) {
            return $default;
        }

        if (is_null($key)) {
            return $settings;
        }

        if (is_array($settings) && key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default;
    }

    public function extra_settings($default = false)
    {
        return $this->get(static::EXTRA_SETTINGS_KEY, $default);
    }

    public function disable_woo($default = false)
    {
        $extra = $this->extra_settings(array());

        if (isset($extra[static::EXTRA_WOO_NOTICE])) {
            return $extra[static::EXTRA_WOO_NOTICE];
        }

        return $default;
    }

    public function disable_log($default = 'no')
    {
        $extra = $this->extra_settings(array());

        if (isset($extra[static::EXTRA_LOG])) {
            return $extra[static::EXTRA_LOG];
        }

        return $default;
    }

    public function plugin_space($default = 'no')
    {
        $extra = $this->extra_settings(array());

        if (isset($extra[static::EXTRA_PLUGIN_SPACE])) {
            return $extra[static::EXTRA_PLUGIN_SPACE];
        }

        return $default;
    }

    public function silent_mode($default = 'no')
    {
        $extra = $this->extra_settings(array());

        if (isset($extra[static::EXTRA_SILENT_MODE])) {
            return $extra[static::EXTRA_SILENT_MODE];
        }

        return $default;
    }

    public function disable_plugins($default = 'no')
    {
        $extra = $this->extra_settings(array());

        if (isset($extra[static::EXTRA_DISABLE_PLUGINS])) {
            return $extra[static::EXTRA_DISABLE_PLUGINS];
        }

        return $default;
    }

    public function is_logging_disabled()
    {
        $disable_log = hp_clean($this->disable_log());

        return ('yes' === $disable_log);
    }

    public function get_activation_status($default = 'Deactivated')
    {
        return trim($this->get(static::DEACTIVATION_KEY, $default));
    }

    public function enable_activation_status()
    {
        return $this->update(static::DEACTIVATION_KEY, 'Activated');
    }

    public function disable_activation_status()
    {
        return $this->update(static::DEACTIVATION_KEY, 'Deactivated');
    }

    public function get_activation_checkbox($default = 'on')
    {
        return trim($this->get(static::DEACTIVATION_CHECKBOX_KEY, $default));
    }

    public function disable_activation_checkbox()
    {
        return $this->update(static::DEACTIVATION_CHECKBOX_KEY, 'off');
    }

    public function enable_activation_checkbox()
    {
        return $this->update(static::DEACTIVATION_CHECKBOX_KEY, 'on');
    }

    public function license_is_activated()
    {
        return $this->get_activation_status() === 'Activated';
    }

    public function disable_api_extra()
    {
        $this->update(static::DEACTIVATION_CHECKBOX_KEY, 'on');
        $this->disable_activation_status();
    }

    public function enable_api_extra()
    {
        $this->update(static::DEACTIVATION_CHECKBOX_KEY, 'off');
        $this->enable_activation_status();
    }

    public function get_notices($default = array())
    {
        return $this->get(static::ADMIN_NOTICES_KEY, $default);
    }

    public function store_notices($notices)
    {
        return $this->update(static::ADMIN_NOTICES_KEY, $notices);
    }

    public function remove_notices()
    {
        return $this->delete(static::ADMIN_NOTICES_KEY);
    }

    public function notice_custom($name, $default = false)
    {
        $key = static::NOTICE_CUSTOM . $name;
        return $this->get($key, $default);
    }

    public function save_notice_custom($name, $value)
    {
        $key = static::NOTICE_CUSTOM . $name;

        return $this->update($key, $value);
    }

    public function remove_notice_custom($name)
    {
        $key = static::NOTICE_CUSTOM . $name;

        return $this->delete($key);
    }

    public function installed_plugins()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $wp_plugins = get_plugins();
        $available_plugins = (array) $this->get_available_plugins();

        $result = array();

        if (!empty($available_plugins)) {
            foreach ($wp_plugins as $key => $plugin) {
                if (array_key_exists($key, $available_plugins)) {
                    $result[$key] = $available_plugins[$key];
                }
            }
        }

        return $result;
    }

    public function installed_themes()
    {
        require_once ABSPATH . 'wp-admin/includes/theme.php';
        $wp_themes = wp_get_themes();
        $available_themes = (array) $this->get_available_themes();

        $result = array();

        if (!empty($available_themes)) {
            foreach ($wp_themes as $key => $plugin) {
                if (array_key_exists($key, $available_themes)) {
                    $result[$key] = $available_themes[$key];
                }
            }
        }

        return $result;
    }

    public function subscription_status($default = array())
    {
        return $this->get(static::USER_SUBS_STATUS, $default);
    }

    public function store_subscription_status($status)
    {
        return $this->update(static::USER_SUBS_STATUS, $status);
    }

    public function remove_subscription_status($keep = false)
    {
        if ($keep) {
            return $this->store_subscription_status(array());
        }
        return $this->delete(static::USER_SUBS_STATUS);
    }
}
