<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Include the WP_Upgrader class.
if (!class_exists('WP_Upgrader', false)) :
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
endif;

class HPack_Theme_Upgrader extends Theme_Upgrader
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
}
