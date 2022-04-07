<?php

function rentpress_syncFeedAndWPProperties()
{
    // require_once( RENTPRESS_PLUGIN_DATA_MODEL . 'property_model.php' );
    // require_once( RENTPRESS_PLUGIN_DATA_MODEL . 'floorplan_model.php' );
    // require_once( RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php' );
    // require_once( RENTPRESS_PLUGIN_DATA_MODEL . 'refresh_model.php' );
    // require_once( RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php' );
    // $rentpress_options = get_option( 'rentpress_options' );
    // $total_prop_count = 1;
    // $rentpress_sync_properties = array();
    // $rentpress_sync_units = array();
    // $total_pages = 1;
    // $corresponding_meta_keys = rentpress_getCorrespondingWordpressToFeedKeyArray();
    // $refresh_data = rentpress_getRefreshData();
    // $new_properties = array();
    // $last_resync_ts = (isset($rentpress_options['rentpress_api_credentials_section_api_token']) && isset($rentpress_options['rentpress_api_credentials_section_username']) ? json_decode(rentpress_getLastTimePropertiesWereUpdatedInTLC($rentpress_options)) : null );
    // $set_available_date = strtotime(' + '.$rentpress_options['rentpress_unit_availability_section_lookahead']) + 3600;
    // $selected_price_type = (isset($rentpress_options['rentpress_unit_rent_type_section_rent_type'])) ? $rentpress_options['rentpress_unit_rent_type_section_rent_type'] : 'Best Price' ;

    // // Get all wordpress Property and Floorplan Data
    // require_once( RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/property_post_type_data.php' );
    // $all_wordpress_meta = rentpress_getAllMetaDataForAllPropertiesAndFloorplans();

    // // if the tlc call errored, then show it, otherwise only attempt to do the resync if credentials are set
    // if (!is_int($last_resync_ts)) {
    //     if (isset($last_resync_ts->error->message)) {
    //         echo('Issue with connection: '.$last_resync_ts->error->message);
    //     } else {
    //         var_dump($last_resync_ts);
    //     }
    // } elseif (is_int($last_resync_ts)) {
    //     // If the refresh time is sooner than the last time tlc was updated, then use the refresh data instead of getting new data
    //     if ($refresh_data != [] && $last_resync_ts < (int) $refresh_data[0]->last_refresh_time) {
    //         foreach ($refresh_data as $property) {
    //             array_push($new_properties, json_decode($property->property_response));
    //         }
    //     } else {
    //         // Get all Rentpress Data from server
    //         for ($i = 1; $i <= $total_pages ; $i++) {
    //             $response = rentpress_getAllDataForPropertiesResponse($i, $rentpress_options);
    //             if (!isset($response->ResponseData->data)) {
    //                 // TODO: Log this event
    //                 continue;
    //             }

    //             foreach ($response->ResponseData->data as $property) {
    //                array_push($new_properties, $property);
    //                rentpress_saveRefreshData($property->Identification->PropertyCode, $property);
    //             }
    //             $total_pages = $response->ResponseMeta->total_pages;
    //         }
    //     }

    //     /******************************************************
    //     *
    //     *  Standardize, Caclulate, and Structure Feed Data
    //     *
    //     *******************************************************/
    //     // the way the feed is structured, everything is nested, so we gotta get through that and put everything into arrays
    //     foreach ($new_properties as $property) {

    //         $feed_floorplans = $property->floorplans->data;
    //         $property = rentpress_standardizePropertyFeedData($property);

    //         $property = setUpPropertyArrayForRanges($property, $selected_price_type);

    //         foreach ($feed_floorplans as $floorplan) {
    //             $feed_units = $floorplan->units->data;
    //             $floorplan = rentpress_standardizeFloorplanFeedData($floorplan);
    //             $floorplan = setUpFloorplanArrayForRanges($floorplan, $selected_price_type);

    //             foreach ($feed_units as $unit) {
    //                 $unit = rentpress_standardizeUnitFeedData($unit);
    //                 $unit = updateUnitRanges($unit, $selected_price_type);
    //                 $unit['unit_available'] = (date_create_from_format('n/j/Y', $unit['unit_ready_date']) < $set_available_date) ? true : false ;
    //                 $floorplan = updateFloorplanRanges($floorplan, $unit);

    //                 // save to seperate array because the data isnt used for wp posts
    //                 $rentpress_sync_units[$unit['unit_code']] = $unit;

    //             }
    //             $floorplan = updateFloorplanPriceSelection($floorplan);
    //             $property = updatePropertyRanges($property, $floorplan);
    //             $property['floorplans'][$floorplan['floorplan_code']] = $floorplan;

    //         }
    //         $property = updatePropertyPriceSelection($property);

    //         // encoding here to save memory
    //         $rentpress_sync_properties[$property['property_code']] = json_encode($property);
    //     }

    //     // encoding here to save memory as well
    //     $rentpress_sync_units = json_encode($rentpress_sync_units);

    //     /******************************************************
    //     *
    //     *  Sync the Feed data with the data on the Posts
    //     *
    //     *******************************************************/
    //     // start syncing data
    //     foreach ($rentpress_sync_properties as $property_feed_code => $property_feed_data) {
    //         // TODO: Apply Rentpress Options to the feed data
    //         $property_feed_data = rentpress_applyRentpressOptionsToFeedData(json_decode($property_feed_data, true), $rentpress_options);

    //         /* Sync the Wordpress and Feed Data */

    //         // the wordpress property posts have the property code as the key
    //         $property_post_meta = isset($all_wordpress_meta['all_properties'][$property_feed_code]) ? $all_wordpress_meta['all_properties'][$property_feed_code] : null;

    //         // if wordpress property doesnt exist, then create it, else update all of the feed data with any overrides
    //         if (is_null($property_post_meta)) {
    //             rentpress_createPropertyPost($property_feed_data, $corresponding_meta_keys);
    //         } else {
    //             $property_feed_data = rentpress_updateFeedDataWithOverrides($property_feed_data, $property_post_meta, $corresponding_meta_keys['rentpress_custom_field_property']);
    //         }

    //         // if there are floorplans, update them
    //         if (isset($property_feed_data['floorplans'])) {
    //             $property_feed_data = rentpress_createOrUpdateFloorplans($property_feed_data, $all_wordpress_meta, $corresponding_meta_keys);
    //         }

    //         // if property is published save to rentpress DB and remove from list, otherwise remove the matched property from both
    //         if (!is_null($property_post_meta) && $property_post_meta['post_information']->post_status == 'publish') {
    //             $property_feed_data['property_post_id'] = $property_post_meta['post_information']->ID;
    //             rentpress_savePropertyData($property_feed_data);

    //             foreach ($property_feed_data['floorplans'] as $floorplan_code => $floorplan_feed_data) {
    //                 rentpress_saveFloorplanData($floorplan_feed_data);
    //                 unset($all_wordpress_meta['all_floorplans'][$floorplan_code]);
    //             }

    //             // Remove this synced property from wp property list to add custom WP properties later
    //             unset($all_wordpress_meta['all_properties'][$property_feed_code]);
    //         } elseif(!is_null($property_post_meta)) {
    //             rentpress_removePropertyData($property_post_meta['rentpress_custom_field_property_code'][0]);

    //             if (isset($property_post_meta['floorplans'])) {
    //                 foreach ($property_post_meta['floorplans'] as $floorplan_code => $floorplan_post_meta) {
    //                     rentpress_removeFloorplanData($floorplan_code);
    //                     unset($all_wordpress_meta['all_floorplans'][$floorplan_code]);
    //                 }
    //             }
    //             unset($all_wordpress_meta['all_properties'][$property_feed_code]);
    //         }

    //     }
    //     // Save all unit data
    //     deleteAllFeedUnits();
    //     foreach (json_decode($rentpress_sync_units, true) as $unit_feed_data) {
    //         rentpress_saveUnitData($unit_feed_data);
    //     }
    // }

    // /*****************************************
    // *
    // *  Save Published Posts to RentPress DB
    // *
    // ******************************************/

    // // Any properties that are manually entered and published need to have their data saved to the DB as well
    // foreach ($all_wordpress_meta['all_properties'] as $property_code => $property_post_meta) {
    //     if (isset($property_post_meta['post_information']->post_status)
    //         && $property_post_meta['post_information']->post_status == 'publish'
    //         && !empty($property_code)) {
    //         $prop_data = rentpress_standardizeWPPostMeta($property_post_meta, $corresponding_meta_keys['rentpress_custom_field_property']);
    //         $prop_data['property_post_id'] = $property_post_meta['post_information']->ID;
    //         rentpress_savePropertyData($prop_data);

    //         if (isset($property_post_meta['floorplans'])) {
    //             foreach ($property_post_meta['floorplans'] as $floorplan_code => $floorplan_name) {
    //                 $fp_data = rentpress_standardizeWPPostMeta($all_wordpress_meta['all_floorplans'][$floorplan_code], $corresponding_meta_keys['rentpress_custom_field_floorplan']);
    //                 $fp_data['floorplan_post_id'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->ID;

    //                 rentpress_saveFloorplanData($fp_data);
    //             }
    //         }
    //     } else {
    //         rentpress_removePropertyData($property_code);
    //         if (isset($property_post_meta['floorplans'])) {
    //             foreach ($property_post_meta['floorplans'] as $floorplan_code => $floorplan_post_meta) {
    //                 rentpress_removeFloorplanData($floorplan_code);
    //             }
    //         }
    //     }
    // }
}

