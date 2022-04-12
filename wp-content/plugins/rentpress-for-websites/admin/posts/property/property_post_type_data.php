<?php

function rentpress_getAllMetaDataForAllPropertiesAndFloorplans()
{
    $all_property_meta = array();

    // Get all property meta
    $args = [
        'post_type' => 'rentpress_property',
        'post_status' => ['publish', 'pending', 'draft', 'private', 'trash'],
        'posts_per_page' => -1,
    ];
    $properties = get_posts($args);

    foreach ($properties as $property) {
        $property_meta = rentpress_setUpMetaForProperty($property);
        //push all data to array
        if (!is_null($property_meta)) {
            $all_property_meta[$property_meta['rentpress_custom_field_property_code'][0]] = $property_meta;
        }
    }

    // get all floorplan meta data
    $all_floorplan_meta = array();

    $args = [
        'post_type' => 'rentpress_floorplan',
        'post_status' => ['publish', 'pending', 'draft', 'private', 'trash'],
        'posts_per_page' => -1,
    ];
    $floorplans = get_posts($args);

    foreach ($floorplans as $floorplan) {
        $floorplan_meta = rentpress_setUpMetaForFloorplan($floorplan);

        // if the floorplan code is not already used, then add the floorplan to the arrays
        if (!is_null($floorplan_meta)) {
            $floorplan_code = $floorplan_meta['rentpress_custom_field_floorplan_code'][0];
            $property_code  = $floorplan_meta['rentpress_custom_field_floorplan_parent_property_code'][0];
            $floorplan_name = isset($floorplan_meta['rentpress_custom_field_floorplan_name'][0]) ? $floorplan_meta['rentpress_custom_field_floorplan_name'][0] : '';
            if (!isset($all_floorplan_meta[$floorplan_code])) {
                // set up all floorplans array so that wp only floorplans can be added to DB as well
                $all_floorplan_meta[$floorplan_code] = $floorplan_meta;
                // add floorplan codes and names to property
                $all_property_meta[$property_code]['floorplans'][$floorplan_code] = $floorplan_name;
            }
        }
    }

    $all_meta['all_properties'] = $all_property_meta;
    $all_meta['all_floorplans'] = $all_floorplan_meta;

    return $all_meta;
}

function rentpress_getAllMetaDataForPropertyAndFloorplansByPropertyId($property_post_id)
{
    //using arrays here for ease of use in resync functions
    $all_property_meta  = array();
    $all_floorplan_meta = array();

    $property      = get_post($property_post_id);
    $property_meta = rentpress_setUpMetaForProperty($property);
    $property_code = $property_meta['rentpress_custom_field_property_code'][0];
    //push all data to array
    if (!is_null($property_meta)) {
        $all_property_meta[$property_code] = $property_meta;
    }

    $args = array(
        'post_type' => 'rentpress_floorplan',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'rentpress_custom_field_floorplan_parent_property_code',
                'value' => $property_code,
            ),
        ),
    );
    $floorplans_query = new WP_Query($args);

    foreach ($floorplans_query->posts as $floorplan) {
        $floorplan_meta = rentpress_setUpMetaForFloorplan($floorplan);

        // if the floorplan code is not already used, then add the floorplan to the arrays
        if (!is_null($floorplan_meta)) {
            $floorplan_code = $floorplan_meta['rentpress_custom_field_floorplan_code'][0];
            $floorplan_name = isset($floorplan_meta['rentpress_custom_field_floorplan_name'][0]) ? $floorplan_meta['rentpress_custom_field_floorplan_name'][0] : '';
            $property_code  = $floorplan_meta['rentpress_custom_field_floorplan_parent_property_code'][0];
            if (!isset($all_floorplan_meta[$floorplan_code])) {
                // set up all floorplans array so that wp only floorplans can be added to DB as well
                $all_floorplan_meta[$floorplan_code] = $floorplan_meta;
                // add floorplan codes and names to property
                $all_property_meta[$property_code]['floorplans'][$floorplan_code] = $floorplan_name;
            }
        }
    }

    $all_meta['all_properties'] = $all_property_meta;
    $all_meta['all_floorplans'] = $all_floorplan_meta;

    return $all_meta;
}

