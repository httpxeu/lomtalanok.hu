<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!function_exists('HPEmitter')) {
    /**
     * @return HPack_Updater_Loader
     */
    function HPEmitter()
    {
        if (!class_exists('HPack_Updater_Loader', false)) {
            require_once HP_UPDATER_INC . 'class-hellopack-loader.php';
        }

        return HPack_Updater_Loader::instance();
    }
}

/**
 * @return HPack_Settings_Manager
 */
function HPOM()
{
    if (!class_exists('HPack_Settings_Manager', false)) {
        require_once HP_UPDATER_INC . 'settings/class-hellopack-settings-manager.php';
    }

    return HPack_Settings_Manager::instance();
}

/**
 * @return HPack_API_Manager
 */
function HPApi()
{
    if (!class_exists('HPack_API_Manager', false)) {
        require_once HP_UPDATER_INC . 'api/class-hellopack-api-manager.php';
    }

    return HPack_API_Manager::instance();
}

if (!function_exists('HPUtil')) :
    function HPUtil()
    {
        if (!class_exists('HPack_Util', false)) {
            require_once HP_UPDATER_INC . 'api/class-hellopack-util.php';
        }

        return HPack_Util::instance();
    }
endif;

if (!function_exists('HPMain')) :
    /**
     * @return HPack_Updater
     */
    function HPMain()
    {
        if (!class_exists('HPack_Updater', false)) {
            require HP_UPDATER_INC . 'class-hellopack-updater.php';
        }

        return HPack_Updater::instance();
    }
endif;

if (!function_exists('hp_doing_it_wrong')) :
    function hp_doing_it_wrong($function, $message, $version)
    {
        // @codingStandardsIgnoreStart
        $message .= ' Backtrace: ' . wp_debug_backtrace_summary();

        if (wp_doing_ajax() || hp_is_rest_request()) {
            do_action('doing_it_wrong_run', $function, $message, $version);
            error_log("{$function} was called incorrectly. {$message}. This message was added in version {$version}.");
        } else {
            _doing_it_wrong($function, $message, $version);
        }
    }
endif;

if (!function_exists('hp_is_rest_request')) :
    function hp_is_rest_request()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $rest_prefix = trailingslashit(rest_get_url_prefix());
        $is_rest_api_request = (false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix)); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        return apply_filters('hellopack_is_rest_api_request', $is_rest_api_request);
    }
endif;

if (!function_exists('hp_create_files')) :
    function hp_create_files()
    {
        $files = array(
            array(
                'base' => HP_UPDATER_LOG_DIR,
                'file' => '.htaccess',
                'content' => 'deny from all',
            ),
            array(
                'base' => HP_UPDATER_LOG_DIR,
                'file' => 'index.html',
                'content' => '',
            ),
        );

        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                $file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w'); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
                if ($file_handle) {
                    fwrite($file_handle, $file['content']); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
                    fclose($file_handle); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
                }
            }
        }
    }
endif;

if (!function_exists('hp_logger')) :
    function hp_logger()
    {
        static $logger = null;

        $class = apply_filters('hellopack_logging_class', 'HPack_Logger');

        if (null !== $logger && is_string($class) && is_a($logger, $class)) {
            return $logger;
        }

        $implements = class_implements($class);

        if (is_array($implements) && in_array('HPack_Logger_Interface', $implements, true)) {
            $logger = is_object($class) ? $class : new $class();
        } else {
            hp_doing_it_wrong(
                __FUNCTION__,
                sprintf(
                    /* translators: 1: class name 2: hellopack_logging_class 3: HPack_Logger_Interface */
                    __('The class %1$s provided by %2$s filter must implement %3$s.', 'hellopack'),
                    '<code>' . esc_html(is_object($class) ? get_class($class) : $class) . '</code>',
                    '<code>hellopack_logging_class</code>',
                    '<code>HPack_Logger_Interface</code>'
                ),
                '2.1.0'
            );

            $logger = is_a($logger, 'HPack_Logger') ? $logger : new HPack_Logger();
        }

        return $logger;
    }
endif;

