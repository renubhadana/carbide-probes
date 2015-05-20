<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
 
// Include local configuration
if (file_exists(dirname(__FILE__) . '/local-config.php')) {
	include(dirname(__FILE__) . '/local-config.php');
}

// Global DB config
if (!defined('DB_NAME')) {
	define('DB_NAME', 'carbide_probes_dev');
}
if (!defined('DB_USER')) {
	define('DB_USER', 'root');
}
if (!defined('DB_PASSWORD')) {
	define('DB_PASSWORD', 'root');
}
if (!defined('DB_HOST')) {
	define('DB_HOST', 'localhost');
}

/** Database Charset to use in creating database tables. */
if (!defined('DB_CHARSET')) {
	define('DB_CHARSET', 'utf8');
}

/** The Database Collate type. Don't change this if in doubt. */
if (!defined('DB_COLLATE')) {
	define('DB_COLLATE', '');
}

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'g0BKTMKn+D+3)uXRg-#{+a0gTP{m_5!7rXYm7 L=yeQsCif.+0 bD$NW3YX36u<1');
define('SECURE_AUTH_KEY',  'YGQ+O*GIT9?)W!6v@ki>x_V>-|-s TLtJ;OG-o/E&soCW8-yr.StgQK|v}>)2q{@');
define('LOGGED_IN_KEY',    'CZO(v}#4VCrsSRGUU)--<|X?mi6;@6FX-3Yj/!ox+N|vTqqJWSk-+|)^SuQ>P6Ld');
define('NONCE_KEY',        'q,/NOxCMR=8%xuI8/qb3!J1 yK04|W^NZ!dY3>|Q=g!_:s60S7#S(dnJ(Pwp~vM`');
define('AUTH_SALT',        '(BUl}uv]!zhLb1P8.YBTixlf%x.!&^}B2~+|T-u/YAsFy7;lP-^~4q<+[/j;lZX=');
define('SECURE_AUTH_SALT', 'S@F|:85!1`(!:L*eVO=-3xSI+`X>D28T1CJ- aE^hi5%nT1P>h!x`C6k+{1na +g');
define('LOGGED_IN_SALT',   '8DqEeOh^if$>$KQ=Q2-`$e1d0~Vfl}f;jfEHJvbG|np->NglB@B%XGcb_&7B#gxf');
define('NONCE_SALT',       ',2Dz|JIq%P;%|ztUBmHh9ExE5;xCDzSZ)d>_n^5|D5Di% i|{k?WqI3|Dbds&6O<');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');


/**
 * Set custom paths
 *
 * These are required because wordpress is installed in a subdirectory.
 */
if (!defined('WP_SITEURL')) {
	define('WP_SITEURL', 'http://' . $_SERVER['SERVER_NAME'] . '/wordpress');
}
if (!defined('WP_HOME')) {
	define('WP_HOME',    'http://' . $_SERVER['SERVER_NAME'] . '');
}
if (!defined('WP_CONTENT_DIR')) {
	define('WP_CONTENT_DIR', dirname(__FILE__) . '/content');
}
if (!defined('WP_CONTENT_URL')) {
	define('WP_CONTENT_URL', 'http://' . $_SERVER['SERVER_NAME'] . '/content');
}


/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
if (!defined('WP_DEBUG')) {
	define('WP_DEBUG', false);
}

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
