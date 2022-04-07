<?php

/*********************************************************
 *
 *  This sync will run everytime a property post is saved
 *  It will save all overrides to the DB
 *
 **********************************************************/

function rentpress_syncFeedAndWPPropertyMeta($property_post_id)
{
    require_once RENTPRESS_PLUGIN_DATA_SYNC . 'resync.php';
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/property_post_type_data.php';
    $rentpress_options = get_option('rentpress_options');
    $corresponding_meta_keys = rentpress_getCorrespondingWordpressToFeedKeyArray();

    // Get all wordpress meta for single property
    $all_wordpress_meta = rentpress_getAllMetaDataForPropertyAndFloorplansByPropertyId($property_post_id);
    $refresh_data = rentpress_getRefreshRow(array_key_first($all_wordpress_meta['all_properties']));

    if (isset($refresh_data->property_response)) {
        $new_properties = array();
        array_push($new_properties, json_decode($refresh_data->property_response));
        $rentpress_sync_properties = rentpress_standardizeSyncData($new_properties, $rentpress_options);
        $all_wordpress_meta = rentpress_mergeSyncFeedWithWordpressMeta($rentpress_sync_properties, $all_wordpress_meta, $corresponding_meta_keys, $rentpress_options);
        // if there are floorplans left, they are manual and need to be resynced as well
        if (count($all_wordpress_meta['all_floorplans']) > 0) {
            rentpress_saveManualWordpressData($all_wordpress_meta, $corresponding_meta_keys, $rentpress_options);
        }
    } else {
        rentpress_saveManualWordpressData($all_wordpress_meta, $corresponding_meta_keys, $rentpress_options);
    }
}
