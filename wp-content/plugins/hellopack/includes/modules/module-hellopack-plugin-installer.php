<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function HelloPack_Disable_Plugin_Menu()
{
    if (!defined('HP_DISABLE_PLUGINS_MENU')) {
        define('HP_DISABLE_PLUGINS_MENU', true);
    }
}

$hellopack_updater_extras = get_option("hellopack_updater_extras");

if (isset($hellopack_updater_extras['disable_plugins']) && $hellopack_updater_extras['disable_plugins'] === 'yes') {
    HelloPack_Disable_Plugin_Menu();
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : false;

if (!isset($tab) and !$tab === 'hellopack') {
    return;
}

if (defined('HP_DISABLE_PLUGINS_MENU') && HP_DISABLE_PLUGINS_MENU === true) {
    return;
}

if (!defined('HP_WORDPRESS_PLUGIN_API_SERVER')) {
    define('HP_WORDPRESS_PLUGIN_API_SERVER', 'api.wordpress.org/plugins/info/1.2/');
}

if (!function_exists('Load_Hellopack_Installer_Admin_scripts')) {
    /**
     * Load HelloPack Plugin Installer Scripts
     *
     * @since 1.3.0
     *
     * @return void
     */
    function Load_Hellopack_Installer_Admin_scripts()
    {
        if (isset($_GET['tab']) && $_GET['tab'] === 'hellopack') {
            wp_enqueue_script('module-hellopack-plugin-installer-js', HP_MODULES_URL . '/module-hellopack-plugin-installer/assets/js/module-hellopack-plugin-installer.min.js', array(), HP_UPDATER_VERSION, true);
            wp_enqueue_style('module-hellopack-plugin-installer-css', HP_MODULES_URL . '/module-hellopack-plugin-installer/assets/css/module-hellopack-plugin-installer.min.css', array(), HP_UPDATER_VERSION, 'all');
        }
    }
    add_action('admin_enqueue_scripts', 'Load_Hellopack_Installer_Admin_scripts');
}

if (!function_exists('Add_Hellopack_Plugin_installer')) {
    /**
     * Load HelloPack Plugin Installer
     *
     * @since 1.3.0
     *
     * @return void
     */
    function Add_Hellopack_Plugin_installer()
    {
        if (get_option('hellopack_updater_api_settings')) {
            $response_api_api_settings = get_option('hellopack_updater_api_settings');
            $response_api_api_key = $response_api_api_settings['api_key'];

            $key = hp_set_key($response_api_api_key);
            $product_id = $response_api_api_settings['product_id'];

            $response_api_url_site_url = get_site_url();
            $response_api_url_parsed_url = parse_url($response_api_url_site_url);
            $object = $response_api_url_parsed_url['host'];
        }

        if (get_option('hellopack_updater_instance')) {
            $instance = get_option('hellopack_updater_instance');
        }

        if (class_exists('HPack_Set_API_Servers')) {
            $translation = new HPack_Set_API_Servers();
            $translation->set_api_servers(HP_WORDPRESS_PLUGIN_API_SERVER . '?action=query_plugins&request%5Bauthor%5D=hellopack', HP_PLUGIN_INSTALLER_SERVER . '/list/?out=');
            $translation->init();

            $plugininfo = new HPack_Set_API_Servers();
            $plugininfo->set_api_servers(HP_WORDPRESS_PLUGIN_API_SERVER . '?action=plugin_information&request%5Bslug%5D=hellopack-', HP_PLUGIN_INSTALLER_SERVER . '/plugin-info/?key=' . $key . '&instance=' . $instance . '&object=' . $object . '&product_id=' . $product_id . '&out=');
            $plugininfo->init();
        }
    }

    add_action('plugins_loaded', 'Add_Hellopack_Plugin_installer');
}


if (!function_exists('Hellopack_Plugins_tab')) {
    /**
     * Add hellopack to plugin tabs.
     *
     * @since 1.3.0
     *
     * @param array $tabs Default plugin tabs.
     *
     * @return array
     */
    function Hellopack_Plugins_tab($tabs)
    {
        return array_merge($tabs, [
            'hellopack' => __('HelloPack', 'hellopack').' <span style="color: green;">BETA</span>',
        ]);
    }
    add_filter('install_plugins_tabs', 'Hellopack_Plugins_tab');
}


if (!function_exists('Display_Hellopack_Plugins_tables')) {
    /**
     * Display HelloPack Plugins Tables.
     *
     * @since 1.3.0
     *
     * @return void
     */
    function Display_Hellopack_Plugins_tables()
    {
        if (isset($_GET['tab']) && !$_GET['tab'] === 'hellopack') {
            return;
        }

        if (isset($_GET['s']) && is_string($_GET['s'])) {
            $search = trim($_GET['s']);
            if (!preg_match("/[<>]/", $search)) {
                $placeholder = (strlen($search) > 0) ? $search : __('Search HelloPack plugins...', 'hellopack');
            } else {
                $search = '';

                $placeholder = __('Search HelloPack plugins...', 'hellopack');
            }
        } else {
            $search = '';
            $placeholder = __('Search HelloPack plugins...', 'hellopack');
        }

        echo '<div id="hellopack-search"><p>'. __('Install plugins from the HelloPack repository.', 'hellopack').'</p>';

        echo '<form id="hellopack-search-form" action="/wp-admin/plugin-install.php?tab=hellopack" class="search-hellopack-plugins" method="get">
     <input type="hidden" name="tab" value="hellopack">
     <label class="screen-reader-text" for="search-plugins">Search</label>
     <input type="search" name="s" id="search-plugins" value="' . $search . '" class="wp-hellopack-filter-search" placeholder="' . $placeholder . '">
     <input type="submit" id="search-submit" class="button" value="'.__('Search', 'hellopack').'">	</form></div>';
    }
}


if (!function_exists('Hellopack_Plugin_List_args')) {
    /**
     * Set hellopack tab args.
     *
     * @since 1.3.0
     *
     * @param $args
     *
     * @return mixed
     */

    function Hellopack_Plugin_List_args($args)
    {
        if (get_option('hellopack_updater_api_settings')) {
            $response_api_api_settings = get_option('hellopack_updater_api_settings');
            $response_api_api_key = $response_api_api_settings['api_key'];
            $response_api_api_key = hp_set_key($response_api_api_key);
            $response_api_product_id = $response_api_api_settings['product_id'];
        }

        if (get_option('hellopack_updater_instance')) {
            $response_api_updater_instance = get_option('hellopack_updater_instance');
        }

        $response_api_url_site_url = get_site_url();
        $response_api_url_parsed_url = parse_url($response_api_url_site_url);
        $response_api_url_host = $response_api_url_parsed_url['host'];

        $search = isset($_GET['s']) ? urlencode($_GET['s']) : '';
        $paged = isset($_GET['paged']) ? urlencode($_GET['paged']) : '';

        add_action('install_plugins_hellopack', 'Display_Hellopack_Plugins_tables');
        add_action('install_plugins_hellopack', 'display_plugins_table');

        $args['author']   = 'hellopack' . '&s=' . $search . '&paged=' . $paged . '&key=' . $response_api_api_key . '&instance=' . $response_api_updater_instance . '&object=' . $response_api_url_host . '&product_id=' . $response_api_product_id;

        $args['per_page'] = 36;

        return $args;
    }

    add_filter('install_plugins_table_api_args_hellopack', 'Hellopack_Plugin_List_args');
}
