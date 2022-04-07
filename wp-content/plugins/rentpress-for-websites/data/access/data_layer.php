<?php
/*****************
 *
 *  Data access
 *
 ******************/

/*****************
 *
 *  Property access
 *
 ******************/

/*
 * Get all properties for search page
 */
function rentpress_getAllProperties()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';

    $properties = $wpdb->get_results("SELECT * FROM $table_name");

    return $properties;
}

/*
 * Get nearby properties for single property page
 */
function rentpress_getNearbyProperties($city, $limit = null, $excluded_property_code = null)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';

    $limit = !empty($limit) ? "LIMIT $limit" : "";
    $excludeString = "";
    if (!empty($excluded_property_code)) {
        $excludeString = "AND NOT `property_code` = '$excluded_property_code'";
    }

    $properties = $wpdb->get_results("SELECT * FROM $table_name WHERE `property_city` = '$city' $excludeString ORDER BY `property_available_units` DESC $limit");

    return $properties;
}

/*
 * Get all properties for taxonomy page
 */
function rentpress_getAllPropertiesForTaxonomies($terms)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';
    $sql_term_str = "WHERE `property_terms` ";
    $terms_count = count($terms);

    if ($terms_count > 1) {
        for ($i = 0; $i < $terms_count; $i++) {
            $term = $terms[$i];
            $sql_term_str .= "REGEXP '\"$term\"'";
            if ($i != $terms_count - 1) {
                $sql_term_str .= " AND `property_terms` ";
            }
        }
    } else {
        $term = $terms[0];
        $sql_term_str .= "REGEXP '\"$term\"'";
    }

    $properties = $wpdb->get_results("SELECT * FROM $table_name $sql_term_str");

    return $properties;
}

/*
 * Get all properties for a featured page by property code or post id
 */
function rentpress_getAllPropertiesWithCodesOrIDs($properties_info)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';

    $sql_code_str = "WHERE ";

    $codes_count = count($properties_info);

    if ($codes_count > 1) {
        for ($i = 0; $i < $codes_count; $i++) {
            $code = $properties_info[$i];
            $sql_code_str .= "(`property_code` = '$code' OR `property_post_id` = '$code')";
            if ($i != $codes_count - 1) {
                $sql_code_str .= " OR ";
            }
        }
    } else {
        $code = $properties_info[0];
        $sql_code_str .= "(`property_code` = '$code' OR `property_post_id` = '$code')";
    }

    $properties = $wpdb->get_results("SELECT * FROM $table_name $sql_code_str");

    return $properties;
}

/*
 * Get property by property code or post id
 */
function rentpress_getAllPropertyWithCodeOrID($property_info)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';
    $properties = $wpdb->get_results("SELECT * FROM $table_name WHERE (`property_code` = '$property_info' OR `property_post_id` = '$property_info')");

    return $properties;
}

/*
 * Get all properties and the related data based on property post id or propert code
 */
function rentpress_getAllPropertyDataWithCodeOrPostID($property_info)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_properties';

    $property = $wpdb->get_row("SELECT * FROM $table_name WHERE (`property_code` = '$property_info' OR `property_post_id` = '$property_info')");

    if (!empty($property)) {
        $property->floorplans = rentpress_getAllFloorplansByParentPropertyCode($property->property_code);
        $units = rentpress_getAllAvailableUnitsByParentPropertyCode($property->property_code);

        if (!empty($property->floorplans)) {
            foreach ($property->floorplans as $fp) {
                $fp->units = array();
                if (!empty($units)) {
                    foreach ($units as $unit_key => $unit) {
                        if ($unit->unit_parent_floorplan_code == $fp->floorplan_code) {
                            array_push($fp->units, $unit);
                            unset($units[$unit_key]);
                        }
                    }
                }
            }
        }
    }

    return $property;

    // TODO: @Charles figure out how to do the complicated join in order to cut the calls
    // $data_set = $wpdb->get_results(
    //     "SELECT *
    //     FROM $property_table p
    //     INNER JOIN $floorplan_table f
    //         ON p.property_code = f.floorplan_parent_property_code
    //     INNER JOIN $unit_table u
    //         ON f.floorplan_parent_property_code = u.unit_parent_property_code
    //     WHERE (`property_code` = '$property_info' OR `property_post_id` = '$property_info')" );

}

