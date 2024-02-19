<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
require_once dirname(__FILE__) . '/interfaces/class-hellopack-logger-interface.php';
require_once dirname(__FILE__) . '/interfaces/class-hellopack-log-handler-interface.php';

require_once dirname(__FILE__) . '/class-hellopack-log-levels.php';
require_once dirname(__FILE__) . '/class-hellopack-log-handler.php';
require_once dirname(__FILE__) . '/class-hellopack-logger.php';

require_once dirname(__FILE__) . '/handlers/class-hellopack-log-handler-file.php';
