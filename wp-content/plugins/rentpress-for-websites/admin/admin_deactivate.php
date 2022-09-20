<?php
/*

This file houses all of the methods for uninstalling the admin menus and posts

 */

// unregister the post type, so the rules are no longer in memory
unregister_post_type('rentpress_property');
unregister_post_type('rentpress_floorplan');
unregister_post_type('rentpress_hood');

// clear the permalinks to remove our post type's rules from the database
flush_rewrite_rules();

// remove crons
if (wp_next_scheduled('rentpress_cron_hook_marketing_sync')) {
    wp_clear_scheduled_hook('rentpress_cron_hook_marketing_sync');
}
if (wp_next_scheduled('rentpress_cron_hook_pricing_sync')) {
    wp_clear_scheduled_hook('rentpress_cron_hook_pricing_sync');
}
if (wp_next_scheduled('rentpress_cron_hook_start_incremental_data_sync')) {
    wp_clear_scheduled_hook('rentpress_cron_hook_start_incremental_data_sync');
}

// remove database tables
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'property_model.php';
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'floorplan_model.php';
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'refresh_model.php';
rentpress_dropPropertyDBTable();
rentpress_dropFloorplanDBTable();
rentpress_dropRefreshDBTable();

delete_option('rentpress_plugin_version');