/*****************
 *
 *  Floorplan access
 *
 ******************/

/*
 * Get all floorplans for search page
 */
function rentpress_getAllFloorplansAndUnits()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    $floorplans = $wpdb->get_results("SELECT * FROM $table_name");
    $units = rentpress_getAllAvailableUnits();

    foreach ($floorplans as $fp) {
        $fp->units = array();
        foreach ($units as $unit_key => $unit) {
            if ($unit->unit_parent_floorplan_code == $fp->floorplan_code) {
                array_push($fp->units, $unit);
                unset($units[$unit_key]);
            }
        }
    }

    return $floorplans;
}

/*
 * Get all floorplans for a property
 */
function rentpress_getAllFloorplansByParentPropertyCode($property_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    $floorplans = $wpdb->get_results("SELECT * FROM $table_name WHERE `floorplan_parent_property_code` = '$property_code'");

    return $floorplans;
}

/*
 * Get floorplan data row from code or post id
 */
function rentpress_getFloorplanDataWithCodeOrPostID($floorplan_info)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    return $wpdb->get_row("SELECT * FROM $table_name WHERE (`floorplan_code` = '$floorplan_info' OR `floorplan_post_id` = '$floorplan_info')");
}

/*
 * Get floorplans and units from floorplan codes or post ids
 */
function rentpress_getFloorplansAndUnitsWithCodesOrPostIDs($floorplans_info)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    $fp_codes_sql = '';
    foreach ($floorplans_info as $fp) {
        $fp_codes_sql .= "(`floorplan_code` = '$fp' OR `floorplan_post_id` = '$fp') OR ";
    }
    $fp_codes_sql = substr($fp_codes_sql, 0, -3);

    $floorplans = $wpdb->get_results("SELECT * FROM $table_name WHERE $fp_codes_sql");
    $units = rentpress_getAllAvailableUnits();

    foreach ($floorplans as $fp) {
        $fp->units = array();
        foreach ($units as $unit_key => $unit) {
            if ($unit->unit_parent_floorplan_code == $fp->floorplan_code) {
                array_push($fp->units, $unit);
                unset($units[$unit_key]);
            }
        }
    }

    return $floorplans;
}

/*
 * Get floorplans and units from parent property codes or post ids
 */
function rentpress_getFloorplansAndUnitsWithParentPropertyCodesOrPostIDs($floorplans_info)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';

    $fp_codes_sql = '';
    foreach ($floorplans_info as $fp) {
        $fp_codes_sql .= "(`floorplan_parent_property_code` = '$fp' OR `floorplan_parent_property_post_id` = '$fp') OR ";
    }
    $fp_codes_sql = substr($fp_codes_sql, 0, -3);

    $floorplans = $wpdb->get_results("SELECT * FROM $table_name WHERE $fp_codes_sql");
    $units = rentpress_getAllAvailableUnits();

    foreach ($floorplans as $fp) {
        $fp->units = array();
        foreach ($units as $unit_key => $unit) {
            if ($unit->unit_parent_floorplan_code == $fp->floorplan_code) {
                array_push($fp->units, $unit);
                unset($units[$unit_key]);
            }
        }
    }

    return $floorplans;
}

/*
 * Get floorplan, parent property, and available unit data from floorplan code or post id
 */
function rentpress_getAllFloorplanDataWithCodeOrPostID($floorplan_info)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';
    $prop_table = $wpdb->prefix . 'rentpress_properties';

    $floorplan = $wpdb->get_row("SELECT * FROM $table_name AS fp INNER JOIN $prop_table as prop ON fp.floorplan_parent_property_code = prop.property_code WHERE (fp.floorplan_code = '$floorplan_info' OR fp.floorplan_post_id = '$floorplan_info')");

    if (!empty($floorplan)) {
        $floorplan->units = rentpress_getAllAvailableUnitsByFloorplanByCode($floorplan->floorplan_code);
    }

    return $floorplan;
}

