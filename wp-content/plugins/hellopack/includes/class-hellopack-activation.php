<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    HPack_Updater
 * @subpackage HPack_Updater/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    HPack_Updater
 * @subpackage HPack_Updater/includes
 * @author     HelloPack <support@hellowp.io>
 */
class HPack_Updater_Activator
{

    /**
     * Run this method during plugin activation
     *
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        if (!wp_next_scheduled('hellopack_six_hours_cron')) {
            wp_schedule_event(time(), 'hp_fourtimes', 'hellopack_six_hours_cron');
        }

        if (!class_exists('HPack_Settings_Manager', false)) {
            require_once HP_UPDATER_INC . 'settings/class-hellopack-settings-manager.php';
        }

        if (!function_exists('HPApi')) {
            require_once HP_UPDATER_INC . 'hellopack-functions.php';
        }

        $settings_manager = HPack_Settings_Manager::instance();

        $settings_manager->set_initial();

        @hp_create_files();
    }
}
