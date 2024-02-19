<?php

/**
 * Plugin Name: HelloPack
 * Plugin URI: https://hellowp.io
 * Description: Updates for premium addons
 * Version: 1.2.22
 * Tested up to: 5.5
 * Author: HelloWP Ltd
 * Author URI: https://hellowp.io
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: hellopack
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Network: False
 */

defined('WPINC') || die("-1");

function HP_LANG()
{
    load_plugin_textdomain('hellopack', false, basename(dirname(__FILE__)) . '/languages/');
    wp_set_script_translations('hp-settings-js', 'hellopack', basename(dirname(__FILE__)) . '/languages/');
}
add_action('init', 'HP_LANG');

if (version_compare(PHP_VERSION, '7.4', '<')) {
    function php_hello_pack_warning()
    {
        ?>
<div class="notice notice-error" style="background-color: #fcd0d8">
     <p><?php _e('For proper functioning of the <strong>HelloPack</strong> plugin, a minimum of PHP 7.4 is required. This is highly recommended for both security and technical reasons. Ask your hosting provider to do this if you are unsure.', 'hellopack'); ?>
     </p>
</div>
<?php
    }
    add_action('admin_notices', 'php_hello_pack_warning');
    return;
}

/** Adding Overrides */
include_once dirname(__FILE__) . '/includes/overrides/includes.php';

defined('HP_UPDATER_VERSION') || define('HP_UPDATER_VERSION', '1.2.22');

defined('HP_UPDATER_NAME') || define('HP_UPDATER_NAME', 'hellopack-updater');

if (!defined('HP_UPDATER_PATH')) {
    define('HP_UPDATER_PATH', plugin_dir_path(__FILE__));
}

if (!defined('HP_UPDATER_URL')) {
    define('HP_UPDATER_URL', plugin_dir_url(__FILE__));
}

if (!defined('HP_UPDATER_INC')) {
    define('HP_UPDATER_INC', plugin_dir_path(__FILE__) . 'includes/');
}

if (!defined('HP_UPDATER_BASENAME')) {
    define('HP_UPDATER_BASENAME', plugin_basename(__FILE__));
}

if (!defined('HP_UPDATER_STATIC_PATH')) {
    define('HP_UPDATER_STATIC_PATH', plugin_dir_path(__FILE__) . 'static/');
}

if (!defined('HP_UPDATER_STATIC_URL')) {
    define('HP_UPDATER_STATIC_URL', plugin_dir_url(__FILE__) . 'static/');
}

if (!defined('HP_MODULES_URL')) {
    define('HP_MODULES_URL', plugin_dir_url(__FILE__) . 'includes/modules/');
}

define('HP_GLOBAL_SERIAL', md5(get_site_url()));

defined('HP_UPDATER_API_URL') || define('HP_UPDATER_API_URL', 'https://hellopack.wp-json.app/');

define('HP_UPDATER_WP_JSON_URL', 'https://api.wp-json.app/');

define('HP_PLUGIN_API_SERVER', 'api.wp-json.app');
define('HP_PLUGIN_REGISTER_SERVER', 'api-register.wp-json.app');
define('HP_PLUGIN_REGISTER_SERVER_HTTPS', 'https://api-register.wp-json.app');
define('HP_PLUGIN_INSTALLER_SERVER', 'plugin-installer.wp-json.app');


defined('HPACKS_RENAME_PLUGINS') || define('HPACKS_RENAME_PLUGINS', true);

if (!defined('HP_UPDATER_LOG_DIR')) {
    $up_dir = wp_upload_dir();

    define('HP_UPDATER_LOG_DIR', $up_dir['basedir'] . '/hellopack-logs/');
}

register_activation_hook(__FILE__, 'hellopack_updater_activate');

register_deactivation_hook(__FILE__, 'hellopack_updater_deactivate');


function hellopack_updater_activate()
{
    require_once HP_UPDATER_INC . 'class-hellopack-activation.php';
    HPack_Updater_Activator::activate();
}

function hellopack_updater_deactivate()
{
    require HP_UPDATER_INC . 'deactivate.php';
    require_once HP_UPDATER_INC . 'class-hellopack-deactivation.php';
    HPack_Updater_Deactivator::deactivate();
}

/**
 * Starts the excution of the plugin here
 */
require HP_UPDATER_INC . 'class-hellopack-updater.php';
require_once HP_UPDATER_INC . 'class-load.php';

// Include modules

require HP_UPDATER_INC . 'modules.php';

/**
 * @return HPack_Updater
 */
function HPMain()
{
    return HPack_Updater::instance();
}

function starts_hellopack()
{
    HPMain()->run();
}
starts_hellopack();

$GLOBALS['hellopack'] = HPMain();