
<?php

    global $rentPress_Service;

    //rentpress vars
    $rentPressOptions        = new rentPress_Options();
    $globalPriceDisable      = $rentPressOptions->getOption('rentPress_disable_pricing');
    $disabledPricing         = $propertyData['propDisablePricing'][0];
    $priceDisplayMode        = $rentPressOptions->getOption('rentPress_override_how_floorplan_pricing_is_display');
    $templateAccentColor     = $rentPressOptions->getOption('templates_accent_color');
    $accentColorHex 		 = sanitize_hex_color_no_hash($templateAccentColor);
    $globalTaxCityImage 	 = $rentPressOptions->getOption('rentPress_cities_default_featured_image');
    $searchTemplateIsActive	 = $rentPressOptions->getOption('override_archive_properties_template_file');

 	// request info url
 	if ( $rentPressOptions->getOption('single_floorplan_request_more_info_url') == '' ) :
 		$requestInfoURL = $rentPressOptions->defaultOptionValues['single_floorplan_request_more_info_url'];
 	else:
 		$requestInfoURL = $rentPressOptions->getOption('single_floorplan_request_more_info_url');
 	endif;

    //this term vars
	$term_slug = get_queried_object()->slug;
	$term_name = get_queried_object()->name;
	$term_id = get_queried_object()->term_id;
	$tax_name = get_queried_object()->taxonomy;
	$term_description = get_queried_object()->description;
	$termMeta = get_term_meta( get_queried_object()->term_id );
	$term_map_shortcode =  stripslashes( get_option( 'taxonomy_' . $term_id )['rp_tax_shortcode'] );

	$favContent = stripslashes( get_option( 'taxonomy_' . $term_id )['rp_local_favs'] );
	$cityRomance = stripslashes( get_option( 'taxonomy_' . $term_id )['rp_city_romance'] ) ;

	// get and set image banner
	$termImage = wp_get_attachment_image_url($termMeta['showcase-taxonomy-image-id'][0], 'full');
	if( $termImage ) :
		$termFeaturedImage = $termImage;
	elseif( $tax_name == 'prop_city' ) :
		$termFeaturedImage = $globalTaxCityImage;
	else :
		$termFeaturedImage = "https://via.placeholder.com/2500x500/$accentColorHex/?text=+";
	endif;

	// city stuff
	$propertyData         = get_post_meta($post->ID);
	$propertyState        = $propertyData['propState'][0];

	//prop vars
	$prop_args = array(
		'post_type' => 'properties',
		'posts_per_page' => -1,
		'order' => 'ASC',
		'tax_query' => array(
			array(
			    'taxonomy' => get_queried_object()->taxonomy,
			    'field' => 'slug',
			    'terms' => $term_slug,
			    'hide_empty' => true
			    )
			)
		);
	$prop_qry = new WP_Query($prop_args);

	if($prop_qry->have_posts()) :
	$numUnitsAvailable=0;
		foreach ($prop_qry->posts as $prop):
			$propMeta = get_post_meta( $prop->ID );
			$numUnitsAvailable += (int)$propMeta['propUnitsAvailable'][0];
		endforeach; 
	endif;

	//city vars
	$citiesTerms = get_terms( array(
	    'taxonomy' => 'prop_city',
	    'hide_empty' => true,
	    'post__not_in' => $term_slug
	) );
	$citiesQuery = wp_list_filter($citiesTerms, array('slug'=>$term_slug),'NOT');
	
	$featuredCities = array_rand( $citiesQuery, 3 );
		
	//functions
	function getFloorplanAndUnitMeta($property_code, $wpdb)
	{
		$property_units = $wpdb->get_results($wpdb->prepare( "SELECT * FROM `$wpdb->rp_units` WHERE `prop_code` = %s", $property_code));

		foreach ($property_units as $unit) {
			$unit->tpl_data = json_decode($unit->tpl_data);
			$date = new DateTime($unit->tpl_data->Information->AvailableOn);
			$unit->tpl_data->Information->AvailableStr = $date->format('m/d/Y');
		}

		return $property_units;
	}

	// checks if string has html
	function isHTML($string){
		return $string != strip_tags($string) ? true:false;
	}