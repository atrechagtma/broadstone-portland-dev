<?php
/**
 * @package RentPress
 */
/*
Plugin Name: RentPress for Websites
Plugin URI: https://rentpress.io/
Description: Connects real estate agents to their property information for any WordPress site. Supports data feeds from: RentCafe, Entrata, RealPage, MRI Software/Vaultware, ResMan, Encasa, Appfolio.
Version: 7.2.0
Requires at least: 5.8
Requires PHP: 7.2
Author: 30 Lines
Author URI: https://rentpress.io/
License: GPLv3
GNU GPLv3 License Origin: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

if (defined('RENTPRESS_PLUGIN_DIR')) {
    echo "You have an old version of RentPress active while attempting to activate RentPress For Websites. Please deactivate or delete your old RentPress plugin and then activate RentPress For Websites.";
    die();
}

define('RENTPRESS_PLUGIN_VERSION', '7.2.0');
define('RENTPRESS_MINIMUM_WP_VERSION', '5.3.2');
define('RENTPRESS_DELETE_LIMIT', 100000);
define('RENTPRESS_MENU_POSITION', 5);
define('RENTPRESS_SERVER_TIME_ZONE', date_default_timezone_get());
define('RENTPRESS_SERVER_ENDPOINT', 'https://toplineconnect.com'); // no trailing slash

// File Paths
define('RENTPRESS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RENTPRESS_PLUGIN_DIST', plugin_dir_url(__FILE__) . 'dist/');
define('RENTPRESS_PLUGIN_VUE_MAIN_DIST', plugin_dir_url(__FILE__) . 'public/vue/main-app/dist/');
define('RENTPRESS_PLUGIN_VUE_MAPBOX_DIST', plugin_dir_url(__FILE__) . 'public/vue/mapbox-app/dist/');
define('RENTPRESS_PLUGIN_ASSETS', plugin_dir_url(__FILE__) . 'assets/');
define('RENTPRESS_PLUGIN_ADMIN_DIR', plugin_dir_path(__FILE__) . 'admin/');
define('RENTPRESS_PLUGIN_ADMIN_VIEW_DIR', plugin_dir_path(__FILE__) . 'admin/view/');
define('RENTPRESS_PLUGIN_ADMIN_VIEW_MENU_DIR', plugin_dir_path(__FILE__) . 'admin/view/menus/');
define('RENTPRESS_PLUGIN_ADMIN_SCRIPTS_DIR', plugin_dir_url(__FILE__) . 'admin/assets/javascript/');
define('RENTPRESS_PLUGIN_ADMIN_IMAGES_DIR', plugin_dir_url(__FILE__) . 'admin/assets/images/');
define('RENTPRESS_PLUGIN_ADMIN_STYLES_DIR', plugin_dir_url(__FILE__) . 'admin/assets/css/');
define('RENTPRESS_PLUGIN_ADMIN_POSTS', plugin_dir_path(__FILE__) . 'admin/posts/');
define('RENTPRESS_PLUGIN_DATA_SYNC', plugin_dir_path(__FILE__) . 'data/sync/');
define('RENTPRESS_PLUGIN_DATA_MODEL', plugin_dir_path(__FILE__) . 'data/model/');
define('RENTPRESS_PLUGIN_DATA_ACCESS', plugin_dir_path(__FILE__) . 'data/access/');
define('RENTPRESS_PLUGIN_PUBLIC_VIEWS', plugin_dir_path(__FILE__) . 'public/views/');
define('RENTPRESS_PLUGIN_PUBLIC_SHORTCODES', plugin_dir_path(__FILE__) . 'public/shortcodes/');
define('RENTPRESS_PLUGIN_PUBLIC_TEMPLATES_DIR', plugin_dir_url(__FILE__) . 'public/templates/');

// add all global rentpress functions and files
require_once RENTPRESS_PLUGIN_DIR . 'rentpress-functions.php';

// Activate Plugin
function rentpress_install()
{
    if (!get_option('rentpress_flush_rewrite_rules_flag')) {
        add_option('rentpress_flush_rewrite_rules_flag', true);
    }
    if (!get_option('rentpress_plugin_version')) {
        add_option('rentpress_plugin_version', RENTPRESS_PLUGIN_VERSION);
    }
    require_once RENTPRESS_PLUGIN_ADMIN_DIR . 'admin_install.php';
}
register_activation_hook(__FILE__, 'rentpress_install');

// Deactivate Plugin
function rentpress_deactivation()
{
    require_once RENTPRESS_PLUGIN_ADMIN_DIR . 'admin_deactivate.php';
}
register_deactivation_hook(__FILE__, 'rentpress_deactivation');