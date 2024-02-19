<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('mts_connection', false)) :
    class mts_connection
    {
    }
endif;

defined('MTS_CONNECT_ACTIVE') || define('MTS_CONNECT_ACTIVE', true);
