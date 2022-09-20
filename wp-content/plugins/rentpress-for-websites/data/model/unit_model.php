<?php

function rentpress_saveUnitData($unit)
{
    if (isset($unit['unit_code']) && $unit['unit_code'] != '' &&
        isset($unit['unit_parent_property_code']) && $unit['unit_parent_property_code'] != '' &&
        isset($unit['unit_parent_floorplan_code']) && $unit['unit_parent_floorplan_code'] != '') {

        global $wpdb;
        $table_name = $wpdb->prefix . 'rentpress_units';
        $result = 'NoCode';

        $result = $wpdb->replace(
            $table_name,
            $unit
        );

        // TODO: this needs to log the result for the user
        if ($result == 0 || $result == 'NoCode') {
            var_dump($result);
        }
    }
}

function rentpress_updateApplyLinkForAllUnitsOfAProperty($property_code, $apply_link)
{
    if (isset($property_code)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rentpress_units';
        $wpdb->query("UPDATE $table_name SET `unit_availability_url` = '$apply_link' WHERE `unit_parent_property_code` = '$property_code'");
    }
}

function rentpress_deleteAllFeedUnits()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';
    $wpdb->query("DELETE FROM $table_name WHERE `unit_is_feed` = 1");
}

function rentpress_deleteAllFeedUnitsForAProperty($property_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';
    $wpdb->query("DELETE FROM $table_name WHERE `unit_is_feed` = 1 AND `unit_parent_property_code` = '$property_code'");
}

function rentpress_deleteAllFeedUnitsForProperties($property_codes)
{
    global $wpdb;

    $codes_count = count($property_codes);
    $sql_code_str = "";
    foreach ($property_codes as $key => $code) {
        $sql_code_str .= "(`unit_parent_property_code` = '$code')";
        if ($key != $codes_count - 1) {
            $sql_code_str .= " OR ";
        }
    }
    $table_name = $wpdb->prefix . 'rentpress_units';
    $wpdb->query("DELETE FROM $table_name WHERE `unit_is_feed` = 1 AND $sql_code_str");
}