function rentpress_getAllPropertyCodes()
{
    $all_property_codes = array();

    $args = [
        'post_type' => 'rentpress_property',
        'post_status' => ['publish', 'pending', 'draft', 'private'],
        'posts_per_page' => -1,
    ];
    $properties = get_posts($args);

    foreach ($properties as $property) {
        $property_meta = get_post_meta($property->ID);

        if (isset($property_meta['rentpress_custom_field_property_code'][0])) {
            $property_name = isset($property_meta['rentpress_custom_field_property_name'][0]) ? $property_meta['rentpress_custom_field_property_name'][0] : $property->post_title;

            $all_property_codes[$property_meta['rentpress_custom_field_property_code'][0]] = $property_name;
        }
    }

    return $all_property_codes;
}

function rentpress_getAllPropertyMetaByCode($property_code)
{
    $args = [
        'post_type'  => 'rentpress_property',
        'meta_query' => [
            [
                'key' => 'rentpress_custom_field_property_code',
                'value' => $property_code,
            ],
        ],

    ];
    $query = new WP_Query($args);
    return get_post_meta($query->posts[0]->ID);
}

// helper functions
function rentpress_isSpecialOutOfDate($special_date)
{
    $isOutOfDate = false;
    if (date('Y-m-d') > $special_date) {
        $isOutOfDate = true;
    }
    return $isOutOfDate;
}

function rentpress_setUpMetaForProperty($property)
{
    $property_meta = get_post_meta($property->ID);

    if (!empty($property_meta['rentpress_custom_field_property_code'][0])) {
        $property_meta['post_information'] = $property;

        $xmlEl = simplexml_load_string(get_the_post_thumbnail($property->ID, 'full'));
        if ($xmlEl) {
            $property_meta['rentpress_custom_field_property_featured_image_src']    = strval($xmlEl['src']);
            $property_meta['rentpress_custom_field_property_featured_image_srcset'] = strval($xmlEl['srcset']);
        }
        $property_meta['rentpress_custom_field_property_gallery_images']         = $property_meta['rentpress_custom_field_property_gallery_images'][0] ?? null;
        $property_meta['rentpress_custom_field_property_gallery_shortcode']      = $property_meta['rentpress_custom_field_property_gallery_shortcode'][0] ?? null;
        $property_meta['rentpress_custom_field_property_neighborhood_post_id']   = $property_meta['rentpress_custom_field_property_neighborhood_post_id'][0] ?? null;
        $property_meta['rentpress_custom_field_property_neighborhood_post_name'] = $property_meta['rentpress_custom_field_property_neighborhood_post_name'][0] ?? null;
        $property_meta['rentpress_custom_field_property_specific_gravity_form'] = $property_meta['rentpress_custom_field_property_specific_gravity_form'][0] ?? null;
        $property_meta['rentpress_custom_field_property_specific_contact_link'] = $property_meta['rentpress_custom_field_property_specific_contact_link'][0] ?? null;
        $property_meta['rentpress_custom_field_property_contact_type'] = $property_meta['rentpress_custom_field_property_contact_type'][0] ?? null;

        // remove special if it is not available anymore
        if (!empty($property_meta['rentpress_custom_field_property_special_text'][0]) &&
            !empty($property_meta['rentpress_custom_field_property_special_expiration'][0]) &&
            rentpress_isSpecialOutOfDate($property_meta['rentpress_custom_field_property_special_expiration'][0])) {
            $property_meta['rentpress_custom_field_property_special_text'][0] = null;
        }
        return $property_meta;
    }
    return null;
}

function rentpress_setUpMetaForFloorplan($floorplan)
{
    $floorplan_meta = get_post_meta($floorplan->ID);
    if (!empty($floorplan_meta['rentpress_custom_field_floorplan_code'][0])) {
        $floorplan_meta['post_information']                                          = $floorplan;
        $floorplan_meta['rentpress_custom_field_floorplan_featured_image']           = get_the_post_thumbnail_url($floorplan->ID, 'full');
        $floorplan_meta['rentpress_custom_field_floorplan_featured_image_thumbnail'] = get_the_post_thumbnail_url($floorplan->ID, 'medium');
        $floorplan_meta['rentpress_custom_field_floorplan_gallery_images']           = $floorplan_meta['rentpress_custom_field_floorplan_gallery_images'][0] ?? null;

        // remove special if it is not available anymore
        if (!empty($floorplan_meta['rentpress_custom_field_floorplan_special_text'][0]) &&
            !empty($floorplan_meta['rentpress_custom_field_floorplan_special_expiration'][0]) &&
            rentpress_isSpecialOutOfDate($floorplan_meta['rentpress_custom_field_floorplan_special_expiration'][0])) {
            $floorplan_meta['rentpress_custom_field_floorplan_special_text'][0] = null;
        }

        return $floorplan_meta;
    }
    return null;
}