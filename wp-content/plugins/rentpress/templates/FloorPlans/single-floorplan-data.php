<?php

global $rentPress_Service;
$rentPressOptions = new rentPress_Options();

//set up variables for later use
$availability_type       = $rentPressOptions->getOption('override_unit_visibility');
$priceDisableMsg         = $rentPressOptions->getOption('rentPress_disable_pricing_message');
$today                   = strtotime( "+1 days", time());
$lookahead               = rp_single_fp_getFutureTime($rentPressOptions);
$disableLeaseTermPricing = $rentPressOptions->getOption('disbale_all_units_lt_pricing');
$globalPriceDisable      = $rentPressOptions->getOption('rentPress_disable_pricing');
$waitlist_url            = ($rentPressOptions->getOption('show_waitlist_override_url') == '/waitlist') 
	? site_url().'/waitlist'
	: $rentPressOptions->getOption('show_waitlist_override_url') ;
$show_waitlist_ctas 	 = ($rentPressOptions->getOption('show_waitlist_ctas') != 'false') ? true : false ;
$globalApplyOverride 	 = $rentPressOptions->getOption('rentPress_override_apply_url');
$useFPMarketingName 	 = $rentPressOptions->getOption('override_single_floorplan_template_title');
$showTourCTAs			 = $rentPressOptions->getOption('tour_cta_button');

// floor plan vars
$currentFloorPlan = $rentPress_Service['floorplans_meta']->setPostID($post->ID);
$fpData           = get_post_meta($post->ID);
$fpName           = sanitize_text_field($currentFloorPlan->name());
$bedrooms         = $currentFloorPlan->beds();
$bathrooms        = $currentFloorPlan->baths();
$sqft             = $currentFloorPlan->sqftg();
$rent             = $currentFloorPlan->rent();
$fpAvailUrl 	  = esc_url($currentFloorPlan->availabilityUrl()) == '' ? '/contact/' : esc_url($currentFloorPlan->availabilityUrl()) ;
$fpMatterport     = get_post_meta($post->ID, 'fpMatterport', true);
$fpDescription    = get_post_meta($post->ID, 'fpDescription', true);
$fpID			  = $fpData['fpID'][0];
$floorPlanSpecial = get_post_meta($post->ID, 'fp_special_text', true);
$floorPlanSpecialExpiration = isExpired(get_post_meta($post->ID, 'fp_special_expiration', true));
$floorPlanSpecialLink = get_post_meta($post->ID, 'fp_special_link', true);

// build the apply now link
if (strpos($globalApplyOverride,'http')) :
    $availabilityUrl = $globalApplyOverride;
else:
    $availabilityUrl = $fpAvailUrl;
endif;

// get floor plan image
$fpImage = esc_url($currentFloorPlan->image());
// We do this to check for cases where urls are comma separated, then we pull out the first one in the list.
$images = explode(',', $fpImage);
if ( is_array($images) && count($images) > 1 ) $fpImage = $images[0];

// Floorplan's Property
$fpProperty=get_posts([
	'post_type' => 'properties',
	'property_code' => $post->parent_property_code,
	'post_status' => ['publish', 'draft'],
])[0];

$thePropertiesAmenities = wp_get_post_terms( $fpProperty->ID, 'prop_amenities', []);
$amenities_filtered = wp_list_filter($thePropertiesAmenities, array('slug'=>'custom-amenity'),'NOT');

$propDescription = get_post_meta($fpProperty->ID, 'propDescription', true);
$disablePricing = get_post_meta($fpProperty->ID, 'propDisablePricing', true );

// phone number
$propertyPhone = get_post_meta($fpProperty->ID,'propPhoneNumber', true);
$trackingNumber = get_post_meta($fpProperty->ID,'property_trackingphone', true);

