<?php
/*

This file houses all of the installation methods for the admin menus and posts

 */
require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'register_rentpress_posts.php';
rentpress_registerAllPostTypes();

// add database tables
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'property_model.php';
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'floorplan_model.php';
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php';
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'refresh_model.php';
require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
$manual_units = rentpress_getAllManualUnits();

rentpress_dropPropertyDBTable();
rentpress_dropFloorplanDBTable();
rentpress_dropUnitDBTable();
rentpress_dropRefreshDBTable();
rentpress_makePropertyDBTable();
rentpress_makeFloorplanDBTable();
rentpress_makeUnitDBTable();
rentpress_makeRefreshDBTable();

foreach ($manual_units as $manual_unit) {
    $manual_unit_array = (array) $manual_unit;
    rentpress_saveUnitData($manual_unit_array);
}