<?php

global $rentPress_Service;
global $post;

//rentpress vars
$rentPressOptions     = new rentPress_Options();
$currentProperty      = $rentPress_Service['properties_meta']->setPostID($post->ID); 
$propertyData         = get_post_meta($post->ID);
$propertyName         = $propertyData['propName'][0];
$propertyAddress      = $propertyData['propAddress'][0];
$propertyCity         = $propertyData['propCity'][0];
$propertyState        = $propertyData['propState'][0];
$propertyZip          = $propertyData['propZip'][0];
$propertyMinBeds      = $propertyData['wpPropMinBeds'][0];
$propertyMaxBeds      = $propertyData['wpPropMaxBeds'][0];
$propertyMinRent      = $propertyData['wpPropMinRent'][0];
$propertyMaxRent      = $propertyData['wpPropMaxRent'][0];
$propertyDescription  = $propertyData['propDescription'][0];
$propertyGallery      = $propertyData['prop_gallery'][0];
$propertyReviews      = $propertyData['prop_reviews'][0];
$propertyApplyLink    = $propertyData['prop_apply'][0];
$propertyTagline      = $propertyData['prop_tagline'][0];
$propertyFacebook     = $propertyData['prop_facebook'][0];
$propertyTwitter      = $propertyData['prop_twitter'][0];
$propertyInstagram    = $propertyData['prop_instagram'][0];
$googleApiToken       = $rentPressOptions->getOption('rentPress_google_api_token');
$googleMapAddress     = $propertyName.','.$propertyAddress.','.$propertyCity.','.$propertyState.','.$propertyZip;
$googleMapAddress     = str_replace(' ', '+', $googleMapAddress);
$cityID               = wp_get_post_terms($post->ID, 'prop_city' )[0]->term_id;
$cityData             = get_term_meta( $cityID );
$cityObj              = get_term( $cityID, 'prop_city' );
$cityDescription      = $cityObj->description;
$cityLink             = get_term_link($cityID, 'prop_city');
$numberInCity         = $cityObj->count;
$propertyURL          = $propertyData['propURL'][0];
$disabledPricing      = $propertyData['propDisablePricing'][0];
$propertyPetPolicy    = $propertyData['prop_pet_policy'][0];
$globalApplyOverride  = $rentPressOptions->getOption('rentPress_override_apply_url');
$globalPriceDisable   = $rentPressOptions->getOption('rentPress_disable_pricing');
$priceDisableMsg      = $rentPressOptions->getOption('rentPress_disable_pricing_message');
$priceDisplayMode     = $rentPressOptions->getOption('rentPress_override_how_floorplan_pricing_is_display');
$fpFeaturedText       = $rentPressOptions->getOption('rentPress_floorplans_grid_featured_image_text');
$noFormatNumber       = $propertyData['propPhoneNumber'][0];
$trackingNumber       = $propertyData['property_trackingphone'][0];
$gallery              = unserialize($propertyData['gallery_data'][0]); 
$propertyLogoImg      = $gallery['image_url'][0]; 
$propertySpecial      = $propertyData['prop_special_text'][0]; 
$propertySpecialLink  = $propertyData['prop_special_link'][0]; 
$propertySpecialExp   = $propertyData['prop_special_expiration'][0]; 
$isExpired            = $currentProperty->isExpired($propertySpecialExp);
$propertyResidentLink = $propertyData['prop_residents_link'][0];
$showTourButton       = $rentPressOptions->getOption('tour_cta_button');
$tourURLoverride      = $rentPressOptions->getOption('single_floorplan_schedule_a_tour_url');
$templateAccentColor  = $rentPressOptions->getOption('templates_accent_color');
$numberAvailableUnits = $propertyData['propUnitsAvailable'][0];
$propertyKeywords     = $propertyData['property_searchterms'][0];
$stateList            = rentPress_searchHelpers::$states;
$propertyStateSearch  = $stateList[$propertyData['propState'][0]];
$isSiteSingleProp     = $rentPressOptions->getOption('is_site_about_a_single_property');
$propertyOfficeHours  = $propertyData['propOfficeHours'][0];
$hideHours            = $propertyData['hide_office_hours'][0];
$propertyImportSource = $propertyData['propSource'][0];
$propertyCode         = $propertyData['prop_code'][0];

// contact leasing link
if ($rentPressOptions->getOption('override_request_link') !== 'true' || ($rentPressOptions->getOption('single_floorplan_request_more_info_url') == '')) :
    $contactLeasingLink   = get_site_url().'/contact/?propertyName='.str_replace(' ', '%20', get_the_title()).'&property_code='.$propertyCode;
else:
    $contactLeasingLink=$rentPressOptions->getOption('single_floorplan_request_more_info_url');
endif;

$amenities = wp_get_post_terms($post->ID, 'prop_amenities');
$amenities_filtered = wp_list_filter($amenities, array('slug'=>'custom-amenity'),'NOT');

// calculate beds
if ($propertyMinBeds == $propertyMaxBeds && $propertyMinBeds == '0' ) {
    $bedLabel = 'Studio';
} elseif ($propertyMinBeds == $propertyMaxBeds) {
    $bedLabel = $propertyMinBeds.' Bed';
} elseif ($propertyMinBeds == '0') {
    $bedLabel = 'Studio - '.$propertyMaxBeds.' Bed';
} else {
    $bedLabel = $propertyMinBeds.' Bed - '.$propertyMaxBeds.' Bed';
}

// calculate price
if (($globalPriceDisable == 'true' ) || ($disabledPricing == 'true')) {
    $priceLabel = '';
} elseif ($propertyMinRent == '' || $propertyMinRent < '99' ) {
    $priceLabel = '';
} elseif ($priceDisplayMode == 'range' ) {
    $priceLabel = '<strong> |</strong> <span>$'.$propertyMinRent.' - $'.$propertyMaxRent. '</span>'; 
} else {
    $priceLabel = '<strong> |</strong> Starting at $'.$propertyMinRent;
}

// build the tour link
if ($showTourButton == 'true') :
  $infoButtonClass = ' class="rp-button-alt"';
  if ($tourURLoverride == '/tour/') :
        $schedule_a_tour_url=get_site_url().$tourURLoverride.'?propertyName='.str_replace(' ', '%20', get_the_title()).'&property_code='.$propertyCode;
    else :
        $schedule_a_tour_url=$rentPressOptions->getOption('single_floorplan_schedule_a_tour_url') ;
    endif;
    $tourLink.= $schedule_a_tour_url;
  else : 
      $infoButtonClass = ' class="rp-button"';
endif;

// mailto link forward to friend
$forwardToFriendLink = 'mailto:?subject=Check Out These Apartments at '. get_the_title() .' | '. $currentProperty->city() .', '. $currentProperty->state() .'&body=I liked these apartments at '. get_the_title() .'. What do you think? Take a look here: '. esc_attr(get_the_permalink());

?>