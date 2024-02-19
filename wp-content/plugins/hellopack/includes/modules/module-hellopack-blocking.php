<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//Admin menu editor
if (hp_is_plugin_activated('admin-menu-editor-pro', 'menu-editor.php')) {
    $adminmenueditor = new HPack_Block_API_Servers();
    $adminmenueditor->set_api_servers("adminmenueditor.com");
    $adminmenueditor->init();
}

// Advanced Custom Fields
if (hp_is_plugin_activated('advanced-custom-fields-pro', 'acf.php')) {
    $advancedcustomfields = new HPack_Block_API_Servers();
    $advancedcustomfields->set_api_servers("connect.advancedcustomfields.com/v2/plugins/update-check");
    $advancedcustomfields->init();
}

// Advanced Custom Fields
if (hp_is_plugin_activated('ameliabooking', 'ameliabooking.php')) {
    $ameliabooking = new HPack_Block_API_Servers();
    $ameliabooking->set_api_servers("store.tms-plugins.com/api/autoupdate/info");
    $ameliabooking->init();
}

// AffiliateWP
if (hp_is_plugin_activated('affiliate-wp', 'affiliate-wp.php')) {
    // Admin notification off (ads...)
    $Affiliate_WP = new HPack_Block_API_Servers();
    $Affiliate_WP->set_api_servers("https://plugin.affiliatewp.com/wp-content/notifications.json");
    $Affiliate_WP->init();

    //Block update check
    $affiliatewpcom = new HPack_Block_API_Servers();
    $affiliatewpcom->set_api_servers("https://affiliatewp.com");
    $affiliatewpcom->init();
}
// Booster Plus for WooCommerce
if (hp_is_plugin_activated('booster-plus-for-woocommerce', 'booster-plus-for-woocommerce.php')) {
    $booster = new HPack_Block_API_Servers();
    $booster->set_api_servers("booster.io");
    $booster->init();
}

// Cartflow PRO
if (hp_is_plugin_activated('cartflows-pro', 'cartflows-pro.php')) {
    $cartflows = new HPack_Block_API_Servers();
    $cartflows->set_api_servers("my.cartflows.com/?wc-api=wc-am-api&request=update&slug=cartflows-pro&plugin_name=cartflows-pro");
    $cartflows->init();

    $tmsplugins = new HPack_Block_API_Servers();
    $tmsplugins->set_api_servers("store.tms-plugins.com/api/autoupdate/info");
    $tmsplugins->init();
}

// Divi responsive helper
if (hp_is_plugin_activated('divi-responsive-helper', 'divi-responsive-helper.php')) {
    $diviresponsive = new HPack_Block_API_Servers();
    $diviresponsive->set_api_servers("www.peeayecreative.com/product/divi-responsive-helper");
    $diviresponsive->init();
    $diviresponsive_license_data['key'] = HP_GLOBAL_SERIAL;
    $diviresponsive_license_data['last_check'] = time();
    HP_check_options('slt_drh_license', $diviresponsive_license_data);
}

// Dynamic.ooo - Dynamic Content for Elementor
if (hp_is_plugin_activated('dynamic-content-for-elementor', 'dynamic-content-for-elementor.php')) {
    $dynamic = new HPack_Block_API_Servers();
    $dynamic->set_api_servers("license.dynamic.ooo");
    $dynamic->init();
}

// Ultimate Addons for Elementor
if (hp_is_plugin_activated('ultimate-elementor', 'ultimate-elementor.php')) {
    $brainstormforce = new HPack_Block_API_Servers();
    $brainstormforce->set_api_servers("support.brainstormforce.com");
    $brainstormforce->init();
}

// Fluent Forms PDF Generator
if (hp_is_plugin_activated('fluentforms-pdf', 'fluentforms-pdf.php')) {
    $fluentformspdf = new HPack_Block_API_Servers();
    $fluentformspdf->set_api_servers("apiv2.wpmanageninja.com/plugin");
    $fluentformspdf->init();
}

// Fluent Forms Signature Addon
if (hp_is_plugin_activated('fluentform-signature', 'fluentform-signature.php')) {
    $fluentformspdf = new HPack_Block_API_Servers();
    $fluentformspdf->set_api_servers("apiv2.wpmanageninja.com/plugin");
    $fluentformspdf->init();
    HP_check_options('_ff_signature_license_status', 'valid');
    HP_check_options('_ff_signature_license_key', HP_GLOBAL_SERIAL);
    delete_option('_ff_signature_license_status_checking');
}

// Translatepress
if (hp_is_plugin_activated('translatepress-business', 'index.php')) {
    $translatepress = new HPack_Block_API_Servers();
    $translatepress->set_api_servers("translatepress.com");
    $translatepress->init();
}

//Gravity Forms
if (hp_is_plugin_activated('gravityforms', 'gravityforms.php')) {
    $gform_installation_wizard_license_key = array(
        'license_key' => 'ea247f6f2342a58670ad96bf98781ebc',
        'accept_terms' => true,
        'is_valid_key' => true,
    );
    HP_check_options('rg_gforms_key', 'ea247f6f2342a58670ad96bf98781ebc');
    HP_check_options('gform_installation_wizard_license_key', $gform_installation_wizard_license_key);
}

