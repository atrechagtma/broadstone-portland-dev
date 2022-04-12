<?php
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'property_model.php';
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'floorplan_model.php';
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'unit_model.php';
require_once RENTPRESS_PLUGIN_DATA_MODEL . 'refresh_model.php';
require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';

function rentpress_standardizeSyncData($new_properties, $rentpress_options)
{
    /******************************************************
     *
     *  Standardize, Caclulate, and Structure Feed Data
     *
     *******************************************************/
    $set_available_date = strtotime(' + ' . $rentpress_options['rentpress_unit_availability_section_lookahead']) + 3600;
    $set_30_days = strtotime(' + 30 days') + 3600;
    $set_60_days = strtotime(' + 60 days') + 3600;
    $selected_price_type = (isset($rentpress_options['rentpress_pricing_display_settings_section_price_display_selection'])) ? $rentpress_options['rentpress_pricing_display_settings_section_price_display_selection'] : 'Best Price';
    $price_range_selection = (isset($rentpress_options['rentpress_unit_availability_section_price_range_selector'])) ? $rentpress_options['rentpress_unit_availability_section_price_range_selector'] : 'Use Floor Plan Price If No Units Are Available';
    $rentpress_sync_units = array();

    // to maintain unique IDs, create arrays that house all of the IDs that have been used. Without unique IDs a lot of logic falls apart and the DB updates the same rows over again
    $unit_ids = array();
    $floorplan_ids = array();
    $property_ids = array();

    $all_manual_units = rentpress_getAllManualUnits();
    $all_manual_units_data = array();
    foreach ($all_manual_units as $unit) {
        if (!isset($all_manual_units_data[$unit->unit_parent_floorplan_code])) {
            $all_manual_units_data[$unit->unit_parent_floorplan_code] = array();
        }
        array_push($all_manual_units_data[$unit->unit_parent_floorplan_code], (array) $unit);
        array_push($unit_ids, $unit->unit_code);
    }

    // the way the feed is structured, everything is nested, so we gotta get through that and put everything into arrays
    foreach ($new_properties as $property) {
        if (in_array($property->Identification->PropertyCode, $property_ids)) {
            continue;
        } else {
            array_push($property_ids, $property->Identification->PropertyCode);
        }

        $feed_floorplans = $property->floorplans->data;
        $property = rentpress_standardizePropertyFeedData($property);

        $property = rentpress_setUpPropertyArrayForRanges($property, $selected_price_type);

        foreach ($feed_floorplans as $floorplan) {
            if (in_array($floorplan->Identification->FloorPlanCode, $floorplan_ids)) {
                continue;
            } else {
                array_push($floorplan_ids, $floorplan->Identification->FloorPlanCode);
            }
            $feed_units = $floorplan->units->data;
            $floorplan = rentpress_standardizeFloorplanFeedData($floorplan);
            $floorplan = rentpress_setUpFloorplanArrayForRanges($floorplan, $selected_price_type);
            $limiter = null;

            // If the rentpress setting that limits units is being used, make sure it is a valid value, then sort units by availability if there are at least as many units as requested
            if (isset($rentpress_options['rentpress_unit_availability_section_limit_unit_count'])) {
                $limiter = intval(preg_replace('/[^0-9]/', '', $rentpress_options['rentpress_unit_availability_section_limit_unit_count']));
                if (!empty($limiter) &&
                    $limiter > 0 &&
                    count($feed_units) >= $limiter) {
                    usort($feed_units, "rentpress_feedUnitComparator");
                }
            }

            // add manual units to the front of the feed units array
            if (isset($all_manual_units_data[$floorplan['floorplan_code']])) {
                foreach ($all_manual_units_data[$floorplan['floorplan_code']] as $manual_unit) {
                    array_unshift($feed_units, $manual_unit);
                }
            }

            foreach ($feed_units as $unit_index => $unit) {
                // break iteration of the for each if reach unit cap defined in rp settings
                if (!empty($limiter) && $unit_index >= $limiter) {
                    break;
                }

                // if the unit is not already an array, that means that it is a feed unit and needs to be standardized. Manual units skip this step
                if (!is_array($unit)) {
                    // if the unit code exists already, skip adding it to avoid conflicts, this means that manual units will take precedence
                    if (in_array($unit->Identification->UnitCode, $unit_ids)) {
                        // if there is a non-unique unit code within the limit bounds, then you have to increase the limit since this unit index is skipped
                        if (!is_null($limiter)) {
                            $limiter = $limiter + 1;
                        }
                        continue;
                    } else {
                        array_push($unit_ids, $unit->Identification->UnitCode);
                        $unit = rentpress_standardizeUnitFeedData($unit);
                    }
                }

                $unit = rentpress_updateUnitRanges($unit, $selected_price_type);
                $unit['unit_available'] = false;

                if (!empty($unit['unit_ready_date'])) {
                    $unit_available_date = date_create_from_format('n/j/Y', $unit['unit_ready_date'])->getTimestamp();
                    $unit['unit_available'] = $unit_available_date < $set_available_date;
                    if ($unit_available_date < $set_30_days) {
                        $floorplan['floorplan_units_available_30']++;
                    }
                    if ($unit_available_date < $set_60_days) {
                        $floorplan['floorplan_units_available_60']++;
                    }
                }

                if (($unit['unit_available'] && $price_range_selection == 'Use Available Unit Price Only') ||
                    $price_range_selection != 'Use Floor Plan Price Only') {
                    $floorplan = rentpress_updateFloorplanRanges($floorplan, $unit);
                }

                if ($unit['unit_available']) {
                    $floorplan['floorplan_available'] = true;
                    $floorplan['floorplan_units_available']++;
                } else {
                    $floorplan['floorplan_units_unavailable']++;
                }
                $floorplan['floorplan_units_total']++;

                // save to seperate array because the data isnt used for wp posts
                $rentpress_sync_units[$unit['unit_code']] = $unit;
            }

            // clear price if available units are only used and there are none
            if ($price_range_selection == 'Use Available Unit Price Only' && !$floorplan['floorplan_available']) {
                $floorplan['floorplan_rent_type_selection_cost'] = null;
            } else {
                $floorplan = rentpress_updateFloorplanPriceSelection($floorplan, $selected_price_type);
            }
            $property['floorplans'][$floorplan['floorplan_code']] = $floorplan;

        }

        // encoding here to save memory
        $rentpress_sync_properties[$property['property_code']] = json_encode($property);
    }

    // Save all unit data
    // if there is only one property (single property sync), only delete the units for that property
    if (count($new_properties) == 1) {
        deleteAllFeedUnitsForAProperty($new_properties[0]->Identification->PropertyCode);
    } else {
        deleteAllFeedUnits();
    }
    foreach ($rentpress_sync_units as $unit_feed_data) {
        rentpress_saveUnitData($unit_feed_data);
    }

    return $rentpress_sync_properties;
}