function rentpress_standardizeUnitFeedData($unit)
{
    return [
        'unit_code' => $unit->Identification->UnitCode,
        'unit_parent_property_code' => $unit->Identification->ParentPropertyCode,
        'unit_parent_floorplan_code' => $unit->Identification->ParentFloorPlanCode,
        'unit_building_number' => isset($unit->Identification->BuildingNumber) ? $unit->Identification->BuildingNumber : null,
        'unit_space_id' => isset($unit->Identification->UnitSpaceID) ? $unit->Identification->UnitSpaceID : null,
        'unit_name' => isset($unit->Information->Name) ? $unit->Information->Name : null,
        'unit_available_on' => isset($unit->Information->AvailableOn) ? $unit->Information->AvailableOn : null,
        'unit_ready_date' => isset($unit->Information->ReadyDate) ? $unit->Information->ReadyDate : null,
        'unit_availability_url' => isset($unit->Information->AvailabilityURL) ? $unit->Information->AvailabilityURL : null,
        'unit_type' => isset($unit->Information->UnitType) ? $unit->Information->UnitType : null,
        'unit_available' => isset($unit->Information->isAvailable) ? $unit->Information->isAvailable : null,
        'unit_specials_message' => isset($unit->Information->SpecialsMessage) ? $unit->Information->SpecialsMessage : null,
        'unit_floorplan_image' => isset($unit->Information->FloorPlanImage) ? $unit->Information->FloorPlanImage : null,
        'unit_schedule_tour_url' => isset($unit->QuickLinks->ScheduleTourUrl) ? $unit->QuickLinks->ScheduleTourUrl : null,
        'unit_quote_url' => isset($unit->QuickLinks->QuoteUrl) ? $unit->QuickLinks->QuoteUrl : null,
        'unit_matterport_url' => isset($unit->QuickLinks->MatterportUrl) ? $unit->QuickLinks->MatterportUrl : null,
        'unit_application_url' => isset($unit->QuickLinks->Application) ? $unit->QuickLinks->Application : null,
        'unit_rent_base' => isset($unit->Rent->Amount) && (int) $unit->Rent->Amount > 100 ? (int) $unit->Rent->Amount : null,
        'unit_rent_effective' => isset($unit->Rent->EffectiveRent) && (int) $unit->Rent->EffectiveRent > 100 ? (int) $unit->Rent->EffectiveRent : null,
        'unit_rent_market' => isset($unit->Rent->MarketRent) && (int) $unit->Rent->MarketRent > 100 ? (int) $unit->Rent->MarketRent : null,
        'unit_rent_min' => isset($unit->Rent->MinRent) && (int) $unit->Rent->MinRent > 100 ? (int) $unit->Rent->MinRent : null,
        'unit_rent_max' => isset($unit->Rent->MaxRent) && (int) $unit->Rent->MaxRent > 100 ? (int) $unit->Rent->MaxRent : null,
        'unit_rent_terms' => isset($unit->Rent->TermRent) ? json_encode($unit->Rent->TermRent) : null,
        'unit_rent_term_best' => null,
        'unit_rent_best' => null,
        'unit_bedrooms' => isset($unit->Rooms->Bedrooms) ? $unit->Rooms->Bedrooms : null,
        'unit_bathrooms' => isset($unit->Rooms->Bathrooms) ? $unit->Rooms->Bathrooms : null,
        'unit_floor_level' => isset($unit->Rooms->FloorLevel) ? $unit->Rooms->FloorLevel : null,
        'unit_sqft' => isset($unit->SquareFeet->Max) ? $unit->SquareFeet->Max : null,
        'unit_features' => isset($unit->Amenities) && count($unit->Amenities) > 0 ? json_encode($unit->Amenities) : null,
        'unit_images' => isset($unit->StandardizedImages) && count($unit->StandardizedImages) > 0 ? json_encode($unit->StandardizedImages) : null,
        'unit_image_urls' => isset($unit->ImageUrls) && count($unit->ImageUrls) > 0 ? json_encode($unit->ImageUrls) : null,
        'unit_images_raw' => isset($unit->Images) && count($unit->Images) > 0 ? json_encode($unit->Images) : null,
        'unit_videos' => isset($unit->Videos) && count($unit->Videos) > 0 ? json_encode($unit->Videos) : null,
        'unit_is_feed' => true,
    ];
}

function rentpress_makeUnitDBTable()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'rentpress_units';

    $sql = "CREATE TABLE $table_name (
		unit_code varchar(191) NOT NULL,
		unit_parent_property_code varchar(191) NOT NULL,
		unit_parent_floorplan_code varchar(191) NOT NULL,
		unit_building_number longtext,
		unit_space_id longtext,
		unit_name longtext,
		unit_available_on longtext,
		unit_ready_date longtext,
		unit_availability_url longtext,
		unit_type longtext,
		unit_available longtext,
		unit_specials_message longtext,
		unit_floorplan_image longtext,
		unit_schedule_tour_url longtext,
		unit_quote_url longtext,
		unit_matterport_url longtext,
		unit_application_url longtext,
		unit_rent_base longtext,
		unit_rent_effective longtext,
		unit_rent_market longtext,
		unit_rent_min longtext,
		unit_rent_max longtext,
		unit_rent_terms longtext,
		unit_rent_term_best longtext,
		unit_rent_best longtext,
		unit_bedrooms longtext,
		unit_bathrooms longtext,
		unit_floor_level longtext,
		unit_sqft longtext,
		unit_features longtext,
		unit_images longtext,
		unit_image_urls longtext,
		unit_images_raw longtext,
		unit_videos longtext,
		unit_rent_type_selection_cost int,
		unit_rent_type_selection varchar(191),
		unit_is_feed boolean,

		UNIQUE KEY unit_code (unit_code)
	) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// TODO: 7.1 @Charles Make this run on plugin update, install, and delete
function rentpress_dropUnitDBTable()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

