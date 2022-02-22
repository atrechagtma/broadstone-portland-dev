<?php
/**
 * RentPress Init Method
 * Author:  foster30lines
 * Company: 30Lines
 * Purpose: The purpose of this method is to initialize the RentPress Service plugin custom post types,
 * taxonomies, meta boxes, menu items, and other various dependencies needed to function smoothly.
 */

function rentPress_init($file) {
	$rentPressPlugin = new rentPress_Plugin();
	$rentPressOptions = new rentPress_Options();
	$rentPressPlugin['version'] = '6.6.5';
	$rentPressPlugin['path'] = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR;
	$rentPressPlugin['url'] = plugin_dir_url(__FILE__);
	$rentPressPlugin['settings_page'] = new rentPress_SettingsPages();

	// Install the plugin
	// NOTE: this file gets run each time you *activate* the plugin.
	// So in WP when you "install" the plugin, all that does is dump its files in the plugin-templates directory
	// but it does not call any of its code.
	// So here, the plugin tracks whether or not it has run its install operation, and we ensure it is run only once
	// on the first activation
	if (!$rentPressPlugin->isInstalled()) {
		$rentPressPlugin->install();
	} else {
		// Perform any version-upgrade activities prior to activation (e.g. database changes)
		$rentPressPlugin->upgrade();
	}

	// Add callbacks to hooks
	$rentPressPlugin->addActionsAndFilters();

	// Add shortcodes
	$rentPressPlugin->addShortCodes();

	$rentPressPlugin->run();

	if (!$file) {
		$file = __FILE__;
	}

	// Register the Plugin Activation Hook
	register_activation_hook($file, [$rentPressPlugin, 'activate']);

	// // Register the Plugin Deactivation Hook
	register_deactivation_hook($file, array($rentPressPlugin, 'deactivate'));
}
