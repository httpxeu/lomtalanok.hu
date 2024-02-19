<?php
if (!defined('ABSPATH')) {
	exit;
}
$host = parse_url(HP_UPDATER_API_URL, PHP_URL_HOST);
?>
 <div id="hp_external_blocking_notice" class="notice notice-error">
	 <?php /* translators: 1: title of affecting items 2: host to add in whitelist 3: constant name where host should be added */?>
                        <p><?php printf(__('<strong>Warning!</strong> You\'re blocking external requests which means you won\'t be able to get %1$s updates. Please add %2$s to %3$s.', 'hellopack'), __('HPack Plugins and Themes', 'hellopack'), '<strong>' . $host . '</strong>', '<code>WP_ACCESSIBLE_HOSTS</code>');?></p>
</div>