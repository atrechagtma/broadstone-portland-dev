<?php

global $rentPress_Service;

/*
    Gather Rentpress Options and set up variables for the page
*/
//create object that holds the rentpress functions
$rentPressOptions = new rentPress_Options();
$globalPropImage = $rentPressOptions->getOption('rentPress_properties_default_featured_image');

//set up arguments for the floorplans post call to the db
$floorplans_query_args = array(
    'post_type' => 'floorplans',
    'post_status' => 'publish',
    'nopaging' => true,
);

/*
    Make calls to the database to set up all of the data for the page
*/
//set up variables for later use
$availability_type = $rentPressOptions->getOption('override_unit_visibility');
$show_waitlist = ($rentPressOptions->getOption('show_waitlist_ctas') != 'false') ? true : false ;
$today = strtotime( "+1 days", time());;
$lookahead = rp_archive_fp_getFutureTime($rentPressOptions);
$all_floorplans = array();
$show_content_top = ($rentPressOptions->getOption('archive_floorplan_content_position') == 'archive_floorplan_content_top') ? true : false ;

//call the database for all of the data that is needed
$data = get_posts($floorplans_query_args);
$all_units = $wpdb->get_results($wpdb->prepare( "SELECT * FROM `$wpdb->rp_units` WHERE `unit_id` != %s", 'null'));

//set up data variables so they can be transferred to js and more easily referenced if they are all together
$all_floorplans_data['data']['show_all_units'] = ($availability_type == 'unit_visibility_5') ? 'true' : 'false';
$all_floorplans_data['data']['show_waitlist'] = $show_waitlist;
$all_floorplans_data['data']['disable_pricing'] = $rentPressOptions->getOption('disable_pricing');
$all_floorplans_data['data']['disable_pricing_message'] = $rentPressOptions->getOption('disable_pricing_message');
$all_floorplans_data['data']['selected_sort'] = $rentPressOptions->getOption('archive_floorplans_default_sort');
$all_floorplans_data['data']['isShortcodeUsingPopup'] = $isShortcodeUsingPopup;
$rent_range = new stdClass();

//this value will change the view loaded based on if there is any pricing that should be shown
$hide_pricing = ($all_floorplans_data['data']['disable_pricing'] == 'true') ? "hidden" : '' ;
// if pricing is disabled, set the rent range manually
if ($all_floorplans_data['data']['disable_pricing'] == 'true') {
    $rent_range->min = 0;
    $rent_range->max = 1;
}

