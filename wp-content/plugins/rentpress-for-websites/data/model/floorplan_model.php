<?php

function rentpress_saveFloorplanData($floorplan)
{
    if (
        isset($floorplan['floorplan_parent_property_code']) &&
        $floorplan['floorplan_parent_property_code'] != '' &&
        isset($floorplan['floorplan_code']) &&
        $floorplan['floorplan_code'] != '' &&
        isset($floorplan['floorplan_post_id']) &&
        $floorplan['floorplan_post_id'] != '') {

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'rentpress_floorplans';
        $result = 'NoCode';

        $result = $wpdb->replace(
            $table_name,
            $floorplan
        );

        // TODO: @Ryan this needs to log the result for the user
        if ($result == 0 || $result == 'NoCode') {
            var_dump($result);
        }
    }
}

function rentpress_standardizeFloorplanFeedData($floorplan)
{
    return [
        'floorplan_code' => $floorplan->Identification->FloorPlanCode,
        'floorplan_parent_property_code' => $floorplan->Identification->ParentPropertyCode,
        'floorplan_unit_type_mapping' => isset($floorplan->Identification->UnitTypeMapping) ? $floorplan->Identification->UnitTypeMapping : null,
        'floorplan_name' => isset($floorplan->Information->FloorPlanName) ? $floorplan->Information->FloorPlanName : null,
        'floorplan_description' => isset($floorplan->Information->Description) ? $floorplan->Information->Description : null,
        'floorplan_availability_url' => isset($floorplan->Information->AvailabilityURL) ? $floorplan->Information->AvailabilityURL : null,
        'floorplan_image' => isset($floorplan->Information->FloorPlanImage) ? $floorplan->Information->FloorPlanImage : null,
        'floorplan_pdf' => isset($floorplan->Information->FloorPlanPDF) ? $floorplan->Information->FloorPlanPDF : null,
        'floorplan_units_available' => null,
        'floorplan_units_available_30' => null,
        'floorplan_units_available_60' => null,
        'floorplan_max_roomates' => isset($floorplan->Information->MaxRoomates) ? $floorplan->Information->MaxRoomates : null,
        'floorplan_offices' => isset($floorplan->Information->Offices) ? $floorplan->Information->Offices : null,
        'floorplan_matterport_url' => isset($floorplan->Information->MatterportUrl) ? $floorplan->Information->MatterportUrl : null,
        'floorplan_specials_message' => isset($floorplan->Specials->Message) ? $floorplan->Specials->Message : null,
        'floorplan_images' => isset($floorplan->Images) ? $floorplan->Images : null,
        'floorplan_videos' => isset($floorplan->Videos) ? $floorplan->Videos : null,
        'floorplan_features' => isset($floorplan->Amenities) ? json_encode($floorplan->Amenities) : null,
        'floorplan_bedrooms' => isset($floorplan->Rooms->Beds) ? $floorplan->Rooms->Beds : null,
        'floorplan_bathrooms' => isset($floorplan->Rooms->Baths) ? $floorplan->Rooms->Baths : null,
        'floorplan_rent_min' => isset($floorplan->Rent->Min) && (int) $floorplan->Rent->Min > 100 ? $floorplan->Rent->Min : null,
        'floorplan_rent_max' => isset($floorplan->Rent->Max) && (int) $floorplan->Rent->Max > 100 ? $floorplan->Rent->Max : null,
        'floorplan_sqft_min' => isset($floorplan->SquareFeet->Min) ? $floorplan->SquareFeet->Min : null,
        'floorplan_sqft_max' => isset($floorplan->SquareFeet->Max) ? $floorplan->SquareFeet->Max : null,
        'floorplan_deposit_min' => isset($floorplan->Deposit->Min) ? $floorplan->Deposit->Min : null,
        'floorplan_deposit_max' => isset($floorplan->Deposit->Max) ? $floorplan->Deposit->Max : null,
    ];
}

function rentpress_retrieveFloorplanCount()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    return $wpdb->query("SELECT COUNT(*) FROM $table_name");
}

function rentpress_removeFloorplanData($floorplan_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    $wpdb->query("DELETE FROM $table_name WHERE `floorplan_code` = '$floorplan_code'");
}

function rentpress_emptyFloorplanTable()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    $wpdb->query("TRUNCATE TABLE $table_name");
}

function rentpress_makeFloorPlanDBTable()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    $sql = "CREATE TABLE $table_name (
		floorplan_code varchar(191) NOT NULL,
        floorplan_post_id varchar(191) NOT NULL,
        floorplan_parent_property_code varchar(191) NOT NULL,
        floorplan_parent_property_post_id varchar(191) NOT NULL,
        floorplan_post_link longtext,
        floorplan_parent_property_post_link longtext,
        floorplan_name longtext,
        floorplan_parent_property_name longtext,
        floorplan_unit_type_mapping longtext,
        floorplan_description longtext,
        floorplan_availability_url longtext,
        floorplan_image longtext,
        floorplan_pdf longtext,
        floorplan_available boolean,
        floorplan_units_total smallint,
        floorplan_units_available smallint,
        floorplan_units_available_30 smallint,
        floorplan_units_available_60 smallint,
        floorplan_units_unavailable smallint,
        floorplan_max_roomates longtext,
        floorplan_offices longtext,
        floorplan_matterport_url longtext,
        floorplan_specials_message longtext,
        floorplan_specials_link longtext,
        floorplan_images longtext,
        floorplan_featured_image longtext,
        floorplan_featured_image_thumbnail longtext,
        floorplan_videos longtext,
        floorplan_features longtext,
        floorplan_bedrooms longtext,
        floorplan_bathrooms longtext,
        floorplan_rent_min longtext,
        floorplan_rent_max longtext,
        floorplan_sqft_min longtext,
        floorplan_sqft_max longtext,
        floorplan_deposit_min longtext,
        floorplan_deposit_max longtext,
        floorplan_rent_base int,
        floorplan_rent_market int,
        floorplan_rent_term int,
        floorplan_rent_effective int,
        floorplan_rent_best int,
        floorplan_rent_type_selection_cost int,
        floorplan_rent_type_selection varchar(191),
        floorplan_parent_property_gravity_form longtext,
        floorplan_parent_property_contact_link longtext,
        floorplan_parent_property_contact_type longtext,
        floorplan_parent_property_accent_color longtext,
        floorplan_parent_property_use_accent_color longtext,
        floorplan_parent_property_add_on_fields longtext,

		UNIQUE KEY floorplan_code (floorplan_code)
	) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// TODO: 7.1 @Charles Make this run on plugin update, install, and delete
function rentpress_dropFloorPlanDBTable()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}