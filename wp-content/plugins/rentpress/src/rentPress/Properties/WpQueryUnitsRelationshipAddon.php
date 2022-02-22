<?php
/** Note: Wheres, orders, joins */

class rentPress_Properties_WpQueryUnitsRelationshipAddon{

	public function __construct() {
		//$this->property_meta = rentPress_Posts_Meta_Properties::get_instance();
		$this->options = new rentPress_Options();

		$this->allowed_sorts_for_units_to_prop=[
			'available_date', 'from_available_units.soonestAvailablity',
		];
	}

	public function run() {
		add_filter('posts_join', [$this, 'wp_query_inner_joins'], 50, 2);
		add_filter('posts_where', [$this, 'wp_query_where'], 50, 2);
		add_filter('posts_orderby', [$this, 'wp_query_orderby'], 50, 2);
		add_filter( 'posts_groupby', [$this, 'wp_query_groupby'], 50, 2);
	}
	
	public function wp_query_inner_joins($join, $query) {
		global $wpdb;
		
		if ($query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {		

			if (
				rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_min_rent'))
				||
				rentPress_searchHelpers::if_query_var_check($query->get('properties_max_rent'))
				||
				is_array($query->get('properties_rent_ors'))
				||
				rentPress_searchHelpers::if_query_is_searching_for_available_units($query)
				||
				(
					rentPress_searchHelpers::if_query_is_searching_for_available_units($query)
					&&
					(
						rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_beds'))
						||
						rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_min_beds'))
						||
						rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_max_beds'))
						||
						rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_baths'))
						||
						rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_min_baths'))
						||
						rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_max_baths'))
						||
						rentPress_searchHelpers::if_query_var_check($query->get('properties_min_sqft'))
						||
						rentPress_searchHelpers::if_query_var_check($query->get('properties_max_sqft'))
					)
				)
				||
				(
					rentPress_searchHelpers::if_query_var_is_sortby( $query->get('properties_sortedby') )
					&&
					in_array(
						rentPress_searchHelpers::parse_sortby(
			          		$query->get('properties_sortedby'),
			          		rentPress_Properties_SearchAddon::$orderby_same_same
			          	)[0], 
			          	$this->allowed_sorts_for_units_to_prop
			        )
				)
			) {
				$join .= " INNER JOIN $wpdb->postmeta propCode_for_units ON propCode_for_units.post_id = {$wpdb->posts}.ID ";
				$join .= " INNER JOIN $wpdb->rp_units units_of_prop ON units_of_prop.prop_code = propCode_for_units.meta_value ";
			}

		}

		return $join;
	}

	public function wp_query_where($where, $query) {
		global $wpdb;

		if ($query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {

			$wheresRelatedToUnits=[];

			// Rent
			if (rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_min_rent'))) {
				$wheresRelatedToUnits[] = $wpdb->prepare(" AND units_of_prop.rent >= %f ", $query->get('properties_min_rent'));
			}

			if (rentPress_searchHelpers::if_query_var_check($query->get('properties_max_rent'))) {
				$wheresRelatedToUnits[] = $wpdb->prepare(" AND units_of_prop.rent <= %f ", $query->get('properties_max_rent'));
			}                                                                                                                

			if (is_array($query->get('properties_rent_ors'))) {
				$properties_rent_or_statements=[];

				foreach ($query->get('properties_rent_ors') as $rawRentVal) {
					$properties_rent_or_statements[]=rentPress_searchHelpers::wpdb_prepare_range_or_min_or_max(
						'units_of_prop.rent',
						$rawRentVal
					);
				}
			

				if (count($properties_rent_or_statements) >= 1) {
					$wheresRelatedToUnits[] = "AND ( ". join(' OR ', $properties_rent_or_statements) ." )";
				}
			}
			
			// Availablity
			if (rentPress_searchHelpers::if_query_var_check( $query->get('properties_available_by') )) {
				$wheresRelatedToUnits[] = $wpdb->prepare(
					" AND units_of_prop.is_available_on <= %s ", 
					date('Y-m-d', strtotime( $query->get('properties_available_by') )) 
				);
			}
			elseif (rentPress_searchHelpers::if_query_var_check( $query->get('properties_with_soon_available_units') )) {

				$days = $this->options->getOption('use_avail_units_before_this_date');

				$wheresRelatedToUnits[] = $wpdb->prepare(
					" AND units_of_prop.is_available_on <= %s ", 
					date('Y-m-d', strtotime('+'. $days .' days')) 
				);

			}
			elseif (rentPress_searchHelpers::if_query_var_is_only_true($query->get('preoprties_with_available_units'))) {
				$wheresRelatedToUnits[] =  " AND units_of_prop.is_available = TRUE ";
			}

			// Search Fast When Searching For Available Units
			if (rentPress_searchHelpers::if_query_is_searching_for_available_units($query)) {
				
				// BEDS
				if (is_array($query->get('properties_beds'))) {
					$beds=array_filter(
						$query->get('properties_beds'), 
						function($bed) {return rentPress_searchHelpers::if_query_var_check_allow_zero($bed);}
					);

					$beds=array_map(
						function($bed) use ($wpdb) {return $wpdb->prepare(' %f ', $bed);},
						$beds
					);

					if (count($beds) >= 1) {
						$wheresRelatedToUnits[] = " AND units_of_prop.beds IN (". join(',', $beds) .")";
					}

				}
				elseif (rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_beds'))) {
					$wheresRelatedToUnits[] = $wpdb->prepare(" AND units_of_prop.beds = %f ", $query->get('properties_beds'));
				}

				if ( rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_min_beds'))) {
					$wheresRelatedToUnits[] = $wpdb->prepare(" AND units_of_prop.beds >= %f ", $query->get('properties_min_beds'));
				}

				if ( rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_max_beds'))) {
					$wheresRelatedToUnits[] = $wpdb->prepare(" AND units_of_prop.beds <= %f ", $query->get('properties_max_beds'));
				}	

				// BATHS
				if ( is_array($query->get('properties_baths'))) {				
					$baths=array_filter(
						$query->get('properties_baths'), 
						function($bath) {return rentPress_searchHelpers::if_query_var_check_allow_zero($bath);
					});
					
					$baths=array_map(
						function($bath) use ($wpdb) {return $wpdb->prepare(' %f ', $bath);},
						$baths
					);

					if (count($baths) >=1 ) {
						$wheresRelatedToUnits[] = " units_of_prop.baths IN (". join(',', $baths) .")";
					}

				}
				elseif (rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_baths'))) {
					$wheresRelatedToUnits[] = $wpdb->prepare(" units_of_prop.baths = %f ", $query->get('properties_baths'));
				}

				if ( rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_min_baths'))) {
					$wheresRelatedToUnits[] = $wpdb->prepare(" units_of_prop.baths >= %f ", $query->get('properties_min_baths'));
				}

				if ( rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_max_baths'))) {
					$wheresRelatedToUnits[] = $wpdb->prepare(" units_of_prop.baths <= %f ", $query->get('properties_max_baths'));
				}	

				// SQFT
				if ( rentPress_searchHelpers::if_query_var_check_allow_zero($query->get('properties_min_sqft'))) {
					$wheresRelatedToUnits[] = $wpdb->prepare(" units_of_prop.sqft >= %f ", $query->get('properties_min_sqft'));
				}

				if ( rentPress_searchHelpers::if_query_var_check($query->get('properties_max_sqft'))) {
					$wheresRelatedToUnits[] = $wpdb->prepare(" units_of_prop.sqft <= %f ", $query->get('properties_max_sqft'));
				}

			}
			
			// Apply Units Wheres
			if (count($wheresRelatedToUnits) > 0) {
				$where .= " AND propCode_for_units.meta_key = 'prop_code' AND units_of_prop.rent > 0 ";
					
				$where .= join(' ', $wheresRelatedToUnits);
			}

		}
		
		return $where;
	}

	public function wp_query_orderby($orderby, $query) {

		if ($query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {
			
			if (rentPress_searchHelpers::if_query_var_check( $query->get('properties_sortedby') )) {

				$sort_by=rentPress_searchHelpers::parse_sortby(
	          		$query->get('properties_sortedby'),
	          		rentPress_Properties_SearchAddon::$orderby_same_same
	          	);

				switch ($sort_by[0]) {
					case 'available_date':
						$orderby=" units_of_prop.is_available_on {$sort_by[1]} ";
						break;

					case 'rent':
						$orderby=" units_of_prop.rent {$sort_by[1]} ";
						
						break;
				}

			}

		}

		return $orderby;
	}

	public function wp_query_groupby($groupby, $query) {
		global $wpdb;

		if ($query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {
			
			if (rentPress_searchHelpers::if_query_var_check( $query->get('properties_sortedby') )) {

				$sort_by=rentPress_searchHelpers::parse_sortby(
	          		$query->get('properties_sortedby'),
	          		rentPress_Properties_SearchAddon::$orderby_same_same
	          	);

	          	if ($sort_by[0] == 'available_date' ) {
	          		$groupby=" units_of_prop.prop_code, $wpdb->posts.ID ";
	          	} 

			}
		}

		return $groupby;
	}

}