// TODO: Make this work with phone number, and application link override
function rentpress_applyRentpressOptionsToFeedData($property_feed_data, $rentpress_options)
{

    return $property_feed_data;
}

function rentpress_createOrUpdateFloorplans($property_feed_data, $all_wordpress_meta, $corresponding_meta_keys)
{
    // update any of the floorplans that exist, if not, make them
    foreach ($property_feed_data['floorplans'] as $floorplans_feed_code => $floorplan_feed_data) {
        if (isset($all_wordpress_meta['all_floorplans'][$floorplans_feed_code])) {
            $property_feed_data['floorplans'][$floorplans_feed_code] = rentpress_updateFeedDataWithOverrides($floorplan_feed_data, $all_wordpress_meta['all_floorplans'][$floorplans_feed_code], $corresponding_meta_keys['rentpress_custom_field_floorplan']);
            $property_feed_data['floorplans'][$floorplans_feed_code]['floorplan_post_id'] = $all_wordpress_meta['all_floorplans'][$floorplans_feed_code]['post_information']->ID;
        } else {
            $floorplan_feed_data['floorplan_post_id'] = rentpress_createFloorplanPost($floorplan_feed_data, $corresponding_meta_keys);
        }
    }
    return $property_feed_data;
}

function rentpress_standardizeWPPostMeta($post_meta, $corresponding_meta_keys)
{
    $new_db_format = array();
    foreach ($corresponding_meta_keys as $post_meta_key => $db_column_name) {
        if (isset($post_meta[$post_meta_key])) {
            $new_db_format[$db_column_name] = $post_meta[$post_meta_key];
        }
    }
    return $new_db_format;
}

