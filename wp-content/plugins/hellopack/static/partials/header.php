<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$tab = isset($_GET['tab']) ? hp_clean($_GET['tab']) : $tab = '';

$hellopack_updater_extras = get_option('hellopack_updater_extras');

if (is_array($hellopack_updater_extras) && isset($hellopack_updater_extras['plugin_space']) && $hellopack_updater_extras['plugin_space'] === 'yes') {
    $hellopack_location =  'plugins.php';
} else {
    $hellopack_location = 'admin.php';
}

$url = get_home_url();

?>
<style>
	body {
		background-color: #eef2f5
	}

	#message,
	.update-nag,
	.updated {
		display: none !important
	}

	.notice.notice-warning {
		display: none !important;
	}

	#wpcontent {
		padding: 0
	}

	.button.installed::before,
	.button.installing::before,
	.button.updated-message::before,
	.button.updating-message::before {
		margin: 0px 4px 0 -2px;
	}

	#hellopack .updating-message>svg {
		display: none !important;
	}
</style>
<div id="hellopack" class="hellopack-dashboard-page">
	<div class="hellopack-header">
		<header id="hellopack-admin-header">

			<h1 id="hellopack-admin-plugin">
				<?php
                if (defined('HELLOPACK_WHITELABEL') && HELLOPACK_WHITELABEL === true) {
                    ?>
					<a href="<?php echo $url; ?>/wp-admin/<?php echo $hellopack_location; ?>?page=hellopack_settings_manager">
						<span class="hellopack-whitelabel">
							<svg class="hellopack-facet-icon">
								<use xlink:href="<?php echo HP_UPDATER_STATIC_URL; ?>/svg/sprite.svg?v=<?php echo HP_UPDATER_VERSION; ?>#hellopack-facet-icon"></use>
							</svg>
							<?php _e('HelloPack Dashboard', 'hellopack') ?></span>
					</a>
				<?php
                } else {
                    ?>
					<a href="<?php echo $url; ?>/wp-admin/<?php echo $hellopack_location; ?>?page=hellopack_settings_manager">
						<img src="<?php echo HP_UPDATER_STATIC_URL; ?>/svg/logo.svg" alt="" width="156" height="48">
						<span class="hellopack-sr-only"><?php _e('HelloPack Dashboard', 'hellopack') ?></span>
					</a>
				<?php
                }
?>

			</h1>

			<code id="hellopack-admin-version">v<?php echo HP_UPDATER_VERSION; ?></code>

			<ul id="hellopack-admin-navigation">
				<li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'hellopack_settings_manager' && $tab != 'hellopack_settings_others') ? 'hellopack-active' : ''; ?>">
					<a href="<?php echo $url; ?>/wp-admin/<?php echo $hellopack_location; ?>?page=hellopack_settings_manager">
						<svg class="hellopack-home-icon">
							<use xlink:href="<?php echo HP_UPDATER_STATIC_URL; ?>/svg/sprite.svg?v=<?php echo HP_UPDATER_VERSION; ?>#hellopack-home-icon"></use>
						</svg><span><?php _e('Dashboard', 'hellopack') ?></span>
					</a>
				</li>
				<li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'hellopack-tester') ? 'hellopack-active' : ''; ?>">
					<a href="<?php echo $url; ?>/wp-admin/tools.php?page=hellopack-tester">
						<svg class="hellopack-grid-icon">
							<use xlink:href="<?php echo HP_UPDATER_STATIC_URL; ?>/svg/sprite.svg?v=<?php echo HP_UPDATER_VERSION; ?>#hellopack-list-check-solid"></use>
						</svg><span><?php _e('Tester', 'hellopack') ?></span>
					</a>
				</li>

				<?php if (!defined('HP_DISABLE_PLUGINS_MENU')) {
				    $tab = isset($_GET['tab']) ? hp_clean($_GET['tab']) : $tab = '';
				    ?>
				<li class="<?php echo ($tab && $tab == 'hellopack') ? 'hellopack-active' : ''; ?>">
					<a href="<?php echo $url; ?>/wp-admin/plugin-install.php?tab=hellopack">
						<svg style="fill:#53627c" class="hellopack-grid-icon">
							<use xlink:href="<?php echo HP_UPDATER_STATIC_URL; ?>/svg/sprite.svg?v=<?php echo HP_UPDATER_VERSION; ?>#hellopack-plugins"></use>
						</svg><span><?php _e('Plugins', 'hellopack') ?></span>
					</a>
				</li>
						<?php } ?>

				<li class="<?php echo (isset($_GET['tab']) && $_GET['tab'] == 'hellopack_settings_others') ? 'hellopack-active' : ''; ?>">
					<a href="<?php echo $url; ?>/wp-admin/<?php echo $hellopack_location; ?>?page=hellopack_settings_manager&tab=hellopack_settings_others">
						<svg class="hellopack-settings-icon">
							<use xlink:href="<?php echo HP_UPDATER_STATIC_URL; ?>/svg/sprite.svg?v=<?php echo HP_UPDATER_VERSION; ?>#hellopack-settings-icon"></use>
						</svg><span><?php _e('Settings', 'hellopack') ?></span>
					</a>
				</li>
			</ul>
		</header>
	</div>