if ( $trackingNumber) {
$displayedPhoneNumber = $trackingNumber;
}  else {
$displayedPhoneNumber = $propertyPhone;
}

	// Units

	$units = $currentFloorPlan->units();
					
	// the below logic needs refactored in the future
    foreach ($units as $key => $unit) {
    	//set up a usable timestamp
		$unit->availableDateTS = ($unit->Information->AvailableOn) ? strtotime($unit->Information->AvailableOn) : null ;

        if (is_null($unit->availableDateTS) || !rp_single_fp_isAvailable($unit, $availability_type, $today, $lookahead)) {
            unset($units[$key]);
        }

    }

	// Gathering Rent Terms Available
    $distinctTermRents=[];

    if ($disableLeaseTermPricing !== 'true') {

	    $unitsTermRents=array_filter(
	    	array_map(
	    		function($unit) {
	    			return $unit->Rent->TermRent;
	    		},
	    		$units
	    	),
	    	function($termRents) {
	    		return is_object($termRents) || is_array($termRents);
		    }
		);

		foreach ($unitsTermRents as $unitTermRents) {
			foreach ($unitTermRents as $termRent) {
				$distinctTermRents[]=$termRent->Term;
			}
		}

		$distinctTermRents=array_unique($distinctTermRents);

    }

	// Similar Floorplans
	$similarFloorPlans = get_posts([
		'post_type' => 'floorplans',
		'posts_per_page' => 3,
		'orderby' => 'rand',
		'floorplans_of_similar' => true,

		'floorplans_beds' => $bedrooms,
		'floorplans_of_property' => $post->parent_property_code
	]);

		if ($showTourCTAs == 'true'){
			$infoButtonClass = "rp-button-alt";

			if ($showTourCTAs == 'true' && ($rentPressOptions->getOption('single_floorplan_schedule_a_tour_url') == '')) {
				$schedule_a_tour_url=get_site_url().'/tour/';
			}
			else {
				$schedule_a_tour_url=$rentPressOptions->getOption('single_floorplan_schedule_a_tour_url');
			}

			$schedule_a_tour_url.='?fpName='. $post->fpName .'&fpBeds='.$post->fpBeds;

		}


		if ($rentPressOptions->getOption('override_request_link') !== 'true' || ($rentPressOptions->getOption('single_floorplan_request_more_info_url') == '')) {
			$requestMoreInfoUrl=get_site_url().'/contact';
		}
		else {
			$requestMoreInfoUrl=$rentPressOptions->getOption('single_floorplan_request_more_info_url');
		}

		$requestMoreInfoUrl.='?fpName='.$fpName .'&property_code='. $post->parent_property_code;


	if ($rentPressOptions->getOption('single_floorplan_content_position') == 'single_floorplan_content_top') { ?>
	<div <?php post_class('rp-default-wp-content'); ?>>
		<?php the_content(); ?>
	</div>
	<?php } 

	if ($rentPressOptions->getOption('hide_floorplan_availability_counter') == true) { ?>
		<style type="text/css">
			.rp-num-avail {
				display: none;
			}
		</style>
<?php } ?>

<?php 

function rp_single_fp_getFutureTime($rentPressOptions)
{
    $date = $rentPressOptions->getOption('use_avail_units_before_this_date');
    return strtotime( "+$date days", time());
}

function rp_single_fp_isAvailable($unit, $option, $today, $lookahead){
    $isAvailable = false;

    switch ($option) {
        // Show all with date and/or availability status
        case 'unit_visibility_1':
            $isAvailable = ($unit->Information->isAvailable || $unit->Information->AvailableOn) ? true : false ;
            break;
        // Show all with availability status
        case 'unit_visibility_2':
            $isAvailable = ($unit->Information->isAvailable) ? true : false ;
            break;
        // Show units only available today
        case 'unit_visibility_3':
            $isAvailable = ($unit->availableDateTS && $unit->availableDateTS < $today) ? true : false ;
            break;
        // Show available today and soon
        case 'unit_visibility_4':
            $isAvailable = ($unit->availableDateTS && $unit->availableDateTS < $lookahead) ? true : false ;
            break;
        case 'unit_visibility_5':
            $isAvailable = true;
            break;
    }

    return $isAvailable;
}

function rp_single_fp_getBathroomString($bathCount)
{
    return (fmod(floatval($bathCount), 1) == 0.5) ? number_format($bathCount, 1) : number_format($bathCount) ;
}

function rp_single_fp_getBedBathString($bedCount, $bathrooms)
{
    return ($bedCount == 0) ? 
            "Studio | ".$bathrooms." Bath" : 
            $bedCount." Bed | ".$bathrooms." Bath" ;
}