//loop through each flooplan post, set up the information as appropriate, and combine the units with their floorplans
foreach ($data as $floorplan_post) {
    /*
        Construct a floorplan object from the floorplan post type and meta data
    */

    $floorplanFeatures = wp_get_post_terms( $floorplan_post->ID, 'fp_features', array( 'fields' => 'names' ));

    //set up the attributes based on global rentpress and wordpress functions
    $floorplan_data  = get_post_meta($floorplan_post->ID);

    if (isset($attributes['property_code']) && $attributes['property_code'] != $floorplan_data['parent_property_code'][0]) {
        continue;
    }

    //ignore floor plan if option is selected and floor plan has no available units
    if ($rentPressOptions->getOption('rentPress_hide_floorplans_without_availability') == 'true' && $floorplan_data['fpUnitsCaptured'][0] == 0) {
        continue;
    }

    $rentpress_floorplan = $rentPress_Service['floorplans_meta']->setPostID($floorplan_post->ID);
    $floorplan_post->displayRent = $rentpress_floorplan->displayRentForTemplate();
    $floorplan_post->post_url = get_permalink($floorplan_post->ID);

    //combine the post with its meta data in a more usable way
    $floorplan_post->fpName = $floorplan_data['fpName'][0];
    $floorplan_post->bedCount = $floorplan_data['fpBeds'][0];
    $floorplan_post->bathCount = rp_archive_fp_getBathroomString($floorplan_data['fpBaths'][0]);
    $floorplan_post->fpMinRent = $floorplan_data['fpMinRent'][0];
    $floorplan_post->fpUnitsCaptured = $floorplan_data['fpUnitsCaptured'][0];
    $floorplan_post->sqft = $floorplan_data['fpMinSQFT'][0];
    $floorplan_post->matterportLink = $floorplan_data['fpMatterport'][0];
    $floorplan_post->featureName = $floorplanFeatures;

    $specialText = $floorplan_data['fp_special_text'][0];
    $specialExpiration = $floorplan_data['fp_special_expiration'][0];
    $isExpired = isExpired($specialExpiration);

    if ($specialText && $specialText !== "" && $isExpired !== true){
        $floorplan_post->has_special = 'true';
    } else {
        $floorplan_post->has_special = 'false';
    }
    

    // Floorplan's Property
    $fpProperty=get_posts([
        'post_type' => 'properties',
        'property_code' => $floorplan_post->parent_property_code,
        'post_status' => ['publish', 'draft'],
    ])[0];

    //display sqft only if there is any
    $floorplan_post->displaySqft = ($floorplan_post->sqft > 0) ? $floorplan_post->sqft." Sq. ft. " : "" ;
    //if pricing is disabled on the site, do not show the rent
    $floorplan_post->displayRent = ($all_floorplans_data['data']['disable_pricing'] == "true") 
        ? $all_floorplans_data['data']['disable_pricing_message'] 
        : $floorplan_post->displayRent ;
    
    //get the floorplan image url and make it usable
    $floorplan_post->fpImg = array();
    $fpImg = esc_url($rentpress_floorplan->image());
    $fpImg = explode(',', $fpImg);
    if ( is_array($fpImg) && count($fpImg) >= 1 ) { $floorplan_post->fpImg['image'] = $fpImg[0]; }

    //create alt tag and if the post title is not used, then flip the values
    if ($rentPressOptions->getOption('override_single_floorplan_template_title') == '') {
        $floorplan_post->fpImg['alt'] = $floorplan_post->post_title;
        $floorplan_post->post_title = rp_archive_fp_getBedBathString($floorplan_post);
    } else {
        $floorplan_post->fpImg['alt'] = rp_archive_fp_getBedBathString($floorplan_post);
    }

    //create an array and for each unit that belongs to this floorplan add it to this array
    $floorplan_post->units = array();
    $available_units = 0;
    $apartments = 0;

    foreach ($all_units as $key => $unit) {
        if ($unit->fpID == $floorplan_data['fpID'][0]) {

            //set up a usable timestamp
            $unit->availableDateTS = ($unit->is_available_on) ? strtotime($unit->is_available_on) : null ;

            if ($unit->is_available_on != '1970-01-01' && rp_archive_fp_isAvailable($unit, $availability_type, $today, $lookahead)) {
                $available_units++;
            }
            $apartments++;

            array_push($floorplan_post->units, $unit);

            //remove this unit from the giant units array to use less resources on each pass
            unset($all_units[$key]);
        }
    }

    //needed for js sorting
    $floorplan_post->fpAvailUnitCount = $available_units;

    //set up the displayed unit count based on client settings
    $floorplan_post->fpAvailUnitCountDisplay = $available_units." Available" ;

    if ($show_waitlist && $available_units == 0) {
        $floorplan_post->fpAvailUnitCountDisplay = "Join Waitlist" ;
    }
    if ($availability_type == 'unit_visibility_5' && $apartments > 1) {
        $floorplan_post->fpAvailUnitCountDisplay = $apartments." Apartments";
    }
    if ($availability_type == 'unit_visibility_5' && $apartments == 1) {
        $floorplan_post->fpAvailUnitCountDisplay = $apartments." Apartment";
    }

    //rent range calculation
    if ($all_floorplans_data['data']['disable_pricing'] != 'true') {
        if (is_numeric($floorplan_data['fpMinRent'][0]) && (!isset($rent_range->min) || $rent_range->min > $floorplan_data['fpMinRent'][0])) {
            $rent_range->min = $floorplan_data['fpMinRent'][0];
        }

        if (is_numeric($floorplan_data['fpMinRent'][0]) && (!isset($rent_range->max) || $rent_range->max < $floorplan_data['fpMinRent'][0])) {
            $rent_range->max = $floorplan_data['fpMinRent'][0];
        }
    }

    //add this completed floorplan object to the floorplan array
    array_push($all_floorplans, $floorplan_post);

    include RENTPRESS_PLUGIN_DIR . 'misc/template-schema/floorplan-card-schema.php';
}

