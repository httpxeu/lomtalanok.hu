<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_i5hzx' );

/** Database username */
define( 'DB_USER', 'wp_vslxr' );

/** Database password */
define( 'DB_PASSWORD', '34nyCX71?zhzv?_C' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'g@0@;!0_T%+RhI[c*3@3Ln&;u5(s4n3euK-N4/x2%T6#c:(Yj!8YA%GiGi8n4@X4');
define('SECURE_AUTH_KEY', 'Uu@7]%rY9(hFf3qd*&NwxY27f+1_+[-*rG%|Cw%Z*mOrygn_3!@b(2yv[*xmUKtC');
define('LOGGED_IN_KEY', '3;_;D;r~kn0I(Dv_JXFzqsD13xvD3my3X|)%k70)q;G*2Nq(eLswKAk;#/6c2qa6');
define('NONCE_KEY', 'f%U1KtfUD5rb+_v@38]Sc9z8|[5ZV+1OKx_F0y7GZHYF@[j[k;q9#B+*8SYhyy56');
define('AUTH_SALT', '9+Q&__q6:dn/sK1~8w:sk_P905;e_pY(7FCYkwe4vr8)P)-|O%;IT41D&o%5:!79');
define('SECURE_AUTH_SALT', '[%7QM#Hq;:Go45f1*M#G*]u@-gO0F@xkvt77-:]b%64K#65:n&CSQ4Q]fxAIBHI[');
define('LOGGED_IN_SALT', '|3c20)6|CCd-e!7(t/Ja6u1*u9c-t9E/C0;6hZ0_0myblrG5qKa:3[JG(m3I4eNF');
define('NONCE_SALT', ')SW&WjuPZ2iS~D#;]#/4Gt7i(5%0L6J0h7Go/2mF7s7PYns2gzuW~%f@|Jll+#90');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'QcxlYmr_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'DISALLOW_FILE_EDIT', true );
define( 'CONCATENATE_SCRIPTS', false );
define( 'DISABLE_WP_CRON', true );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
