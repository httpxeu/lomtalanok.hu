<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Include the WP_Upgrader class.
if (!class_exists('WP_Upgrader', false)) :
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
endif;

class HPack_Plugin_Upgrader extends Plugin_Upgrader
{
    /**
     * Initialize the upgrade strings.
     *
     * @since 1.0.0
     */
    public function upgrade_strings()
    {
        parent::upgrade_strings();

        $this->strings['downloading_package'] = __('Downloading the HPack upgrade package&#8230;', 'hellopack');
    }

    /**
     * Initialize the install strings.
     *
     * @since 1.0.0
     */
    public function install_strings()
    {
        parent::install_strings();

        $this->strings['downloading_package'] = __('Downloading the HPack install package&#8230;', 'hellopack');
    }

    /**
     * Download a package.
     *
     * @since 2.8.0
     *
     * @param string $package          The URI of the package. If this is the full path to an
     *                                 existing local file, it will be returned untouched.
     * @param bool   $check_signatures Whether to validate file signatures. Default false.
     * @return string|WP_Error The full path to the downloaded package file, or a WP_Error object.
     */
    public function download_package($package, $check_signatures = false)
    {

        /**
         * Filters whether to return the package.
         *
         * @since 3.7.0
         *
         * @param bool        $reply   Whether to bail without returning the package.
         *                             Default false.
         * @param string      $package The package file name.
         * @param WP_Upgrader $this    The WP_Upgrader instance.
         */
        $reply = apply_filters('upgrader_pre_download', false, $package, $this);
        if (false !== $reply) {
            return $reply;
        }

        if (!preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package)) { //Local file or remote?
            return $package; //must be a local file..
        }

        if (empty($package)) {
            return new WP_Error('no_package', $this->strings['no_package']);
        }

        $this->skin->feedback('downloading_package', $package);

        $timeout = apply_filters('hp_download_timeout', 500);
        $download_file = download_url($package, (int) $timeout, $check_signatures);

        if (is_wp_error($download_file) && !$download_file->get_error_data('softfail-filename')) {
            return new WP_Error('download_failed', $this->strings['download_failed'], $download_file->get_error_message());
        }

        return $download_file;
    }

    /**
     * Install a plugin package.
     *
     * @since 2.8.0
     * @since 3.7.0 The `$args` parameter was added, making clearing the plugin update cache optional.
     *
     * @param string $package The full local path or URI of the package.
     * @param array  $args {
     *     Optional. Other arguments for installing a plugin package. Default empty array.
     *
     *     @type bool $clear_update_cache Whether to clear the plugin updates cache if successful.
     *                                    Default true.
     * }
     * @return bool|WP_Error True if the installation was successful, false or a WP_Error otherwise.
     */
    public function install($package, $args = array())
    {
        $defaults = array(
            'clear_update_cache' => true,
        );
        $parsed_args = wp_parse_args($args, $defaults);

        $this->init();
        $this->install_strings();

        add_filter('upgrader_source_selection', array($this, 'check_package'));
        if ($parsed_args['clear_update_cache']) {
            // Clear cache so wp_update_plugins() knows about the new plugin.
            add_action('upgrader_process_complete', 'wp_clean_plugins_cache', 9, 0);
        }

        $this->run(
            array(
                'package' => $package,
                'destination' => WP_PLUGIN_DIR,
                'clear_destination' => true, // Do not overwrite files.
                'clear_working' => true,
                'hook_extra' => array(
                    'type' => 'plugin',
                    'action' => 'install',
                ),
            )
        );

        remove_action('upgrader_process_complete', 'wp_clean_plugins_cache', 9);
        remove_filter('upgrader_source_selection', array($this, 'check_package'));

        if (!$this->result || is_wp_error($this->result)) {
            return $this->result;
        }

        // Force refresh of plugin update information
        wp_clean_plugins_cache($parsed_args['clear_update_cache']);

        return true;
    }
}