function rentpress_createFloorplanPost($floorplan_feed_data, $corresponding_meta_keys)
{
    // Create new floorplan post
    $new_floorplan_post = [
        'post_title'    => $floorplan_feed_data['floorplan_name'],
        'post_status'   => 'publish',
        'post_type'     => 'rentpress_floorplan',
    ];
    $new_post_id = wp_insert_post( $new_floorplan_post );

    // for each corresponding meta key, save the property feed value if it exists
    foreach ($corresponding_meta_keys['rentpress_custom_field_floorplan'] as $floorplan_post_meta_key => $floorplan_feed_data_key) {
        if (isset($floorplan_feed_data[$floorplan_feed_data_key])) {
            update_post_meta($new_post_id, $floorplan_post_meta_key, $floorplan_feed_data[$floorplan_feed_data_key]);
        }
    }

    return $new_post_id;
}

function rentpress_createPropertyPost($property_feed_data, $corresponding_meta_keys)
{
    // Create new property post
    $new_property_post = [
        'post_title'    => $property_feed_data['property_name'],
        'post_status'   => 'draft',
        'post_type'     => 'rentpress_property',
    ];
    $new_post_id = wp_insert_post( $new_property_post );

    // for each corresponding meta key, save the property feed value if it exists
    foreach ($corresponding_meta_keys['rentpress_custom_field_property'] as $property_post_meta_key => $property_feed_data_key) {
        if (isset($property_feed_data[$property_feed_data_key])) {
            update_post_meta($new_post_id, $property_post_meta_key, $property_feed_data[$property_feed_data_key]);
        }
    }
}