// iThemes Security Pro
if (hp_is_plugin_activated('ithemes-security-pro', 'ithemes-security-pro.php')) {
    $security = new HPack_Block_API_Servers();
    $security->set_api_servers("api.ithemes.com/updater");
    $security->init();
}

// Ninja Forms
if (hp_is_plugin_activated('ninja-forms', 'ninja-forms.php')) {
    $ninjaforms = new HPack_Block_API_Servers();
    $ninjaforms->set_api_servers("api.ninjaforms.com");
    $ninjaforms->init();

    $ninjaformsupdate = new HPack_Block_API_Servers();
    $ninjaformsupdate->set_api_servers("ninjaforms.com/update-check");
    $ninjaformsupdate->init();
}

// woocommerce-pretty-emails
if (hp_is_plugin_activated('woocommerce-pretty-emails', 'emailplus.php')) {
    $ninjaforms = new HPack_Block_API_Servers();
    $ninjaforms->set_api_servers("http://www.mbcreation.com");
    $ninjaforms->init();
}

// QuadMenu PRO
if (hp_is_plugin_activated('quadmenu-pro', 'quadmenu-pro.php')) {
    $quadmenu = new HPack_Block_API_Servers();
    $quadmenu->set_api_servers("quadmenu.com/wp-json/wc/wlm/product/information");
    $quadmenu->init();
}

// Restrict-content-pro
if (hp_is_plugin_activated('restrict-content-pro', 'restrict-content-pro.php')) {
    $restrict = new HPack_Block_API_Servers();
    $restrict->set_api_servers("api.freemius.com/v1/plugins/10401");
    $restrict->init();
    $security = new HPack_Block_API_Servers();
    $security->set_api_servers("api.ithemes.com/updater");
    $security->init();
}

// Slider Revolution
if (hp_is_plugin_activated('revslider', 'revslider.php')) {
    $revslider = new HPack_Block_API_Servers();
    $revslider->set_api_servers("updates.themepunch.tools");
    $revslider->init();

    $library = new HPack_Block_API_Servers();
    $library->set_api_servers("library.themepunch.tools");
    $library->init();

    $templates = new HPack_Block_API_Servers();
    $templates->set_api_servers("templates.themepunch.tools");
    $templates->init();

    $themepunch = new HPack_Block_API_Servers();
    $themepunch->set_api_servers("themepunch.tools");
    $themepunch->init();

    HP_check_options('revslider-valid', 'true');
    HP_check_options('revslider-code', HP_GLOBAL_SERIAL);
    HP_check_options('revslider-temp-active-notice', 'false');
}

// WPBakery Page Builder
if (hp_is_plugin_activated('js_composer', 'js_composer.php')) {
    $updates = new HPack_Block_API_Servers();
    $updates->set_api_servers("updates.wpbakery.com");
    $updates->init();

    $support = new HPack_Block_API_Servers();
    $support->set_api_servers("support.wpbakery.com");
    $support->init();
}
// Yoast SEO Premium
if (hp_is_plugin_activated('wordpress-seo-premium', 'wordpress-seo-premium.php')) {
    $updates = new HPack_Block_API_Servers();
    $updates->set_api_servers("tracking.yoast.com/stats");
    $updates->init();
}

// WP All Export Pro & WP All Import Pro
if (hp_is_plugin_activated('wp-all-export-pro', 'wp-all-export-pro.php') or hp_is_plugin_activated('wp-all-import-pro', 'wp-all-import-pro.php')) {
    $wp_all_import_export = new HPack_Block_API_Servers();
    $wp_all_import_export->set_api_servers("www.wpallimport.com");
    $wp_all_import_export->init();
}

if (hp_is_plugin_activated('wp-booking-system-premium', 'index.php')) {
    $bookingsys = new HPack_Block_API_Servers();
    $bookingsys->set_api_servers("www.wpbookingsystem.com/u/");
    $bookingsys->init();
}

if (hp_is_plugin_activated('wp-grid-builder', 'wp-grid-builder.php')) {
    $wpgridbuilder = new HPack_Block_API_Servers();
    $wpgridbuilder->set_api_servers("wpgridbuilder.com");
    $wpgridbuilder->init();
}

if (hp_is_plugin_activated('wp-rocket', 'wp-rocket.php')) {
    $wprocket = new HPack_Block_API_Servers();
    $wprocket->set_api_servers("wp-rocket.me/check_update.php");
    $wprocket->init();
}

if (hp_is_plugin_activated('fluentcampaign-pro', 'fluentcampaign-pro.php')) {
    $fluentcampaign = new HPack_Block_API_Servers();
    $fluentcampaign->set_api_servers("apiv2.wpmanageninja.com/plugin");
    $fluentcampaign->init();
}
if (hp_is_plugin_activated('automatorwp-pro', 'automatorwp-pro.php')) {
    $automatorwp = new HPack_Block_API_Servers();
    $automatorwp->set_api_servers("automatorwp.com/edd-sl-api");
    $automatorwp->init();
}
if (hp_is_plugin_activated('updraftplus', 'updraftplus.php')) {
    $updraftplus = new HPack_Block_API_Servers();
    $updraftplus->set_api_servers("updraftplus.com/plugin-info");
    $updraftplus->init();
}