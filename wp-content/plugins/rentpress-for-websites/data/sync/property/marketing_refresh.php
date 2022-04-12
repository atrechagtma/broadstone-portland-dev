<?php

function rentpress_syncFeedAndWPProperties()
{
    require_once( RENTPRESS_PLUGIN_DATA_SYNC . 'resync.php' );
    $rentpress_options = get_option( 'rentpress_options' );
    $rentpress_sync_properties = array();
    $total_pages = 1;
    $corresponding_meta_keys = rentpress_getCorrespondingWordpressToFeedKeyArray();
    $refresh_data = rentpress_getRefreshData();
    $new_properties = array();
    $hasCredentials = isset($rentpress_options['rentpress_api_credentials_section_api_token']) && isset($rentpress_options['rentpress_api_credentials_section_username']);
    $last_resync_data_call = $hasCredentials ? rentpress_getLastTimePropertiesWereUpdatedInTLC($rentpress_options) : null;

    // Get all wordpress Property and Floorplan Data
    require_once( RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/property_post_type_data.php' );
    $all_wordpress_meta = rentpress_getAllMetaDataForAllPropertiesAndFloorplans();

    // if the tlc call errored and there are credentials, then show it, otherwise only attempt to do the resync if a timestamp was returned from tlc
    if ($hasCredentials && is_object($last_resync_data_call)) {
        if (isset($last_resync_data_call->errors) && $last_resync_data_call->errors['http_request_failed']) {
            echo('Issue with connection: '.$last_resync_data_call->errors['http_request_failed'][0]);
        } else {
            echo('Error Occured: Check Console for more information. Contact 30 Lines if problem persists.');
            var_dump($last_resync_data_call);
        }
    } elseif ($hasCredentials) {
        $last_resync_ts = json_decode($last_resync_data_call['body']);
        if (is_int($last_resync_ts)) {

            // If the refresh time is sooner than the last time tlc was updated, then use the refresh data instead of getting new data
            if ($refresh_data != [] && $last_resync_ts < (int) $refresh_data[0]->last_refresh_time) {
                foreach ($refresh_data as $property) {
                    array_push($new_properties, json_decode($property->property_response));
                }
            } else {
                // Get all Rentpress Data from server
                for ($i = 1; $i <= $total_pages ; $i++) {
                    $response = rentpress_getAllDataForPropertiesResponse($i, $rentpress_options);
                    if (!isset($response->ResponseData->data)) {
                        // TODO: @Ryan Log this event
                        continue;
                    }

                    foreach ($response->ResponseData->data as $property) {
                    array_push($new_properties, $property);
                    rentpress_saveRefreshData($property->Identification->PropertyCode, $property);
                    }
                    $total_pages = $response->ResponseMeta->total_pages;
                }
            }

            if (count($new_properties) > 0) {
                $rentpress_sync_properties = rentpress_standardizeSyncData($new_properties, $rentpress_options);
                $all_wordpress_meta = rentpress_mergeSyncFeedWithWordpressMeta($rentpress_sync_properties, $all_wordpress_meta, $corresponding_meta_keys, $rentpress_options);
            }
        } elseif (isset($last_resync_ts->error->message)) {
            echo('Issue with connection: '.$last_resync_ts->error->message);
        } else {
            echo('Error Occured: Check Console for more information. Contact 30 Lines if problem persists.');
            var_dump($last_resync_ts);
        }
    }
    rentpress_saveManualWordpressData($all_wordpress_meta, $corresponding_meta_keys, $rentpress_options);
}
