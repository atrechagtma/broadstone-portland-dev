<?php
/**
 * Manage Floor Plans Search Querys
 * @author  Joshua
 */

class rentPress_FloorPlans_SearchAddon {
	static public $orderby_same_same=[
		'available_units' => 'from_available_units.numOfAvailable',
		'available_date' => 'from_available_units.soonestAvailablity',

		'title' => '_title',
		'date' => '_date',
		'modified' => '_modified',

		'rent' => 'rent',
		'beds' => 'meta_data.fpBeds',
		'baths' => 'meta_data.fpBaths',
	];

	/**
	 * Initializes floor plan search addon actions and filters
	 * 
	 * @return [void]
	 */
	public function run() {
		add_filter('query_vars', [$this, 'addQueryVars'], 10, 1);
		add_action('pre_get_posts', [$this, 'pre_get_floorplans'], 31, 1);
		add_action('pre_get_posts', [$this, 'pre_get_similar_floorplans'], 32, 1);
	}

	/**
	 * Add query variables for 'floorplans' custom post type querying through WordPress
	 * @param [array] $vars [Current WordPress global $vars variable]
	 */
	public function addQueryVars($vars) {

		$vars[]='floorplans_of_property';

		$vars[]='floorplan_code';

		$vars[]='fpID';
		
		$vars[] = 'floorplans_min_rent';
		$vars[] = 'floorplans_max_rent';
		$vars[] = 'floorplans_rent_ors';

		$vars[] = 'floorplans_beds';
		$vars[] = 'floorplans_min_beds';
		$vars[] = 'floorplans_max_beds';

		$vars[] = 'floorplans_baths';
		$vars[] = 'floorplans_min_baths';
		$vars[] = 'floorplans_max_baths';

		$vars[] = 'floorplans_min_sqft';
		$vars[] = 'floorplans_max_sqft';
		$vars[] = 'floorplans_sqft_ors';

		$vars[]='floorplans_with_available_units';

		$vars[]='floorplans_available_by';

		$vars[]='floorplans_of_similar';

		$vars[]='floorplans_sortedby';

		return $vars;
		
	}

	/**
	 * Perform operations for custom query vars on the pre_get_post hook if querying for 'floorplans' custom post type data
	 * @param  [WP_Query] $query [Current WP_Query] 
	 * @return [WP_Query object]
	 */
	public static function pre_get_floorplans($query) {
		if ($query->get('post_type') == 'floorplans' && ! $query->is_singular) {
			global $wpdb;

			// Defeaults
			if ($query->get('orderby') == "") {
				$query->set('orderby', 'title');
				$query->set('order', 'ASC');
			}

			$query->set('suppress_filters', false);

			// Start Of Meta And Tax Queries
			$floorplans_meta_query=[];
			$floorplans_identification_meta_query=[];

			if (rentPress_searchHelpers::if_query_var_check($query->get('floorplans_of_property'))) {
				$is_this_a_post_property_id = get_post_meta($query->get('floorplans_of_property'), 'prop_code', true);
				
				if ( rentPress_searchHelpers::if_query_var_check($is_this_a_post_property_id) ) {
					$query->set('floorplans_of_property', $is_this_a_post_property_id);
				}
				
				$floorplans_identification_meta_query['parent_property_code']=[
					'key' => 'parent_property_code',
					'value' => $query->get('floorplans_of_property'),
				];
			}

			if (rentPress_searchHelpers::if_query_var_check($query->get('floorplan_code'))) {
				$floorplans_identification_meta_query[]=[
					'key' => 'fpID',
					'value' => $query->get('floorplan_code'),
				];
			}
			elseif (rentPress_searchHelpers::if_query_var_check($query->get('fpID'))) {
				$floorplans_identification_meta_query[]=[
					'key' => 'fpID',
					'value' => $query->get('fpID'),
				];

				/*
					@ToDo Maybe Add 
						$query->set('per_posts_page', 1);
				*/
			}

			rentPress_searchHelpers::apply_meta_query_to_query($query, $floorplans_identification_meta_query, 'fp_idenification');

			// Beds
			if (is_array($query->get('floorplans_beds'))) {
				$floorplans_meta_query['beds']=[
					'type' => 'NUMERIC',
					'key' => 'fpBeds',
					'compare' => 'IN',
					'value' => array_filter($query->get('floorplans_beds'), function($bed) {return rentPress_searchHelpers::if_query_var_check_allow_zero($bed);}),
				];
			}
			else if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get('floorplans_beds') )) {			
				$floorplans_meta_query['beds']=[
					'type' => 'NUMERIC',
					'key' => 'fpBeds',
					'compare' => '=',
					'value' => $query->get('floorplans_beds'),
				];
			}

