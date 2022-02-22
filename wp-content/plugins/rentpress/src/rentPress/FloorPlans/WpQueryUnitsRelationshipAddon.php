<?php

class rentPress_FloorPlans_WpQueryUnitsRelationshipAddon{	
	public function __construct() {
		//$this->property_meta = rentPress_Posts_Meta_Properties::get_instance();
		$this->options = new rentPress_Options();

		$this->allowed_sorts_for_fp_to_units=[
			'available_units', 'from_available_units.numOfAvailable',
			'available_date', 'from_available_units.soonestAvailablity',
			'rent'
		];
	}

	public function run() {
		add_action('pre_get_posts', [$this, 'pre_get_floorplans'], 40, 1);
		
		add_filter('posts_distinct', [$this, 'wp_query_distinct'], 40, 2);
		add_filter('posts_join', [$this, 'wp_query_inner_joins'], 40, 2);
		add_filter('posts_where', [$this, 'wp_query_where'], 40, 2);
		add_filter('posts_orderby', [$this, 'wp_query_orderby'], 40, 2);
		add_filter('posts_groupby', [$this, 'wp_query_groupby'], 40, 2);
	}

	public function wp_query_distinct($distinct, $query) {
		if ( $query->get('post_type') == RENTPRESS_FLOORPLANS_CPT && ! $query->is_singular) {
			$distinct="DISTINCT";
		}
	
		return $distinct;
	}

