<?php

function rentpress_shortcode_vue_scripts($tracking_id = null, $use_mapbox = false)
{
    if (!empty($tracking_id)) {
        wp_enqueue_script('google_analytics', "https://www.googletagmanager.com/gtag/js?id=$tracking_id#asyncload", '', '', true);
    }

    if ($use_mapbox) {
        wp_enqueue_script('rentpress_shortcode_js', RENTPRESS_PLUGIN_VUE_MAPBOX_DIST . 'app.js', [], '1.0.0', true);
        wp_enqueue_style('rentpress_shortcode_css', RENTPRESS_PLUGIN_VUE_MAPBOX_DIST . 'app.css');
    } else {
        wp_enqueue_script('rentpress_shortcode_js', RENTPRESS_PLUGIN_VUE_MAIN_DIST . 'app.js', [], '1.0.0', true);
        wp_enqueue_style('rentpress_shortcode_css', RENTPRESS_PLUGIN_VUE_MAIN_DIST . 'app.css');
    }
}

// this function runs when scripts are being enqueued, any script that needs to be async just needs to be appended with '#asyncload'
function rentpress_async_scripts($url)
{
    if (strpos($url, '#asyncload') === false) {
        return $url;
    } else if (is_admin()) {
        return str_replace('#asyncload', '', $url);
    } else {
        return str_replace('#asyncload', '', $url) . "' async='async";
    }
}
add_filter('clean_url', 'rentpress_async_scripts', 11, 1);

/************************************
 *
 *  RentPress Single Floorplan Page
 *
 ************************************/
add_shortcode('rentpress_single_floorplan', 'rentpress_single_floorplan_shortcode_cb');
function rentpress_single_floorplan_shortcode_cb($atts = [], $content = '')
{
    $options = get_option('rentpress_options');
    $options["site_url"] = get_bloginfo("url");
    $options["site_name"] = get_bloginfo("name");
    rentpress_shortcode_vue_scripts($options["rentpress_google_analytics_api_section_tracking_id"]);

    // get data for floorplan and child units
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $floorplan = null;
    if (isset($atts['id'])) {
        $floorplan = rentpress_getAllFloorplanDataWithCodeOrPostID($atts['id']);
    } elseif (isset($atts['code'])) {
        $floorplan = rentpress_getAllFloorplanDataWithCodeOrPostID($atts['code']);
    } else {
        return "Shortcode Requires a valid floorplan code or floorplan post id";
    }

    $formShortcodeHTML = '';
    $hasFormShortcode = !is_null($floorplan->floorplan_parent_property_contact_type) && isset($floorplan->floorplan_parent_property_contact_type) ? $floorplan->floorplan_parent_property_contact_type == "3" : '';
    if ($hasFormShortcode) {
        $shortcode = $floorplan->floorplan_parent_property_gravity_form;
        $shortcode = str_replace("[","",$shortcode);
        $shortcode = str_replace("]","",$shortcode);
        $shortcode = '[' . $shortcode . ' field_values="property_code='. $floorplan->floorplan_parent_property_code .'&floorplan_code=floorplan_code"]';

        $formShortcodeHTML = '<div id="'. $floorplan->floorplan_parent_property_code .'_form_wrapper">' . do_shortcode($shortcode) . '</div>';
    }

    $similar_floorplans = rentpress_getSimilarFloorplans($floorplan);
    if (is_string($similar_floorplans)) {
        $similar_floorplans = '';
    } else {
        $similar_floorplans = htmlspecialchars(json_encode($similar_floorplans), ENT_QUOTES, 'UTF-8');
    }

    $floorplan = htmlspecialchars(json_encode($floorplan), ENT_QUOTES, 'UTF-8');
    $options = json_encode($options);

    $content = $content . "
        <div id='rentpress-app' data-floorplan='$floorplan' data-floorplans='$similar_floorplans' data-options='$options' data-shortcode='single-floorplan-page'></div><div id='floorplan-search-form-shortcodes' style='display: none;'>$formShortcodeHTML</div>
    ";

    return $content;
}

/************************************
 *
 *  RentPress Single Property Page
 *
 ************************************/
