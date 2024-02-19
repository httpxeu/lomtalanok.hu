<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Include the WP_Upgrader_Skin class.
if (!class_exists('WP_Upgrader_Skin', false)) :
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skins.php';
endif;

class HPack_Plugin_Installer_Skin extends Plugin_Installer_Skin
{
    /**
     * Modify the install actions.
     *
     * @since 1.0.0
     */
    public function after()
    {
        $plugin_file = $this->upgrader->plugin_info();
        $install_actions = array();

        if (current_user_can('activate_plugins')) {
            $install_actions['activate_plugin'] = '<a href="' . esc_url(wp_nonce_url('plugins.php?action=activate&amp;plugin=' . urlencode($plugin_file), 'activate-plugin_' . $plugin_file)) . '" target="_parent">' . __('Activate Plugin', 'hellopack') . '</a>';
        }

        if (is_multisite()) {
            unset($install_actions['activate_plugin']);

            if (current_user_can('manage_network_plugins')) {
                $install_actions['network_activate'] = '<a href="' . esc_url(network_admin_url(wp_nonce_url('plugins.php?action=activate&amp;plugin=' . urlencode($plugin_file), 'activate-plugin_' . $plugin_file))) . '" target="_parent">' . __('Network Activate', 'hellopack') . '</a>';
            }
        }

        $install_actions['plugins_page'] = '<a href="' . esc_url(admin_url('admin.php?page=' . HPack_Admin::SLUG_PLUGINS)) . '" target="_parent">' . __('Return to Plugin Installer', 'hellopack') . '</a>';

        if (!$this->result || is_wp_error($this->result)) {
            unset($install_actions['activate_plugin'], $install_actions['site_activate'], $install_actions['network_activate']);
        }

        if (!empty($install_actions)) {
            $this->feedback(implode(' | ', $install_actions));
        }
    }
}
