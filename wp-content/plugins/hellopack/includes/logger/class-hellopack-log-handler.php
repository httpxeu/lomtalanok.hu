<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

abstract class HPack_Log_Handler implements HPack_Log_Handler_Interface
{
    protected static function format_time($timestamp)
    {
        return date('c', $timestamp);
    }

    protected static function format_entry($timestamp, $level, $message, $context)
    {
        $time_string = self::format_time($timestamp);
        $level_string = strtoupper($level);
        $entry = "{$time_string} {$level_string} {$message}";

        return apply_filters('hellopack_format_log_entry', $entry, array(
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ));
    }
}
