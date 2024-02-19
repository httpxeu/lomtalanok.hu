<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Check if the plugin is activated
 *
 * @since      1.0.0
 *
 * @package    HPack_Updater
 * @subpackage HPack_Updater/includes
 */

/**
 * Check if the plugin is activated
 *
 * This class defines all code necessary to check plugins.
 *
 * @since      1.0.0
 * @package    HPack_Updater
 * @subpackage HPack_Updater/includes
 * @author     HelloPack <support@hellowp.io>
 */


class HP_PluginActivationStatus
{
    protected $define_name;
    protected $is_defined;

    public function __construct($define_name)
    {
        $this->define_name = $define_name;
        add_action('plugins_loaded', array($this, 'check_define'));
    }

    public function check_define()
    {
        if (defined($this->define_name)) {
            $this->is_defined = true;
        } else {
            $this->is_defined = false;
        }
    }

    public function is_defined()
    {
        return $this->is_defined;
    }
}
