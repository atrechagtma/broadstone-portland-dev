<?php

function rentpress_getAllMetaDataForAllFloorplans()
{
	$all_floorplan_meta = array();

	$args = [
	    'post_type'      => 'rentpress_floorplan'
	];
	$floorplans = get_posts( $args );

	foreach ($floorplans as $floorplan) {
		$floorplan_meta = rentpress_getAllMetaDataForAFloorplan($floorplan->ID);
		$floorplan_meta['post_information'] = $floorplan;

		$all_floorplan_meta[$floorplan_meta['rentpress_custom_field_floorplan_code'][0]] = $floorplan_meta;
	}

	return $all_floorplan_meta;

}

function rentpress_getAllMetaDataForAFloorplan($floorplanID)
{
	$meta = get_post_meta( $floorplanID);
	$meta['post_id'] = $floorplanID;
	return $meta;
}

function rentpress_getAllFLoorplansPostsForPropertyCode($property_code) {
	// get all not just first 10
	$args = array(
		'post_type' => 'rentpress_floorplan',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => 'rentpress_custom_field_floorplan_parent_property_code',
				'value' => $property_code
			)
		)
	);
	$query = new WP_Query($args);
	return $query->posts;
}