function rentpress_mergeSyncFeedWithWordpressMeta($rentpress_sync_properties, $all_wordpress_meta, $corresponding_meta_keys, $rentpress_options)
{
    /******************************************************
     *
     *  Sync the Feed data with the data on the Posts
     *
     *******************************************************/
    $selected_price_type = (isset($rentpress_options['rentpress_pricing_display_settings_section_price_display_selection'])) ? $rentpress_options['rentpress_pricing_display_settings_section_price_display_selection'] : 'Best Price';
    $set_available_date = strtotime(' + ' . $rentpress_options['rentpress_unit_availability_section_lookahead']) + 3600;

    // start syncing data
    foreach ($rentpress_sync_properties as $property_feed_code => $property_feed_data) {
        $property_feed_data = json_decode($property_feed_data, true);

        /* Sync the Wordpress and Feed Data */

        // the wordpress property posts have the property code as the key
        $property_post_meta = isset($all_wordpress_meta['all_properties'][$property_feed_code]["rentpress_custom_field_property_code"]) ? $all_wordpress_meta['all_properties'][$property_feed_code] : null;

        // if there are floorplans, update them
        if (isset($property_feed_data['floorplans'])) {
            $property_feed_data = rentpress_createOrUpdateFloorplans($property_feed_data, $all_wordpress_meta, $corresponding_meta_keys);
        }

        // if wordpress property doesnt exist, then create it, else update all of the feed data with any overrides
        if (is_null($property_post_meta)) {
            rentpress_createPropertyPost($property_feed_data, $corresponding_meta_keys);
        } else {
            $property_feed_data = rentpress_updateFeedDataWithOverrides($property_feed_data, $property_post_meta, $corresponding_meta_keys['rentpress_custom_field_property']);

            $property_feed_data['property_featured_image_src'] = $property_post_meta['rentpress_custom_field_property_featured_image_src'] ?? null;
            $property_feed_data['property_featured_image_srcset'] = $property_post_meta['rentpress_custom_field_property_featured_image_srcset'] ?? null;
            $property_feed_data['property_gallery_images'] = $property_post_meta['rentpress_custom_field_property_gallery_images'];
            $property_feed_data['property_gallery_shortcode'] = $property_post_meta['rentpress_custom_field_property_gallery_shortcode'];
            $property_feed_data['property_neighborhood_post_id'] = !empty($property_post_meta['rentpress_custom_field_property_neighborhoods']) ? $property_post_meta['rentpress_custom_field_property_neighborhoods'] : null;

            // if a property is overriding apply links, save a different link to all of the units
            if (isset($property_post_meta['rentpress_custom_field_property_link_options'])
                && $property_post_meta['rentpress_custom_field_property_link_options'][0] == 'Override every apply link') {
                if (isset($property_post_meta['rentpress_custom_field_property_apply_link'])
                    && !empty($property_post_meta['rentpress_custom_field_property_apply_link'][0])) {
                    updateApplyLinkForAllUnitsOfAProperty($property_feed_code, $property_post_meta['rentpress_custom_field_property_apply_link'][0]);
                } else {
                    updateApplyLinkForAllUnitsOfAProperty($property_feed_code, '');
                }
            }

            // if a property is not using the global pricing selection, update all of the units to use the selected price
            if (isset($property_post_meta['rentpress_custom_field_property_rent_type_selection_override'])) {
                rentpress_updateAllUnitPricingForAProperty($property_post_meta);
            }

            // if the office hours have been overridden then save all of the new office hours to a json field
            // else update wordpress meta with office hours
            if (isset($property_post_meta['rentpress_custom_field_property_office_hours_checkbox'][0])) {
                $office_hours = rentpress_setUpOfficeHoursMetaValuesOverride($property_post_meta);
                $property_feed_data['property_office_hours'] = json_encode([
                    "Monday" => [
                        "openTime" => $office_hours['Monday']['open'],
                        "closeTime" => $office_hours['Monday']['close'],
                    ],
                    "Tuesday" => [
                        "openTime" => $office_hours['Tuesday']['open'],
                        "closeTime" => $office_hours['Tuesday']['close'],
                    ],
                    "Wednesday" => [
                        "openTime" => $office_hours['Wednesday']['open'],
                        "closeTime" => $office_hours['Wednesday']['close'],
                    ],
                    "Thursday" => [
                        "openTime" => $office_hours['Thursday']['open'],
                        "closeTime" => $office_hours['Thursday']['close'],
                    ],
                    "Friday" => [
                        "openTime" => $office_hours['Friday']['open'],
                        "closeTime" => $office_hours['Friday']['close'],
                    ],
                    "Saturday" => [
                        "openTime" => $office_hours['Saturday']['open'],
                        "closeTime" => $office_hours['Saturday']['close'],
                    ],
                    "Sunday" => [
                        "openTime" => $office_hours['Sunday']['open'],
                        "closeTime" => $office_hours['Sunday']['close'],
                    ],
                ]);
            } elseif (!empty($property_feed_data['property_office_hours'])) {
                // add all of the office hours to the post meta
                $office_hours = rentpress_setUpOfficeHoursMetaValues($property_feed_data);
                foreach ($office_hours as $office_hour_meta_key => $office_hour_meta_value) {
                    update_post_meta($property_post_meta['post_information']->ID, $office_hour_meta_key, $office_hour_meta_value);
                }
                // remove any office hours that are no longer in the feed
                $office_hours_meta_keys = rentpress_setUpOfficeHoursMetaKeys($property_post_meta);
                $empty_office_hours = array_diff($office_hours_meta_keys, array_keys($office_hours));
                foreach ($empty_office_hours as $empty_value_key) {
                    delete_post_meta($property_post_meta['post_information']->ID, $empty_value_key);
                }

            }

            // TODO: 7.1 @Charles Separate this into a marketing resync to keep this sync lightweight
            $property_feed_data = rentpress_mergeTaxonmies($property_feed_data, $property_post_meta);
        }

        // if property is published save to rentpress DB and add a marker to indicate that it is a feed property, otherwise remove the matched property from both
        if (!is_null($property_post_meta) && $property_post_meta['post_information']->post_status == 'publish') {
            $property_feed_data['property_name'] = $property_post_meta['post_information']->post_title;
            $property_feed_data['property_post_id'] = $property_post_meta['post_information']->ID;
            $property_feed_data['property_post_link'] = get_permalink($property_post_meta['post_information']->ID);
            // if pricing is disabled, remove the pricing from selected cost
            // else if the pricing is not the global price, update it
            if (isset($rentpress_options['rentpress_disable_pricing_section_disable_pricing']) || isset($property_post_meta['rentpress_custom_field_property_disable_pricing'])) {
                $property_feed_data['property_rent_type_selection_cost'] = null;
                $property_feed_data['property_rent_type_selection'] = 'Disabled';
            } elseif ($property_feed_data['property_rent_type_selection'] != 'Global Setting') {
                $property_feed_data = rentpress_updatePropertyPriceSelection($property_feed_data);
                update_post_meta($property_feed_data['property_post_id'], 'rentpress_custom_field_property_rent_type_selection_cost', $property_feed_data['property_rent_type_selection_cost']);
            }
            rentpress_savePropertyData($property_feed_data);

            if (isset($property_feed_data['floorplans'])) {
                foreach ($property_feed_data['floorplans'] as $floorplan_code => $floorplan_feed_data) {
                    // floorplans are created as published, but if the user marks them as NOT published, then remove them from the db table
                    if (isset($all_wordpress_meta['all_floorplans'][$floorplan_code]) && $all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->post_status == 'publish') {
                        // if pricing is disabled, remove the pricing from selected cost
                        if (isset($rentpress_options['rentpress_disable_pricing_section_disable_pricing']) || isset($property_post_meta['rentpress_custom_field_property_disable_pricing'])) {
                            $floorplan_feed_data['floorplan_rent_type_selection_cost'] = null;
                            $floorplan_feed_data['floorplan_rent_type_selection'] = 'Disabled';
                            update_post_meta($floorplan_feed_data['floorplan_post_id'], 'rentpress_custom_field_floorplan_rent_type_selection', 'Disabled');
                        } elseif ($property_feed_data['property_rent_type_selection'] != 'Global Setting') {
                            $floorplan_feed_data = rentpress_updateFloorplanPriceSelection($floorplan_feed_data, $property_feed_data['property_rent_type_selection']);
                            update_post_meta($floorplan_feed_data['floorplan_post_id'], 'rentpress_custom_field_floorplan_rent_type_selection_cost', $floorplan_feed_data['floorplan_rent_type_selection_cost']);
                        }
                        // if a property is overriding apply links, save a different link to the floorplan
                        if (
                            isset($property_post_meta['rentpress_custom_field_property_apply_link'])
                            && !empty($property_post_meta['rentpress_custom_field_property_apply_link'][0])
                            && ((isset($property_post_meta['rentpress_custom_field_property_link_options']) && $property_post_meta['rentpress_custom_field_property_link_options'][0] == 'Override every apply link')
                                || empty($floorplan_feed_data['floorplan_availability_url']))
                        ) {
                            $floorplan_feed_data['floorplan_availability_url'] = $property_post_meta['rentpress_custom_field_property_apply_link'][0];
                        }
                        $floorplan_feed_data['floorplan_post_link'] = get_permalink($all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->ID);
                        $floorplan_feed_data['floorplan_parent_property_post_id'] = $property_feed_data['property_post_id'];
                        $floorplan_feed_data['floorplan_parent_property_post_link'] = $property_feed_data['property_post_link'];
                        rentpress_saveFloorplanData($floorplan_feed_data);
                        unset($all_wordpress_meta['all_floorplans'][$floorplan_code]);
                    } elseif (isset($all_wordpress_meta['all_floorplans'][$floorplan_code]) && $all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->post_status != 'publish') {
                        rentpress_removeFloorplanData($floorplan_code);
                    }
                }
            }

            // Mark this property as synced so that it isnt considered a manual property later
            $all_wordpress_meta['all_properties'][$property_feed_code]['is_feed'] = true;
        } elseif (!is_null($property_post_meta)) {
            rentpress_removePropertyData($property_post_meta['rentpress_custom_field_property_code'][0]);

            if (isset($property_post_meta['floorplans'])) {
                foreach ($property_post_meta['floorplans'] as $floorplan_code => $floorplan_post_meta) {
                    rentpress_removeFloorplanData($floorplan_code);
                    unset($all_wordpress_meta['all_floorplans'][$floorplan_code]);
                }
            }
            // Mark this property as synced so that it isnt considered a manual property later
            $all_wordpress_meta['all_properties'][$property_feed_code]['is_feed'] = true;
        }

    }

    return $all_wordpress_meta;
}

