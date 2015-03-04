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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'aspect_final_upgrade');

/** MySQL database username */
define('DB_USER', 'aspect_final');

/** MySQL database password */
define('DB_PASSWORD', '84JUz(fNHTVu');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ';+YC7;K^}8IYwCxl(tSXN9a5x,X&w:+;>)aO+=cUx$O|d7j<5`H>0O9KvEQ1!P|,');
define('SECURE_AUTH_KEY',  '}/~f}|lSw*cUsD6JfuE8+-t)2ZG[FVn82@Y>-@Aa~?)uM@^!$&cFhV/+#3W*[Th]');
define('LOGGED_IN_KEY',    '%5If?^ay>E@78:tH7Dsdi0GPD&%-Gz8Zaa>x[$#tZD%Rsy(2^Q{TXD;145pGew@%');
define('NONCE_KEY',        '.0b31.13dGk06T6x/:B|u[d!@n-CRvH8dxiM_Ki7:x?-3w5<5/[/yYYp_m oLAv>');
define('AUTH_SALT',        'k6<)pH{LV7QX1=tU/p/Doc4Voq~h@TpWol_h*;2tQ4y7/5|0M2-n5XryY`[IJ>jt');
define('SECURE_AUTH_SALT', 'avBz@#b:nU``%`Ji[hRXon/MQ]U$ToGin[oQ.e90s@~`GgGO.34-fsSP54K4xm{y');
define('LOGGED_IN_SALT',   'WcFsL*D5WM>b6Dxj<;WJ n RomMaj@+G)|6W<^__Xz-NfdvU@g?UK}@_V(zW5$iO');
define('NONCE_SALT',       '&vyPL-OyA;/9E.Zy1T.Mh-pu#8c]_>X~OBYF2}d$c&L4(.rh 2<(=(=r*>`h0?.[');

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
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
