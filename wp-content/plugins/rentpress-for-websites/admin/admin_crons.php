<?php
// Add neccessary cron functions
// Cron just needs to load the marketing resync functions and run it
function rentpress_cron_marketing_sync()
{
    require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/marketing_refresh.php';
    rentpress_syncFeedAndWPProperties();
}
add_action('rentpress_cron_hook_marketing_sync', 'rentpress_cron_marketing_sync');

// If cron does not exist, make it exist
// this is temp
if (!wp_next_scheduled('rentpress_cron_hook_marketing_sync')) {
    wp_schedule_event(time(), 'hourly', 'rentpress_cron_hook_marketing_sync');
}

// // Cron just needs to load the pricing resync functions and run it
// function rentpress_cron_pricing_sync()
// {
//     require_once( RENTPRESS_PLUGIN_DATA_SYNC . 'property/pricing_refresh.php' );
//     rentpress_syncFeedAndWPProperties();
// }
// add_action( 'rentpress_cron_hook_pricing_sync', 'rentpress_cron_pricing_sync' );

// // If cron does not exist, make it exist
// if ( ! wp_next_scheduled( 'rentpress_cron_hook_pricing_sync' ) ) {
//     wp_schedule_event( time(), 'hourly', 'rentpress_cron_hook_pricing_sync' );
// }