add_shortcode('rentpress_single_property', 'rentpress_single_property_shortcode_cb');
function rentpress_single_property_shortcode_cb($atts = [], $content = '')
{
    $options = get_option('rentpress_options');
    $options["site_name"] = get_bloginfo("name");
    $options["site_url"] = get_bloginfo("url");
    $options["default_sort"] = isset($atts['default_sort']) ? $atts['default_sort'] : "AVAIL";
    $attributes_string = strtoupper(json_encode($atts));

    // Is the shortcode using mapbox, or is it overridden from default settings
    $use_mapbox = isset($options['rentpress_map_source_api_section_selection']) && $options['rentpress_map_source_api_section_selection'] == "Mapbox";
    if (strpos($attributes_string, 'MAPBOX')) {
        $use_mapbox = true;
    }
    if (strpos($attributes_string, 'GOOGLEMAPS')) {
        $use_mapbox = false;
    }
    rentpress_shortcode_vue_scripts($options["rentpress_google_analytics_api_section_tracking_id"], $use_mapbox);

    // TODO: 7.2 @Ryan figure out a way to insert Gravity forms

    // get data for property
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $property = null;
    if (isset($atts['id']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_post_id != $atts['id'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['id']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (isset($atts['code']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_code != $atts['code'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['code']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (!empty($GLOBALS['rentpress_property_data'])) {
        $GLOBALS['rentpress_property_data'] = $property;
        $property = $GLOBALS['rentpress_property_data'];
    } else {
        return "Shortcode Requires a valid property code or property post id";
    }

    $formShortcodeHTML = '';
    $hasFormShortcode = !is_null($property->property_contact_type) && isset($property->property_contact_type) ? $property->property_contact_type == "3" : '';

    if ($hasFormShortcode) {
        $shortcode = $property->property_gravity_form;
        $shortcode = str_replace("[","",$shortcode);
        $shortcode = str_replace("]","",$shortcode);
        $shortcode = '[' . $shortcode . ' field_values="property_code='. $property->property_code .'&floorplan_code=floorplan_code"]';

        $formShortcodeHTML = '<div id="'. $property->property_code .'_form_wrapper">' . do_shortcode($shortcode) . '</div>';
    }

    $nearby_properties = rentpress_getNearbyProperties($property->property_city, 3, $property->property_code);
    if (is_string($nearby_properties)) {
        $nearby_properties = '';
    } else {
        $nearby_properties = htmlspecialchars(json_encode($nearby_properties), ENT_QUOTES, 'UTF-8');
    }

    $gallery_shortcode_html = '';
    $has_gallery_shortcode = 'false';
    if (isset($property->property_gallery_shortcode) && $property->property_gallery_shortcode != '') {
        $gallery_shortcode_html = do_shortcode($property->property_gallery_shortcode);
        $has_gallery_shortcode = 'true';
    }

    $hide_neighborhood = 'false';
    $neighborhood_meta = '';
    $city_meta = '';
    if (isset($atts['hide_neighborhood']) && $atts['hide_neighborhood'] == 'true') {
        $hide_neighborhood = 'true';
    } elseif (isset($atts['neighborhood_id']) || isset($property->property_neighborhood_post_id)) {
        $neighborhood_id = $atts['neighborhood_id'] ?? $property->property_neighborhood_post_id;
        $neighborhood_meta = get_post_meta($neighborhood_id);
        $neighborhood_post = get_post($neighborhood_id);
        $neighborhood_meta['post']['post_title'] = $neighborhood_post->post_title;
        $neighborhood_meta['post']['guid'] = $neighborhood_post->guid;
        $neighborhood_image_xml = simplexml_load_string(get_the_post_thumbnail($neighborhood_id, 'full'));
        if ($neighborhood_image_xml) {
            $neighborhood_meta['image_src'] = strval($neighborhood_image_xml['src']);
            $neighborhood_meta['image_srcset'] = strval($neighborhood_image_xml['srcset']);
        }
        $neighborhood_meta = htmlspecialchars(json_encode($neighborhood_meta), ENT_QUOTES, 'UTF-8');
    } else {
        $city_term = get_term_by('name', $property->property_city, 'city');
        $city_meta = get_term_meta($city_term->term_id);
        $city_meta['post']['post_title'] = $city_term->name;
        $city_meta['post']['guid'] = get_term_link($city_term);
        $city_meta['image_src'] = !empty($city_meta["rentpress_custom_field_city_image"][0]) ? $city_meta["rentpress_custom_field_city_image"][0] : '';
        $city_meta['image_srcset'] = '';
        $city_meta['rentpress_custom_field_neighborhood_romance_copy'] = !empty($city_meta['rentpress_custom_field_city_short_description'][0]) ? $city_meta['rentpress_custom_field_city_short_description'] : '';
        $city_meta = htmlspecialchars(json_encode($city_meta), ENT_QUOTES, 'UTF-8');
    }

    $use_modals = 'true';
    if (strpos($attributes_string, 'USEPOSTPAGE')) {
        $use_modals = 'false';
    }

    // a lot of special characters in property descriptions
    $property = htmlspecialchars(json_encode($property), ENT_QUOTES, 'UTF-8');
    $options = json_encode($options);

    $content = $content . "
        <div
            id='rentpress-app'
            data-shortcode='single-property-page'
            data-property='$property'
            data-properties='$nearby_properties'
            data-options='$options'
            data-hide_neighborhood='$hide_neighborhood'
            data-neighborhood='$neighborhood_meta'
            data-city='$city_meta'
            data-has_gallery_shortcode='$has_gallery_shortcode'
            data-use_mapbox='$use_mapbox'
            data-use_modals='$use_modals'
        ></div>
        <div id='floorplan-search-form-shortcodes' style='display: none;'>$formShortcodeHTML</div>
        $gallery_shortcode_html
    ";
    return $content;
}

/************************************
 *
 *  RentPress Floorplan Search
 *
 ************************************/
add_shortcode('rentpress_floorplan_search', 'rentpress_floorplan_search_shortcode_cb');
function rentpress_floorplan_search_shortcode_cb($atts = [], $content = '')
{
    $options = get_option('rentpress_options');
    $options["site_url"] = get_bloginfo("url");
    $options["site_name"] = get_bloginfo("name");
    $options["default_sort"] = isset($atts['default_sort']) ? $atts['default_sort'] : "AVAIL";
    rentpress_shortcode_vue_scripts($options["rentpress_google_analytics_api_section_tracking_id"]);

    // TODO: 7.1 @Charles add features search to shortcode
    // TODO: 7.1 @Charles add ability to search for specific bed count
    // TODO: 7.1 @Charles add ability to search for specific price max
    // TODO: 7.1 @Charles add ability to search for specific price min
    // TODO: 7.1 @Charles add ability to search for specials
    // TODO: 7.1 @Charles add ability to search for parent property taxonomy term

    // TODO: 7.1 @Charles add ability to limit floorplans
    // TODO: 7.1 @Charles add an 'order by' attribute is people want to limit by something other than most available

    // get all floorplans that match taxonomy and term criteria
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $attributes_string = strtoupper(json_encode($atts));

    if (isset($atts['codes'])) {
        $floorplans_info = explode(',', $atts['codes']);
        $floorplans = rentpress_getFloorplansAndUnitsWithCodesOrPostIDs($floorplans_info);
    } elseif (isset($atts['post_ids'])) {
        $floorplans_info = explode(',', $atts['post_ids']);
        $floorplans = rentpress_getFloorplansAndUnitsWithCodesOrPostIDs($floorplans_info);
    } elseif (isset($atts['property_codes'])) {
        $floorplans_info = explode(',', $atts['property_codes']);
        $floorplans = rentpress_getFloorplansAndUnitsWithParentPropertyCodesOrPostIDs($floorplans_info);
    } elseif (isset($atts['property_post_ids'])) {
        $floorplans_info = explode(',', $atts['property_post_ids']);
        $floorplans = rentpress_getFloorplansAndUnitsWithParentPropertyCodesOrPostIDs($floorplans_info);
    } else {
        $floorplans = rentpress_getAllFloorplansAndUnits();
    }

    $formShortcodes = [];
    $formShortcodesHTML = [];
    foreach ($floorplans as $floorplan) {
        $parent_property_contact_type = isset($floorplan->floorplan_parent_property_contact_type) ? $floorplan->floorplan_parent_property_contact_type : null;
        $hasFormShortcode = !is_null($parent_property_contact_type) && isset($parent_property_contact_type) ? $parent_property_contact_type == "3" : '';
        if ($hasFormShortcode) {
            if (!in_array($floorplan->floorplan_parent_property_gravity_form, $formShortcodes)) {

                $shortcode = $floorplan->floorplan_parent_property_gravity_form;
                $shortcode = str_replace("[","",$shortcode);
                $shortcode = str_replace("]","",$shortcode);
                $shortcode = '[' . $shortcode . ' field_values="property_code='. $floorplan->floorplan_parent_property_code .'&floorplan_code=floorplan_code"]';

                $formShortcodesHTML[] = '<div id="'. $floorplan->floorplan_parent_property_code .'_form_wrapper">' . do_shortcode($shortcode) . '</div>';
                $formShortcodes[$floorplan->floorplan_parent_property_code] = $floorplan->floorplan_parent_property_gravity_form;
            }
        }
    }

    if (is_countable($formShortcodesHTML) ? count($formShortcodesHTML) : '') {
        $formShortcodesHTML = implode($formShortcodesHTML);
        $options['floorplan_forms'] = $formShortcodes;
    }

    if (count($floorplans) == 0) {
        return "No floorplans found with given information";
    }

    // TODO: simplify this by just adding them to the options object
    $hidefilters = 'false';
    if (strpos($attributes_string, 'HIDEFILTERS')) {
        $hidefilters = 'true';
    }

    // TODO: simplify this by just adding them to the options object
    $sidebarfilters = 'false';
    if (strpos($attributes_string, 'SIDEBARFILTERS')) {
        $sidebarfilters = 'true';
    }

    // TODO: simplify this by just adding them to the options object
    $use_modals = 'false';
    if (strpos($attributes_string, 'USEMODALS')) {
        $use_modals = 'true';
    }

    // a lot of special characters in floorplans descriptions
    $floorplans = htmlspecialchars(json_encode($floorplans), ENT_QUOTES, 'UTF-8');
    $options = json_encode($options);

    $content = $content . "
        <div id='rentpress-app' data-floorplans='$floorplans' data-options='$options' data-shortcode='floorplan-search' data-hidefilters='$hidefilters' data-sidebarfilters='$sidebarfilters' data-use_modals='$use_modals'/></div><div id='floorplan-search-form-shortcodes' style='display: none;'>$formShortcodesHTML</div>
    ";
    return $content;
}

/************************************
 *
 *  RentPress Properties Search
 *
 ************************************/
add_shortcode('rentpress_property_search', 'rentpress_property_search_shortcode_cb');
function rentpress_property_search_shortcode_cb($atts = [], $content = '')
{
    $options = get_option('rentpress_options');
    $options["site_url"] = get_bloginfo("url");
    $options["site_name"] = get_bloginfo("name");
    $attributes_string = strtoupper(json_encode($atts));

    // Is the shortcode using mapbox, or is it overridden from default settings
    $use_mapbox = isset($options['rentpress_map_source_api_section_selection']) && $options['rentpress_map_source_api_section_selection'] == "Mapbox";
    if (strpos($attributes_string, 'MAPBOX')) {
        $use_mapbox = true;
    }
    if (strpos($attributes_string, 'GOOGLEMAPS')) {
        $use_mapbox = false;
    }
    rentpress_shortcode_vue_scripts($options["rentpress_google_analytics_api_section_tracking_id"], $use_mapbox);

    // TODO: 7.1 @Charles add ability to search for specials
    // TODO: 7.1 @Charles add an 'order by' attribute is people want to limit by something other than most available
    // TODO: 7.2 @Charles allow shortcode attribute to select default sort

    // get all properties that match taxonomy and term criteria
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    if (isset($atts['codes']) && isset($atts['post_ids'])) {
        $codes = explode(',', $atts['codes']);
        $post_ids = explode(',', $atts['post_ids']);
        foreach ($post_ids as $id) {
            if (strlen($id) > 0) {
                array_push($codes, $id);
            }
        }
        $properties = rentpress_getAllPropertiesWithCodesOrIDs($codes);
    } elseif (isset($atts['codes'])) {
        $codes = explode(',', $atts['codes']);
        $properties = rentpress_getAllPropertiesWithCodesOrIDs($codes);
    } elseif (isset($atts['post_ids'])) {
        $post_ids = explode(',', $atts['post_ids']);
        $properties = rentpress_getAllPropertiesWithCodesOrIDs($post_ids);
    } elseif (isset($atts['terms']) && isset($atts['city'])) {
        $terms = explode('|', $atts['terms']);
        $limit = isset($atts['limit']) ? $atts['limit'] : '';
        $properties = rentpress_getCityPropertiesWithTaxonomies($atts['city'], $terms, $limit);
    } elseif (isset($atts['terms']) && isset($atts['neighborhood'])) {
        $terms = explode('|', $atts['terms']);
        $limit = isset($atts['limit']) ? $atts['limit'] : '';
        $properties = rentpress_getNeighborhoodPropertiesWithTaxonomies($atts['neighborhood'], $terms, $limit);
    } elseif (isset($atts['terms'])) {
        $terms = explode('|', $atts['terms']);
        $properties = rentpress_getAllPropertiesForTaxonomies($terms);
    } elseif (isset($atts['city'])) {
        $limit = isset($atts['limit']) ? $atts['limit'] : '';
        $properties = rentpress_getNearbyProperties($atts['city'], $limit);
    } elseif (isset($atts['neighborhood'])) {
        $limit = isset($atts['limit']) ? $atts['limit'] : '';
        $properties = rentpress_getNeighborhoodProperties($atts['neighborhood'], $limit);
    } else {
        $properties = rentpress_getAllProperties();
    }

    $hidefilters = 'false';
    if (strpos($attributes_string, 'HIDEFILTERS')) {
        $hidefilters = 'true';
    }

    $showmap = 'false';
    if (strpos($attributes_string, 'SHOWMAP')) {
        $showmap = 'true';
    }

    $options['show_matrix'] = false;
    if (strpos($attributes_string, 'SHOWMATRIX')) {
        $options['show_matrix'] = true;
    }

    $options['requested_beds'] = "";
    if (isset($atts['bed'])) {
        $options['requested_beds'] = $atts['bed'];
    }

    $options['max_price'] = "";
    if (isset($atts['max_price'])) {
        $options['max_price'] = $atts['max_price'];
    }

    $options['only_available'] = false;
    if (strpos($attributes_string, 'ONLYAVAILABLE')) {
        $options['only_available'] = true;
    }

    $featured_search_terms = new stdClass();
    if (isset($atts['pets'])) {
        $featured_search_terms->pets = explode('|', $atts['pets']);
    }
    if (isset($atts['property_type'])) {
        $featured_search_terms->propertyType = explode('|', $atts['property_type']);
    }
    if (isset($atts['featured_amenities'])) {
        $featured_search_terms->featuredAmenities = explode('|', $atts['featured_amenities']);
    }
    $featured_search_terms = htmlspecialchars(json_encode($featured_search_terms), ENT_QUOTES, 'UTF-8');

    // a lot of special characters in properties descriptions
    $properties = htmlspecialchars(json_encode($properties), ENT_QUOTES, 'UTF-8');
    $options = json_encode($options);

    $content = $content . "
        <div id='rentpress-app'
            data-properties='$properties'
            data-options='$options'
            data-hidefilters='$hidefilters'
            data-usemap='$showmap'
            data-use_mapbox='$use_mapbox'
            data-featured_search_terms='$featured_search_terms'
            data-shortcode='property-search'/></div>
    ";
    return $content;
}

/************************************
 *
 *  RentPress Property Misc Shortcodes
 *
 ************************************/
add_shortcode('rentpress_property_hours', 'rentpress_property_hours_shortcode_cb');
function rentpress_property_hours_shortcode_cb($atts = [], $content = '')
{
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $property = null;
    if (isset($atts['id']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_post_id != $atts['id'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['id']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (isset($atts['code']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_code != $atts['code'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['code']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (!empty($GLOBALS['rentpress_property_data'])) {
        $property = $GLOBALS['rentpress_property_data'];
    } else {
        return "Shortcode Requires a valid property code or property post id";
    }

    $officeHourHTMLString = "<div class='rentpress-property-hours-shortcode-wrapper'><ul>";
    $officeHours = !empty($property->property_office_hours) ? json_decode($property->property_office_hours) : array();
    foreach ($officeHours as $day => $dayHours) {
        $officeHourHTMLString .= "<li>$day: $dayHours->openTime - $dayHours->closeTime</li>";
    }
    $officeHourHTMLString .= "</ul></div>";

    return $content . $officeHourHTMLString;
}

add_shortcode('rentpress_property_address', 'rentpress_property_address_shortcode_cb');
function rentpress_property_address_shortcode_cb($atts = [], $content = '')
{
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $property = null;
    if (isset($atts['id']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_post_id != $atts['id'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['id']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (isset($atts['code']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_code != $atts['code'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['code']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (!empty($GLOBALS['rentpress_property_data'])) {
        $property = $GLOBALS['rentpress_property_data'];
    } else {
        return "Shortcode Requires a valid property code or property post id";
    }

    return $content . "<div>" . $property->property_address . "<br>" . $property->property_city . ", " . $property->property_state . " " . $property->property_zip . "</div>";

}

add_shortcode('rentpress_property_phone', 'rentpress_property_phone_shortcode_cb');
function rentpress_property_phone_shortcode_cb($atts = [], $content = '')
{
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $property = null;
    if (isset($atts['id']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_post_id != $atts['id'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['id']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (isset($atts['code']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_code != $atts['code'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['code']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (!empty($GLOBALS['rentpress_property_data'])) {
        $property = $GLOBALS['rentpress_property_data'];
    } else {
        return "Shortcode Requires a valid property code or property post id";
    }

    return $content . "<div>" . $property->property_phone_number . "</div>";

}

add_shortcode('rentpress_property_social', 'rentpress_property_social_shortcode_cb');
function rentpress_property_social_shortcode_cb($atts = [], $content = '')
{
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $property = null;
    if (isset($atts['id']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_post_id != $atts['id'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['id']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (isset($atts['code']) && (empty($GLOBALS['rentpress_property_data']) || $GLOBALS['rentpress_property_data']->property_code != $atts['code'])) {
        $property = rentpress_getAllPropertyDataWithCodeOrPostID($atts['code']);
        $GLOBALS['rentpress_property_data'] = $property;
    } elseif (!empty($GLOBALS['rentpress_property_data'])) {
        $property = $GLOBALS['rentpress_property_data'];
    } else {
        return "Shortcode Requires a valid property code or property post id";
    }

    $options = get_option('rentpress_options');
    $fillColor = !empty($options['rentpress_accent_color_section_input']) ? $options['rentpress_accent_color_section_input'] : 'black';
    $attributes_string = strtoupper(json_encode($atts));
    if (strpos($attributes_string, 'SECONDARY')) {
        $fillColor = !empty($options['rentpress_secondary_accent_color_section_input']) ? $options['rentpress_secondary_accent_color_section_input'] : 'black';
    } elseif (isset($atts['color'])) {
        $fillColor = $atts['color'];
    }

    $size = '2em';
    if (isset($atts['size'])) {
        $size = $atts['size'];
    }

    $socialString = "<div class='rentpress-property-social-shortcode-wrapper'><div style='display:flex;justify-content:space-evenly;'>";
    if (!empty($property->property_facebook_link)) {
        $socialString .= "<a href='$property->property_facebook_link'><svg xmlns='http://www.w3.org/2000/svg' width='$size' height='$size' viewBox='0 0 24 24' fill='$fillColor'><path d='M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-3 7h-1.924c-.615 0-1.076.252-1.076.889v1.111h3l-.238 3h-2.762v8h-3v-8h-2v-3h2v-1.923c0-2.022 1.064-3.077 3.461-3.077h2.539v3z'/></svg></a>";
    }
    if (!empty($property->property_twitter_link)) {
        $socialString .= "<a href='$property->property_twitter_link'><svg xmlns='http://www.w3.org/2000/svg' width='$size' height='$size' viewBox='0 0 24 24' fill='$fillColor'><path d='M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-.139 9.237c.209 4.617-3.234 9.765-9.33 9.765-1.854 0-3.579-.543-5.032-1.475 1.742.205 3.48-.278 4.86-1.359-1.437-.027-2.649-.976-3.066-2.28.515.098 1.021.069 1.482-.056-1.579-.317-2.668-1.739-2.633-3.26.442.246.949.394 1.486.411-1.461-.977-1.875-2.907-1.016-4.383 1.619 1.986 4.038 3.293 6.766 3.43-.479-2.053 1.08-4.03 3.199-4.03.943 0 1.797.398 2.395 1.037.748-.147 1.451-.42 2.086-.796-.246.767-.766 1.41-1.443 1.816.664-.08 1.297-.256 1.885-.517-.439.656-.996 1.234-1.639 1.697z'/></svg></a>";
    }
    if (!empty($property->property_instagram_link)) {
        $socialString .= "<a href='$property->property_instagram_link'><svg xmlns='http://www.w3.org/2000/svg' width='$size' height='$size' viewBox='0 0 24 24' fill='$fillColor'><path d='M15.233 5.488c-.843-.038-1.097-.046-3.233-.046s-2.389.008-3.232.046c-2.17.099-3.181 1.127-3.279 3.279-.039.844-.048 1.097-.048 3.233s.009 2.389.047 3.233c.099 2.148 1.106 3.18 3.279 3.279.843.038 1.097.047 3.233.047 2.137 0 2.39-.008 3.233-.046 2.17-.099 3.18-1.129 3.279-3.279.038-.844.046-1.097.046-3.233s-.008-2.389-.046-3.232c-.099-2.153-1.111-3.182-3.279-3.281zm-3.233 10.62c-2.269 0-4.108-1.839-4.108-4.108 0-2.269 1.84-4.108 4.108-4.108s4.108 1.839 4.108 4.108c0 2.269-1.839 4.108-4.108 4.108zm4.271-7.418c-.53 0-.96-.43-.96-.96s.43-.96.96-.96.96.43.96.96-.43.96-.96.96zm-1.604 3.31c0 1.473-1.194 2.667-2.667 2.667s-2.667-1.194-2.667-2.667c0-1.473 1.194-2.667 2.667-2.667s2.667 1.194 2.667 2.667zm4.333-12h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm.952 15.298c-.132 2.909-1.751 4.521-4.653 4.654-.854.039-1.126.048-3.299.048s-2.444-.009-3.298-.048c-2.908-.133-4.52-1.748-4.654-4.654-.039-.853-.048-1.125-.048-3.298 0-2.172.009-2.445.048-3.298.134-2.908 1.748-4.521 4.654-4.653.854-.04 1.125-.049 3.298-.049s2.445.009 3.299.048c2.908.133 4.523 1.751 4.653 4.653.039.854.048 1.127.048 3.299 0 2.173-.009 2.445-.048 3.298z'/></svg></a>";
    }

    return $content . $socialString . "</div></div>";

}

/************************************
 *
 *  RentPress Misc Shortcodes
 *
 ************************************/
add_shortcode('rentpress_equal_housing', 'rentpress_equal_housing_shortcode_cb');
function rentpress_equal_housing_shortcode_cb($atts = [], $content = '')
{
    $options = get_option('rentpress_options');
    $fillColor = !empty($options['rentpress_accent_color_section_input']) ? $options['rentpress_accent_color_section_input'] : 'black';
    $attributes_string = strtoupper(json_encode($atts));
    if (strpos($attributes_string, 'SECONDARY')) {
        $fillColor = !empty($options['rentpress_secondary_accent_color_section_input']) ? $options['rentpress_secondary_accent_color_section_input'] : 'black';
    } elseif (isset($atts['color'])) {
        $fillColor = $atts['color'];
    }

    $size = '10em';
    if (isset($atts['size'])) {
        $size = $atts['size'];
    }

    $svgString = "<svg xmlns='http://www.w3.org/2000/svg' width='$size' height='$size' viewBox='0 0 192.756 192.756'><g fill-rule='evenodd' clip-rule='evenodd'><path fill='$fillColor' d='M26.473 148.555h-7.099v2.81h6.52v2.373h-6.52v3.453h7.414v2.375H16.636v-13.378h9.837v2.367zM35.45 155.928l1.342 1.264a3.247 3.247 0 0 1-1.509.357c-1.51 0-3.635-.93-3.635-4.674s2.125-4.674 3.635-4.674c1.509 0 3.632.93 3.632 4.674 0 1.254-.242 2.18-.614 2.873l-1.416-1.322-1.435 1.502zm6.317 3.09l-1.457-1.371c.82-1.045 1.4-2.572 1.4-4.771 0-6.277-4.658-7.039-6.428-7.039-1.769 0-6.425.762-6.425 7.039 0 6.281 4.656 7.041 6.425 7.041.78 0 2.16-.146 3.427-.898l1.586 1.514 1.472-1.515zM54.863 154.889c0 3.516-2.127 5.027-5.499 5.027-1.228 0-3.054-.297-4.246-1.619-.726-.814-1.006-1.904-1.042-3.242v-8.867h2.85v8.678c0 1.869 1.08 2.684 2.382 2.684 1.921 0 2.701-.93 2.701-2.551v-8.811h2.855v8.701h-.001zM62.348 149.207h.041l1.655 5.291H60.63l1.718-5.291zm-2.464 7.594h4.939l.858 2.766h3.037l-4.71-13.379h-3.225l-4.769 13.379h2.943l.927-2.766zM73.692 157.145h6.65v2.421h-9.448v-13.378h2.798v10.957zM90.938 153.562v6.004h-2.79v-13.378h2.79v5.066h5.218v-5.066h2.791v13.378h-2.791v-6.004h-5.218zM104.273 152.875c0-3.744 2.127-4.674 3.631-4.674 1.512 0 3.637.93 3.637 4.674s-2.125 4.674-3.637 4.674c-1.504 0-3.631-.93-3.631-4.674zm-2.791 0c0 6.281 4.66 7.041 6.422 7.041 1.777 0 6.432-.76 6.432-7.041 0-6.277-4.654-7.039-6.432-7.039-1.761 0-6.422.762-6.422 7.039zM127.676 154.889c0 3.516-2.127 5.027-5.5 5.027-1.23 0-3.051-.297-4.248-1.619-.725-.814-1.006-1.904-1.039-3.242v-8.867h2.846v8.678c0 1.869 1.084 2.684 2.391 2.684 1.918 0 2.699-.93 2.699-2.551v-8.811h2.852v8.701h-.001zM132.789 155.445c.025.744.4 2.162 2.838 2.162 1.32 0 2.795-.316 2.795-1.736 0-1.039-1.006-1.322-2.42-1.656l-1.436-.336c-2.168-.502-4.252-.98-4.252-3.924 0-1.492.807-4.119 5.145-4.119 4.102 0 5.199 2.68 5.219 4.32h-2.686c-.072-.592-.297-2.012-2.738-2.012-1.059 0-2.326.391-2.326 1.602 0 1.049.857 1.264 1.41 1.395l3.264.801c1.826.449 3.5 1.195 3.5 3.596 0 4.029-4.096 4.379-5.271 4.379-4.877 0-5.715-2.814-5.715-4.471h2.673v-.001zM146.186 159.566H143.4v-13.378h2.786v13.378zM157.35 146.188h2.605v13.378h-2.791l-5.455-9.543h-.047v9.543h-2.605v-13.378H152l5.303 9.316h.047v-9.316zM169.307 152.355h5.584v7.211h-1.859l-.279-1.676c-.707.812-1.732 2.025-4.174 2.025-3.221 0-6.143-2.309-6.143-7.002 0-3.648 2.031-7.098 6.533-7.078 4.105 0 5.727 2.66 5.867 4.512h-2.791c0-.523-.953-2.203-2.924-2.203-1.998 0-3.84 1.377-3.84 4.803 0 3.654 1.994 4.602 3.893 4.602.615 0 2.67-.238 3.242-2.943h-3.109v-2.251zM18.836 173.197c0-3.744 2.123-4.678 3.63-4.678 1.509 0 3.631.934 3.631 4.678 0 3.742-2.122 4.68-3.631 4.68-1.507 0-3.63-.938-3.63-4.68zm-2.794 0c0 6.275 4.656 7.049 6.425 7.049 1.77 0 6.426-.773 6.426-7.049s-4.657-7.039-6.426-7.039c-1.769 0-6.425.764-6.425 7.039zM36.549 172.748v-3.934h2.217c1.731 0 2.459.545 2.459 1.85 0 .596 0 2.084-2.088 2.084h-2.588zm0 2.314h3.202c3.597 0 4.265-3.059 4.265-4.268 0-2.625-1.561-4.285-4.153-4.285h-6.107v13.379h2.793v-4.826zM51.599 172.748v-3.934h2.213c1.733 0 2.46.545 2.46 1.85 0 .596 0 2.084-2.083 2.084h-2.59zm0 2.314h3.204c3.594 0 4.267-3.059 4.267-4.268 0-2.625-1.563-4.285-4.153-4.285h-6.113v13.379h2.795v-4.826zM66.057 173.197c0-3.744 2.118-4.678 3.633-4.678 1.502 0 3.63.934 3.63 4.678 0 3.742-2.127 4.68-3.63 4.68-1.515 0-3.633-.938-3.633-4.68zm-2.795 0c0 6.275 4.655 7.049 6.428 7.049 1.765 0 6.421-.773 6.421-7.049s-4.656-7.039-6.421-7.039c-1.773 0-6.428.764-6.428 7.039zM83.717 172.396v-3.582h3.479c1.64 0 1.954 1.049 1.954 1.756 0 1.324-.705 1.826-2.159 1.826h-3.274zm-2.746 7.493h2.746v-5.236h2.882c2.07 0 2.184.705 2.184 2.531 0 1.375.105 2.064.292 2.705h3.095v-.361c-.596-.221-.596-.707-.596-2.656 0-2.504-.596-2.91-1.694-3.396 1.322-.443 2.064-1.713 2.064-3.182 0-1.158-.648-3.783-4.207-3.783H80.97v13.378h.001zM102.355 179.889h-2.793v-11.012h-4.04v-2.367H106.4v2.367h-4.045v11.012zM121.395 175.207c0 3.52-2.123 5.039-5.498 5.039-1.223 0-3.049-.311-4.244-1.631-.727-.816-1.006-1.898-1.039-3.238v-8.867h2.846v8.678c0 1.863 1.082 2.689 2.385 2.689 1.918 0 2.699-.938 2.699-2.557v-8.811h2.852v8.698h-.001zM134.916 166.51h2.613v13.379h-2.8l-5.459-9.543h-.03v9.543h-2.613V166.51h2.943l5.313 9.312h.033v-9.312zM145.412 179.889h-2.803V166.51h2.803v13.379zM156.32 179.889h-2.793v-11.012h-4.035v-2.367h10.873v2.367h-4.045v11.012zM170.928 179.889h-2.799v-5.051l-4.615-8.328h3.295l2.775 5.814 2.652-5.814h3.162l-4.47 8.361v5.018zM95.706 6.842L5.645 51.199v20.836h10.08v62.502h159.284V72.035h12.104V51.199L95.706 6.842zm59.815 108.871H35.216V58.592l60.49-30.914 59.816 30.914v57.121h-.001z'/><path fill='$fillColor' d='M123.256 78.75H67.479V58.592h55.777V78.75zM123.256 107.662H67.479V87.491h55.777v20.171z'/></g></svg>";

    return $content . "<div class='rentpress-equal-housing-shortcode-wrapper'>$svgString</div>";

}