<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . '/tester/tester-class.php';

if (!defined('HP_TESTER')) {
    define('HP_TESTER', 'YES');
}

add_action('admin_enqueue_scripts', 'hp_admin_scripts_and_styles');

function hp_admin_scripts_and_styles()
{
    wp_enqueue_style('hp-admin-helper-css');
}

function register_hellopack_tester_page()
{
    add_submenu_page(
        'tools.php',
        __('HelloPack tester', 'hellopack'),
        __('HelloPack tester', 'hellopack'),
        'manage_options',
        'hellopack-tester',
        'render_hellopack_tester_content'
    );
}
add_action('admin_menu', 'register_hellopack_tester_page');

function render_hellopack_tester_content()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $hp_tester = HP_TESTER::instance();
    $limits = $hp_tester->get_server_limits();
    //  print_r($limits);
    include HP_UPDATER_STATIC_PATH . 'partials/header.php';
    echo '<main id="hellopack-admin-main">
<div id="hellopack-admin-content">

    <div class="hellopack-admin-panel">
        <h2><svg class="hellopack-grid-icon hellopack-text-icon"><use xlink:href="' . HP_UPDATER_STATIC_URL . '/svg/sprite.svg?v=' . HP_UPDATER_VERSION . '#hellopack-network-wired-solid"></use></svg> ' . __('Hosting and communication test', 'hellopack') . '</h2>

        <p>' . __('Check the necessary hosting settings and communication between servers.', 'hellopack') . '</p>

        <ul class="hellopack-tester-list">';

    foreach ($limits as $item) {
        $class = 1 == $item['ok'] ? "ok" : "bug";
        echo '<li class="hellopack-list-table-row ' . $class . '">';
        echo '<div class="hellopack-list-table-name">' . $item['title'] . '</div>';
        echo '<div class="hellopack-list-table-description">' . $item['message'] . '</div>';
        echo '</li>';
    }

    echo ' </ul>

    </div>';

    ?>

    <?php

        if (!defined('HELLOPACK_WHITELABEL')) {
            echo '    <div class="hellopack-admin-panel">
<h2><svg style="fill:red" class="hellopack-grid-icon hellopack-text-icon"><use xlink:href="' . HP_UPDATER_STATIC_URL . '/svg/sprite.svg?v=' . HP_UPDATER_VERSION . '#hellopack-bug"></use></svg> ' . __('Troubleshooting', 'hellopack') . ' </h2>

<div id="hellopack-system-info-raw"><p>
' . __('We have compiled the issues that typically arise when using HelloPack. Please review them: ', 'hellopack') . ' <a target="_blank" href="https://hub.hellowp.io/docs/dokumentacio/hellopack/hibaelharitas"> ' . __('Troubleshooting', 'hellopack') . ' </a> 
</p> </div>
</div>
';
        }

    ?>

    <?php

    echo '
    <div class="hellopack-admin-panel">
    <h2><svg class="hellopack-grid-icon hellopack-text-icon"><use xlink:href="' . HP_UPDATER_STATIC_URL . '/svg/sprite.svg?v=' . HP_UPDATER_VERSION . '#hellopack-copy-solid"></use></svg> ' . __('Copy & Paste Info', 'hellopack') . '</h2>' . __('You can copy the below info as simple text with Ctrl+C and Ctrl+V | ⌘+C and ⌘+V', 'hellopack') . ':


    <div id="hellopack-system-info-raw">

<textarea id="hellopack-system-info-raw-code" readonly="">
== ' . __('Hosting and communication test', 'hellopack') . ' ==
';
    foreach ($limits as $item) {
        echo '
        ';
        echo $item['title'] . ': ';
        echo $item['message'];
        echo '
        ';
    }
    ?>

<?php
    echo '

</textarea>
				<script>
					var textarea = document.getElementById( "hellopack-system-info-raw-code" );
					var selectRange = function() {
						textarea.setSelectionRange( 0, textarea.value.length );
					};
					textarea.onfocus = textarea.onblur = textarea.onclick = selectRange;
					textarea.onfocus();
				</script>
			</div>

    </div>


</div>
</main>
</div>
';
}
