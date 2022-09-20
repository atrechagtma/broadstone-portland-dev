<?php
require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
require_once RENTPRESS_PLUGIN_DATA_SYNC . 'resync.php';
require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/property_post_type_data.php';

function rentpress_syncFeedAndWPProperties($rentpress_refresh_properties = array())
{
    $property_codes = array();
    if (empty($rentpress_refresh_properties) && empty($_POST['propertyCodes'])) {
        return 'no property information provided';
    } elseif (!empty($_POST['propertyCodes']) && is_array($_POST['propertyCodes'])) {
        foreach ($_POST['propertyCodes'] as $post_property_code) {
            array_push($property_codes, sanitize_text_field($post_property_code));
        }
        $rentpress_refresh_properties = rentpress_getRefreshRowsFromPropertyCodes($property_codes);
    } elseif (!empty($rentpress_refresh_properties)) {
        foreach ($rentpress_refresh_properties as $rentpress_refresh_property) {
            array_push($property_codes, $rentpress_refresh_property->property_code);
        }
    }
    if (!is_array($rentpress_refresh_properties) || empty($rentpress_refresh_properties)) {
        return 'need array of property refresh data';
    }

    $rentpress_options = get_option('rentpress_options');
    $corresponding_meta_keys = rentpress_getCorrespondingWordpressToFeedKeyArray();

    $property_responses = array();
    foreach ($rentpress_refresh_properties as $rentpress_refresh_property) {
        array_push($property_responses, json_decode($rentpress_refresh_property->property_response));
    }

    $all_wordpress_meta = rentpress_getAllMetaDataForAllPropertiesAndFloorplans($property_codes);
    $rentpress_standardized_properties = rentpress_standardizeSyncData($property_responses, $rentpress_options);
    rentpress_mergeSyncFeedWithWordpressMeta($rentpress_standardized_properties, $all_wordpress_meta, $corresponding_meta_keys, $rentpress_options);
}