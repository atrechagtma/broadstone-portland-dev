<?php

function rentpress_savePropertyData($property)
{
    if (isset($property['property_code']) && $property['property_code'] != '' &&
        isset($property['property_post_id']) && $property['property_post_id'] != '') {
        // this check is because the resync adds values to the property array
        unset($property['floorplans']);

        if (isset($property['property_bed_types'])) {
            sort($property['property_bed_types']);
            $property['property_bed_types'] = json_encode($property['property_bed_types']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'rentpress_properties';
        $result = 'NoCode';

        $result = $wpdb->replace(
            $table_name,
            $property
        );

        // TODO: @Ryan this needs to log the result for the user
        if ($result == false || $result == 'NoCode') {

        }
    }
}

function rentpress_standardizePropertyFeedData($property)
{
    return [
        'property_code' => $property->Identification->PropertyCode,
        'property_source' => isset($property->Identification->PropertySource) ? $property->Identification->PropertySource : null,
        'property_name' => isset($property->Information->PropertyName) ? $property->Information->PropertyName : null,
        'property_description' => isset($property->Information->Description) ? $property->Information->Description : null,
        'property_staff_description' => isset($property->Information->StaffDescription) ? $property->Information->StaffDescription : null,
        'property_email' => isset($property->Information->Email) ? $property->Information->Email : null,
        'property_phone_number' => isset($property->Information->PhoneNumber) ? $property->Information->PhoneNumber : null,
        'property_website' => isset($property->Information->Website) ? $property->Information->Website : null,
        'property_availability_url' => isset($property->Information->AvailabilityURL) ? $property->Information->AvailabilityURL : null,
        'property_fax' => isset($property->Information->Fax) ? $property->Information->Fax : null,
        'property_map_pdf' => isset($property->Information->MapPDF) ? $property->Information->MapPDF : null,
        'property_office_hours' => isset($property->Information->OfficeHoursStandard) ? json_encode($property->Information->OfficeHoursStandard) : null,
        'property_timezone' => isset($property->Information->TimeZone) ? $property->Information->TimeZone : null,
        'property_tour_url' => isset($property->Information->TourUrl) ? $property->Information->TourUrl : null,
        'property_specials_message' => (isset($property->Specials) && isset($property->Specials->Message)) ? $property->Specials->Message : null,
        'property_address' => isset($property->Location->Address) ? $property->Location->Address : null,
        'property_city' => isset($property->Location->City) ? $property->Location->City : null,
        'property_state' => isset($property->Location->State) ? $property->Location->State : null,
        'property_zip' => isset($property->Location->ZipCode) ? $property->Location->ZipCode : null,
        'property_latitude' => isset($property->Location->Latitude) ? $property->Location->Latitude : null,
        'property_longitude' => isset($property->Location->Longitude) ? $property->Location->Longitude : null,
        'property_staff' => isset($property->Staff) ? json_encode($property->Staff) : null,
        'property_images' => isset($property->Images) ? json_encode($property->Images) : null,
        'property_rooms' => isset($property->Rooms) ? json_encode($property->Rooms) : null,
        'property_rankings' => isset($property->Analytics->Rankings) ? json_encode($property->Analytics->Rankings) : null,
        'property_ratings' => isset($property->Analytics->ApartmentRatings) ? json_encode($property->Analytics->ApartmentRatings) : null,
        'property_fees' => isset($property->Fees) ? json_encode($property->Fees) : null,
        'property_matterport_url' => isset($property->MatterportUrl) ? $property->MatterportUrl : null,
        'property_community_matterports' => isset($property->CommunityMatterports) ? $property->CommunityMatterports : null,
        'property_features' => null,
        'property_community_amenities' => isset($property->Amenities) ? json_encode($property->Amenities) : null,
        'property_awards' => isset($property->Awards) ? json_encode($property->Awards) : null,
        'property_videos' => isset($property->Videos) ? json_encode($property->Videos) : null,
        'property_structure_type' => isset($property->StructureType) ? $property->StructureType : null,
        'property_active' => isset($property->Active) ? strval($property->Active) : null,
        'property_ils_tracking_codes' => isset($property->ILSTrackingCodes) ? json_encode($property->ILSTrackingCodes) : null,
        'property_floorplan_count' => '1',
    ];
}

function rentpress_retrievePropertyCount()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';

    return $wpdb->query("SELECT COUNT(*) FROM $table_name");
}

function rentpress_removePropertyData($property_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';

    $wpdb->query("DELETE FROM $table_name WHERE `property_code` = '$property_code'");
}

function rentpress_emptyPropertyTable()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';

    $wpdb->query("TRUNCATE TABLE $table_name");
}

function rentpress_makePropertyDBTable()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'rentpress_properties';

    $sql = "CREATE TABLE $table_name (
		property_code varchar(191) NOT NULL,
		property_post_id varchar(191) NOT NULL,
		property_post_link longtext,
		property_source longtext,
		property_name longtext,
		property_description longtext,
		property_staff_description longtext,
		property_email longtext,
		property_phone_number longtext,
		property_website longtext,
		property_availability_url longtext,
		property_fax longtext,
		property_map_pdf longtext,
		property_office_hours longtext,
		property_timezone longtext,
		property_tour_url longtext,
		property_specials_message longtext,
		property_specials_link longtext,
		property_facebook_link longtext,
		property_twitter_link longtext,
		property_instagram_link longtext,
		property_residents_link longtext,
		property_address longtext,
		property_city longtext,
		property_state longtext,
		property_zip longtext,
		property_latitude longtext,
		property_longitude longtext,
		property_neighborhood_post_id longtext,
		property_staff longtext,
		property_images longtext,
		property_gallery_shortcode longtext,
		property_gallery_images longtext,
		property_featured_image_src longtext,
		property_featured_image_srcset longtext,
		property_rooms longtext,
		property_rankings longtext,
		property_ratings longtext,
		property_fees longtext,
		property_matterport_url longtext,
		property_community_matterports longtext,
		property_features longtext,
		property_community_amenities longtext,
		property_awards longtext,
		property_videos longtext,
		property_structure_type longtext,
		property_active longtext,
		property_ils_tracking_codes longtext,
		property_floorplan_count longtext,
		property_bed_types longtext,
		property_available_units smallint,
		property_unavailable_units smallint,
		property_available_floorplans smallint,
		property_unavailable_floorplans smallint,
		property_rent_min int,
		property_rent_max int,
		property_rent_base int,
		property_rent_market int,
		property_rent_term int,
		property_rent_effective int,
		property_rent_best int,
		property_rent_type_selection_cost int,
		property_rent_type_selection varchar(191),
		property_terms longtext,
		property_pet_policy longtext,
		property_additional_keywords longtext,


		UNIQUE KEY property_code (property_code)
	) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// TODO: 7.1 @Charles Make this run on plugin update, install, and delete
function rentpress_dropPropertyDBTable()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

function rentpress_updatePropertyDBColumn($property_code_to_update, $column_to_update, $new_value)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';
    $result = 'NoCode';
    $change_data = array(
        $column_to_update => $new_value,
    );
    $where_information = array(
        'property_code' => $property_code_to_update,
    );

    $result = $wpdb->update(
        $table_name,
        $change_data,
        $where_information
    );

    return $result;
}

function rentpress_updatePropertyDBRangeColumn($property_code_to_update, $column_to_update, $increase_value_by)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';
    $result = 'NoCode';

    $result = $wpdb->query($wpdb->prepare("UPDATE $table_name SET $column_to_update = $column_to_update + %d WHERE `property_code` = '$property_code_to_update'", $increase_value_by));

    return $result;
}