if (!function_exists('hp_log')) :
    function hp_log($type, $message, $context = array())
    {
        $logger = hp_logger();
        $type = strtolower($type);

        if (HPOM()->is_logging_disabled()) {
            return;
        }

        if (method_exists($logger, $type)) {
            $context['source'] = isset($context['source']) ? $context['source'] : 'hp-debug';
            $logger->{$type}($message, $context);
        }
    }
endif;

if (!function_exists('hp_debug')) :
    function hp_debug($message)
    {
        $logger = hp_logger();

        if (HPOM()->is_logging_disabled()) {
            return;
        }

        $logger->debug($message, array('source' => 'debug-log'));
    }
endif;

if (!function_exists('hp_api_debug')) :
    function hp_api_debug($message)
    {
        $logger = hp_logger();

        if (HPOM()->is_logging_disabled()) {
            return;
        }

        $logger->debug($message . PHP_EOL, array('source' => 'api-debug-log'));
    }
endif;

if (!function_exists('hp_schema_debug')) :
    function hp_schema_debug($message)
    {
        $logger = hp_logger();

        if (HPOM()->is_logging_disabled()) {
            return;
        }

        $logger->debug($message . PHP_EOL, array('source' => 'schema-debug-log'));
    }
endif;

if (!function_exists('hp_settings_debug')) :
    function hp_settings_debug($message)
    {
        $logger = hp_logger();

        if (HPOM()->is_logging_disabled()) {
            return;
        }

        $logger->debug($message, array('source' => 'settings-debug-log'));
    }
endif;

if (!function_exists('hp_installed_plugins_data')) {
    function hp_installed_plugins_data()
    {
        $hp_plugins = HPOM()->get_available_plugins();
        $result = array();

        if (empty($hp_plugins)) {
            return $result;
        }

        $installed = get_plugins();

        $active_plugins = get_option('active_plugins', array());

        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }

        foreach ($installed as $key => $plugin) {
            // consider plugins excluding HPack Updater and only those are supplied by hellowp.io
            if (array_key_exists($key, $hp_plugins) && strpos($key, 'hellopack-updater') === false) {
                $plugin_data = hp_take($hp_plugins[$key], array('product_id', 'version', 'type'));
                $plugin_data['installed_version'] = $plugin['Version'];
                $plugin_data['is_active'] = (in_array($key, $active_plugins) || array_key_exists($key, $active_plugins));
                $result[$key] = $plugin_data;
            }
        }

        return $result;
    }
}

if (!function_exists('hp_installed_themes_data')) {
    function hp_installed_themes_data()
    {
        $hp_themes = HPOM()->get_available_themes();
        $result = array();

        if (empty($hp_themes)) {
            return $result;
        }

        $installed = wp_get_themes();

        $active_theme = wp_get_theme();
        $main_theme = $active_theme->parent() ? $active_theme->parent() : $active_theme;

        foreach ($installed as $key => $theme) {
            if (array_key_exists($key, $hp_themes)) {
                $theme_data = hp_take($hp_themes[$key], array('product_id', 'version', 'type'));
                $theme_data['installed_version'] = $main_theme->get('Version');
                $theme_data['is_active'] = $main_theme->get_template() === $key;
                $result[$key] = $theme_data;
            }
        }

        return $result;
    }
}

if (!function_exists('hp_installed_themes')) {
    function hp_installed_themes()
    {
        return array_keys(wp_get_themes());
    }
}

if (!function_exists('hp_installed_plugins')) {
    function hp_installed_plugins()
    {
        return array_keys(get_plugins());
    }
}

if (!function_exists('hp_license_maybe_active')) {
    function hp_license_maybe_active()
    {
        return HPOM()->api_key_exists() && HPOM()->product_id_exists() && strtolower(HPOM()->get_activation_status()) === 'activated';
    }
}

if (!function_exists('hp_set_key')) {
    function hp_set_key($value)
    {
        $salt = 'a7c9e1d3f8b60d54f97e2896ba4f6173';
        return base64_encode(md5($salt) . $value . md5(md5($salt)));
    }
}

if (!function_exists('HP_check_options')) {
    function HP_check_options($name, $value)
    {
        $current_status = get_option($name);
        if ($current_status) {
            update_option($name, $value);
        } else {
            add_option($name, $value, '', 'yes');
        }
    }
}
