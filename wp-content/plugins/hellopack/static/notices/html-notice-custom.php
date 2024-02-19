<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!empty($notice) && !empty($notice_html)) {
	?>
	<div id="<?php echo hp_clean($notice) ?>" class="updated hp-notice-custom">
		<a class="hp-notice-close notice-dismiss"
		   href="<?php echo esc_url(wp_nonce_url(add_query_arg('hp-hide-notice', $notice), 'hp_hide_notices_nonce', '_hp_notice_nonce')); ?>"><?php esc_html_e('Dismiss', 'hellopack');?></a>
		<?php echo wp_kses_post(wpautop($notice_html)); ?>
	</div>
<?php }?>