function rentpress_updateFeedDataWithOverrides($feed_data, $post_meta, $corresponding_meta_keys)
{

    foreach ($corresponding_meta_keys as $post_meta_key => $feed_data_key) {
        $override_key = $post_meta_key.'_override';

        // update feed values with wp data if overriden
        // else update wp meta with new synced values
        if (isset($post_meta[$override_key]) &&
            $post_meta[$override_key][0] == 'on') {

            $feed_data[$feed_data_key] = $post_meta[$post_meta_key][0];

        } elseif (isset($feed_data[$feed_data_key])) {
            update_post_meta($post_meta['post_information']->ID, $post_meta_key, $feed_data[$feed_data_key]);
        }
    }

    return $feed_data;
}

function updatePropertyPriceSelection($property)
{
    switch ($property['property_rent_type_selection']) {
        case 'Best Price':
            $property['property_rent_type_selection_cost'] = $property['property_rent_best'];
        case 'Term Rent':
            $property['property_rent_type_selection_cost'] = $property['property_rent_term'];
        case 'Effective Rent':
            $property['property_rent_type_selection_cost'] = $property['property_rent_effective'];
        case 'Market Rent':
            $property['property_rent_type_selection_cost'] = $property['property_rent_market'];
        case 'Base':
            $property['property_rent_type_selection_cost'] = $property['property_rent_base'];
        default:
            $property['property_rent_type_selection_cost'] = $property['property_rent_base'];
    }
    return $property;
}

function updateFloorplanPriceSelection($floorplan)
{
    switch ($floorplan['floorplan_rent_type_selection']) {
        case 'Best Price':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_best'];
        case 'Term Rent':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_term'];
        case 'Effective Rent':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_effective'];
        case 'Market Rent':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_market'];
        case 'Base':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_base'];
        default:
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_base'];
    }
    return $floorplan;
}

function updateUnitPriceSelection($unit)
{
    switch ($unit['unit_rent_type_selection']) {
        case 'Best Price':
            $unit['unit_rent_type_selection_cost'] = $unit['unit_rent_best'];
            break;
        case 'Term Rent':
            $unit['unit_rent_type_selection_cost'] = $unit['unit_rent_term_best'];
            break;
        case 'Effective Rent':
            $unit['unit_rent_type_selection_cost'] = $unit['unit_rent_effective'];
            break;
        case 'Market Rent':
            $unit['unit_rent_type_selection_cost'] = $unit['unit_rent_market'];
            break;
        case 'Base':
            $unit['unit_rent_type_selection_cost'] = $unit['unit_rent_base'];
            break;
        default:
            $unit['unit_rent_type_selection_cost'] = $unit['unit_rent_base'];
            break;
    }

    return $unit;
}

