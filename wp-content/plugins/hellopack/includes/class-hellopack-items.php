<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class HPack_Items
{
    protected static $singleton;
    /**
     * @var HPack_Updater_Loader $eventEmitter
     */
    protected $eventEmitter;

    /**
     * @var HPack_Settings_Manager $settings
     */
    protected $settings;

    /**
     * @var HPack_API_Manager $api
     */
    private $api;

    public static function instance()
    {
        //		if (is_null(static::$singleton)) {
        //			static::$singleton = new static;
        //		}
        //
        //		return static::$singleton;
        return new static();
    }

    private function __construct()
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        include_once ABSPATH . 'wp-admin/includes/theme.php';
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }

    public function init()
    {
        $this->eventEmitter = HPack_Updater_Loader::instance();
        $this->settings = HPack_Settings_Manager::instance();
        $this->api = HPack_API_Manager::instance();

        // Check for theme & plugin updates.
        $this->eventEmitter->add_filter('http_request_args', $this, 'update_check', 5, 2);

        if (is_admin()) {
            // Inject plugin updates into the response array.
            $this->eventEmitter->add_filter('pre_set_site_transient_update_plugins', $this, 'update_plugins', 999999999, 1);
            $this->eventEmitter->add_filter('pre_set_transient_update_plugins', $this, 'update_plugins', 999999999, 1);

            // customization on transient getter to override our supplied plugins
            $this->eventEmitter->add_filter('site_transient_update_plugins', $this, 'update_plugins', 999999999, 1);

            // Inject theme updates into the response array.
            $this->eventEmitter->add_filter('pre_set_site_transient_update_themes', $this, 'update_themes', 999999999, 1);
            $this->eventEmitter->add_filter('pre_set_transient_update_themes', $this, 'update_themes', 999999999, 1);

            // customization on transient getter to override our supplied themes
            $this->eventEmitter->add_filter('site_transient_update_themes', $this, 'update_themes', 999999999, 1);

            // remove old settings for hellopack items just before setting our own information
            $this->eventEmitter->add_filter('site_transient_update_plugins', $this, 'disable_update_plugins', 999999997, 1);
            $this->eventEmitter->add_filter('site_transient_update_themes', $this, 'disable_update_themes', 999999997, 1);

            // Inject plugin information into the API calls.
            $this->eventEmitter->add_filter('plugins_api', $this, 'plugins_api', 999999999, 3);
            $this->eventEmitter->add_filter('themes_api', $this, 'themes_api', 999999999, 3);

            // force to use native upgrade path rather than premium plugin upgrader
            add_filter('upgrader_pre_download', array($this, 'disable_upgrader_pre_download'), 999999999, 3);
        }
    }

    public function remove_hooks()
    {
        remove_filter('http_request_args', array($this, 'update_check'), 5);

        remove_filter('pre_set_site_transient_update_plugins', array($this, 'update_plugins'), 999999999);
        remove_filter('pre_set_transient_update_plugins', array($this, 'update_plugins'), 999999999);
        remove_filter('site_transient_update_plugins', array($this, 'update_plugins'), 999999999);

        remove_filter('pre_set_site_transient_update_themes', array($this, 'update_themes'), 999999999);
        remove_filter('pre_set_transient_update_themes', array($this, 'update_themes'), 999999999);
        remove_filter('site_transient_update_themes', array($this, 'update_themes'), 999999999);

        remove_filter('site_transient_update_plugins', array($this, 'disable_update_plugins'), 999999997);
        remove_filter('site_transient_update_themes', array($this, 'disable_update_themes'), 999999997);

        remove_filter('plugins_api', array($this, 'plugins_api'), 999999999);
        remove_filter('themes_api', array($this, 'themes_api'), 999999999);
        remove_filter('upgrader_pre_download', array($this, 'disable_upgrader_pre_download'), 999999999);
    }

    public function disable_upgrader_pre_download($reply, $package, $upgrader)
    {
        // TODO: need to identify specific theme or plugin to return false for them only
        return false;
    }

    public function update_check($request, $url)
    {
        if (false !== strpos($url, '//api.wordpress.org/themes/update-check/1.1/')) {
            $installed_themes = $this->installed_themes();

            $data = json_decode($request['body']['themes']);

            foreach ($installed_themes as $slug => $theme) {
                unset($data->themes->{$slug});
            }

            // Encode back into JSON and update the response.
            $request['body']['themes'] = wp_json_encode($data);
        }

        if (false !== strpos($url, '//api.wordpress.org/plugins/update-check/1.1/')) {
            $installed_plugins = $this->installed_plugins();

            // Decode JSON so we can manipulate the array.
            $data = json_decode($request['body']['plugins']);

            // Remove the excluded themes.
            foreach ($installed_plugins as $slug => $plugin) {
                unset($data->plugins->$slug);
            }

            // Encode back into JSON and update the response.
            $request['body']['plugins'] = wp_json_encode($data);
        }

        return $request;
    }

    public function update_plugins($transient)
    {
        if ($this->settings->get_activation_status() === 'Activated') {
            $all_plugins = get_plugins();
            $plugins = $this->installed_plugins();


            if (!empty($plugins)) {
                foreach ($plugins as $key => $plugin) {
                    if (isset($all_plugins[$key])) {
                        if (version_compare($all_plugins[$key]['Version'], $plugin['version'], '<')) {
                            $plugin_url = $this->api->deferred_download($plugin['product_id']);

                            $_plugin = array(
                                'slug' => $plugin['slug'],
                                'plugin' => $key,
                                'new_version' => $plugin['version'],
                                'url' => isset($plugin['url']) ? $plugin['url'] : '',
                                'package' => $plugin_url ? $plugin_url : '',
                            );

                            $transient = isset($transient) && is_object($transient) ? $transient : new stdClass();

                            $transient->response[$key] = (object) $_plugin;
                        } else {
                            if (isset($transient->response[$key])) {
                                unset($transient->response[$key]);
                            }
                        }
                    }
                }
            }
        }
        return $transient;
    }

    public function disable_update_plugins($transient)
    {
        /*
        if ($this->settings->get_activation_status() === 'Activated') {
            $plugins = $this->installed_plugins();

            if (!empty($plugins)) {
                foreach ($plugins as $key => $plugin) {
                    $container = new stdClass();
                    $container->plugin = $key;
                    $transient->response[$key] = $container;
                    $temp = $container;

                    if (isset($transient) && is_object($transient)) {
                        foreach ($temp as $plug) {
                            if (isset($transient->response[$plug])) {
                                unset($transient->response[$plug]);
                            }
                        }
                    }
                }
            }
        }
        */
        return $transient;
    }

    public function disable_update_themes($transient)
    {
        /*
        if ($this->settings->get_activation_status() === 'Activated') {
            $themes = $this->installed_themes();

            if (!empty($themes)) {
                foreach ($themes as $key => $theme) {
                    $container = new stdClass();
                    $container->theme = $key;
                    $transient->response[$key] = $container;
                    $temp = $container;

                    if (isset($transient) && is_object($transient)) {
                        foreach ($temp as $thm) {
                            if (isset($transient->response[$thm])) {
                                unset($transient->response[$thm]);
                            }
                        }
                    }
                }
            }
        }*/
        return $transient;
    }

    public function update_themes($transient)
    {
        if ($this->settings->get_activation_status() === 'Activated') {
            $all_themes = wp_get_themes();
            $themes = $this->installed_themes();

            if (!empty($themes)) {
                foreach ($all_themes as $key => $theme) {
                    if (isset($themes[$key])) {
                        if ($theme->exists()) {
                            if (version_compare($theme->get('Version'), $themes[$key]['version'], '<')) {
                                $theme_url = $this->api->deferred_download($themes[$key]['product_id']);

                                $transient->response[$key] = array(
                                    'theme' => $key,
                                    'new_version' => $themes[$key]['version'],
                                    'url' => $themes[$key]['url'] ? $themes[$key]['url'] : '',
                                    'package' => $theme_url ? $theme_url : '',
                                );
                            } else {
                                if (isset($transient->response[$key])) {
                                    unset($transient->response[$key]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $transient;
    }

    public function plugins_api($response, $action, $args)
    {
        if ($this->settings->get_activation_status() === 'Activated') {
            $installed_plugins = $this->settings->get_available_plugins();

            if (!empty($installed_plugins)) {
                if ('plugin_information' === $action && isset($args->slug)) {
                    foreach ($installed_plugins as $key => $plugin) {
                        if ($plugin['slug'] === $args->slug) {
                            //							$plugin_file = $this->api->set_initials()->download(array('product_id' => $plugin['product_id']));
                            $plugin_file = $this->api->deferred_download($plugin['product_id']);
                            $response = new stdClass();
                            $response->slug = $args->slug;
                            $response->name = !empty($plugin['short_name']) ? $plugin['short_name'] : $plugin['name'];
                            $response->plugin_name = $plugin['plugin_basename'];
                            $response->version = $plugin['version'];
                            $response->author = $plugin['author'];
                            $response->homepage = isset($plugin['url']) ? $plugin['url'] : '';
                            if (isset($plugin['last_updated']) && $plugin['last_updated']) {
                                $response->last_updated = $plugin['last_updated'];
                            }
                            $response->requires = $plugin['wp_version'];
                            $response->tested = $plugin['wp_version_tested'];
                            $response->sections = array('description' => strip_tags($plugin['description']));
                            $response->download_link = $plugin_file ? $plugin_file : '';
                            break;
                        }
                    }
                }
            }
        }

        return $response;
    }

    public function themes_api($response, $action, $args)
    {
        if ($this->settings->get_activation_status() === 'Activated') {
            $installed_themes = $this->settings->get_available_themes();

            if (!empty($installed_themes)) {
                if ('theme_information' === $action && isset($args->slug)) {
                    foreach ($installed_themes as $key => $theme_data) {
                        if ($theme_data['slug'] === $args->slug) {
                            //							$theme_file = $this->api->set_initials()->download(array('product_id' => $theme_data['product_id']));

                            $theme_file = $this->api->deferred_download($theme_data['product_id']);

                            $response = new stdClass();
                            $response->slug = $args->slug;
                            $response->name = !empty($theme_data['short_name']) ? $theme_data['short_name'] : $theme_data['name'];
                            $response->version = $theme_data['version'];
                            $response->author = $theme_data['author'];
                            //							$response->screenshot_url = isset($theme_data['screenshot']) ? $theme_data['screenshot'] : '';
                            if (isset($theme_data['last_updated']) && $theme_data['last_updated']) {
                                $response->last_updated = $theme_data['last_updated'];
                            }
                            $response->requires = $theme_data['wp_version'];
                            $response->requires_php = $theme_data['php_version'];
                            $response->sections = array('description' => strip_tags($theme_data['description']));
                            $response->download_link = $theme_file ? $theme_file : '';
                            break;
                        }
                    }
                }
            }
        }

        return $response;
    }

    protected function installed_themes()
    {
        $installed = wp_get_themes();
        $hp_themes = (array) $this->settings->get_available_themes();

        $result = array();

        foreach ($installed as $key => $theme) {
            if (array_key_exists($key, $hp_themes)) {
                $result[$key] = $hp_themes[$key];
            }
        }

        return $result;
    }

    protected function installed_plugins()
    {
        $installed = get_plugins();
        $hp_plugins = (array) $this->settings->get_available_plugins();

        $result = array();

        foreach ($installed as $key => $plugin) {
            if (array_key_exists($key, $hp_plugins)) {
                $result[$key] = $hp_plugins[$key];
            }
        }

        return $result;
    }
}