			if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get('floorplans_min_beds') )) {
				$floorplans_meta_query['min_beds']=[
					'type' => 'NUMERIC',
					'key' => 'fpBeds',
					'compare' => '>=',
					'value' => $query->get('floorplans_min_beds'),
				];
			}

			if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get('floorplans_max_beds') )) {
				$floorplans_meta_query['max_beds']=[
					'type' => 'NUMERIC',
					'key' => 'fpBeds',
					'compare' => '<=',
					'value' => $query->get('floorplans_max_beds'),
				];
			}

			// Baths
			if (is_array($query->get('floorplans_baths'))) {
				$floorplans_meta_query['baths']=[
					'key' => 'fpBaths',
					'compare' => 'IN',
					'value' => array_filter($query->get('floorplans_baths'), function($bath) {return rentPress_searchHelpers::if_query_var_check_allow_zero($bath);}),
				];
			}
			elseif (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get('floorplans_baths') )) {
				$floorplans_meta_query['baths']=[
					'key' => 'fpBaths',
					'compare' => '=',
					'value' => number_format($query->get('floorplans_baths'), 2),
				];
			}

			if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get('floorplans_min_baths') )) {
				$floorplans_meta_query['min_baths']=[
					'key' => 'fpBaths',
					'compare' => '>=',
					'value' => number_format($query->get('floorplans_min_baths'), 2),
				];
			}

			if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get('floorplans_max_baths') )) {
				$floorplans_meta_query['max_baths']=[
					'key' => 'fpBaths',
					'compare' => '<=',
					'value' => number_format($query->get('floorplans_max_baths'), 2),
				];
			}

			// SQFT
			if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get('floorplans_min_sqft') )) {
				$floorplans_meta_query['min_sqft']=[
					'type' => 'NUMERIC',
					'key' => 'fpMinSQFT',
					'compare' => '>=',
					'value' => $query->get('floorplans_min_sqft'),
				];
			}

			if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get('floorplans_max_sqft') )) {
				$floorplans_meta_query['max_sqft']=[
					'type' => 'NUMERIC',
					'key' => 'fpMinSQFT',
					'compare' => '<=',
					'value' => $query->get('floorplans_max_sqft'),
				];
			}
		
			if (is_array($query->get('floorplans_sqft_ors'))) {
				$floorplans_meta_query['sqft_ors']=[
					'relation' => 'OR',
				];

				foreach ($query->get('floorplans_sqft_ors') as $rawValue) {
					$floorplans_meta_query['sqft_ors'][]=rentPress_searchHelpers::wp_meta_query_prepare_range_or_min_or_max(
						'fpMinSQFT',
						$rawValue
					);
				}
			} 
			
			// Meta Field Sorting
			if (rentPress_searchHelpers::if_query_var_check( $query->get('floorplans_sortedby') )) {
				
              	$sort_by=rentPress_searchHelpers::parse_sortby(
              		$query->get('floorplans_sortedby'),
              		rentPress_FloorPlans_SearchAddon::$orderby_same_same
              	);

              	if (strpos($sort_by['orginal'], 'meta_data') !== false) {
					$meta_key=str_replace('meta_data.', '', $sort_by['orginal']);

					$floorplans_meta_query[]=[
			            'key' => $meta_key,
			            'compare' => 'EXISTS', 
					];

					$query->set('orderby', array(
						$meta_key => strtoupper($sort_by[1]),
					));	

					$query->set('order', $sort_by[1]);												
				}
			}
			
			// The Not In Clase
			rentPress_searchHelpers::the_post_not_in_clause($query);	

			// Setting Meta Query
			rentPress_searchHelpers::apply_meta_query_to_query($query, $floorplans_meta_query, 'fp_meta');
		}

		return $query;
	}

	public function pre_get_similar_floorplans($query) {
		global $wpdb;

		if ($query->get('post_type') == 'floorplans' && ! $query->is_singular) {

			if ($query->get('floorplans_of_similar') === true || $query->get('floorplans_of_similar') === 'true') {

				if (
					$query->get('floorplans_of_property')
					&&
					$query->get('floorplans_beds')
					&&
					$query->get('floorplans_baths')
					&&
					$query->get('post__not_in')
				) {
					if ($query->get('posts_per_page') > 0) {
						$mustBeGreaterThen=round($query->get('posts_per_page')/2);	
					}
					else {
						$mustBeGreaterThen=2;
					}

					$filter_by=(object) [
						'prop_code' => false,
						'beds_and_baths' => false,
						'beds_or_baths' => false,
					];

					$currentMetaQuery=$query->get('meta_query');

					// Testing If Property Has Enough With The Given Beds And Baths
					$number_of_similar_floorplans=$wpdb->get_var($wpdb->prepare(
						"
							SELECT COUNT(prop_code.post_id) as numberOf
							FROM $wpdb->postmeta prop_code
							LEFT JOIN $wpdb->postmeta beds ON beds.post_id = prop_code.post_id
							LEFT JOIN $wpdb->postmeta baths ON baths.post_id = beds.post_id
							WHERE prop_code.meta_key = 'parent_property_code' AND prop_code.meta_value = %s
							AND beds.meta_key='fpBeds' AND beds.meta_value = %f
							AND baths.meta_key='fpBaths' AND baths.meta_value = %f
							AND prop_code.post_id != %d
						",
						$query->get('floorplans_of_property'),
						$query->get('floorplans_beds'),
						$query->get('floorplans_baths'),
						$query->get('post__not_in')[0]
					));

					if ($number_of_similar_floorplans >= $mustBeGreaterThen) {
						$filter_by->prop_code=true;		
						$filter_by->beds_and_baths=true;						
					}
					
					else /* If is not single prop site */ {
						// Testing If Properties Has Enough With The Given Beds Or Baths
						$number_of_similar_floorplans=$wpdb->get_var($wpdb->prepare(
							"
								SELECT COUNT(prop_code.post_id) as numberOf
								FROM $wpdb->postmeta prop_code
								LEFT JOIN $wpdb->postmeta beds ON beds.post_id = prop_code.post_id
								LEFT JOIN $wpdb->postmeta baths ON baths.post_id = beds.post_id
								WHERE prop_code.meta_key = 'parent_property_code' AND prop_code.meta_value = %s
								AND ((beds.meta_key='fpBeds' AND beds.meta_value = %f) OR (baths.meta_key='fpBaths' AND baths.meta_value = %f)) 
								AND prop_code.post_id != %d
							",
							$query->get('floorplans_of_property'),
							$query->get('floorplans_beds'),
							$query->get('floorplans_baths'),
							$query->get('post__not_in')[0]
						));

						if ($number_of_similar_floorplans >= $mustBeGreaterThen) {
							$filter_by->prop_code=true;		
							$filter_by->beds_or_baths=true;
						}
						else {
							// Testing If Any Other Properties That Have The Given Beds And Baths
							$number_of_similar_floorplans=$wpdb->get_var($wpdb->prepare(
								"
									SELECT COUNT(beds.post_id) as numberOf
									FROM $wpdb->postmeta beds
									LEFT JOIN $wpdb->postmeta baths ON baths.post_id = beds.post_id
									WHERE beds.meta_key='fpBeds' AND beds.meta_value = %f
									AND baths.meta_key='fpBaths' AND baths.meta_value = %f
									AND baths.post_id != %d
								",
								$query->get('floorplans_beds'),
								$query->get('floorplans_baths'),
								$query->get('post__not_in')[0]
							));

							if ($number_of_similar_floorplans > $mustBeGreaterThen) {
								$filter_by->prop_code=false;
								$filter_by->beds_and_baths=true;
							}
							else {
								// Final Stop = Any Other Properties That Have The Given Beds Or Baths
								$filter_by->prop_code=false;
								$filter_by->beds_or_baths=true;
							}
						}
					}

					// Change Current Meta Query To Apply With Result Of $filter_by
					if ($filter_by->prop_code === false) {
						unset($currentMetaQuery['fp_idenification']['parent_property_code']);
					}

					if ($filter_by->beds_or_baths === true) {
						$new_query_meta_item=[
							'relation' => "OR",
							'beds' => $currentMetaQuery['fp_meta']['beds'],
							'baths' => $currentMetaQuery['fp_meta']['baths'],
						];

						unset($currentMetaQuery['fp_meta']['beds']);
						unset($currentMetaQuery['fp_meta']['baths']);
						
						$currentMetaQuery['beds_or_baths'] = $new_query_meta_item;
					}

					// Set Meta Query
					$query->set('meta_query', $currentMetaQuery);
				}
			}
		}

		return $query;
	}
}
