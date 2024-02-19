<?php
if (!defined('ABSPATH')) {
    exit;
}

$hellopack_link = '<a href="https://hellowp.io/hu/helloconsole/" target="_blank">HelloPack</a>';
$plugin_name = '<strong>HPack Update Manager</strong>';
?>
<div id="hp_subcription_message" class="notice notice-warning">
	<p><strong><?php _e('Your subscription is Pending Cancellation', 'hellopack');?></strong>
		<?php /* translators: 1: title of the plugin 2: link for purchasing new license */?>
		<br><?php printf(__('Your subscription is awaiting for cancellation. To continue using the %1$s, please reactivate your subscription at %2$s.', 'hellopack'), $plugin_name, $hellopack_link);?></p>
</div>
