<?php 
/** Note: Distance & floor plan connections */

class rentPress_Properties_WpQueryFloorplansRelationshipAddon{
	public static $query_vars_related_to_fp=[
		'properties_min_sqft' => 'fpMinSQFT', 
		'properties_max_sqft' => 'fpMinSQFT',
	];
	
	public static $query_vars_related_to_fp_that_allow_zero=[
		'properties_beds' => 'fpBeds', 
		'properties_min_beds' => 'fpBeds', 
		'properties_max_beds' => 'fpBeds',
		
		'properties_baths' => 'fpBaths', 
		'properties_min_baths' => 'fpBaths', 
		'properties_max_baths' => 'fpBaths',
	];

	public static $query_vars_related_to_availablity=[
		'properties_beds', 'properties_min_beds', 'properties_max_beds',
		
		'properties_baths', 'properties_min_baths', 'properties_max_baths',

		'properties_min_sqft', 'properties_max_sqft'
	];

	public static $prefix_of_fp_meta_table='table_';

	public function run() {
		add_action('pre_get_posts', [$this, 'pre_get_properties'], 40, 1);

		add_filter('posts_distinct', [$this, 'wp_query_distinct'], 40, 2);
		// add_filter('posts_fields', [$this, 'wp_query_posts_fields'], 40, 2);
		add_filter('posts_join', [$this, 'wp_query_inner_joins'], 40, 2);
		add_filter('posts_where', [$this, 'wp_query_where'], 40, 2);
		// add_filter('posts_orderby', [$this, 'wp_query_orderby'], 40, 2);
	}
	
	/*
		public function wp_query_posts_fields($fields, $query) {
			global $wpdb;

			if ( $query->get('post_type') == RENTPRESS_PROPERTIES_CPT ) {


			}
			
			return $fields;
		}
	*/

	public function wp_query_distinct($distinct, $query) {
		if ( $query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {
			$distinct="DISTINCT";
		}
	
		return $distinct;
	}

	public function wp_query_inner_joins($join, $query) {
		global $wpdb;
		
		if ($query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {
			$joinsToAdd=[];

			foreach (self::$query_vars_related_to_fp as $query_var => $fp_meta_key) {
				if (rentPress_searchHelpers::if_query_var_check( $query->get($query_var) )) {
					$mockTableName=self::$prefix_of_fp_meta_table.$query_var;
					
					if (rentPress_searchHelpers::if_query_is_searching_for_available_units($query)) {
						if (! in_array($query_var, self::$query_vars_related_to_availablity)) {
							$joinsToAdd[] = " LEFT JOIN $wpdb->postmeta $mockTableName ON {$mockTableName}.post_id = fp_of_prop.post_id ";	
						}
					}
					else {
						$joinsToAdd[] = " LEFT JOIN $wpdb->postmeta $mockTableName ON {$mockTableName}.post_id = fp_of_prop.post_id ";	
					}	
															
				}				
			}
			
			foreach (self::$query_vars_related_to_fp_that_allow_zero as $query_var => $fp_meta_key) {
				if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get($query_var) )) {
					$mockTableName=self::$prefix_of_fp_meta_table.$query_var;
					
					if (rentPress_searchHelpers::if_query_is_searching_for_available_units($query)) {
						if (! in_array($query_var, self::$query_vars_related_to_availablity)) {
							$joinsToAdd[] = " LEFT JOIN $wpdb->postmeta $mockTableName ON {$mockTableName}.post_id = fp_of_prop.post_id ";
						}
					}
					else {
						$joinsToAdd[] = " LEFT JOIN $wpdb->postmeta $mockTableName ON {$mockTableName}.post_id = fp_of_prop.post_id ";	
					}
				}
			}

			if (is_array($query->get('properties_sqft_ors'))) {

				$joinsToAdd[] = "LEFT JOIN $wpdb->postmeta fp_sqft ON fp_sqft.post_id = fp_of_prop.post_id ";

			}

			// Really Joining Things Here!			
			if (count($joinsToAdd) > 0) {
				$join .= " LEFT JOIN $wpdb->postmeta prop_code_for_fp ON prop_code_for_fp.post_id = {$wpdb->posts}.ID ";
				$join .= " LEFT JOIN $wpdb->postmeta fp_of_prop ON fp_of_prop.meta_value = prop_code_for_fp.meta_value ";
				
				foreach ($joinsToAdd as $a_joint) {
					$join.=$a_joint;
				}
			}
		}

		return $join;
	}

