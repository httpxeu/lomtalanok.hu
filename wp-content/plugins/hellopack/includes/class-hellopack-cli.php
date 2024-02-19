<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_CLI')) {
    return;
}

/**
 * Handles the activation, deactivation, and status checking of the HelloPack plugin license.
 *
 * This class defines all the necessary methods for handling license activation, deactivation, and status checking. It communicates with the HelloPack API to perform these actions. It provides three WP CLI commands for managing the plugin's license.
 *
 * @since      1.2.10
 * @package    HPack_Updater
 * @subpackage HPack_Updater/includes
 * @author     HelloPack <support@hellowp.io>
 */

class HelloPack_Command
{
    public function activate_license($args, $assoc_args)
{
    if (empty($args)) {
        WP_CLI::error('License key is missing!');
    }

    $license_key = $args[0];
    $object = parse_url(home_url(), PHP_URL_HOST);
    $instance = get_option('hellopack_updater_instance');
    $product_id = 51507;
    $url = 'https://helloapi.wp-json.app/wp-json/helloup/v2/api/?api_key=' . $license_key . '&instance=' . $instance . '&wc-api=wc-am-api&object='.$object.'&product_id=' . $product_id . '&wc_am_action=activate';
    $response = wp_remote_get($url);
    $body = wp_remote_retrieve_body($response);

    $json = json_decode($body, true);

    if (isset($json['activated']) && $json['activated'] == 'true') {
        update_option('hellopack_updater_activated', 'Activated');
        update_option('hellopack_updater_api_settings', array(
            'api_key' => $license_key,
            'product_id' => 51507,
        ));
        WP_CLI::success('License activated successfully!');
    } elseif ($json['code'] == '100') {
        if (get_option('hellopack_updater_activated') == 'Deactivated') {
            update_option('hellopack_updater_activated', 'Activated');
            update_option('hellopack_updater_api_settings', array(
                'api_key' => $license_key,
                'product_id' => 51507,
            ));
            WP_CLI::success('License activated successfully!');
        } else {
            WP_CLI::error('Cannot activate API Key. The API Key has already been activated with the same unique instance ID sent with this request.');
        }
    } else {
        WP_CLI::error('License activation failed!');
    }
}

    public function deactivate_license($args, $assoc_args)
    {
        if (get_option('hellopack_updater_activated') == 'Activated') {
            $license_key = get_option('hellopack_updater_api_settings')['api_key'];
            $object = parse_url(home_url(), PHP_URL_HOST);
            $instance = get_option('hellopack_updater_instance');
            $product_id = 51507;
            $url = 'https://helloapi.wp-json.app/wp-json/helloup/v2/api/?api_key=' . $license_key . '&instance=' . $instance . '&wc-api=wc-am-api&object='.$object.'&product_id=' . $product_id . '&wc_am_action=deactivate';
            $response = wp_remote_get($url);

            delete_option('hellopack_updater_api_settings');
            update_option('hellopack_updater_activated', 'Deactivated');
            WP_CLI::success('License deactivated successfully!');
        } else {
            WP_CLI::error('License is not activated!');
        }
    }

    public function check_license($args, $assoc_args)
    {
        if (get_option('hellopack_updater_activated') == 'Activated') {
            $api_key = get_option('hellopack_updater_api_settings')['api_key'];
            $instance = get_option('hellopack_updater_instance');
            $product_id = get_option('hellopack_updater_api_settings')['product_id'];
            $object = parse_url(home_url(), PHP_URL_HOST);
            $url = 'https://helloapi.wp-json.app/wp-json/helloup/v2/api/?api_key=' . $api_key . '&instance=' . $instance . '&wc-api=wc-am-api&object=l$object&product_id=' . $product_id . '&wc_am_action=status';
            $response = wp_remote_get($url);
            $body = wp_remote_retrieve_body($response);
            $json = json_decode($body, true);
            WP_CLI::success('License status: ' . $json['status_check']);
        } else {
            WP_CLI::error('License is not activated!');
        }
    }

    public function hellopack_help($args, $assoc_args)
    {
        WP_CLI::line('HelloPack Commands:');
        WP_CLI::line('');
        WP_CLI::line('  wp hellopack license activate <license-key>   Activate the HelloPack license');
        WP_CLI::line('  wp hellopack license deactivate              Deactivate the HelloPack license');
        WP_CLI::line('  wp hellopack license status                  Check the status of the HelloPack license');
    }
}

WP_CLI::add_command('hellopack license activate', array('HelloPack_Command', 'activate_license'));
WP_CLI::add_command('hellopack license deactivate', array('HelloPack_Command', 'deactivate_license'));
WP_CLI::add_command('hellopack license status', array('HelloPack_Command', 'check_license'));
WP_CLI::add_command('hellopack help', array( 'HelloPack_Command', 'hellopack_help' ));
