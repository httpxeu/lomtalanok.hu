<?php
if (!defined('ABSPATH')) {
	exit;
}

?>
<div id="hp_cron_message" class="notice notice-error">
    <p><strong><?php esc_html_e('WP CRON Deactivated!', 'hellopack');?></strong>
        <?php /* translators: 1: constant definition to disable cron 2: value to activate cron */?>
        &#8211; <?php printf(__('HPack Updater depends on WP Cron to sync data, please remove %1$s or set to - %2$s.', 'hellopack'), "<code>define('DISABLE_WP_CRON', true)</code>", "<code>false</code>");?> </p>
</div>
