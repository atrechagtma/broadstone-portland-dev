<?php
/*
Plugin Name: RentPress
Plugin URI: https://rentpress.io/
Version: 6.6.5
Author: <a href="https://30lines.com/">30lines</a>
Description: Connects real estate agents to their property information for any WordPress site. Supports data feeds from: RentCafe, Entrata, RealPage, MRI Software/Vaultware, ResMan, Encasa.
Text Domain: rentPress-wp
License: GPLv3
GNU GPLv3 License Origin: http://www.gnu.org/licenses/gpl-3.0.html
 */
define('RENTPRESS_PLUGIN_DIR', dirname(__FILE__) . '/');
define('RENTPRESS_PLUGIN_ASSETS', plugin_dir_url(__FILE__) . '');
$environmentVariable = defined('WP_RENTPRESS_ENV') ? WP_RENTPRESS_ENV : 'production';
define('RENTPRESS_ENV', $environmentVariable); // change to production for release version
define('RENTPRESS_REFRESH_FREQUENCY', HOUR_IN_SECONDS);
define('RENTPRESS_LANG_KEY', 'rentpress-wp');
define('RENTPRESS_PROPERTIES_CPT', 'properties');
define('RENTPRESS_FLOORPLANS_CPT', 'floorplans');
define('RENTPRESS_NEIGHBORHOODS_CPT', 'neighborhoods');

$rentPress_minimalRequiredPhpVersion = '5.3';

spl_autoload_register('rentPress_autoloader');
function rentPress_autoloader($class_name) {
	if (false !== strpos($class_name, 'rentPress_')) {
		$classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
		$class_file = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
		require_once $classes_dir . $class_file;
	}
}

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function rentPress_noticePhpVersionWrong() {
	global $rentPress_minimalRequiredPhpVersion;
	echo '<div class="updated fade">' .
	__('Error: plugin "RentPress" requires a newer version of PHP to be running.', RENTPRESS_LANG_KEY) .
	'<br/>' . __('Minimal version of PHP required: ', RENTPRESS_LANG_KEY) . '<strong>' . $rentPress_minimalRequiredPhpVersion . '</strong>' .
	'<br/>' . __('Your server\'s PHP version: ', RENTPRESS_LANG_KEY) . '<strong>' . phpversion() . '</strong>' .
		'</div>';
}

function rentPress_PhpVersionCheck() {
	global $rentPress_minimalRequiredPhpVersion;
	if (version_compare(phpversion(), $rentPress_minimalRequiredPhpVersion) < 0) {
		add_action('admin_notices', 'rentPress_noticePhpVersionWrong');
		return false;
	}
	return true;
}

/**
 * Initialize internationalization (i18n) for this plugin.
 * Dev References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 * @return void
 */
function rentPress_i18n_init() {
	$pluginDir = dirname(plugin_basename(__FILE__));
	load_plugin_textdomain(RENTPRESS_LANG_KEY, false, $pluginDir . '/languages/');
}

//////////////////////////////////
// Run initialization
/////////////////////////////////

// Initialize i18n
add_action('plugins_loadedi', 'rentPress_i18n_init');

// Run the version check.
// If it is successful, continue with initialization for this plugin
if (rentPress_PhpVersionCheck()) {
	add_action('plugins_loaded', 'init_rentPress');
}

function init_rentPress() {
	// Only load and run the init function if we know PHP version can parse it
	include_once 'rentPressWP_init.php';
	rentPress_init(__FILE__);

	global $wpdb;
	$wpdb->rp_units = $wpdb->prefix . 'rp_units';
}

// Init service classes for template use
$rentPress_Service['properties'] = rentPress_Properties_Property::get_instance();
$rentPress_Service['properties_meta'] = rentPress_Posts_Meta_Properties::get_instance();
$rentPress_Service['floorplans'] = rentPress_FloorPlans_FloorPlan::get_instance();
$rentPress_Service['floorplans_meta'] = rentPress_Posts_Meta_FloorPlans::get_instance();
$rentPress_Service['units'] = rentPress_Units_Units::get_instance();
$rentPress_Service['unit_meta'] = rentPress_Posts_Meta_Units::get_instance();
$rentPress_Service['search'] = rentPress_Search_Properties::get_instance();

// Data
function darken_color($rgb, $darker = 2) {

	$hash = (strpos($rgb, '#') !== false) ? '#' : '';
	$rgb = (strlen($rgb) == 7) ? str_replace('#', '', $rgb) : ((strlen($rgb) == 6) ? $rgb : false);
	if (strlen($rgb) != 6) {
		return $hash . '000000';
	}

	$darker = ($darker > 1) ? $darker : 1;

	list($R16, $G16, $B16) = str_split($rgb, 2);

	$R = sprintf("%02X", floor(hexdec($R16) / $darker));
	$G = sprintf("%02X", floor(hexdec($G16) / $darker));
	$B = sprintf("%02X", floor(hexdec($B16) / $darker));

	return $hash . $R . $G . $B;
}

// needs conditional for search page
// if( is rentpress template )) {
function rp_vendor_enqueue_script() {
	wp_enqueue_script('range_slider', plugin_dir_url(__FILE__) . 'build/js/client-side.noMaps.js', ['jquery'], null, false);
}
add_action('wp_enqueue_scripts', 'rp_vendor_enqueue_script');
// }
