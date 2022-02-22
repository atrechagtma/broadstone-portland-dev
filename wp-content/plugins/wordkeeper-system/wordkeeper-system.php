<?php

/**
 *
 * @link              http://wordkeeper.com
 * @since             1.0.0
 * @package           WordKeeper_System
 *
 * @wordpress-plugin
 * Plugin Name:       WordKeeper System
 * Plugin URI:        http://wordkeeper.com/plugins/wordkeeper-system
 * Description:       WordKeeper hosting management plugin
 * Version:           1.1.0
 * Author:            WordKeeper
 * Author URI:        http://wordkeeper.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordkeeper-system
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wordkeeper-system-activator.php
 */
function activate_wordkeeper_system() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordkeeper-system-activator.php';
	WordKeeper_System_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wordkeeper-system-deactivator.php
 */
function deactivate_wordkeeper_system() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordkeeper-system-deactivator.php';
	WordKeeper_System_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wordkeeper_system' );
register_deactivation_hook( __FILE__, 'deactivate_wordkeeper_system' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordkeeper-system.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wordkeeper_system() {

	$plugin = new WordKeeper_System();
	$plugin->run();

}
run_wordkeeper_system();