function updatePropertyRanges($property, $floorplan)
{
    if ($floorplan['floorplan_available']) {
        $property['property_available_floorplans']++;
    } else {
        $property['property_unavailable_floorplans']++;
    }

    $property['property_available_units'] += $floorplan['floorplan_units_available'];
    $property['property_unavailable_units'] += $floorplan['floorplan_units_unavailable'];

    if (!in_array($floorplan['floorplan_bedrooms'], $property['property_bed_types'])) {
        $property['property_bed_types'][] = $floorplan['floorplan_bedrooms'];
    }


    if (is_null($property['property_rent_min']) ||
        (!is_null($floorplan['floorplan_rent_min']) && $floorplan['floorplan_rent_min'] < $property['property_rent_min'])) {
        $property['property_rent_min'] = $floorplan['floorplan_rent_min'];
    }

    if (is_null($property['property_rent_max']) ||
        (!is_null($floorplan['floorplan_rent_max']) && $floorplan['floorplan_rent_max'] > $property['property_rent_max'])) {
        $property['property_rent_max'] = $floorplan['floorplan_rent_max'];
    }

    if (is_null($property['property_rent_base']) ||
        (!is_null($floorplan['floorplan_rent_base']) && $floorplan['floorplan_rent_base'] < $property['property_rent_base'])) {
        $property['property_rent_base'] = $floorplan['floorplan_rent_base'];
    }

    if (is_null($property['property_rent_market']) ||
        (!is_null($floorplan['floorplan_rent_market']) && $floorplan['floorplan_rent_market'] < $property['property_rent_market'])) {
        $property['property_rent_market'] = $floorplan['floorplan_rent_market'];
    }

    if (is_null($property['property_rent_term']) ||
        (!is_null($floorplan['floorplan_rent_term']) && $floorplan['floorplan_rent_term'] < $property['property_rent_term'])) {
        $property['property_rent_term'] = $floorplan['floorplan_rent_term'];
    }

    if (is_null($property['property_rent_effective']) ||
        (!is_null($floorplan['floorplan_rent_effective']) && $floorplan['floorplan_rent_effective'] < $property['property_rent_effective'])) {
        $property['property_rent_effective'] = $floorplan['floorplan_rent_effective'];
    }

    if (is_null($property['property_rent_best']) ||
        (!is_null($floorplan['floorplan_rent_best']) && $floorplan['floorplan_rent_best'] < $property['property_rent_best'])) {
        $property['property_rent_best'] = $floorplan['floorplan_rent_best'];
    }
    return $property;
}

function updateUnitRanges($unit, $selected_price_type)
{
    $unit['unit_rent_type_selection'] = $selected_price_type;
    if (!empty($unit['unit_rent_terms']) && $unit['unit_rent_type_selection'] == 'Term Rent') {
        $term_rents = json_decode($unit['unit_rent_terms']);
        foreach ($term_rents as $term) {
            $rent = (int) $term->Rent;
            if (is_null($unit['unit_rent_term_best']) || $rent < $unit['unit_rent_term_best']) {
                $unit['unit_rent_term_best'] = $rent;
            }
            if (is_null($unit['unit_rent_min']) || $rent < $unit['unit_rent_min']) {
                $unit['unit_rent_min'] = $rent;
            }
            if (is_null($unit['unit_rent_best']) || $rent < $unit['unit_rent_best']) {
                $unit['unit_rent_best'] = $rent;
            }
            if (is_null($unit['unit_rent_max']) || $rent > $unit['unit_rent_max']) {
                $unit['unit_rent_max'] = $rent;
            }
        }
    }
    $unit = updateUnitPriceSelection($unit);

    return $unit;
}