function rentpress_saveManualWordpressData($all_wordpress_meta, $corresponding_meta_keys, $rentpress_options)
{
    /*****************************************
     *
     *  Save Published Posts to RentPress DB
     *
     ******************************************/

    $set_available_date = strtotime(' + ' . $rentpress_options['rentpress_unit_availability_section_lookahead']) + 3600;
    $set_30_days = strtotime(' + 30 days') + 3600;
    $set_60_days = strtotime(' + 60 days') + 3600;
    $selected_price_type = (isset($rentpress_options['rentpress_pricing_display_settings_section_price_display_selection'])) ? $rentpress_options['rentpress_pricing_display_settings_section_price_display_selection'] : 'Best Price';
    $price_range_selection = (isset($rentpress_options['rentpress_unit_availability_section_price_range_selector'])) ? $rentpress_options['rentpress_unit_availability_section_price_range_selector'] : 'Use Floor Plan Price If No Units Are Available';

    // Any properties that are manually entered and published need to have their data saved to the DB as well
    foreach ($all_wordpress_meta['all_properties'] as $property_code => $property_post_meta) {
        // first check to see if the property has already been saved
        if (isset($property_post_meta['is_feed']) && $property_post_meta['is_feed']) {
            continue;
        }
        if (isset($property_post_meta['post_information']->post_status)
            && $property_post_meta['post_information']->post_status == 'publish'
            && !empty($property_code)) {
            $prop_ranges = rentpress_setUpPropertyArrayForRanges(array(), $selected_price_type);
            $prop_data = rentpress_standardizeWPPostMeta($property_post_meta, $corresponding_meta_keys['rentpress_custom_field_property']);
            $prop_data = array_merge($prop_ranges, $prop_data);
            $prop_data['property_name'] = $property_post_meta['post_information']->post_title;
            $prop_data['property_post_id'] = $property_post_meta['post_information']->ID;
            $prop_data['property_post_link'] = get_permalink($property_post_meta['post_information']->ID);
            $prop_data['property_featured_image_src'] = $property_post_meta['rentpress_custom_field_property_featured_image_src'] ?? null;
            $prop_data['property_featured_image_srcset'] = $property_post_meta['rentpress_custom_field_property_featured_image_srcset'] ?? null;
            $prop_data['property_gallery_images'] = $property_post_meta['rentpress_custom_field_property_gallery_images'];
            $prop_data['property_gallery_shortcode'] = $property_post_meta['rentpress_custom_field_property_gallery_shortcode'];
            $prop_data['property_neighborhood_post_id'] = !empty($property_post_meta['rentpress_custom_field_property_neighborhoods']) ? $property_post_meta['rentpress_custom_field_property_neighborhoods'] : null;
            // if pricing is disabled, remove the pricing from selected cost
            if (isset($rentpress_options['rentpress_disable_pricing_section_disable_pricing']) || isset($property_post_meta['rentpress_custom_field_property_disable_pricing'])) {
                $prop_data['property_rent_type_selection_cost'] = null;
                $prop_data['property_rent_type_selection'] = 'Disabled';
            } else {
                // if it is equal to the global setting, make it a value that makes sense
                $prop_data['property_rent_type_selection_cost'] = $prop_data['property_rent_type_selection_cost'] == 'Global Setting' ? $selected_price_type : $prop_data['property_rent_type_selection_cost'];
                $prop_data = rentpress_updatePropertyPriceSelection($prop_data);
            }

            $office_hours = rentpress_setUpOfficeHoursMetaValuesOverride($property_post_meta);
            $prop_data['property_office_hours'] = json_encode([
                "Monday" => [
                    "openTime" => $office_hours['Monday']['open'],
                    "closeTime" => $office_hours['Monday']['close'],
                ],
                "Tuesday" => [
                    "openTime" => $office_hours['Tuesday']['open'],
                    "closeTime" => $office_hours['Tuesday']['close'],
                ],
                "Wednesday" => [
                    "openTime" => $office_hours['Wednesday']['open'],
                    "closeTime" => $office_hours['Wednesday']['close'],
                ],
                "Thursday" => [
                    "openTime" => $office_hours['Thursday']['open'],
                    "closeTime" => $office_hours['Thursday']['close'],
                ],
                "Friday" => [
                    "openTime" => $office_hours['Friday']['open'],
                    "closeTime" => $office_hours['Friday']['close'],
                ],
                "Saturday" => [
                    "openTime" => $office_hours['Saturday']['open'],
                    "closeTime" => $office_hours['Saturday']['close'],
                ],
                "Sunday" => [
                    "openTime" => $office_hours['Sunday']['open'],
                    "closeTime" => $office_hours['Sunday']['close'],
                ],
            ]);

            if (isset($property_post_meta['floorplans'])) {
                foreach ($property_post_meta['floorplans'] as $floorplan_code => $floorplan_name) {
                    if ($all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->post_status == 'publish') {
                        $fp_data = rentpress_standardizeWPPostMeta($all_wordpress_meta['all_floorplans'][$floorplan_code], $corresponding_meta_keys['rentpress_custom_field_floorplan']);
                        $fp_data['floorplan_name'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->post_title;
                        $fp_data['floorplan_post_id'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->ID;
                        $fp_data['floorplan_post_link'] = get_permalink($all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->ID);
                        $fp_data['floorplan_featured_image'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['rentpress_custom_field_floorplan_featured_image'];
                        $fp_data['floorplan_featured_image_thumbnail'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['rentpress_custom_field_floorplan_featured_image_thumbnail'];
                        $fp_data['floorplan_images'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['rentpress_custom_field_floorplan_gallery_images'] ?? null;
                        // if pricing is disabled, remove the pricing from selected cost
                        if (isset($rentpress_options['rentpress_disable_pricing_section_disable_pricing']) || isset($property_post_meta['rentpress_custom_field_property_disable_pricing'])) {
                            $fp_data['floorplan_rent_type_selection_cost'] = null;
                            $fp_data['floorplan_rent_type_selection'] = 'Disabled';
                            update_post_meta($fp_data['floorplan_post_id'], 'rentpress_custom_field_floorplan_rent_type_selection', 'Disabled');
                        } elseif ($property_feed_data['property_rent_type_selection'] != 'Global Setting') {
                            $fp_data = rentpress_updateFloorplanPriceSelection($fp_data, $property_post_meta['property_rent_type_selection']);
                            update_post_meta($fp_data['floorplan_post_id'], 'rentpress_custom_field_floorplan_rent_type_selection_cost', $fp_data['floorplan_rent_type_selection_cost']);
                        }
                        $prop_data = rentpress_updatePropertyRanges($prop_data, $fp_data);
                        $fp_data['floorplan_parent_property_post_id'] = $prop_data['property_post_id'];
                        $fp_data['floorplan_parent_property_post_link'] = $prop_data['property_post_link'];
                        rentpress_saveFloorplanData($fp_data);
                    } else {
                        rentpress_removeFloorplanData($floorplan_code);
                    }
                    unset($all_wordpress_meta['all_floorplans'][$floorplan_code]);
                }
            }
            rentpress_savePropertyData($prop_data);

        } else {
            rentpress_removePropertyData($property_code);
            if (isset($property_post_meta['floorplans'])) {
                foreach ($property_post_meta['floorplans'] as $floorplan_code => $floorplan_post_meta) {
                    rentpress_removeFloorplanData($floorplan_code);
                }
            }
        }
        unset($all_wordpress_meta['all_properties'][$property_code]);
    }

    // sync manual floorplan data
    // first get any manual units and put them in an assoc array for indexing
    $manual_units = rentpress_getAllManualUnits();
    $all_manual_floorplans_units = array();
    foreach ($manual_units as $unit) {
        $all_manual_floorplans_units[$unit->unit_parent_floorplan_code][$unit->unit_code] = (array) $unit;
    }

    // for each floorplan, update all of the pricing, if parent property is published and floorplan is published, save data to db
    foreach ($all_wordpress_meta['all_floorplans'] as $floorplan_code => $manual_floorplan) {
        $property_post_meta = $all_wordpress_meta['all_properties'][$manual_floorplan['rentpress_custom_field_floorplan_parent_property_code'][0]];
        $manual_floorplan_units = isset($all_manual_floorplans_units[$manual_floorplan['rentpress_custom_field_floorplan_parent_property_code'][0]]) ? $all_manual_floorplans_units[$manual_floorplan['rentpress_custom_field_floorplan_parent_property_code'][0]] : array();
        $floorplan = array();
        $property = array();
        $floorplan['floorplan_available'] = false;
        $floorplan['floorplan_units_available'] = 0;
        $floorplan['floorplan_units_available_30'] = 0;
        $floorplan['floorplan_units_available_60'] = 0;
        $floorplan['floorplan_units_unavailable'] = 0;
        $floorplan['floorplan_units_total'] = 0;

        // If the rentpress setting that limits units is being used, make sure it is a valid value, then sort units by availability if there are at least as many units as requested
        $limiter = null;
        if (isset($rentpress_options['rentpress_unit_availability_section_limit_unit_count'])) {
            $limiter = intval(preg_replace('/[^0-9]/', '', $rentpress_options['rentpress_unit_availability_section_limit_unit_count']));
            if (!empty($limiter) &&
                $limiter > 0 &&
                count($manual_floorplan_units) >= $limiter) {
                usort($manual_floorplan_units, "rentpress_unitComparator");
            }
        }

        foreach ($manual_floorplan_units as $unit_code => $unit) {
            // break iteration of the for each if reach unit cap defined in rp settings
            if (!empty($limiter) && $unit_index >= $limiter) {
                break;
            }

            // set availability
            $unit['unit_available'] = false;
            if (!empty($unit['unit_ready_date'])) {
                $unit_available_date = date_create_from_format('n/j/Y', $unit['unit_ready_date'])->getTimestamp();
                $unit['unit_available'] = $unit_available_date < $set_available_date;
                if ($unit_available_date < $set_30_days) {
                    $floorplan['floorplan_units_available_30']++;
                }
                if ($unit_available_date < $set_60_days) {
                    $floorplan['floorplan_units_available_60']++;
                }
            }

            // if the unit should be used for calculations based on settings, update all floorplan meta to match unit data
            if (($unit['unit_available'] && $price_range_selection == 'Use Available Unit Price Only') ||
                $price_range_selection != 'Use Floor Plan Price Only') {
                $manual_floorplan = rentpress_updateManualFloorplanRanges($manual_floorplan, $unit);
            }

            if ($unit['unit_available']) {
                $floorplan['floorplan_available'] = true;
                $floorplan['floorplan_units_available']++;
            } else {
                $floorplan['floorplan_units_unavailable']++;
            }
            $floorplan['floorplan_units_total']++;
        }

        rentpress_updateManualFloorplanMetaPrices($manual_floorplan);

        if (isset($property_post_meta) && $manual_floorplan['post_information']->post_status == 'publish' && $property_post_meta['post_information']->post_status == 'publish') {
            $fp_data = rentpress_standardizeWPPostMeta($manual_floorplan, $corresponding_meta_keys['rentpress_custom_field_floorplan']);
            $fp_data = array_merge($fp_data, $floorplan);
            $fp_data['floorplan_name'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->post_title;
            $fp_data['floorplan_post_id'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->ID;
            $fp_data['floorplan_post_link'] = get_permalink($all_wordpress_meta['all_floorplans'][$floorplan_code]['post_information']->ID);
            $fp_data['floorplan_featured_image'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['rentpress_custom_field_floorplan_featured_image'];
            $fp_data['floorplan_featured_image_thumbnail'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['rentpress_custom_field_floorplan_featured_image_thumbnail'];
            $fp_data['floorplan_images'] = $all_wordpress_meta['all_floorplans'][$floorplan_code]['rentpress_custom_field_floorplan_gallery_images'] ?? null;
            $fp_data['floorplan_parent_property_post_id'] = $property_post_meta['post_information']->ID;
            $fp_data['floorplan_parent_property_post_link'] = $property_post_meta['post_information']->guid;

            // if pricing is disabled, remove the pricing from selected cost
            if (isset($rentpress_options['rentpress_disable_pricing_section_disable_pricing']) || isset($property_post_meta['rentpress_custom_field_property_disable_pricing'])) {
                $fp_data['floorplan_rent_type_selection_cost'] = null;
                $fp_data['floorplan_rent_type_selection'] = 'Disabled';
                update_post_meta($fp_data['floorplan_post_id'], 'rentpress_custom_field_floorplan_rent_type_selection', 'Disabled');
            } elseif (isset($property_post_meta['rentpress_custom_field_property_rent_type_selection_override'])) {
                $fp_data = rentpress_updateFloorplanPriceSelection($fp_data, $property_post_meta['rentpress_custom_field_property_rent_type_selection'][0]);
            } else {
                $fp_data = rentpress_updateFloorplanPriceSelection($fp_data, $selected_price_type);
            }
            update_post_meta($fp_data['floorplan_post_id'], 'rentpress_custom_field_floorplan_rent_type_selection_cost', $fp_data['floorplan_rent_type_selection_cost']);
            rentpress_saveFloorplanData($fp_data);

            // if the price is lower on the floorplan, than the property, then update the property price
            $all_wordpress_meta['all_properties'][$fp_data['floorplan_parent_property_code']] = rentpress_updatePropertyRangesForManualFloorplans($property_post_meta, $fp_data, $selected_price_type);
        } else {
            rentpress_removeFloorplanData($floorplan_code);
        }
    }

}

function rentpress_updateManualFloorplanMetaPrices($manual_floorplan)
{
    // if the range value is not overriden, save the new value
    if (!isset($manual_floorplan['rentpress_custom_field_floorplan_rent_min_override']) && isset($manual_floorplan['rentpress_custom_field_floorplan_rent_min'])) {
        update_post_meta($manual_floorplan['post_information']->ID, 'rentpress_custom_field_floorplan_rent_min', $manual_floorplan['rentpress_custom_field_floorplan_rent_min'][0]);
    }
    if (!isset($manual_floorplan['rentpress_custom_field_floorplan_rent_max_override']) && isset($manual_floorplan['rentpress_custom_field_floorplan_rent_max'])) {
        update_post_meta($manual_floorplan['post_information']->ID, 'rentpress_custom_field_floorplan_rent_max', $manual_floorplan['rentpress_custom_field_floorplan_rent_max'][0]);
    }
    if (!isset($manual_floorplan['rentpress_custom_field_floorplan_rent_base_override']) && isset($manual_floorplan['rentpress_custom_field_floorplan_rent_base'])) {
        update_post_meta($manual_floorplan['post_information']->ID, 'rentpress_custom_field_floorplan_rent_base', $manual_floorplan['rentpress_custom_field_floorplan_rent_base'][0]);
    }
    if (!isset($manual_floorplan['rentpress_custom_field_floorplan_rent_market_override']) && isset($manual_floorplan['rentpress_custom_field_floorplan_rent_market'])) {
        update_post_meta($manual_floorplan['post_information']->ID, 'rentpress_custom_field_floorplan_rent_market', $manual_floorplan['rentpress_custom_field_floorplan_rent_market'][0]);
    }
    if (!isset($manual_floorplan['rentpress_custom_field_floorplan_rent_term_override']) && isset($manual_floorplan['rentpress_custom_field_floorplan_rent_term'])) {
        update_post_meta($manual_floorplan['post_information']->ID, 'rentpress_custom_field_floorplan_rent_term', $manual_floorplan['rentpress_custom_field_floorplan_rent_term'][0]);
    }
    if (!isset($manual_floorplan['rentpress_custom_field_floorplan_rent_effective_override']) && isset($manual_floorplan['rentpress_custom_field_floorplan_rent_effective'])) {
        update_post_meta($manual_floorplan['post_information']->ID, 'rentpress_custom_field_floorplan_rent_effective', $manual_floorplan['rentpress_custom_field_floorplan_rent_effective'][0]);
    }
    if (!isset($manual_floorplan['rentpress_custom_field_floorplan_rent_best_override']) && isset($manual_floorplan['rentpress_custom_field_floorplan_rent_best'])) {
        update_post_meta($manual_floorplan['post_information']->ID, 'rentpress_custom_field_floorplan_rent_best', $manual_floorplan['rentpress_custom_field_floorplan_rent_best'][0]);
    }
}

function rentpress_updateManualFloorplanRange($floorplan_post_meta, $unit, $meta_key, $unit_key)
{
    $override_key = $meta_key . "_override";
    // if the value is not overridden then set the value if it is less or not set yet
    if (!isset($floorplan_post_meta[$override_key])
        && isset($unit[$unit_key])
        && (
            !isset($floorplan_post_meta[$meta_key][0])
            || $floorplan_post_meta[$meta_key][0] > $unit[$unit_key]
        )
    ) {
        $floorplan_post_meta[$meta_key][0] = $unit[$unit_key];
    }
    return $floorplan_post_meta;
}

function rentpress_updateManualFloorplanRanges($floorplan_post_meta, $unit)
{
    $floorplan_post_meta = rentpress_updateManualFloorplanRange($floorplan_post_meta, $unit, 'rentpress_custom_field_floorplan_rent_min', 'unit_rent_min');
    $floorplan_post_meta = rentpress_updateManualFloorplanRange($floorplan_post_meta, $unit, 'rentpress_custom_field_floorplan_rent_max', 'unit_rent_max');
    $floorplan_post_meta = rentpress_updateManualFloorplanRange($floorplan_post_meta, $unit, 'rentpress_custom_field_floorplan_rent_base', 'unit_rent_base');
    $floorplan_post_meta = rentpress_updateManualFloorplanRange($floorplan_post_meta, $unit, 'rentpress_custom_field_floorplan_rent_market', 'unit_rent_market');
    $floorplan_post_meta = rentpress_updateManualFloorplanRange($floorplan_post_meta, $unit, 'rentpress_custom_field_floorplan_rent_term', 'unit_rent_term');
    $floorplan_post_meta = rentpress_updateManualFloorplanRange($floorplan_post_meta, $unit, 'rentpress_custom_field_floorplan_rent_effective', 'unit_rent_effective');
    $floorplan_post_meta = rentpress_updateManualFloorplanRange($floorplan_post_meta, $unit, 'rentpress_custom_field_floorplan_rent_best', 'unit_rent_best');

    return $floorplan_post_meta;
}

function rentpress_feedUnitComparator($a, $b)
{
    if (empty($a->Information->ReadyDate) && !empty($b->Information->ReadyDate)) {
        return 1;
    } elseif (!empty($a->Information->ReadyDate) && empty($b->Information->ReadyDate)) {
        return -1;
    } elseif ($a->Information->ReadyDate == $b->Information->ReadyDate) {
        return 0;
    }
    $aReadyDate = date_create_from_format('n/j/Y', $a->Information->ReadyDate);
    $bReadyDate = date_create_from_format('n/j/Y', $b->Information->ReadyDate);
    return $aReadyDate <=> $bReadyDate;
}

function rentpress_unitComparator($a, $b)
{
    if (empty($a['unit_ready_date']) && !empty($b['unit_ready_date'])) {
        return 1;
    } elseif (!empty($a['unit_ready_date']) && empty($b['unit_ready_date'])) {
        return -1;
    } elseif ($a['unit_ready_date'] == $b['unit_ready_date']) {
        return 0;
    }
    $aReadyDate = date_create_from_format('n/j/Y', $a['unit_ready_date']);
    $bReadyDate = date_create_from_format('n/j/Y', $b['unit_ready_date']);
    return $aReadyDate <=> $bReadyDate;
}

function rentpress_setUpObjectForFloorplanFeedImages($floorplan_images)
{
    $images = null;
    if (!is_null($floorplan_images)) {
        $images = array();
        foreach (explode(',', json_decode($floorplan_images)) as $image) {
            $this_image['url'] = $image;
            $this_image['alt'] = '';
            array_push($images, $this_image);
        }
    }
    return $images;
}

function rentpress_updateAllUnitPricingForAProperty($property_post_meta)
{
    $units = json_decode(json_encode(rentpress_getAllUnitsByParentPropertyCode($property_post_meta['rentpress_custom_field_property_code'][0])), true);
    if (!empty($units) && count($units) > 0) {
        foreach ($units as $unit) {
            $unit['unit_rent_type_selection'] = $property_post_meta['rentpress_custom_field_property_rent_type_selection'][0];
            $unit = rentpress_updateUnitPriceSelection($unit);
            rentpress_saveUnitData($unit);
        }
    }
}

function rentpress_updateParentPropertyFeatures($property_feed_data, $floorplan_features)
{
    $floorplan_features = json_decode($floorplan_features, true);
    if (is_null($property_feed_data['property_features'])) {
        $property_features = array();
    } else {
        $property_features = json_decode($property_feed_data['property_features']);
    }

    foreach ($floorplan_features as $floorplan_feature) {
        if (!in_array($floorplan_feature, $property_features)) {
            array_push($property_features, $floorplan_feature);
        }
    }

    $property_feed_data['property_features'] = json_encode($property_features);
    return $property_feed_data;
}

function rentpress_mergeFloorplanTaxonomies($floorplan_feed_data, $floorplan_post_meta)
{
    $post_id = $floorplan_post_meta['post_information']->ID;

    $terms = array();

    // Make checks to see if the floorplan has all features that are in the feed
    // if not add them
    $floorplan_feed_features = json_decode($floorplan_feed_data['floorplan_features']);
    $feature_terms = get_the_terms($post_id, 'feature');

    // do features first to save the data into a different DB field
    if (isset($floorplan_feed_features) && count($floorplan_feed_features) > 0) {
        foreach ($floorplan_feed_features as $feed_feature) {
            // if it is bad data, then ignore it
            if ($feed_feature->Title == 'Custom Amenity' || $feed_feature->Title == 'Other') {
                continue;
            }
            // if it doesn't exist on property in wordpress, create it
            $exists_in_wp = false;
            if (!empty($feature_terms)) {
                foreach ($feature_terms as $feature_term_key => $feature_term) {
                    if ($feed_feature->Title == $feature_term->name) {
                        $exists_in_wp = true;
                        array_push($terms, $feature_term->name);
                        unset($feature_terms[$feature_term_key]);
                        break;
                    }
                }
            }

            if (!$exists_in_wp) {
                wp_set_object_terms($post_id, esc_attr($feed_feature->Title), 'feature', true);
            }

        }
    }

    // any leftover custom site features are not on the feed, so add them to the terms
    if ($feature_terms && count($feature_terms) > 0) {
        foreach ($feature_terms as $feature_term_key => $feature_term) {
            array_push($terms, htmlspecialchars_decode($feature_term->name, ENT_QUOTES));
        }
    }
    // save current terms into features column
    $floorplan_feed_data['floorplan_features'] = count($terms) > 0 ? json_encode($terms) : null;

    return $floorplan_feed_data;

}

function rentpress_mergeTaxonmies($property_feed_data, $post_meta)
{
    $post_id = $post_meta['post_information']->ID;

    // Get All current Terms for each taxonomy
    $terms = array();

    // Make checks to see if the property has all amenities that are in the feed
    // if not add them
    $property_feed_amenities = json_decode($property_feed_data['property_community_amenities']);
    $amenity_terms = get_the_terms($post_id, 'amenity');
    // do amenities first to save the data into a different DB field
    if (isset($property_feed_amenities) && count($property_feed_amenities) > 0) {
        foreach ($property_feed_amenities as $feed_amenity_key => $feed_amenity) {
            // if it is bad data, then ignore it
            if ($feed_amenity->Title == 'Custom Amenity' || $feed_amenity->Title == 'Other') {
                continue;
            }
            // if it doesn't exist on property in wordpress, create it
            $exists_in_wp = false;
            if (!empty($amenity_terms)) {
                foreach ($amenity_terms as $amenity_term_key => $amenity_term) {
                    if ($feed_amenity->Title == $amenity_term->name) {
                        $exists_in_wp = true;
                        //trim or contains underscore
                        array_push($terms, $amenity_term->name);
                        unset($amenity_terms[$amenity_term_key]);
                        break;
                    }
                }
            }

            if (!$exists_in_wp) {
                wp_set_object_terms($post_id, esc_attr($feed_amenity->Title), 'amenity', true);
            }

        }
    }
    // any leftover custom site amenities are not on the feed, so add them to the terms
    if ($amenity_terms && count($amenity_terms) > 0) {
        foreach ($amenity_terms as $amenity_term_key => $amenity_term) {
            array_push($terms, htmlspecialchars_decode($amenity_term->name, ENT_QUOTES));
        }
    }
    // save current terms into amenities column to be used separately
    $property_feed_data['property_community_amenities'] = count($terms) > 0 ? json_encode($terms) : null;

    // START AGAIN WITH FEATURES
    $secondary_terms = array();

    // all of the features from the property and floorplans feed are already added to the features array
    // add all of the feed terms to wordpress, and to the property as needed
    $feature_terms = get_the_terms($post_id, 'feature');
    $property_feed_features = json_decode($property_feed_data['property_features']);
    // var_dump($property_feed_data['property_name']);
    //         var_dump($property_feed_features);
    //         var_dump($feature_terms);
    if (isset($property_feed_features) && count($property_feed_features) > 0) {
        foreach ($property_feed_features as $feed_feature) {
            // if it is bad data, then ignore it
            if ($feed_feature == 'Custom Amenity' || $feed_feature == 'Other') {
                continue;
            }
            // if it doesn't exist on property in wordpress, create it
            $exists_in_wp = false;
            if (!empty($feature_terms)) {
                foreach ($feature_terms as $feature_term_key => $feature_term) {
                    if ($feed_feature == $feature_term->name) {
                        $exists_in_wp = true;
                        array_push($secondary_terms, $feature_term->name);
                        unset($feature_terms[$feature_term_key]);
                        break;
                    }
                }
            }

            if (!$exists_in_wp) {
                wp_set_object_terms($post_id, esc_attr($feed_feature), 'feature', true);
            }

        }
    }
    // any leftover custom site features are not on the feed, so add them to the terms
    if ($feature_terms && count($feature_terms) > 0) {
        foreach ($feature_terms as $feature_term_key => $feature_term) {
            array_push($secondary_terms, htmlspecialchars_decode($feature_term->name, ENT_QUOTES));
        }
    }
    // save the secondary terms into features column to be used separately
    $property_feed_data['property_features'] = count($secondary_terms) > 0 ? json_encode($secondary_terms) : null;

    // combine the features and amenities so that they are able to be searched easier
    $terms = array_merge($terms, $secondary_terms);

    $pet_terms = get_the_terms($post_id, 'pet');
    if ($pet_terms) {
        foreach ($pet_terms as $pt) {
            array_push($terms, $pt->name);
        }
    }

    $city_terms = get_the_terms($post_id, 'city');
    if ($city_terms && $city_terms[0]->name == $property_feed_data['property_city']) {
        array_push($terms, $city_terms[0]->name);
    } else {
        $new_term = $property_feed_data['property_city'];
        // put false here because we only want them to be in one city
        wp_set_object_terms($post_id, $new_term, 'city', false);
        array_push($terms, $new_term);
    }

    // save all terms to a DB column for advanced searching
    $property_feed_data['property_terms'] = json_encode($terms);
    return $property_feed_data;
}

function rentpress_createOrUpdateFloorplans($property_feed_data, $all_wordpress_meta, $corresponding_meta_keys)
{
    // update any of the floorplans that exist, if not, make them
    // explicity check to see if a floorplan doesn't exist in the list before making it to avoid duplicate floorplan codes
    foreach ($property_feed_data['floorplans'] as $floorplans_feed_code => $floorplan_feed_data) {
        if (isset($all_wordpress_meta['all_floorplans'][$floorplans_feed_code]) && isset($all_wordpress_meta['all_floorplans'][$floorplans_feed_code]['post_information']->ID)) {
            $floorplan_feed_data = rentpress_updateFeedDataWithOverrides($floorplan_feed_data, $all_wordpress_meta['all_floorplans'][$floorplans_feed_code], $corresponding_meta_keys['rentpress_custom_field_floorplan']);
            $floorplan_feed_data['floorplan_post_id'] = $all_wordpress_meta['all_floorplans'][$floorplans_feed_code]['post_information']->ID;
            $floorplan_feed_data['floorplan_name'] = $all_wordpress_meta['all_floorplans'][$floorplans_feed_code]['post_information']->post_title;
            $floorplan_feed_data['floorplan_featured_image'] = $all_wordpress_meta['all_floorplans'][$floorplans_feed_code]['rentpress_custom_field_floorplan_featured_image'];
            $floorplan_feed_data['floorplan_featured_image_thumbnail'] = $all_wordpress_meta['all_floorplans'][$floorplans_feed_code]['rentpress_custom_field_floorplan_featured_image_thumbnail'];
            $floorplan_feed_data['floorplan_images'] = $all_wordpress_meta['all_floorplans'][$floorplans_feed_code]['rentpress_custom_field_floorplan_gallery_images'] ?? rentpress_setUpObjectForFloorplanFeedImages($floorplan_feed_data['floorplan_images']);
            // update parent property pricing based on the overridden floorplan pricing fields
            $property_feed_data = rentpress_updatePropertyRanges($property_feed_data, $floorplan_feed_data);
            // update features taxonomy
            $floorplan_feed_data = rentpress_mergeFloorplanTaxonomies($floorplan_feed_data, $all_wordpress_meta['all_floorplans'][$floorplans_feed_code]);
            if (!empty($floorplan_feed_data['floorplan_features'])) {
                $property_feed_data = rentpress_updateParentPropertyFeatures($property_feed_data, $floorplan_feed_data['floorplan_features']);
            }
            $property_feed_data['floorplans'][$floorplans_feed_code] = $floorplan_feed_data;
            unset($all_wordpress_meta['all_floorplans'][$floorplans_feed_code]);

        } elseif (!isset($all_wordpress_meta['all_floorplans'][$floorplans_feed_code])) {
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
            $new_db_format[$db_column_name] = $post_meta[$post_meta_key][0];
        }
    }
    return $new_db_format;
}

function rentpress_createFloorplanPost($floorplan_feed_data, $corresponding_meta_keys)
{
    // Create new floorplan post
    $new_floorplan_post = [
        'post_title' => $floorplan_feed_data['floorplan_name'],
        'post_status' => 'publish',
        'post_type' => 'rentpress_floorplan',
    ];
    $new_post_id = wp_insert_post($new_floorplan_post);

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
        'post_title' => $property_feed_data['property_name'],
        'post_status' => 'draft',
        'post_type' => 'rentpress_property',
    ];
    $new_post_id = wp_insert_post($new_property_post);

    // for each corresponding meta key, save the property feed value if it exists
    foreach ($corresponding_meta_keys['rentpress_custom_field_property'] as $property_post_meta_key => $property_feed_data_key) {
        if (isset($property_feed_data[$property_feed_data_key])) {
            update_post_meta($new_post_id, $property_post_meta_key, $property_feed_data[$property_feed_data_key]);
        }
    }
    update_post_meta($new_post_id, 'rentpress_custom_field_property_rent_type_selection', 'Global Setting');
}

function rentpress_updateFeedDataWithOverrides($feed_data, $post_meta, $corresponding_meta_keys)
{

    foreach ($corresponding_meta_keys as $post_meta_key => $feed_data_key) {

        $override_key = $post_meta_key . '_override';

        // update feed values with wp data if overriden
        // else update wp meta with new synced values
        if (isset($post_meta[$override_key]) &&
            $post_meta[$override_key][0] == 'on') {

            $feed_data[$feed_data_key] = htmlspecialchars_decode($post_meta[$post_meta_key][0], ENT_QUOTES);

        } elseif (isset($feed_data[$feed_data_key]) && isset($post_meta['post_information']->ID)) {
            update_post_meta($post_meta['post_information']->ID, $post_meta_key, $feed_data[$feed_data_key]);
        } elseif (isset($post_meta['post_information']->ID)) {
            update_post_meta($post_meta['post_information']->ID, $post_meta_key, '');
        }
    }

    return $feed_data;
}

function rentpress_updatePropertyPriceSelection($property)
{

    switch ($property['property_rent_type_selection']) {
        case 'Best Price':
            $property['property_rent_type_selection_cost'] = $property['property_rent_best'];
            break;
        case 'Term Rent':
            $property['property_rent_type_selection_cost'] = $property['property_rent_term'];
            break;
        case 'Effective Rent':
            $property['property_rent_type_selection_cost'] = $property['property_rent_effective'];
            break;
        case 'Market Rent':
            $property['property_rent_type_selection_cost'] = $property['property_rent_market'];
            break;
        case 'Base Rent':
            $property['property_rent_type_selection_cost'] = $property['property_rent_base'];
            break;
        default:
            $property['property_rent_type_selection_cost'] = $property['property_rent_min'];
    }
    return $property;
}

function rentpress_updateFloorplanPriceSelection($floorplan, $parent_property_price_type_selection)
{
    $floorplan['floorplan_rent_type_selection'] = $parent_property_price_type_selection;
    switch ($floorplan['floorplan_rent_type_selection']) {
        case 'Best Price':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_best'];
            break;
        case 'Term Rent':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_term'];
            break;
        case 'Effective Rent':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_effective'];
            break;
        case 'Market Rent':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_market'];
            break;
        case 'Base Rent':
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_base'];
            break;
        default:
            $floorplan['floorplan_rent_type_selection_cost'] = $floorplan['floorplan_rent_min'];
    }
    return $floorplan;
}

function rentpress_updateUnitPriceSelection($unit)
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
        case 'Base Rent':
            $unit['unit_rent_type_selection_cost'] = $unit['unit_rent_base'];
            break;
        default:
            $unit['unit_rent_type_selection_cost'] = $unit['unit_rent_min'];
            break;
    }

    return $unit;
}

function rentpress_updatePropertyRanges($property, $floorplan)
{
    if ($floorplan['floorplan_available']) {
        $property['property_available_floorplans']++;
    } else {
        $property['property_unavailable_floorplans']++;
    }

    $property['property_available_units'] += $floorplan['floorplan_units_available'];
    $property['property_unavailable_units'] += $floorplan['floorplan_units_unavailable'];

    // TODO: 7.1 @Charles add pricing matrix calculations here

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

function rentpress_updatePostMetaValue($post_meta, $value_key, $new_value)
{
    update_post_meta($post_meta['post_information']->ID, $value_key, $new_value);
    $post_meta[$value_key][0] = $new_value;
    return $post_meta;
}

function rentpress_updatePropertyPricingForManualFloorplans($property_post_meta, $floorplan_data, $property_post_rent_key, $property_db_key, $floorplan_rent_key)
{
    $property_override_key = $property_post_rent_key . '_override';

    // if the override is not set at the property AND
    // if the rent is not set for the property but is for the floorplan OR if the rent is set for both the floorplan and the property, but the floorplan rent is smaller
    // then update the property post meta and the database row for the property
    if (!isset($property_post_meta[$property_override_key])
        && (
            (
                !isset($property_post_meta[$property_post_rent_key])
                && isset($floorplan_data[$floorplan_rent_key])
            ) || (
                isset($property_post_meta[$property_post_rent_key])
                && isset($floorplan_data[$floorplan_rent_key])
                && $floorplan_data[$floorplan_rent_key] < $property_post_meta[$property_post_rent_key][0]
            )
        )
    ) {
        rentpress_updatePropertyDBColumn($property_post_meta['rentpress_custom_field_property_code'][0], $property_db_key, $floorplan_data[$floorplan_rent_key]);
        $property_post_meta = rentpress_updatePostMetaValue($property_post_meta, $property_post_rent_key, $floorplan_data[$floorplan_rent_key]);
    }
    return $property_post_meta;
}

function rentpress_updatePropertyPriceSelectionForPostMeta($property_post_meta, $selected_price_type)
{

    // if the property selected price is global, set key with selected price
    // else the price is not global so nothing needs to be done
    $price_key = $selected_price_type;
    if (isset($property_post_meta['rentpress_custom_field_property_rent_type_selection_override'])) {
        $price_key = $property_post_meta['rentpress_custom_field_property_rent_type_selection'][0];
    }

    switch ($price_key) {
        case 'Best Price':
            rentpress_updatePropertyDBColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_best'][0]);
            $property_post_meta = rentpress_updatePostMetaValue($property_post_meta, 'rentpress_custom_field_property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_best'][0]);
            break;
        case 'Term Rent':
            rentpress_updatePropertyDBColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_term'][0]);
            $property_post_meta = rentpress_updatePostMetaValue($property_post_meta, 'rentpress_custom_field_property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_term'][0]);
            break;
        case 'Effective Rent':
            rentpress_updatePropertyDBColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_effective'][0]);
            $property_post_meta = rentpress_updatePostMetaValue($property_post_meta, 'rentpress_custom_field_property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_effective'][0]);
            break;
        case 'Market Rent':
            rentpress_updatePropertyDBColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_market'][0]);
            $property_post_meta = rentpress_updatePostMetaValue($property_post_meta, 'rentpress_custom_field_property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_market'][0]);
            break;
        case 'Base Rent':
            rentpress_updatePropertyDBColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_base'][0]);
            $property_post_meta = rentpress_updatePostMetaValue($property_post_meta, 'rentpress_custom_field_property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_base'][0]);
            break;
        default:
            rentpress_updatePropertyDBColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_min'][0]);
            $property_post_meta = rentpress_updatePostMetaValue($property_post_meta, 'rentpress_custom_field_property_rent_type_selection_cost', $property_post_meta['rentpress_custom_field_property_rent_min'][0]);
    }
    return $property_post_meta;
}

function rentpress_updatePropertyRangesForManualFloorplans($property_post_meta, $floorplan_data, $selected_price_type)
{
    $property_post_meta = rentpress_updatePropertyPricingForManualFloorplans($property_post_meta, $floorplan_data, 'rentpress_custom_field_property_rent_min', 'property_rent_min', 'floorplan_rent_min');
    $property_post_meta = rentpress_updatePropertyPricingForManualFloorplans($property_post_meta, $floorplan_data, 'rentpress_custom_field_property_rent_base', 'property_rent_base', 'floorplan_rent_base');
    $property_post_meta = rentpress_updatePropertyPricingForManualFloorplans($property_post_meta, $floorplan_data, 'rentpress_custom_field_property_rent_market', 'property_rent_market', 'floorplan_rent_market');
    $property_post_meta = rentpress_updatePropertyPricingForManualFloorplans($property_post_meta, $floorplan_data, 'rentpress_custom_field_property_rent_term', 'property_rent_term', 'floorplan_rent_term');
    $property_post_meta = rentpress_updatePropertyPricingForManualFloorplans($property_post_meta, $floorplan_data, 'rentpress_custom_field_property_rent_effective', 'property_rent_effective', 'floorplan_rent_effective');
    $property_post_meta = rentpress_updatePropertyPricingForManualFloorplans($property_post_meta, $floorplan_data, 'rentpress_custom_field_property_rent_best', 'property_rent_best', 'floorplan_rent_best');

    // max is the only different one
    if (!isset($property_post_meta['rentpress_custom_field_property_rent_max_override'])
        && (
            (
                !isset($property_post_meta['rentpress_custom_field_property_rent_max'])
                && isset($floorplan_data['floorplan_rent_max'])
            ) || (
                isset($property_post_meta['rentpress_custom_field_property_rent_max'])
                && isset($floorplan_data['floorplan_rent_max'])
                && $floorplan_data['floorplan_rent_max'] > $property_post_meta['rentpress_custom_field_property_rent_max'][0]
            )
        )
    ) {
        rentpress_updatePropertyDBColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_rent_max', $floorplan_data['floorplan_rent_max']);
        $property_post_meta = rentpress_updatePostMetaValue($property_post_meta, 'rentpress_custom_field_property_rent_max', $floorplan_data['floorplan_rent_max']);
    }

    $property_post_meta = rentpress_updatePropertyPriceSelectionForPostMeta($property_post_meta, $selected_price_type);

    if ($floorplan_data['floorplan_available']) {
        rentpress_updatePropertyDBRangeColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_available_floorplans', 1);
    } else {
        rentpress_updatePropertyDBRangeColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_unavailable_floorplans', 1);
    }

    if (isset($floorplan_data['floorplan_units_available']) && $floorplan_data['floorplan_units_available'] > 0) {
        rentpress_updatePropertyDBRangeColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_available_units', $floorplan_data['floorplan_units_available']);
    }
    if (isset($floorplan_data['floorplan_units_unavailable']) && $floorplan_data['floorplan_units_unavailable'] > 0) {
        rentpress_updatePropertyDBRangeColumn($property_post_meta['rentpress_custom_field_property_code'][0], 'property_unavailable_units', $floorplan_data['floorplan_units_unavailable']);
    }

    return $property_post_meta;
}

function rentpress_updateUnitRanges($unit, $selected_price_type)
{
    $unit['unit_rent_type_selection'] = $selected_price_type;
    // If a unit has term rents
    if (!empty($unit['unit_rent_terms'])) {
        $term_rents = json_decode($unit['unit_rent_terms']);
        foreach ($term_rents as $term) {
            $rent = (int) $term->Rent;
            if ($rent > 100 && (is_null($unit['unit_rent_term_best']) || $rent < $unit['unit_rent_term_best'])) {
                $unit['unit_rent_term_best'] = $rent;
            }
        }
    }
    // // clear any prices that are below 100
    // $unit['unit_rent_min'] = !empty($unit['unit_rent_min']) && $unit['unit_rent_min'] > 100 ? $unit['unit_rent_min'] : null;
    // $unit['unit_rent_max'] = !empty($unit['unit_rent_max']) && $unit['unit_rent_max'] > 100 ? $unit['unit_rent_max'] : null;
    // $unit['unit_rent_base'] = !empty($unit['unit_rent_base']) && $unit['unit_rent_base'] > 100 ? $unit['unit_rent_base'] : null;
    // $unit['unit_rent_effective'] = !empty($unit['unit_rent_effective']) && $unit['unit_rent_effective'] > 100 ? $unit['unit_rent_effective'] : null;
    // $unit['unit_rent_market'] = !empty($unit['unit_rent_market']) && $unit['unit_rent_market'] > 100 ? $unit['unit_rent_market'] : null;
    // $unit['unit_rent_best'] = !empty($unit['unit_rent_best']) && $unit['unit_rent_best'] > 100 ? $unit['unit_rent_best'] : null;
    // $unit['unit_rent_term_best'] = !empty($unit['unit_rent_term_best']) && $unit['unit_rent_term_best'] > 100 ? $unit['unit_rent_term_best'] : null;

    $unit = rentpress_updateUnitPriceSelection($unit);

    return $unit;
}

function rentpress_updateFloorplanRanges($floorplan, $unit)
{

    $floorplan_rent_min = $unit['unit_rent_min'] ?? $floorplan['floorplan_rent_min'];
    $floorplan_rent_max = $unit['unit_rent_max'] ?? $floorplan['floorplan_rent_max'];
    $floorplan_rent_base = $unit['unit_rent_base'] ?? $floorplan['floorplan_rent_base'];
    $floorplan_rent_effective = $unit['unit_rent_effective'];
    $floorplan_rent_market = $unit['unit_rent_market'];
    $floorplan_rent_best = $unit['unit_rent_best'] ?? $floorplan_rent_min;
    $floorplan_rent_term = $unit['unit_rent_term_best'] ?? null;

    // Check to see if this value is different than the previously saved one
    $floorplan['floorplan_rent_min'] = ((is_null($floorplan['floorplan_rent_min'])) || (!is_null($floorplan_rent_min) && $floorplan_rent_min < $floorplan['floorplan_rent_min'])) ? $floorplan_rent_min : $floorplan['floorplan_rent_min'];
    $floorplan['floorplan_rent_max'] = ((is_null($floorplan['floorplan_rent_max'])) || (!is_null($floorplan_rent_max) && $floorplan_rent_max > $floorplan['floorplan_rent_max'])) ? $floorplan_rent_max : $floorplan['floorplan_rent_max'];
    $floorplan['floorplan_rent_base'] = ((is_null($floorplan['floorplan_rent_base'])) || (!is_null($floorplan_rent_base) && $floorplan_rent_base < $floorplan['floorplan_rent_base'])) ? $floorplan_rent_base : $floorplan['floorplan_rent_base'];
    $floorplan['floorplan_rent_effective'] = ((is_null($floorplan['floorplan_rent_effective'])) || (!is_null($floorplan_rent_effective) && $floorplan_rent_effective < $floorplan['floorplan_rent_effective'])) ? $floorplan_rent_effective : $floorplan['floorplan_rent_effective'];
    $floorplan['floorplan_rent_market'] = ((is_null($floorplan['floorplan_rent_market'])) || (!is_null($floorplan_rent_market) && $floorplan_rent_market < $floorplan['floorplan_rent_market'])) ? $floorplan_rent_market : $floorplan['floorplan_rent_market'];
    $floorplan['floorplan_rent_best'] = ((is_null($floorplan['floorplan_rent_best'])) || (!is_null($floorplan_rent_best) && $floorplan_rent_best < $floorplan['floorplan_rent_best'])) ? $floorplan_rent_best : $floorplan['floorplan_rent_best'];
    $floorplan['floorplan_rent_term'] = ((is_null($floorplan['floorplan_rent_term'])) || (!is_null($floorplan_rent_term) && $floorplan_rent_term < $floorplan['floorplan_rent_term'])) ? $floorplan_rent_term : $floorplan['floorplan_rent_term'];

    return $floorplan;
}

function rentpress_setUpFloorplanArrayForRanges($floorplan, $selected_price_type)
{
    $floorplan['floorplan_available'] = false;
    $floorplan['floorplan_units_total'] = 0;
    $floorplan['floorplan_units_available'] = 0;
    $floorplan['floorplan_units_available_30'] = 0;
    $floorplan['floorplan_units_available_60'] = 0;
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

function rentpress_setUpPropertyArrayForRanges($property, $selected_price_type)
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

function rentpress_setUpOfficeHoursMetaValuesOverride($property_post_meta)
{
    $office_hours = array();
    $office_hours['Monday']['open'] = (isset($property_post_meta['rentpress_custom_field_property_monday_open']) && !empty($property_post_meta['rentpress_custom_field_property_monday_open'][0])) ? $property_post_meta['rentpress_custom_field_property_monday_open'][0] : null;
    $office_hours['Monday']['close'] = (isset($property_post_meta['rentpress_custom_field_property_monday_close']) && !empty($property_post_meta['rentpress_custom_field_property_monday_close'][0])) ? $property_post_meta['rentpress_custom_field_property_monday_close'][0] : null;
    $office_hours['Tuesday']['open'] = (isset($property_post_meta['rentpress_custom_field_property_tuesday_open']) && !empty($property_post_meta['rentpress_custom_field_property_tuesday_open'][0])) ? $property_post_meta['rentpress_custom_field_property_tuesday_open'][0] : null;
    $office_hours['Tuesday']['close'] = (isset($property_post_meta['rentpress_custom_field_property_tuesday_close']) && !empty($property_post_meta['rentpress_custom_field_property_tuesday_close'][0])) ? $property_post_meta['rentpress_custom_field_property_tuesday_close'][0] : null;
    $office_hours['Wednesday']['open'] = (isset($property_post_meta['rentpress_custom_field_property_wednesday_open']) && !empty($property_post_meta['rentpress_custom_field_property_wednesday_open'][0])) ? $property_post_meta['rentpress_custom_field_property_wednesday_open'][0] : null;
    $office_hours['Wednesday']['close'] = (isset($property_post_meta['rentpress_custom_field_property_wednesday_close']) && !empty($property_post_meta['rentpress_custom_field_property_wednesday_close'][0])) ? $property_post_meta['rentpress_custom_field_property_wednesday_close'][0] : null;
    $office_hours['Thursday']['open'] = (isset($property_post_meta['rentpress_custom_field_property_thursday_open']) && !empty($property_post_meta['rentpress_custom_field_property_thursday_open'][0])) ? $property_post_meta['rentpress_custom_field_property_thursday_open'][0] : null;
    $office_hours['Thursday']['close'] = (isset($property_post_meta['rentpress_custom_field_property_thursday_close']) && !empty($property_post_meta['rentpress_custom_field_property_thursday_close'][0])) ? $property_post_meta['rentpress_custom_field_property_thursday_close'][0] : null;
    $office_hours['Friday']['open'] = (isset($property_post_meta['rentpress_custom_field_property_friday_open']) && !empty($property_post_meta['rentpress_custom_field_property_friday_open'][0])) ? $property_post_meta['rentpress_custom_field_property_friday_open'][0] : null;
    $office_hours['Friday']['close'] = (isset($property_post_meta['rentpress_custom_field_property_friday_close']) && !empty($property_post_meta['rentpress_custom_field_property_friday_close'][0])) ? $property_post_meta['rentpress_custom_field_property_friday_close'][0] : null;
    $office_hours['Saturday']['open'] = (isset($property_post_meta['rentpress_custom_field_property_saturday_open']) && !empty($property_post_meta['rentpress_custom_field_property_saturday_open'][0])) ? $property_post_meta['rentpress_custom_field_property_saturday_open'][0] : null;
    $office_hours['Saturday']['close'] = (isset($property_post_meta['rentpress_custom_field_property_saturday_close']) && !empty($property_post_meta['rentpress_custom_field_property_saturday_close'][0])) ? $property_post_meta['rentpress_custom_field_property_saturday_close'][0] : null;
    $office_hours['Sunday']['open'] = (isset($property_post_meta['rentpress_custom_field_property_sunday_open']) && !empty($property_post_meta['rentpress_custom_field_property_sunday_open'][0])) ? $property_post_meta['rentpress_custom_field_property_sunday_open'][0] : null;
    $office_hours['Sunday']['close'] = (isset($property_post_meta['rentpress_custom_field_property_sunday_close']) && !empty($property_post_meta['rentpress_custom_field_property_sunday_close'][0])) ? $property_post_meta['rentpress_custom_field_property_sunday_close'][0] : null;

    return $office_hours;
}

function rentpress_setUpOfficeHoursMetaValues($property)
{
    $office_hours = array();
    foreach (json_decode($property['property_office_hours']) as $officeDayKey => $officeDay) {
        switch ($officeDayKey) {
            case 'Monday':
                $office_hours['rentpress_custom_field_property_monday_open'] = $officeDay->openTime;
                $office_hours['rentpress_custom_field_property_monday_close'] = $officeDay->closeTime;
                break;

            case 'Tuesday':
                $office_hours['rentpress_custom_field_property_tuesday_open'] = $officeDay->openTime;
                $office_hours['rentpress_custom_field_property_tuesday_close'] = $officeDay->closeTime;
                break;

            case 'Wednesday':
                $office_hours['rentpress_custom_field_property_wednesday_open'] = $officeDay->openTime;
                $office_hours['rentpress_custom_field_property_wednesday_close'] = $officeDay->closeTime;
                break;

            case 'Thursday':
                $office_hours['rentpress_custom_field_property_thursday_open'] = $officeDay->openTime;
                $office_hours['rentpress_custom_field_property_thursday_close'] = $officeDay->closeTime;
                break;

            case 'Friday':
                $office_hours['rentpress_custom_field_property_friday_open'] = $officeDay->openTime;
                $office_hours['rentpress_custom_field_property_friday_close'] = $officeDay->closeTime;
                break;

            case 'Saturday':
                $office_hours['rentpress_custom_field_property_saturday_open'] = $officeDay->openTime;
                $office_hours['rentpress_custom_field_property_saturday_close'] = $officeDay->closeTime;
                break;

            case 'Sunday':
                $office_hours['rentpress_custom_field_property_sunday_open'] = $officeDay->openTime;
                $office_hours['rentpress_custom_field_property_sunday_close'] = $officeDay->closeTime;
                break;
        }
    }
    return $office_hours;
}

function rentpress_setUpOfficeHoursMetaKeys($property_post_meta)
{
    $office_hour_meta_keys = [
        'rentpress_custom_field_property_monday_open',
        'rentpress_custom_field_property_monday_close',
        'rentpress_custom_field_property_tuesday_open',
        'rentpress_custom_field_property_tuesday_close',
        'rentpress_custom_field_property_wednesday_open',
        'rentpress_custom_field_property_wednesday_close',
        'rentpress_custom_field_property_thursday_open',
        'rentpress_custom_field_property_thursday_close',
        'rentpress_custom_field_property_friday_open',
        'rentpress_custom_field_property_friday_close',
        'rentpress_custom_field_property_saturday_open',
        'rentpress_custom_field_property_saturday_close',
        'rentpress_custom_field_property_sunday_open',
        'rentpress_custom_field_property_sunday_close',
    ];

    return array_intersect(array_keys($property_post_meta), $office_hour_meta_keys);
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
                'include' => 'floorplans.units',
            ],
            'compress' => true,
            'headers' => array( /* set token and username */
                'X-Topline-Token' => $rentpress_options['rentpress_api_credentials_section_api_token'],
                'X-Topline-User' => $rentpress_options['rentpress_api_credentials_section_username'],
            ),
            'timeout' => 60,
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
                'X-Topline-User' => $rentpress_options['rentpress_api_credentials_section_username'],
            ),
            'timeout' => 60
        ),
    );

    return $results;
}

