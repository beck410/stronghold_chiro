<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpressuser');

/** MySQL database password */
define('DB_PASSWORD', 'c8*TlcVSj3fm');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('FS_METHOD', 'direct');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Zgu>AH-fnV~x+EFOio-OG^CUq)T;:tgnj~[BqEXr]jdFe6R`pM|~()2?+9FQ>/ %');
define('SECURE_AUTH_KEY',  'J/0h3lSZ+%F_X^u+<RN#-R+GC00E~:4^#n|lu:!EWmjg+QDi:/.%c-JXL/ :+|Rw');
define('LOGGED_IN_KEY',    'g|U-oW)F4w-P-dr;#1Cb|o@;=m]%: PI-Abg-)))C+3j##6UW[9v1I>]l1- 1|Tu');
define('NONCE_KEY',        '?.d9:+hd.(+#oIg<y+iarFI[r;;Fhn0j,Sp0Tk}#ciR$U#RRg{Vm|A>ZWR.rQN/|');
define('AUTH_SALT',        '.h!![.mkoEA66+gEaq9 ,IY-C);+=9c>&QRl8@JtiGPh#vRoH0PtJHBgg%&z}mG.');
define('SECURE_AUTH_SALT', 'Y<6d7q&@`!=r 77e{8X{YbhTF+9l]w3n_{[8Mq=nYQ5&Vf1pGn&qM;0C(8D($;c*');
define('LOGGED_IN_SALT',   '^-3QP}9Dse-d%G.[olR 92r++5+~@~p{K+i2}-S)wXm[D8;>WR~CuRYPVhVk $8-');
define('NONCE_SALT',       'wYFIJxJjd__R-WbuM8-+acB7d-0v{mS5P8r!&Gqv,op|}J^iBr]-mCn>ey6A/:Qy');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Frontend */
define( 'WP_MEMORY_LIMIT', '96M' );
/* Backend */
define( 'WP_MAX_MEMORY_LIMIT', '128M' );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