function updateFloorplanRanges($floorplan, $unit)
{
    if ($unit['unit_available']) {
        $floorplan['floorplan_available'] = true;
        $floorplan['floorplan_units_available']++;
    } else {
        $floorplan['floorplan_units_unavailable']++;
    }


    $floorplan_rent_base = (!is_null($unit['unit_rent_base'])) ? $unit['unit_rent_base'] : $floorplan['floorplan_rent_base'];
    $floorplan_rent_min = (!is_null($unit['unit_rent_min'])) ? $unit['unit_rent_min'] : $floorplan_rent_base;
    $floorplan_rent_max = (!is_null($unit['unit_rent_max'])) ? $unit['unit_rent_max'] : $floorplan_rent_base;
    $floorplan_rent_effective = $unit['unit_rent_effective'];
    $floorplan_rent_market = $unit['unit_rent_market'];
    $floorplan_rent_best = (!is_null($unit['unit_rent_best'])) ? $unit['unit_rent_best'] : $floorplan_rent_min;
    $floorplan_rent_term = (!is_null($unit['unit_rent_term_best'])) ? $unit['unit_rent_term_best'] : null;

    if (!is_null($floorplan_rent_effective) && $floorplan_rent_effective < $floorplan_rent_best) {
        $floorplan_rent_best = $floorplan_rent_effective;
    }

    if (!is_null($floorplan_rent_market) && $floorplan_rent_market < $floorplan_rent_best) {
        $floorplan_rent_best = $floorplan_rent_market;
    }

    if (!is_null($floorplan_rent_term) && $floorplan_rent_term < $floorplan_rent_best) {
        $floorplan_rent_best = $floorplan_rent_term;
    }

    if (!is_null($floorplan_rent_best) && $floorplan_rent_best < $floorplan_rent_min) {
        $floorplan_rent_min = $floorplan_rent_best;
    }


    $floorplan['floorplan_rent_min'] = ((is_null($floorplan['floorplan_rent_min'])) || (!is_null($floorplan_rent_min) && $floorplan_rent_min < $floorplan['floorplan_rent_min'])) ? $floorplan_rent_min : $floorplan['floorplan_rent_min'] ;

    $floorplan['floorplan_rent_max'] = ((is_null($floorplan['floorplan_rent_max'])) || (!is_null($floorplan_rent_max) && $floorplan_rent_max > $floorplan['floorplan_rent_max'])) ? $floorplan_rent_max : $floorplan['floorplan_rent_max'] ;

    $floorplan['floorplan_rent_base'] = ((is_null($floorplan['floorplan_rent_base'])) || (!is_null($floorplan_rent_base) && $floorplan_rent_base < $floorplan['floorplan_rent_base'])) ? $floorplan_rent_base : $floorplan['floorplan_rent_base'] ;

    $floorplan['floorplan_rent_effective'] = ((is_null($floorplan['floorplan_rent_effective'])) || (!is_null($floorplan_rent_effective) && $floorplan_rent_effective < $floorplan['floorplan_rent_effective'])) ? $floorplan_rent_effective : $floorplan['floorplan_rent_effective'] ;

    $floorplan['floorplan_rent_market'] = ((is_null($floorplan['floorplan_rent_market'])) || (!is_null($floorplan_rent_market) && $floorplan_rent_market < $floorplan['floorplan_rent_market'])) ? $floorplan_rent_market : $floorplan['floorplan_rent_market'] ;

    $floorplan['floorplan_rent_best'] = ((is_null($floorplan['floorplan_rent_best'])) || (!is_null($floorplan_rent_best) && $floorplan_rent_best < $floorplan['floorplan_rent_best'])) ? $floorplan_rent_best : $floorplan['floorplan_rent_best'] ;

    $floorplan['floorplan_rent_term'] = ((is_null($floorplan['floorplan_rent_term'])) || (!is_null($floorplan_rent_term) && $floorplan_rent_term < $floorplan['floorplan_rent_term'])) ? $floorplan_rent_term : $floorplan['floorplan_rent_term'] ;

    return $floorplan;

}

