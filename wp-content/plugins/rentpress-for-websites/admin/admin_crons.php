<?php
// Add necessary cron functions
// Cron just needs to load the marketing resync functions and run it
function rentpress_cron_sync_data()
{
    $sync_index = get_option('rentpress_cron_start_incremental_data_sync_index');
    $sync_limit = 50;
    if ($sync_index === "start_tlc_sync") {
        require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/remote_data_refresh.php';
        rentpress_updateRefreshTable();
        update_option('rentpress_cron_start_incremental_data_sync_index', "zero");
        wp_schedule_single_event(time(), 'rentpress_cron_hook_sync_data');
    } else {
        if ($sync_index === "zero") {
            $sync_index = 0;
            error_log("starting meta sync");
        }
        require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
        require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/marketing_refresh.php';
        error_log("meta sync at " . $sync_index);
        $refresh_properties = rentpress_getPaginatedRefreshData($sync_index, $sync_limit);
        if (empty($refresh_properties)) {
            delete_option('rentpress_cron_start_incremental_data_sync_index');
            error_log("ending meta sync");
        } else {
            update_option('rentpress_cron_start_incremental_data_sync_index', $sync_index + $sync_limit);
            rentpress_syncFeedAndWPProperties($refresh_properties);
            wp_schedule_single_event(time(), 'rentpress_cron_hook_sync_data');
        }
    }
}
add_action('rentpress_cron_hook_sync_data', 'rentpress_cron_sync_data');

function rentpress_cron_start_incremental_data_sync()
{
    if (!get_option('rentpress_cron_start_incremental_data_sync_index')) {
        add_option('rentpress_cron_start_incremental_data_sync_index', "start_tlc_sync");
        wp_schedule_single_event(time(), 'rentpress_cron_hook_sync_data');
    }
}
add_action('rentpress_cron_hook_start_incremental_data_sync', 'rentpress_cron_start_incremental_data_sync');

if (!wp_next_scheduled('rentpress_cron_hook_start_incremental_data_sync')) {
    wp_schedule_event(time(), 'hourly', 'rentpress_cron_hook_start_incremental_data_sync');
    delete_option('rentpress_cron_start_incremental_data_sync_index');
}