/// Ajax functions
function rentpress_createUnit()
{
    global $wpdb;

    $unit_code = sanitize_text_field($_POST['unit_code']);
    $unit_parent_property_code = sanitize_text_field($_POST['unit_parent_property_code']);
    $unit_parent_floorplan_code = sanitize_text_field($_POST['unit_parent_floorplan_code']);
    $unit_name = sanitize_text_field($_POST['unit_name']);
    $unit_sqft = !empty($_POST['unit_sqft']) ? sanitize_text_field($_POST['unit_sqft']) : null;
    $unit_rent_min = !empty($_POST['unit_rent_min']) ? sanitize_text_field($_POST['unit_rent_min']) : null;
    $unit_rent_max = !empty($_POST['unit_rent_max']) ? sanitize_text_field($_POST['unit_rent_max']) : null;
    $unit_rent_base = !empty($_POST['unit_rent_base']) ? sanitize_text_field($_POST['unit_rent_base']) : null;
    $unit_rent_market = !empty($_POST['unit_rent_market']) ? sanitize_text_field($_POST['unit_rent_market']) : null;
    $unit_rent_term_best = !empty($_POST['unit_rent_term_best']) ? sanitize_text_field($_POST['unit_rent_term_best']) : null;
    $unit_rent_effective = !empty($_POST['unit_rent_effective']) ? sanitize_text_field($_POST['unit_rent_effective']) : null;
    $unit_rent_best = !empty($_POST['unit_rent_best']) ? sanitize_text_field($_POST['unit_rent_best']) : null;
    $unit_ready_date = date_create_from_format('Y-m-d', $_POST['unit_ready_date'])->format('n/j/Y');
    $unit_availability_url = !empty($_POST['unit_availability_url']) ? esc_url_raw($_POST['unit_availability_url']) : null;
    $unit_is_feed = false;
    $table_name = $wpdb->prefix . 'rentpress_units';

    if ($wpdb->insert($table_name, array(
        'unit_code' => $unit_code,
        'unit_parent_property_code' => $unit_parent_property_code,
        'unit_parent_floorplan_code' => $unit_parent_floorplan_code,
        'unit_name' => $unit_name,
        'unit_sqft' => $unit_sqft,
        'unit_rent_min' => $unit_rent_min,
        'unit_rent_max' => $unit_rent_max,
        'unit_rent_base' => $unit_rent_base,
        'unit_rent_market' => $unit_rent_market,
        'unit_rent_term_best' => $unit_rent_term_best,
        'unit_rent_effective' => $unit_rent_effective,
        'unit_rent_best' => $unit_rent_best,
        'unit_ready_date' => $unit_ready_date,
        'unit_availability_url' => $unit_availability_url,
        'unit_is_feed' => $unit_is_feed,
    )) == false) {
        echo 0;
    } else {
        echo 1;
    }
    // When floorplan is updated, run the single property resync to maintain data integrity
    if (isset($unit_parent_property_code)) {
        $args = array(
            'post_type' => 'rentpress_property',
            'meta_query' => array(
                array(
                    'key' => 'rentpress_custom_field_property_code',
                    'value' => $unit_parent_property_code,
                ),
            ),
        );
        $property_query = new WP_Query($args);
        require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/single_property_refresh.php';
        rentpress_syncFeedAndWPPropertyMeta($property_query->posts[0]->ID);
    }
}