function rentpress_getCorrespondingWordpressToFeedKeyArray()
{
    return [
        'rentpress_custom_field_property' => [
            'rentpress_custom_field_property_code' => 'property_code',
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
            'rentpress_custom_field_property_special_link' => 'property_specials_link',
            'rentpress_custom_field_property_facebook_url' => 'property_facebook_link',
            'rentpress_custom_field_property_twitter_url' => 'property_twitter_link',
            'rentpress_custom_field_property_instagram_url' => 'property_instagram_link',
            'rentpress_custom_field_property_residents_link' => 'property_residents_link',
            'rentpress_custom_field_property_search_keywords' => 'property_additional_keywords',
            'rentpress_custom_field_property_pet_policy' => 'property_pet_policy',
            'rentpress_custom_field_property_rent_min' => 'property_rent_min',
            'rentpress_custom_field_property_rent_max' => 'property_rent_max',
            'rentpress_custom_field_property_rent_base' => 'property_rent_base',
            'rentpress_custom_field_property_rent_market' => 'property_rent_market',
            'rentpress_custom_field_property_rent_term' => 'property_rent_term',
            'rentpress_custom_field_property_rent_effective' => 'property_rent_effective',
            'rentpress_custom_field_property_rent_best' => 'property_rent_best',

            // Meta does not exist for the following DB columns
            // 'rentpress_custom_field_property_available_units' => 'property_available_units',
            // 'rentpress_custom_field_property_unavailable_units' => 'property_unavailable_units',
            // 'rentpress_custom_field_property_available_floorplans' => 'property_available_floorplans',
            // 'rentpress_custom_field_property_unavailable_floorplans' => 'property_unavailable_floorplans',
        ],
        'rentpress_custom_field_floorplan' => [
            'rentpress_custom_field_floorplan_parent_property_code' => 'floorplan_parent_property_code',
            'rentpress_custom_field_floorplan_code' => 'floorplan_code',
            'rentpress_custom_field_floorplan_bedroom_count' => 'floorplan_bedrooms',
            'rentpress_custom_field_floorplan_bathroom_count' => 'floorplan_bathrooms',
            'rentpress_custom_field_floorplan_min_sqft' => 'floorplan_sqft_min',
            'rentpress_custom_field_floorplan_max_sqft' => 'floorplan_sqft_max',
            'rentpress_custom_field_floorplan_min_rent' => 'floorplan_rent_min',
            'rentpress_custom_field_floorplan_max_rent' => 'floorplan_rent_max',
            'rentpress_custom_field_floorplan_availability_url' => 'floorplan_availability_url',
            'rentpress_custom_field_floorplan_unit_type_mapping' => 'floorplan_unit_type_mapping',
            'rentpress_custom_field_floorplan_matterport_video' => 'floorplan_matterport_url',
            'rentpress_custom_field_floorplan_image' => 'floorplan_image',
            'rentpress_custom_field_floorplan_description' => 'floorplan_description',
            'rentpress_custom_field_floorplan_special_text' => 'floorplan_specials_message',
            'rentpress_custom_field_floorplan_special_link' => 'floorplan_specials_link',
            'rentpress_custom_field_floorplan_rent_min' => 'floorplan_rent_min',
            'rentpress_custom_field_floorplan_rent_max' => 'floorplan_rent_max',
            'rentpress_custom_field_floorplan_rent_base' => 'floorplan_rent_base',
            'rentpress_custom_field_floorplan_rent_market' => 'floorplan_rent_market',
            'rentpress_custom_field_floorplan_rent_term' => 'floorplan_rent_term',
            'rentpress_custom_field_floorplan_rent_effective' => 'floorplan_rent_effective',
            'rentpress_custom_field_floorplan_rent_best' => 'floorplan_rent_best',
            'rentpress_custom_field_floorplan_rent_type_selection' => 'floorplan_rent_type_selection',
            'rentpress_custom_field_floorplan_rent_type_selection_cost' => 'floorplan_rent_type_selection_cost',
        ],
        // No meta exists for this feed data, Encasa Feed uses most of these
        // 'property_staff_description'
        // 'property_fax'
        // 'property_map_pdf'
        // 'property_office_hours'
        // 'property_timezone'
        // 'property_tour_url'
        // 'property_staff'
        // 'property_images'
        // 'property_rooms'
        // 'property_rankings'
        // 'property_ratings'
        // 'property_fees'
        // 'property_matterport_url'
        // 'property_community_matterports'
        // 'property_features'
        // 'property_community_amenities'
        // 'property_awards'
        // 'property_videos'
        // 'property_structure_type'
        // 'property_active'
        // 'property_ils_tracking_codes'
        // 'property_floorplan_count'

        // TODO: Make database columns for following meta data
        // 'rentpress_custom_field_property_special_link' => '',
        // 'rentpress_custom_field_property_facebook_url' => '',
        // 'rentpress_custom_field_property_twitter_url' => '',
        // 'rentpress_custom_field_property_instagram_url' => '',
        // 'rentpress_custom_field_property_reviews_shortcode' => '',

        // TODO: Encasa @Charles Make meta for the following feed data
        // 'floorplan_offices'
        // 'floorplan_videos'
        // TODO: 7.1 @Charles Make meta for the following feed data
        // 'floorplan_pdf'
        // 'floorplan_deposit_min'
        // 'floorplan_deposit_max'

    ];
}
