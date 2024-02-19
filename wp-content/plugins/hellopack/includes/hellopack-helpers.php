<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!function_exists('hp_clean')) {
    /**
     * This function sanitizes input text field
     *
     * This function is copy of WooCommerce `wc_clean` function.
     *
     * @param $var
     *
     * @return array|string
     */
    function hp_clean($var)
    {
        if (is_array($var)) {
            return array_map('hp_clean', $var);
        } else {
            return is_scalar($var) ? sanitize_text_field($var) : $var;
        }
    }
}

if (!function_exists('hp_generate_password')) :
    function hp_generate_password($length = 12, $special_chars = true, $extra_special_chars = false)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($special_chars) {
            $chars .= '!@#$%^&*()';
        }
        if ($extra_special_chars) {
            $chars .= '-_ []{}<>~`+=,.;:/?|';
        }

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= substr($chars, hp_rand(0, strlen($chars) - 1), 1);
        }

        return $password;
    }
endif;
if (!function_exists('hp_rand')) :
    /**
     * Generates a random number.
     *
     * @since 2.6.2
     * @since 4.4.0 Uses PHP7 random_int() or the random_compat library if available.
     *
     * @global string $rnd_value
     * @staticvar string $seed
     * @staticvar bool $use_random_int_functionality
     *
     * @param int $min Lower limit for the generated number
     * @param int $max Upper limit for the generated number
     * @return int A random number between min and max
     */
    function hp_rand($min = 0, $max = 0)
    {
        global $rnd_value;

        if (!function_exists('random_int')) {
            @include_once ABSPATH . WPINC . '/random_compat/random.php';
        }
        // Some misconfigured 32bit environments (Entropy PHP, for example) truncate integers larger than PHP_INT_MAX to PHP_INT_MAX rather than overflowing them to floats.
        $max_random_number = 3000000000 === 2147483647 ? (float) '4294967295' : 4294967295; // 4294967295 = 0xffffffff

        // We only handle Ints, floats are truncated to their integer value.
        $min = (int) $min;
        $max = (int) $max;

        // Use PHP's CSPRNG, or a compatible method
        static $use_random_int_functionality = true;
        if ($use_random_int_functionality) {
            try {
                $_max = (0 != $max) ? $max : $max_random_number;
                // wp_rand() can accept arguments in either order, PHP cannot.
                $_max = max($min, $_max);
                $_min = min($min, $_max);
                $val = random_int($_min, $_max);
                if (false !== $val) {
                    return absint($val);
                } else {
                    $use_random_int_functionality = false;
                }
            } catch (Error $e) {
                $use_random_int_functionality = false;
            } catch (Exception $e) {
                $use_random_int_functionality = false;
            }
        }

        // Reset $rnd_value after 14 uses
        // 32(md5) + 40(sha1) + 40(sha1) / 8 = 14 random numbers from $rnd_value
        if (strlen($rnd_value) < 8) {
            if (defined('WP_SETUP_CONFIG')) {
                static $seed = '';
            } else {
                $seed = get_transient('random_seed');
            }
            $rnd_value = md5(uniqid(microtime() . mt_rand(), true) . $seed);
            $rnd_value .= sha1($rnd_value);
            $rnd_value .= sha1($rnd_value . $seed);
            $seed = md5($seed . $rnd_value);
            if (!defined('WP_SETUP_CONFIG') && !defined('WP_INSTALLING')) {
                set_transient('random_seed', $seed);
            }
        }

        // Take the first 8 digits for our value
        $value = substr($rnd_value, 0, 8);

        // Strip the first eight, leaving the remainder for the next call to wp_rand().
        $rnd_value = substr($rnd_value, 8);

        $value = abs(hexdec($value));

        // Reduce the value to be within the min - max range
        if ($max != 0) {
            $value = $min + ($max - $min + 1) * $value / ($max_random_number + 1);
        }

        return abs(intval($value));
    }
endif;

if (!function_exists('hp_print_r')) :
    function hp_print_r($expression, $return = false)
    {
        $alternatives = array(
            array(
                'func' => 'print_r',
                'args' => array($expression, true),
            ),
            array(
                'func' => 'var_export',
                'args' => array($expression, true),
            ),
            array(
                'func' => 'json_encode',
                'args' => array($expression),
            ),
            array(
                'func' => 'serialize',
                'args' => array($expression),
            ),
        );

        $alternatives = apply_filters('hellopack_print_r_alternatives', $alternatives, $expression);

        foreach ($alternatives as $alternative) {
            if (function_exists($alternative['func'])) {
                $res = call_user_func_array($alternative['func'], $alternative['args']);
                if ($return) {
                    return $res;
                }

                echo $res;
                return true;
            }
        }

        return false;
    }
endif;

if (!function_exists('hp_take')) {
    function hp_take($payload, $keys)
    {
        $accumulator = array();

        $payload_data = is_array($payload) ? $payload : (is_object($payload) ? (array) $payload : array());

        foreach ($keys as $key) {
            $accumulator[$key] = array_key_exists($key, $payload_data) ? $payload_data[$key] : null;
        }

        return $accumulator;
    }
}