function rentpress_editUnit()
{
    global $wpdb;
    $unit_code = sanitize_text_field($_POST['unit_edit_code']);
    $unit_parent_property_code = sanitize_text_field($_POST['unit_edit_parent_property_code']);
    $unit_parent_floorplan_code = sanitize_text_field($_POST['unit_edit_parent_floorplan_code']);
    $unit_name = sanitize_text_field($_POST['unit_edit_name']);
    $unit_sqft = !empty($_POST['unit_edit_sqft']) ? sanitize_text_field($_POST['unit_edit_sqft']) : null;
    $unit_rent_min = !empty($_POST['unit_edit_rent_min']) ? sanitize_text_field($_POST['unit_edit_rent_min']) : null;
    $unit_rent_max = !empty($_POST['unit_edit_rent_max']) ? sanitize_text_field($_POST['unit_edit_rent_max']) : null;
    $unit_rent_base = !empty($_POST['unit_edit_rent_base']) ? sanitize_text_field($_POST['unit_edit_rent_base']) : null;
    $unit_rent_market = !empty($_POST['unit_edit_rent_market']) ? sanitize_text_field($_POST['unit_edit_rent_market']) : null;
    $unit_rent_term_best = !empty($_POST['unit_edit_rent_term_best']) ? sanitize_text_field($_POST['unit_edit_rent_term_best']) : null;
    $unit_rent_effective = !empty($_POST['unit_edit_rent_effective']) ? sanitize_text_field($_POST['unit_edit_rent_effective']) : null;
    $unit_rent_best = !empty($_POST['unit_edit_rent_best']) ? sanitize_text_field($_POST['unit_edit_rent_best']) : null;
    $unit_ready_date = date_create_from_format('Y-m-d', $_POST['unit_edit_ready_date'])->format('n/j/Y');
    $unit_availability_url = !empty($_POST['unit_edit_availability_url']) ? esc_url_raw($_POST['unit_edit_availability_url']) : null;
    $unit_is_feed = false;
    $table_name = $wpdb->prefix . 'rentpress_units';

    if ($wpdb->replace($table_name, array(
        'unit_code' => $unit_code,
        'unit_parent_property_code' => $unit_parent_property_code,
        'unit_parent_floorplan_code' => $unit_parent_floorplan_code,
        'unit_name' => $unit_name,
        'unit_sqft' => $unit_sqft,
        'unit_rent_min' => $unit_rent_min,
        'unit_rent_max' => $unit_rent_max,
        'unit_rent_base' => $unit_rent_base,
        'unit_rent_market' => $unit_rent_market,
        'unit_rent_term_best' => $unit_rent_term_best,
        'unit_rent_effective' => $unit_rent_effective,
        'unit_rent_best' => $unit_rent_best,
        'unit_ready_date' => $unit_ready_date,
        'unit_availability_url' => $unit_availability_url,
        'unit_is_feed' => $unit_is_feed,
    )) == false) {
        echo 0;
    } else {
        echo 1;
    }
    // When floorplan is updated, run the single property resync to maintain data integrity
    if (isset($unit_parent_property_code)) {
        $args = array(
            'post_type' => 'rentpress_property',
            'meta_query' => array(
                array(
                    'key' => 'rentpress_custom_field_property_code',
                    'value' => $unit_parent_property_code,
                ),
            ),
        );
        $property_query = new WP_Query($args);
        require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/single_property_refresh.php';
        rentpress_syncFeedAndWPPropertyMeta($property_query->posts[0]->ID);
    }
}

function rentpress_refreshAddedUnits()
{
    if (isset($_POST['unit_parent_floorplan_code'])) {
        require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
        $units = rentpress_getAllUnitsForFloorplan($_POST['unit_parent_floorplan_code']);
        foreach ($units as $unit) {
            if ($unit->unit_is_feed) {
                $source = 'Feed';
                $editButton = '';
            } else {
                $source = 'Manual';
                $encodedUnit = json_encode($unit);
                $editButton = "<div class='rentpress-edit-unit' onclick='openEditUnitModal($encodedUnit)'>EDIT</div>";
            }

            if ($unit->unit_ready_date != null) {
                $available_date = 'Available: ' . $unit->unit_ready_date;
            } else {
                $available_date = '';
            }

            echo '<div class="rentpress-unit-card">
					<div class="rentpress-unit-card-info">
						<h3>' . $unit->unit_name . '</h3>
						<span>Selected Rent: ' . $unit->unit_rent_type_selection_cost . '</span>
						<span>Sq Ft: ' . $unit->unit_sqft . '</span>
						<span>' . $available_date . '</span>
						<span>Source: ' . $source . '</span>
						' . $editButton . '
					</div>
				</div>';
        }
    }
}

function rentpress_delete_unit()
{
    global $wpdb;
    $unitCode = sanitize_text_field($_POST['unit_code']);
    $table_name = $wpdb->prefix . 'rentpress_units';
    $where = array('unit_code' => $unitCode);
    if ($wpdb->delete($table_name, $where) == false) {
        echo 0;
    } else {
        echo 1;
    }
}