function setUpFloorplanArrayForRanges($floorplan, $selected_price_type)
{
    $floorplan['floorplan_available'] = false;
    $floorplan['floorplan_units_available'] = 0;
    $floorplan['floorplan_units_unavailable'] = 0;
    $floorplan['floorplan_rent_base'] = null;
    $floorplan['floorplan_rent_market'] = null;
    $floorplan['floorplan_rent_term'] = null;
    $floorplan['floorplan_rent_effective'] = null;
    $floorplan['floorplan_rent_best'] = null;
    $floorplan['floorplan_rent_type_selection_cost'] = null;

    $floorplan['floorplan_rent_type_selection'] = $selected_price_type;

    return $floorplan;
}

function setUpPropertyArrayForRanges($property, $selected_price_type)
{
    $property['property_bed_types'] = [];
    $property['property_available_units'] = 0;
    $property['property_unavailable_units'] = 0;
    $property['property_available_floorplans'] = 0;
    $property['property_unavailable_floorplans'] = 0;
    $property['property_rent_min'] = null;
    $property['property_rent_max'] = null;
    $property['property_rent_base'] = null;
    $property['property_rent_market'] = null;
    $property['property_rent_term'] = null;
    $property['property_rent_effective'] = null;
    $property['property_rent_best'] = null;
    $property['property_rent_type_selection_cost'] = null;

    $property['property_rent_type_selection'] = $selected_price_type;

    return $property;

}

function rentpress_getAllDataForPropertiesResponse($page, $rentpress_options)
{
   $results = wp_remote_request(
        RENTPRESS_SERVER_ENDPOINT . '/api/v1/properties', // build request url
        array(
            'method' => 'GET',
            'sslverify' => false,
            'body' => [
                'limit' => 10,
                'page' => $page,
                'include' => 'floorplans.units'
            ],
            'compress' => true,
            'headers' => array( /* set token and username */
                'X-Topline-Token' => $rentpress_options['rentpress_api_credentials_section_api_token'],
                'X-Topline-User' => $rentpress_options['rentpress_api_credentials_section_username']
            ),
            'timeout' => 60
        )
    );

    return json_decode($results['body']);
}

function rentpress_getLastTimePropertiesWereUpdatedInTLC($rentpress_options)
{
    $results = wp_remote_request(
        RENTPRESS_SERVER_ENDPOINT . '/api/v1/properties/last_sync', // build request url
        array(
            'method' => 'GET',
            'sslverify' => false,
            'body' => [
            ],
            'compress' => true,
            'headers' => array( /* set token and username */
                'X-Topline-Token' => $rentpress_options['rentpress_api_credentials_section_api_token'],
                'X-Topline-User' => $rentpress_options['rentpress_api_credentials_section_username']
            ),
            'timeout' => 60
        )
    );

    return $results['body'];
}

