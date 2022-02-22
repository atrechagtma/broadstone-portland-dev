<?php

global $rentPress_Service;

//rentpress vars
	$rentPressOptions        = new rentPress_Options();
	$globalPriceDisable      = $rentPressOptions->getOption('rentPress_disable_pricing');
	$disabledPricing         = $propertyData['propDisablePricing'][0];
	$priceDisplayMode        = $rentPressOptions->getOption('rentPress_override_how_floorplan_pricing_is_display');
	$templateAccentColor     = $rentPressOptions->getOption('templates_accent_color');
	$globalTaxCityImage      = $rentPressOptions->getOption('cities_default_featured_image');
	$searchTemplateIsActive	 = $rentPressOptions->getOption('override_archive_properties_template_file');
	$requestInfoURL		     = $rentPressOptions->getOption('single_floorplan_request_more_info_url');
	$accentColorHex = sanitize_hex_color_no_hash($templateAccentColor);

//city vars
$citiesTerms = get_terms( 'prop_city', array(
    'orderby'    => 'name',
    'hide_empty' => true
) );