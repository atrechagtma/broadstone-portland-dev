<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://wordkeeper.com
 * @since      1.0.0
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/includes
 * @author     Lance Dockins <info@wordkeeper.com>
 */
class WordKeeper_System_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option('wordkeeper-system-settings');
		delete_option('wordkeeper-system-security-settings');
	}

}
