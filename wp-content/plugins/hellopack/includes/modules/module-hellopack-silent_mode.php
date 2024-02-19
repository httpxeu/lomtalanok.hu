<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('HELLOPAC_SILENT_MODE')) {
    class HELLOPAC_SILENT_MODE
    {
        public const VER = "1.0";
        public $options = array();
        public $options_name = "HELLOPAC_SILENT_MODE";

        public function __construct()
        {
            $this->update(array());
            $this->store_options();
            if ($this->options["notifications"] == 1) {
                add_action("in_admin_header", array($this, "skip_notices"), 100000);
            }
            add_filter('transient_update_plugins', array($this, 'skip_updates'), 10000, 1);
            add_filter('site_transient_update_plugins', array($this, 'skip_updates'), 10000, 1);
        }


        public function getVersion()
        {
            return self::VER;
        }

        public function activate()
        {
            $this->options = get_option($this->options_name);
            if (!is_array($this->options) or empty($this->options)) {
                $this->default_options();
                $this->store_options();
            }
        }

        public function deactivate()
        {
        }

        private function default_options()
        {
            $defaults = array("ver" => $this->getVersion(), "notifications" => "1", "updates" => array());

            $this->options = $defaults;
            $this->store_options();
        }

        private function update($old_options = array())
        {
            global $wpdb;

            $sql = "SELECT `option_id`,`option_name` FROM `" . $wpdb->options . "` WHERE LEFT(`option_name`,CHAR_LENGTH('" . $this->options_name . "'))='" . $this->options_name . "' ORDER BY `option_id` ASC";
            $opts = $wpdb->get_results($sql);

            $nOptions = array();
            if (is_array($opts) and !empty($opts)) {
                foreach ($opts as $i => $op) {
                    $cOp = get_option($op->option_name);
                    $nOptions = array_merge($nOptions, $cOp);
                }
            }

            $this->default_options();
            $this->options = array_merge($this->options, $nOptions);
            $this->store_options();
        }

        private function store_options()
        {
            update_option($this->options_name, $this->options);
        }


        public function skip_updates($transientData)
        {
            foreach ($this->options["updates"] as $ix => $plugin_file) {
                if (isset($transientData->response[$plugin_file])) {
                    unset($transientData->response[$plugin_file]);
                }
            }

            return $transientData;
        }

        public function skip_notices()
        {
            global $wp_filter;

            if (is_network_admin() and isset($wp_filter["network_admin_notices"])) {
                unset($wp_filter['network_admin_notices']);
            } elseif (is_user_admin() and isset($wp_filter["user_admin_notices"])) {
                unset($wp_filter['user_admin_notices']);
            } else {
                if (isset($wp_filter["admin_notices"])) {
                    unset($wp_filter['admin_notices']);
                }
            }

            if (isset($wp_filter["all_admin_notices"])) {
                unset($wp_filter['all_admin_notices']);
            }
        }
    }
}


if (!function_exists('hellopack_silent_mode_load')) {
    function hellopack_silent_mode_load()
    {
        if (!isset($GLOBALS["HELLOPAC_SILENT_MODE"]) && $_SERVER['PHP_SELF'] !== '/wp-admin/update-core.php') {
            $GLOBALS["HELLOPAC_SILENT_MODE"] = new HELLOPAC_SILENT_MODE();
        }
    }
}
