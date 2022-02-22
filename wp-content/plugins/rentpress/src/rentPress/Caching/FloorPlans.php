<?php

class rentPress_Caching_FloorPlans extends rentPress_Base_Caching {

	public function byProperty($taxonomyterm, $arguments = array()) {
        $fpList = [];
    	$floorPlanArgs = [
            'post_type' => 'floorplans',
            'property_relationship' => [$taxonomyterm],
            'post_status' => 'publish',
            'posts_per_page' => -1
        ];
        $floorPlanArgs = is_array($arguments) ? array_merge($floorPlanArgs, $arguments) : $floorPlanArgs;
        // Make sure post type stays what it should be
        $floorPlanArgs['post_type'] = 'floorplans';
        $floorplans = new WP_Query($floorPlanArgs);
        if ( $floorplans->have_posts() ) : while ( $floorplans->have_posts() ) : $floorplans->the_post();
            $terms = get_the_terms($floorplans->post->ID, 'property_relationship');
	        $image = get_post_meta($floorplans->post->ID, 'fpImg', true);
	        $image = $image ? $image : wp_get_attachment_url( get_post_thumbnail_id($floorplans->post->ID) );
	        $image = $image ? $image : 'https://placehold.it/400x350?text=Floor+Plan+Img';

            $fpList[] = [
                'fpID' 		  => get_post_meta( $floorplans->post->ID, 'fpID', true),
                'fpName'          => get_post_meta( $floorplans->post->ID, 'fpName', true),
                'fpBeds'          => get_post_meta( $floorplans->post->ID, 'fpBeds', true),
                'fpBaths'         => get_post_meta( $floorplans->post->ID, 'fpBaths', true),
                'fpMinSQFT'       => get_post_meta( $floorplans->post->ID, 'fpMinSQFT', true),
                'fpMaxSQFT'       => get_post_meta( $floorplans->post->ID, 'fpMaxSQFT', true),
                'fpMinRent'       => get_post_meta( $floorplans->post->ID, 'fpMinRent', true),
                'fpMaxRent'       => get_post_meta( $floorplans->post->ID, 'fpMaxRent', true),
                'fpMinDeposit'    => get_post_meta( $floorplans->post->ID, 'fpMinDeposit', true),
                'fpMaxDeposit'    => get_post_meta( $floorplans->post->ID, 'fpMaxDeposit', true),
                'fpAvailUnitCount'=> get_post_meta( $floorplans->post->ID, 'fpAvailUnitCount', true),
                'fpAvailURL'      => get_post_meta( $floorplans->post->ID, 'fpAvailURL', true),
                'fpImg'           => $image ? $image[0] : get_post_meta( $floorplans->post->ID, 'fpImg', true),
                'fpPhone'         => get_post_meta( $floorplans->post->ID, 'fpPhone', true),
                'fpUnits'         => get_post_meta( $floorplans->post->ID, 'fpUnits', true),
                'fpPostID' 		  => $floorplans->post->ID
            ];
        endwhile;endif;wp_reset_postdata();

        return $fpList;
	}

	/**
	 * Fetch cached floor plans, or perform a fresh query
	 * @return array
	 */
	public function all($arguments = array()) {
		// Call the API.
		$floorPlanArgs = [
			'post_type' => 'floorplans',
			'post_status' => 'publish',
			'posts_per_page' => -1
		];
		// Will only try to add manual arguments if parameter is of type array
		$floorPlanArgs = is_array($arguments) ? array_merge($floorPlanArgs, $arguments) : $floorPlanArgs;
		// Make sure no one overwrites the post type in the merge, then make the request
		$floorPlanArgs['post_type'] = 'floorplans';
		$floorplans = new WP_Query($floorPlanArgs);
		$results = [];
		if($floorplans->have_posts()) : while($floorplans->have_posts()) : $floorplans->the_post();
			$results[] = [
				'fpID' 		  => get_post_meta($floorplans->post->ID, 'fpID', true),
				'fpName'          => get_post_meta($floorplans->post->ID, 'fpName', true),
				'fpBeds'          => get_post_meta($floorplans->post->ID, 'fpBeds', true),
				'fpBaths'         => get_post_meta($floorplans->post->ID, 'fpBaths', true),
				'fpMinSQFT'       => get_post_meta($floorplans->post->ID, 'fpMinSQFT', true),
				'fpMaxSQFT'       => get_post_meta($floorplans->post->ID, 'fpMaxSQFT', true),
				'fpMinRent'       => get_post_meta($floorplans->post->ID, 'fpMinRent', true),
				'fpMaxRent'       => get_post_meta($floorplans->post->ID, 'fpMaxRent', true),
				'fpMinDeposit'    => get_post_meta($floorplans->post->ID, 'fpMinDeposit', true),
				'fpMaxDeposit'    => get_post_meta($floorplans->post->ID, 'fpMaxDeposit', true),
				'fpAvailUnitCount'=> get_post_meta($floorplans->post->ID, 'fpAvailUnitCount', true),
				'fpAvailURL'      => get_post_meta($floorplans->post->ID, 'fpAvailURL', true),
				'fpImg'           => get_post_meta($floorplans->post->ID, 'fpImg', true),
				'fpPhone'         => get_post_meta($floorplans->post->ID, 'fpPhone', true),
				'fpUnits'         => get_post_meta($floorplans->post->ID, 'fpUnits', true),
                'fpPostID'        => $floorplans->post->ID
			];
		endwhile;endif;wp_reset_postdata();

		// if there are floor plans to cache, cache them
		if(count($results) > 0) return $results;
		else return [];
	}
}