	public function wp_query_inner_joins($join, $query) {
		global $wpdb;
		
		if ($query->get('post_type') == RENTPRESS_FLOORPLANS_CPT && ! $query->is_singular) {		

			if (
				rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('floorplans_min_rent'))
				||
				rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('floorplans_max_rent'))
				||
				is_array($query->get('floorplans_rent_ors'))
				||
				rentPress_searchHelpers::if_query_var_check($query->get('floorplans_available_by'))
				||
				rentPress_searchHelpers::if_query_var_check( $query->get('floorplans_with_soon_available_units') )
				||
				rentPress_searchHelpers::if_query_var_is_only_true($query->get('floorplans_with_available_units'))
				||
				(
					rentPress_searchHelpers::if_query_var_is_sortby( $query->get('floorplans_sortedby') )
					&&
					in_array( 
						rentPress_searchHelpers::parse_sortby(
			          		$query->get('floorplans_sortedby'),
			          		rentPress_FloorPlans_SearchAddon::$orderby_same_same
			          	)[0], 
			          	$this->allowed_sorts_for_fp_to_units 
			        )
				)
			) {
				$join .= " INNER JOIN $wpdb->postmeta fpID_for_units ON fpID_for_units.post_id = {$wpdb->posts}.ID ";
				$join .= " INNER JOIN $wpdb->rp_units units_of_fp ON units_of_fp.fpID = fpID_for_units.meta_value ";
			}
		}

		return $join;
	}

	public function wp_query_where($where, $query) {
		global $wpdb;

		if ($query->get('post_type') == RENTPRESS_FLOORPLANS_CPT && ! $query->is_singular) {

			$wheresRelatedToUnits=[];

			if (rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('floorplans_min_rent'))) {
				$wheresRelatedToUnits[] = $wpdb->prepare(" AND units_of_fp.rent >= %f ", $query->get('floorplans_min_rent'));
			}

			if (rentPress_searchHelpers::if_query_var_check($query->get('floorplans_max_rent'))) {
				$wheresRelatedToUnits[] = $wpdb->prepare(" AND units_of_fp.rent <= %f ", $query->get('floorplans_max_rent'));
			}                                                                                                                

			if (is_array($query->get('floorplans_rent_ors'))) {
				$floorplans_rent_or_statements=[];

				foreach ($query->get('floorplans_rent_ors') as $rawRentVal) {
					$properties_rent_or_statements=rentPress_searchHelpers::wpdb_prepare_range_or_min_or_max(
						$rawRentVal,
						'units_of_fp.rent'
					);
				}

				$wheresRelatedToUnits[] = "AND ( ". join(' OR ', $floorplans_rent_or_statements) ." )";
			}
			
			if (rentPress_searchHelpers::if_query_var_check( $query->get('floorplans_available_by') )) {
				$wheresRelatedToUnits[] = $wpdb->prepare(
					" AND units_of_fp.is_available_on <= %s ", 
					date('Y-m-d', strtotime( $query->get('floorplans_available_by') )) 
				);
			}
			elseif (rentPress_searchHelpers::if_query_var_check( $query->get('floorplans_with_soon_available_units') )) {

				$days = $this->options->getOption('use_avail_units_before_this_date');

				$wheresRelatedToUnits[] = $wpdb->prepare(
					" AND units_of_fp.is_available_on <= %s ", 
					date('Y-m-d', strtotime('+'. $days .' days')) 
				);

			}
			elseif (rentPress_searchHelpers::if_query_var_is_only_true($query->get('floorplans_with_available_units'))) {
				$wheresRelatedToUnits[] =  " AND units_of_fp.is_available = TRUE ";
			}

			// Apply Units Wheres
			if (count($wheresRelatedToUnits) > 0) {
				$where .= " AND fpID_for_units.meta_key = 'fpID' AND units_of_fp.rent > 0 ";
		
				$where .= join(' ', $wheresRelatedToUnits);
			}
		}
		
		return $where;
	}

	public function wp_query_orderby($orderby, $query) {

		if ($query->get('post_type') == RENTPRESS_FLOORPLANS_CPT && ! $query->is_singular) {
			
			if (rentPress_searchHelpers::if_query_var_check( $query->get('floorplans_sortedby') )) {

				$sort_by=rentPress_searchHelpers::parse_sortby(
	          		$query->get('floorplans_sortedby'),
	          		rentPress_FloorPlans_SearchAddon::$orderby_same_same
	          	);

				switch ($sort_by[0]) {
					case 'available_date':
						$orderby=" units_of_fp.is_available_on {$sort_by[1]} ";
						break;

					case 'available_units':
						$orderby=" COUNT(units_of_fp.tpl_data) {$sort_by[1]} ";
						break;

					case 'rent':
						$orderby=" units_of_fp.rent {$sort_by[1]} ";
						
						break;
				}

			}

		}

		return $orderby;

	}

	public function pre_get_floorplans($query) {
		if ($query->get('post_type') == RENTPRESS_FLOORPLANS_CPT && ! $query->is_singular) {
			$floorplans_meta_query=[];

			// No Availalbity 
			if (rentPress_searchHelpers::if_query_var_is_only_false($query->get('floorplans_with_available_units'))) {
				$floorplans_meta_query['unavailable'] = [
					'relation' => 'OR',

					[
						'type' => 'NUMERIC',
						'key' => 'fpUnitsCaptured',
						'value' => 0
					],

					[
						'type' => 'NUMERIC',
						'key' => 'fpAvailUnitCount',
						'value' => 0,
					]

				];
			}

			// Setting Meta Query
			rentPress_searchHelpers::apply_meta_query_to_query($query, $floorplans_meta_query, 'units');
		}

		return $query;
	}

	public function wp_query_groupby($groupby, $query) {
		global $wpdb;

		if ($query->get('post_type') == RENTPRESS_FLOORPLANS_CPT && ! $query->is_singular) {
			
			if (rentPress_searchHelpers::if_query_var_check( $query->get('floorplans_sortedby') )) {

				$sort_by=rentPress_searchHelpers::parse_sortby(
	          		$query->get('floorplans_sortedby'),
	          		rentPress_FloorPlans_SearchAddon::$orderby_same_same
	          	);

	          	if ($sort_by[0] == 'available_date' || $sort_by[0] == 'available_units' ) {
	          		$groupby=" units_of_fp.fpID, $wpdb->posts.ID ";
	          	} 

			}
		}

		return $groupby;
	}
}
