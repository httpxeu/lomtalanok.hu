<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    HPack_Updater
 * @subpackage HPack_Updater/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    HPack_Updater
 * @subpackage HPack_Updater/includes
 * @author     HelloPack <support@hellowp.io>
 */
class HPack_Updater_Deactivator
{
    /**
     * Run this method during plugin activation
     *
     *
     * @since    1.0.0
     */
    public static function deactivate()
    {
        foreach (array(
            'hellopack_thrice_daily_cron',
            'hellopack_six_hours_cron',
            'hellopack_hourly_cron',
            'hellopack_two_hourly_cron',
            'hellopack_five_minutes_cron',
        ) as $cron_action) {
            wp_clear_scheduled_hook($cron_action);
        }

        if (!class_exists('HPack_Settings_Manager', false)) {
            require_once HP_UPDATER_INC . 'settings/class-hellopack-settings-manager.php';
        }

        if (!function_exists('HPUtil')) {
            require_once HP_UPDATER_INC . 'hellopack-functions.php';
        }

        $settings_manager = HPack_Settings_Manager::instance();

        try {
            if ($settings_manager->has_api_settings()) {
                HPApi()->set_initials()->deactivate();
            }
        } catch (Exception $e) {
        }
        HPUtil()->cleanup();

        //		$settings_manager->deactivation();
    }
}