	public static function wpdb_prepare_from_query_var_of_prop_to_fp($query, $query_var, $fp_meta_key) {
		global $wpdb;

		$mockTableName=self::$prefix_of_fp_meta_table.$query_var;

		if (strpos('_min', $query_var) !== false) {
			return $wpdb->prepare(
				" AND {$mockTableName}.meta_key = %s AND {$mockTableName}.meta_value >= %f", 
				$fp_meta_key,
				$query->get($query_var) 
			);
		}
		elseif (strpos('_max', $query_var) !== false) {
			return $wpdb->prepare(
				" AND {$mockTableName}.meta_key = %s AND {$mockTableName}.meta_value <= %f", 
				$fp_meta_key,
				$query->get($query_var)
			);
		}
		else {
			if (is_array($query->get($query_var))) {
				$checked_values=array_filter(
					$query->get($query_var), 
					function($Avalue) {return rentPress_searchHelpers::if_query_var_check_allow_zero($Avalue);}
				);

				$checked_values=array_map(
					function($Avalue) use ($wpdb) {
						if (is_numeric($Avalue)) {
							return $wpdb->prepare(' %f ', $Avalue);
						}
						else {
							return $wpdb->prepare(' %s ', $Avalue);
						}
					},
					$checked_values
				);
			
				if (isset($checked_values) && count($checked_values) >= 1) {
					return $wpdb->prepare(
						" AND {$mockTableName}.meta_key = %s AND {$mockTableName}.meta_value IN (". join(',', $checked_values) .") ",
						$fp_meta_key
					);
				}
			}
			else {
				return $wpdb->prepare(
					" AND {$mockTableName}.meta_key = %s AND {$mockTableName}.meta_value = %s", 
					$fp_meta_key,
					$query->get($query_var)
				);				
			}
		}
	}
	
	public function wp_query_where($where, $query) {
		global $wpdb;

		if ($query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {
			$wheresRelatedToFp=[];

			foreach (self::$query_vars_related_to_fp as $query_var => $fp_meta_key) {
				if (rentPress_searchHelpers::if_query_var_check( $query->get($query_var) )) {
					if (rentPress_searchHelpers::if_query_is_searching_for_available_units($query)) {
						if (! in_array($query_var, self::$query_vars_related_to_availablity)) {
							$wheresRelatedToFp[]=self::wpdb_prepare_from_query_var_of_prop_to_fp($query, $query_var, $fp_meta_key);
						}
					}
					else {
						$wheresRelatedToFp[]=self::wpdb_prepare_from_query_var_of_prop_to_fp($query, $query_var, $fp_meta_key);				
					}
				}
			}

			foreach (self::$query_vars_related_to_fp_that_allow_zero as $query_var => $fp_meta_key) {
				if (rentPress_searchHelpers::if_query_var_check_allow_zero( $query->get($query_var) )) {
					if (rentPress_searchHelpers::if_query_is_searching_for_available_units($query)) {
						if (! in_array($query_var, self::$query_vars_related_to_availablity)) {
							$wheresRelatedToFp[]=self::wpdb_prepare_from_query_var_of_prop_to_fp($query, $query_var, $fp_meta_key);
						}
					}
					else {
						$wheresRelatedToFp[]=self::wpdb_prepare_from_query_var_of_prop_to_fp($query, $query_var, $fp_meta_key);
					}
				}
			}
			
			if (is_array($query->get('properties_sqft_ors'))) {
				$properties_sqft_or_statements=[];

				foreach ($query->get('properties_sqft_ors') as $rawValue) {
					$properties_sqft_or_statements[]=rentPress_searchHelpers::wpdb_prepare_range_or_min_or_max(
						$rawValue,
						'fp_sqft.meta_value'
					);
				}

				$wheresRelatedToFp[] = " fp_sqft.meta_key = 'fpMinSQFT' AND ( ". join(' OR ', $properties_sqft_or_statements) ." )";
			}

			// Really Appling Here
			if (count($wheresRelatedToFp) >= 1) {
				$where .= " AND ( prop_code_for_fp.meta_key = 'prop_code' AND fp_of_prop.meta_key = 'parent_property_code' )";

				$where .= join(' ', $wheresRelatedToFp);
			}

		}
		
		return $where;
	}

	/*
		public function wp_query_orderby($orderby, $query) {

			if ($query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {

			}

			return $orderby;

		}
	*/

	public function pre_get_properties($query) {
		if ($query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {
			
			$properties_meta_query=[];

			if (rentPress_searchHelpers::if_query_var_is_only_false($query->get('properties_with_available_units'))) {

				// $units_properties_query_args['is_available']=false;

				$properties_meta_query['unavailable'] = [
					'relation' => 'OR',

					[
						'type' => 'NUMERIC',
						'key' => 'propUnitsCaptured',
						'value' => 0
					],

					[
						'type' => 'NUMERIC',
						'key' => 'propUnitsAvailable',
						'value' => 0,
					]
				];
			}
		
			rentPress_searchHelpers::apply_meta_query_to_query($query, $properties_meta_query, 'units');
		}

		return $query;
	}
}