function rentpress_getCorrespondingWordpressToFeedKeyArray()
{
    return [
        'rentpress_custom_field_property' => [
            'rentpress_custom_field_property_code' => 'property_code',
            'rentpress_custom_field_property_name' => 'property_name',
            'rentpress_custom_field_property_description' => 'property_description',
            'rentpress_custom_field_property_address' => 'property_address',
            'rentpress_custom_field_property_city' => 'property_city',
            'rentpress_custom_field_property_state' => 'property_state',
            'rentpress_custom_field_property_zip' => 'property_zip',
            'rentpress_custom_field_property_website' => 'property_website',
            'rentpress_custom_field_property_apply_link' => 'property_availability_url',
            'rentpress_custom_field_property_email' => 'property_email',
            'rentpress_custom_field_property_latitude' => 'property_latitude',
            'rentpress_custom_field_property_longitude' => 'property_longitude',
            'rentpress_custom_field_property_phone' => 'property_phone_number',
            'rentpress_custom_field_property_import_source' => 'property_source',
            'rentpress_custom_field_property_special_text' => 'property_specials_message',

            // TODO: make meta for the following data
            'rentpress_custom_field_property_bed_min' => 'property_bed_min',
            'rentpress_custom_field_property_bed_max' => 'property_bed_max',
            'rentpress_custom_field_property_available_units' => 'property_available_units',
            'rentpress_custom_field_property_unavailable_units' => 'property_unavailable_units',
            'rentpress_custom_field_property_available_floorplans' => 'property_available_floorplans',
            'rentpress_custom_field_property_unavailable_floorplans' => 'property_unavailable_floorplans',

            // TODO: make meta for the following feed data, also calculate the different rents
            // TODO: work with ryan to figure out how to calculate these
            'rentpress_custom_field_property_rent_min' => 'property_rent_min',
            'rentpress_custom_field_property_rent_max' => 'property_rent_max',
            'rentpress_custom_field_property_rent_type_selection' => 'property_rent_type_selection',
            'rentpress_custom_field_property_rent_base' => 'property_rent_base',
            'rentpress_custom_field_property_rent_market' => 'property_rent_market',
            'rentpress_custom_field_property_rent_term' => 'property_rent_term',
            'rentpress_custom_field_property_rent_effective' => 'property_rent_effective',
            'rentpress_custom_field_property_rent_best' => 'property_rent_best',


        ],
        'rentpress_custom_field_floorplan' => [
            'rentpress_custom_field_floorplan_parent_property_code' => 'floorplan_parent_property_code',
            'rentpress_custom_field_floorplan_code' => 'floorplan_code',
            'rentpress_custom_field_floorplan_name' => 'floorplan_name',
            'rentpress_custom_field_floorplan_bedroom_count' => 'floorplan_bedrooms',
            'rentpress_custom_field_floorplan_bathroom_count' => 'floorplan_bathrooms',
            'rentpress_custom_field_floorplan_min_sqft' => 'floorplan_sqft_min',
            'rentpress_custom_field_floorplan_max_sqft' => 'floorplan_sqft_max',
            'rentpress_custom_field_floorplan_min_rent' => 'floorplan_rent_min',
            'rentpress_custom_field_floorplan_max_rent' => 'floorplan_rent_max',
            'rentpress_custom_field_floorplan_availability_url' => 'floorplan_availability_url',
            'rentpress_custom_field_floorplan_unit_type_mapping' => 'floorplan_unit_type_mapping',
            'rentpress_custom_field_floorplan_matterport_video' => 'floorplan_matterport_url',
            'rentpress_custom_field_floorplan_unit_count_total' => 'floorplan_units_total',
            'rentpress_custom_field_floorplan_unit_count_available' => 'floorplan_units_available',
            'rentpress_custom_field_floorplan_unit_count_available_30' => 'floorplan_units_available_30',
            'rentpress_custom_field_floorplan_unit_count_available_60' => 'floorplan_units_available_60',


            // TODO: make meta for the following data
            'rentpress_custom_field_property_available_units' => 'property_available_units',
            'rentpress_custom_field_property_unavailable_units' => 'property_unavailable_units',

            // TODO: make meta for the following feed data, also calculate the different rents
            // TODO: work with ryan to figure out how to calculate these
            'rentpress_custom_field_property_rent_min' => 'property_rent_min',
            'rentpress_custom_field_property_rent_max' => 'property_rent_max',
            'rentpress_custom_field_property_rent_type_selection' => 'property_rent_type_selection',
            'rentpress_custom_field_property_rent_base' => 'property_rent_base',
            'rentpress_custom_field_property_rent_market' => 'property_rent_market',
            'rentpress_custom_field_property_rent_term' => 'property_rent_term',
            'rentpress_custom_field_property_rent_effective' => 'property_rent_effective',
            'rentpress_custom_field_property_rent_best' => 'property_rent_best',
        ],

    ];
}