//set up the array of floorplans to the data array for js
$all_floorplans_data['all_floorplans'] = $all_floorplans;
$rent_range->min = ($rent_range->min < 0) ? 0 : $rent_range->min ;
$rent_range->max = ($rent_range->max < 1) ? 1 : $rent_range->max ;
$all_floorplans_data['data']['rent_range'] = $rent_range;

function rp_archive_fp_isAvailable($unit, $option, $today, $lookahead){
    $isAvailable = false;

    switch ($option) {
        // Show all with date and/or availability status
        case 'unit_visibility_1':
            $isAvailable = ($unit->is_available || $unit->is_available_on) ? true : false ;
            break;
        // Show all with availability status
        case 'unit_visibility_2':
            $isAvailable = ($unit->is_available) ? true : false ;
            break;
        // Show units only available today
        case 'unit_visibility_3':
            $isAvailable = ($unit->availableDateTS && $unit->availableDateTS < $today) ? true : false ;
            break;
        // Show available today and soon
        case 'unit_visibility_4':
            $isAvailable = ($unit->availableDateTS && $unit->availableDateTS < $lookahead) ? true : false ;
            break;
    }

    return $isAvailable;
}

function rp_archive_fp_getFutureTime($rentPressOptions)
{
    $date = $rentPressOptions->getOption('use_avail_units_before_this_date');
    return strtotime( "+$date days", time());
}

function rp_archive_fp_getBathroomString($bathCount)
{
    return (fmod(floatval($bathCount), 1) == 0.5) ? number_format($bathCount, 1) : number_format($bathCount) ;
}

function rp_archive_fp_getBedBathString($floorplan_post)
{
    return ($floorplan_post->bedCount == 0) ? 
            "Studio | ".$floorplan_post->bathCount." Bath" : 
            $floorplan_post->bedCount." Bed | ".$floorplan_post->bathCount." Bath" ;
}

function rp_archive_fp_wordpressLoop($displayContentHere)
{
    if ($displayContentHere) {
        echo "<div class='rp-default-wp-content'>";
            if ( have_posts() ) {
                while ( have_posts() ) {
                    the_post();
                    the_content();
                }
            }
        echo "</div>";
    }
}

//enque the javascript here so you can add all of the data gathered
function rp_archive_fp_basicLoader($all_floorplans_data){

    wp_enqueue_script('archive-floorplans-basic', RENTPRESS_PLUGIN_ASSETS.'build/js/templates/archive-floorplans-basic.min.js', null, null, true);
    wp_localize_script('archive-floorplans-basic', 'all_floorplans_data_encoded', json_encode($all_floorplans_data));
}

rp_archive_fp_basicLoader($all_floorplans_data);

//will return true if Internet Explorer has been detected
function ae_detect_ie()
{
    $isIE = false;
    if ((isset($_SERVER['HTTP_USER_AGENT']) && 
        (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) || 
        preg_match("/(Trident\/(\d{2,}|7|8|9)(.*)rv:(\d{2,}))|(MSIE\ (\d{2,}|8|9)(.*)Tablet)|(Trident\/(\d{2,}|7|8|9))/", $_SERVER["HTTP_USER_AGENT"], $match) != 0 ){
        $isIE = true;
    }

    return $isIE;
}

