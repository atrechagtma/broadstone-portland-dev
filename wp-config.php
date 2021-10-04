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
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '3HyrWJPjLfIGpkK3LIHvPjquHGxWeZ/qDHg+VikA5GOhy+uCyuLWgdKSNkFeBTp68/PqbF/sK9AMZqHAlhOGTA==');
define('SECURE_AUTH_KEY',  'YXn1MFGuwuYpczkhg1ciWV0eeIzvJjPUwKC7hKzyK1dg0Wa2AGY02qmoUUEuPvuG0Ah3p39AEls8aDxSsWfMew==');
define('LOGGED_IN_KEY',    'sjH+/KHxkBXTfaN1vVYapOcLyBpB1Wrv0IX+6pEvuvVqGA/dcAqZbG8Yj+VGurcNSICwU228NHjN8QHqThy2rQ==');
define('NONCE_KEY',        'RM+7YrcYiEbCzakSZ+hOIpYCZNPDzL2VW9suocHTZ+71PWe0AalmEYwnzk5wJ66zIJ11mqaW19ruKge+SrEQeg==');
define('AUTH_SALT',        'Xw3Vkz+56F5qYJt9xdHIEZZqx5ulIa6Tudb115p0FUPTAOaEkfzBa+5eM/rOGUH9on41qFaUvRdkDJu9KsDA+g==');
define('SECURE_AUTH_SALT', 'n0TnFKa9a2QDyoJ1G6tnnshdwxPqcqELw3zws5ixS6RnI4sD60/eCnhcyMKPYJiVgv6c+5NAbWbbMsV7upzNZg==');
define('LOGGED_IN_SALT',   'Ie2kQBBAHwOkAWvG/+IHV76BtCUnbPcYLnIMLnCpoLk2phorROwtXl+QnFAvDknb581DN1JwzfvKbO+o/4zqPw==');
define('NONCE_SALT',       'kXWAJZo8FoE3EzplrxxGRtdDAUFUHtc0aVUBCyblq5c0A8/ALq0lJ3ryFSDNmxkA3WjQ6iofcfL86E6ymd8IVA==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
