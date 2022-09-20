<?php

add_action('wp_ajax_rentpress_create_unit_action', 'rentpress_create_unit_action');
add_action('wp_ajax_rentpress_edit_unit_action', 'rentpress_edit_unit_action');
add_action('wp_ajax_rentpress_refresh_added_units_action', 'rentpress_refresh_added_units_action');
add_action('wp_ajax_rentpress_delete_unit_action', 'rentpress_delete_unit_action');
add_action('wp_ajax_rentpress_getAllRemoteData', 'rentpress_getAllRemoteData');
add_action('wp_ajax_rentpress_getAllMarketingDataForProperties', 'rentpress_getAllMarketingDataForProperties');
add_action('wp_ajax_rentpress_saveManualPropertyDataToDB', 'rentpress_saveManualPropertyDataToDB');

function rentpress_create_unit_action()
{
    require_once RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php';
    rentpress_createUnit();
    wp_die();
}

function rentpress_edit_unit_action()
{
    require_once RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php';
    rentpress_editUnit();
    wp_die();
}

function rentpress_refresh_added_units_action()
{
    require_once RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php';
    rentpress_refreshAddedUnits();
    wp_die();
}

function rentpress_delete_unit_action()
{
    require_once RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php';
    rentpress_delete_unit();
    wp_die();
}

function rentpress_getAllRemoteData()
{
    require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/remote_data_refresh.php';
    $results = rentpress_updateRefreshTable();
    wp_send_json($results); // this is required to terminate immediately and return a proper response
}

function rentpress_getAllMarketingDataForProperties()
{
    require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/marketing_refresh.php';
    $results = rentpress_syncFeedAndWPProperties();
    wp_send_json($results); // this is required to terminate immediately and return a proper response
}

function rentpress_saveManualPropertyDataToDB()
{
    require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/manual_property_refresh.php';
    $results = rentpress_saveWPProperties();
    wp_send_json($results); // this is required to terminate immediately and return a proper response
}