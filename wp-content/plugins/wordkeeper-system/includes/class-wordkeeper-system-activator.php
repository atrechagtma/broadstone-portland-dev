<?php

/**
 * Fired during plugin activation
 *
 * @link       http://wordkeeper.com
 * @since      1.0.0
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/includes
 * @author     Lance Dockins <info@wordkeeper.com>
 */
class WordKeeper_System_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$settings = get_option('wordkeeper-system-settings');
		$security_settings = get_option('wordkeeper-system-security-settings');
		
		if(empty($security_settings)) {
			$default = array(
				'protection-comments' => false,
				'protection-login' => false
			);
			update_option('wordkeeper-system-security-settings', $default, false);
		}
		
		if(empty($settings)) {
			$default = array(
				'protocol-agnostic' => true,
				'heartbeat-frequency' => 60,
				'heartbeat-permission' => 'allow-heartbeat-post-edit'
			);
			update_option('wordkeeper-system-settings', $default, false);
		}
	}

}
