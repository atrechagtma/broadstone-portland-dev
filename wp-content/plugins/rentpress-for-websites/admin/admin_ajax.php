<?php

add_action('wp_ajax_rentpress_create_unit_action', 'rentpress_create_unit_action');
add_action('wp_ajax_rentpress_edit_unit_action', 'rentpress_edit_unit_action');
add_action('wp_ajax_rentpress_refresh_added_units_action', 'rentpress_refresh_added_units_action');
add_action('wp_ajax_rentpress_delete_unit_action', 'rentpress_delete_unit_action');


function rentpress_create_unit_action() {
    require_once(RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php');
    rentpress_createUnit();
    wp_die();
}

function rentpress_edit_unit_action() {
    require_once(RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php');
    rentpress_editUnit();
    wp_die();
}

function rentpress_refresh_added_units_action() {
    require_once(RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php');
    rentpress_refreshAddedUnits();
    wp_die();
}

function rentpress_delete_unit_action() {
    require_once(RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php');
    rentpress_delete_unit();
    wp_die();
}