/*
 * Get similar floorplans from floorplan object or post_id/floorplan_code
 */
function rentpress_getSimilarFloorplans($floorplan, $comparing = 'relevance', $number_of_floorplans = 3, $same_property = true)
{

    // check to see if passed a floorplan object
    if (empty($floorplan->floorplan_code)) {
        $floorplan = rentpress_getFloorplanDataWithCodeOrPostID($floorplan);
        if (empty($floorplan->floorplan_code)) {
            return "there are no floorplans that match this floorplan_code or post_id";
        }
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_floorplans';
    $similar_floorplans = null;
    $data_missing = '';

    $same_floorplan_filter = "`floorplan_code` != '$floorplan->floorplan_code'";
    $same_property_filter = '';
    if ($same_property) {
        $same_property_filter = "AND `floorplan_parent_property_code` = '$floorplan->floorplan_parent_property_code'";
    }

    // if the required data is not present, return an error
    $cost_order = '';
    if (!empty($floorplan->floorplan_rent_type_selection_cost)) {
        $cost_order = "-ABS(`floorplan_rent_type_selection_cost` - $floorplan->floorplan_rent_type_selection_cost) DESC";
    } elseif ($comparing == 'relevance' || $comparing == 'price' || $comparing == 'special') {
        $comparing = 'bedrooms';
        $data_missing .= '(floorplan->floorplan_rent_type_selection_cost)';
    }

    $bedroom_order = '';
    if (!empty($floorplan->floorplan_bedrooms)) {
        $bedroom_order = "-ABS(`floorplan_bedrooms` - $floorplan->floorplan_bedrooms) DESC";
    } elseif ($comparing == 'relevance' || $comparing == 'bedrooms' || $comparing == 'special') {
        $comparing = 'error';
        $data_missing .= '(floorplan->floorplan_bedrooms)';
    }

    $sqft_order = '';
    if (!empty($floorplan->floorplan_sqft_min)) {
        $sqft_order = "-ABS(`floorplan_sqft_min` - $floorplan->floorplan_sqft_min) DESC";
    } elseif ($comparing == 'sqft') {
        $comparing = 'error';
        $data_missing .= '(floorplan->floorplan_sqft_min)';
    }

    switch ($comparing) {
        // cost ignoring bedroom count
        case 'price':
            $similar_floorplans = $wpdb->get_results(
                "SELECT *
                FROM $table_name
                WHERE $same_floorplan_filter
                $same_property_filter
                ORDER BY $cost_order
                LIMIT $number_of_floorplans");
            break;

        case 'bedrooms':
            // number of bedrooms, then availability
            $similar_floorplans = $wpdb->get_results(
                "SELECT *
                FROM $table_name
                WHERE $same_floorplan_filter
                $same_property_filter
                ORDER BY $bedroom_order, `floorplan_units_available` DESC
                LIMIT $number_of_floorplans");
            break;

        case 'sqft':
            // most similar in size
            $similar_floorplans = $wpdb->get_results(
                "SELECT *
                FROM $table_name
                WHERE $same_floorplan_filter
                $same_property_filter
                ORDER BY $sqft_order
                LIMIT $number_of_floorplans");
            break;

        case 'special':
            // if there is a special, then by bedroom count, then by cost
            $similar_floorplans = $wpdb->get_results(
                "SELECT *
                FROM $table_name
                WHERE $same_floorplan_filter
                $same_property_filter
                ORDER BY if(`floorplan_specials_message` = '' or `floorplan_specials_message` is null,1,0), $bedroom_order, $cost_order
                LIMIT $number_of_floorplans");
            break;

        case 'availability':
            // most available floorplans first (most units currently available)
            $similar_floorplans = $wpdb->get_results(
                "SELECT *
                FROM $table_name
                WHERE $same_floorplan_filter
                $same_property_filter
                ORDER BY `floorplan_units_available` DESC
                LIMIT $number_of_floorplans");
            break;

        case 'relevance':
            // order by Bedrooms, then if the floorplan has a special, then closest price
            $similar_floorplans = $wpdb->get_results(
                "SELECT *
                FROM $table_name
                WHERE $same_floorplan_filter
                $same_property_filter
                ORDER BY $bedroom_order, if(`floorplan_specials_message` = '' or `floorplan_specials_message` is null,1,0), `floorplan_units_available` DESC, $cost_order
                LIMIT $number_of_floorplans");
            break;

        case 'error':
            $similar_floorplans = "Missing data for comparison: $data_missing";
            break;

        default:
            // closest name if user typed something wrong as a 'comparing' parameter or missing required data
            $similar_floorplans = $wpdb->get_results(
                "SELECT *
                FROM $table_name
                WHERE $same_floorplan_filter
                $same_property_filter
                ORDER BY `floorplan_name`
                LIMIT $number_of_floorplans");
            break;
    }

    return $similar_floorplans;
}

/*****************
 *
 *  Unit access
 *
 ******************/

/*
 * Get all units for search page
 */
function rentpress_getAllUnits()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $units = $wpdb->get_results("SELECT * FROM $table_name");

    return $units;
}

/*
 * Get all manual units for resync
 */
function rentpress_getAllManualUnits()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $units = $wpdb->get_results("SELECT * FROM $table_name WHERE `unit_is_feed` = '0' ORDER BY `unit_ready_date` DESC");

    return $units;
}

/*
 * Get all units codes
 */
function rentpress_getAllUnitCodes()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $unit_codes_array = array();
    $unit_codes = $wpdb->get_results("SELECT `unit_code` FROM $table_name");
    foreach ($unit_codes as $unit_code) {
        $unit_codes_array[] = $unit_code->unit_code;

    }

    return $unit_codes_array;
}

/*
 * Get all units for property
 */
function rentpress_getAllUnitsByParentPropertyCode($property_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $units = $wpdb->get_results("SELECT * FROM $table_name WHERE `unit_parent_property_code` = '$property_code'");

    return $units;
}

/*
 * Get all units for floorplan
 */
function rentpress_getAllUnitsForFloorplan($floorplan_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $units = $wpdb->get_results("SELECT * FROM $table_name WHERE `unit_parent_floorplan_code` = '$floorplan_code'");

    return $units;
}

/*
 * Get all available units
 */
function rentpress_getAllAvailableUnits()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $units = $wpdb->get_results("SELECT * FROM $table_name WHERE `unit_available` = 1");

    return $units;
}

/*
 * Get all available units for a property
 */
function rentpress_getAllAvailableUnitsByParentPropertyCode($property_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $units = $wpdb->get_results("SELECT * FROM $table_name WHERE `unit_parent_property_code` = '$property_code' AND `unit_available` = 1");

    return $units;
}

/*
 * Get all available units for a floorplan
 */
function rentpress_getAllAvailableUnitsByFloorplanByCode($floorplan_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_units';

    $units = $wpdb->get_results("SELECT * FROM $table_name WHERE `unit_parent_floorplan_code` = '$floorplan_code' AND `unit_available` = 1");
    return $units;
}

/*
 * Get all unit and floorplan data by unit code
 */
function rentpress_getUnitAndFloorplanDataByUnitCode($unit_code)
{
    global $wpdb;
    $unit_table = $wpdb->prefix . 'rentpress_units';
    $fp_table = $wpdb->prefix . 'rentpress_floorplans';

    return $wpdb->get_row("SELECT * FROM $unit_table AS u INNER JOIN $fp_table as fp ON u.unit_parent_floorplan_code = fp.floorplan_code WHERE u.unit_code = '$unit_code'");
}

/*****************
 *
 *  Refresh access
 *
 ******************/

/*
 * Get all refresh data
 */
function rentpress_getRefreshData()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_refresh';

    $data = $wpdb->get_results("SELECT * FROM $table_name");

    return $data;
}

/*
 * Get refresh data for specific property
 */
function rentpress_getRefreshRow($property_code)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rentpress_refresh';

    $data = $wpdb->get_row("SELECT * FROM $table_name WHERE `property_code` = '$property_code'");

    return $data;
}
