<?php

global $rentPress_Service;

$globalPriceDisable = $rentPressOptions->getOption('rentPress_disable_pricing');
$priceDisplayMode   = $rentPressOptions->getOption('rentPress_override_how_floorplan_pricing_is_display');
$globalPropertyImage = $rentPressOptions->getOption('rentPress_properties_default_featured_image');

$rent_range = new stdClass();
$bed_range = new stdClass();

$properties = array();
$post_type = 'properties';
$post_status = 'publish';
$property_load_limit = $attributes['load_limit'];
$propertyTypes = [];
$post_ids = $wpdb->get_col($wpdb->prepare("
	SELECT ID 
	FROM ".$wpdb->prefix."posts
	WHERE post_type = %s
	AND post_status = %s", $post_type, $post_status));

foreach ($post_ids as $id) {

	$property = get_post_meta($id);
	$currentProperty = $rentPress_Service['properties_meta']->setPostID($post->ID);
	$property['specialIsExpired'] = $currentProperty->isExpired($property['prop_special_expiration'][0]);
	$petTaxonomyTerms = get_the_terms($id, 'prop_pet_restrictions');
	$property['pet_restrictions'] = ($petTaxonomyTerms) ? array_map('strtolower', array_column($petTaxonomyTerms, 'name')) : [];
	$stateList = rentPress_searchHelpers::$states;
	$property['propStateSearch'] = $stateList[strtoupper($property['propState'][0])];
	$propertyType = get_the_terms($id, 'prop_type');
	$property['propType'] = ($propertyType) ? array_column($propertyType, 'name') : [];
	$property['community_types'] = ($propertyType) ? array_map('strtolower', array_column($propertyType, 'name')) : [];

	if ($property['propType'] !== false) {
		foreach ($property['propType'] as $propType) {
			if (!in_array($propType, $propertyTypes)) {
				array_push($propertyTypes, $propType);
			}
		}
	}

	if(
		($property['wpPropMaxBeds'][0] < $attributes['min_beds']) 
		|| 
		($property['wpPropMinBeds'][0] > $attributes['max_beds'])
		||		
		($property['wpPropMaxRent'][0] < $attributes['min_rent'])
		||		
		($property['wpPropMinRent'][0] > $attributes['max_rent'])
	) {
		continue;
	}

	if(strtolower($attributes['has_special']) == "true") {
		if($property['prop_special_text'][0] == '' || $property['specialIsExpired'] == true) {
			continue;
		}
	}

	if($attributes['city'] !== false) {
		if(strtolower($attributes['city']) !== strtolower($property['propCity'][0])) {
			continue;
		}
	}

	if(strtolower($attributes['pets']) == "true") {
		if (empty($property['pet_restrictions'])) {
			continue;
		}
	} elseif($attributes['pets'] !== false) {
		if (in_array(strtolower($attributes['pets']),$property['pet_restrictions'] ) == false) {
			continue;
		}
	}	

	if(strtolower($attributes['community_type']) == "true") {
		if (empty($property['community_types'])) {
			continue;
		}
	} elseif($attributes['community_type'] !== false) {
		if (in_array(strtolower($attributes['community_type']),$property['community_types'] ) == false) {
			continue;
		}
	}
	
	$property['image'] = setPropImage($id, $globalPropertyImage);
	$property['imageSizes'] = getImageSizes($id);
	$property['post_ID'] = $id;
	$property['url'] = get_permalink($id);
	$property['displayPrice'] = '<div class="rp-prop-price-range"><span>Starting At $'.$property['wpPropMinRent'][0].'</span></div>';

	$disabledPricing = $property['propDisablePricing'][0];
	if ($globalPriceDisable == 'true' 
		|| $disabledPricing == 'true' 
		|| (int) $property['wpPropMinRent'][0] < 100 
		|| (int) $property['wpPropMaxRent'][0] < 100 
		|| $property['wpPropMinRent'][0] == '') {
		$property['displayPrice'] = '';
	} elseif ($priceDisplayMode != 'starting-at') {
		$property['displayPrice'] = '<div class="rp-prop-price-range"><span>$'.$property['wpPropMinRent'][0].' - $'.$property['wpPropMaxRent'][0].'</span></div>';
	}

	$property['propName'] = (isset($property['post_title']) && $property['post_title'][0] != '') ? $property['post_title'] : $property['propName'] ;

	$rent_range->min = (!isset($rent_range->min) || $property['wpPropMinRent'] < $rent_range->min) ? $property['wpPropMinRent'] : $rent_range->min ;
	$rent_range->max = (!isset($rent_range->max) || $property['wpPropMaxRent'] > $rent_range->max) ? $property['wpPropMaxRent'] : $rent_range->max ;
	
	$bed_range->min = (!isset($bed_range->min) || (int) $property['wpPropMinBeds'][0] < $bed_range->min) ? (int) $property['wpPropMinBeds'][0] : $bed_range->min ;
	$bed_range->max = (!isset($bed_range->max) || (int) $property['wpPropMaxBeds'][0] > $bed_range->max) ? (int) $property['wpPropMaxBeds'][0] : $bed_range->max ;

	if ($property['wpPropMinBeds'][0] == '' || $property['wpPropMaxBeds'][0] == '') {
		break;
	} elseif (!isset($property['propBedsList'])) {
		$property = calculatePropRanges($property);
	}

	array_push($properties, $property);

	include RENTPRESS_PLUGIN_DIR . 'misc/template-schema/property-search-card-schema.php';
}

$taxonomies = array();
$selected_taxonomies = ['prop_pet_restrictions'];
$petsArray = array_keys($taxonomies);

$results = $wpdb->get_results($wpdb->prepare("
	SELECT r.object_id, t.name 
	FROM ".$wpdb->prefix."term_relationships AS r
	JOIN ".$wpdb->prefix."term_taxonomy AS tt 
	ON r.term_taxonomy_id = tt.term_taxonomy_id
	JOIN ".$wpdb->prefix."terms AS t
	ON tt.term_id = t.term_id 
	WHERE tt.taxonomy = %s", $selected_taxonomies));

foreach ($results as $r) {
	$name = "$r->name";
	$prop_number = "$r->object_id";
	if (!isset($taxonomies[$name])) {
		$tax = new stdClass();
		$tax->associated_properties = [$prop_number];
		$taxonomies[$name] = $tax;
	} else {
		array_push($taxonomies[$name]->associated_properties, $prop_number);
	}
}

function getImageSizes($id) {
	$imageMeta = wp_get_attachment_metadata( get_post_thumbnail_id( $id ) );
	$fullImage = get_the_post_thumbnail_url( $id );

	if ( $imageMeta ) {
		$imageSizes = array();
		$imageSizes[] = array(
			'url' => $fullImage, 
			'size' => 'full', 
			'width' => $imageMeta['width'],
			'height' => $imageMeta['height']
		);
		foreach ($imageMeta['sizes'] as $key => $size) {
			$imageUrl = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), $key );
			$imageSizes[] = 
				array( 
					'url' => $imageUrl[0], 
					'size' => $key, 
					'width' => $size['width'],
					'height' => $size['height'] 
			);
		}
	}
	return $imageSizes;
}

function setPropImage($id, $defaultImage) {
	$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'medium_large')[0];

	if ($image == '' || !$image) {
		$image = $defaultImage;
	}

	if ($image == '' || !$image) {
		$image = 'https://placehold.it/400x250?text=Featured%20Property%20Image';
	}

	return $image;
}

function calculatePropRanges($prop) {
	$list = [];
	for ($i = $prop['wpPropMinBeds'][0]; $i <= $prop['wpPropMaxBeds'][0]; $i++) {
	    array_push($list, $i);
	}

	$prop['propBedsList'][0] = json_encode($list);

	return $prop;
}