<?php
defined('ABSPATH') || die(-1);
$settings_tabs = $this->get_tab_settings();
$current_tab = isset($_GET['tab']) ? hp_clean($_GET['tab']) : HPack_Admin::TAB_ACTIVATION;
$tab = isset($_GET['tab']) ? hp_clean($_GET['tab']) : HPack_Admin::TAB_ACTIVATION;

?>
<?php include 'header.php';?>
<main id="hellopack-admin-main">
<div id="hellopack-admin-content" class="wrap">

<div id="custom_errors_container"></div>
<?php settings_errors();?>
<h2></h2>

    <div class="hellopack-admin-panel">
    <div class="">



<?php
$tab = isset($_GET['tab']) ? hp_clean($_GET['tab']) : $tab = '';
if (isset($_GET['page']) && $_GET['page'] == 'hellopack_settings_manager' && $tab != 'hellopack_settings_others') {
    echo '
    <h2>
    
    <svg class="hellopack-license-icon hellopack-text-icon"><use xlink:href="'.HP_UPDATER_STATIC_URL.'/svg/sprite.svg?v='.HP_UPDATER_VERSION.'#hellopack-license-icon"></use></svg>
    
    '.__('HelloPack License Key Activation', 'hellopack').'</h2>
    <p class="hellopack-admin-section-subtitle">
    '.__('You can manage the HelloPack package manager here. You can activate your license key,<br> manage your plugins, and more.', 'hellopack').'
    </p>';
} else {
    echo '
    <h2>

    <svg class="hellopack-license-icon hellopack-text-icon"><use xlink:href="'.HP_UPDATER_STATIC_URL.'/svg/sprite.svg?v='.HP_UPDATER_VERSION.'#hellopack-settings-icon"></use></svg>
    
    '.__('Settings', 'hellopack').'</h2>
    <p class="hellopack-admin-section-subtitle">
    '.__('You can manage the HelloPack package manager here.', 'hellopack').'
    </p>';
}


?>



</div>

<?php if ($current_tab === HPack_Admin::TAB_ACTIVATION): ?>
    <form method="POST" action="">
<?php else: ?>
    <form method="POST" action="options.php">
<?php endif;?>
       <div id="hellopack_settings_main" class="main">
           <?php
if ($current_tab === HPack_Admin::TAB_ACTIVATION) {
    settings_fields(HPack_Settings_Manager::API_SETTINGS_MAIN);
    do_settings_sections(HPack_Admin::TAB_ACTIVATION);?>
                   <button type="button" id="save_api_settings" class="hellopack-button hellopack-green" data-nonce="<?php echo wp_create_nonce('hp_cleanup_settings'); ?>"><svg class="hellopack-padlock-open-icon"><use xlink:href="<?php echo HP_UPDATER_STATIC_URL; ?>/svg/sprite.svg?v=<?php echo HP_UPDATER_VERSION; ?>#hellopack-padlock-open-icon"></use></svg><?php _e('Activate API Key', 'hellopack');?></button>
			   <?php } elseif ($current_tab === HPack_Admin::TAB_OTHERS) {
			       settings_fields(HPack_Settings_Manager::EXTRA_SETTINGS_KEY);
			       do_settings_sections(HPack_Admin::TAB_OTHERS);
			       submit_button(__('Save Changes', 'hellopack'));
			   }
?>
       </div>
    </form>

    <style>
 

    .footer-out-links {
        content: "Lorem Ipsum";
        position: absolute;
        bottom: 20px;
        right: 20px;
     
    }
    .footer-out-links a {
        color: #53627c !important;
        text-decoration: none;
    
    }
</style>
   <div class="footer-out-links">


<?php

if (!defined('HELLOPACK_WHITELABEL')) {
    ?>
 <a href="https://hub.hellowp.io/docs/dokumentacio/hellopack/" target="_blank"><?php echo __('Documentation', 'hellopack'); ?></a> | <a href="https://hub.hellowp.io/docs/dokumentacio/hellopack/hibaelharitas" target="_blank"><?php echo __('Troubleshooting', 'hellopack'); ?></a> | <a href="https://hellowp.io/hu/hellopack-changelog/" target="_blank"><?php echo __('Changelog', 'hellopack'); ?></a>
   <?php
}
?>


   </div>
    </div>
 
</div> <!-- .wrap -->
</main>
<?php include 'footer.php';?>