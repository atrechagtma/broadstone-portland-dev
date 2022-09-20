<?php
require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
require_once RENTPRESS_PLUGIN_DATA_SYNC . 'resync.php';
require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/property_post_type_data.php';

function rentpress_saveWPProperties()
{
    $excluded_property_codes = array();
    $response = array(
        "finished_sync" => false,
        "error" => false,
        "errorMessage" => '',
        "message" => '',
        "manualPropertyCount" => 0,
    );
    if (!isset($_POST['offset'])) {
        $response["error"] = true;
        $response["errorMessage"] = "Offset has to be defined";
        return $response;
    }
    $offset = intval(sanitize_text_field($_POST['offset']));
    $limit = !empty($_POST['limit']) ? intval(sanitize_text_field($_POST['limit'])) : 10;

    if (!empty($_POST['propertyCodes']) && is_array($_POST['propertyCodes'])) {
        foreach ($_POST['propertyCodes'] as $post_property_code) {
            array_push($excluded_property_codes, sanitize_text_field($post_property_code));
        }
    }
    $response['manual_property_count'] = empty($_POST['manualPropertyCount']) ? rentpress_getManualPropertiesCountForSync($excluded_property_codes) : intval(sanitize_text_field($_POST['manualPropertyCount']));

    $rentpress_options = get_option('rentpress_options');
    $corresponding_meta_keys = rentpress_getCorrespondingWordpressToFeedKeyArray();
    $all_wordpress_meta = rentpress_getPaginatedMetaDataForManualPropertiesSync($excluded_property_codes, $offset, $limit);
    if (!empty($all_wordpress_meta['all_properties'])) {
        rentpress_saveManualWordpressData($all_wordpress_meta, $corresponding_meta_keys, $rentpress_options);
    } else {
        $response["message"] = "WordPress didn't return any published manual properties";
        $response["finished_sync"] = true;
    }

    return $response;
}