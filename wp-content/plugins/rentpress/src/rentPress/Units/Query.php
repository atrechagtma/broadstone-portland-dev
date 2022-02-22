 <?php 
	class rentPress_Units_Query {
		/*
			QUERY ARGS:
				unit_code : '',

				prop_code : '',

				fpID : '',

				available_by: DATE,
				is_available: TRUE || FALSE,
				dont_show_1970: TRUE

				rent_min: ,
				rent_max: ,

				beds: ==
				min_beds: >=,
				max_beds: <=,

				// Exclude units
				not_in: [<array, of, unit, codes>]

				baths: == 
				min_baths: >=,
				max_baths: <=,
		
				sqft: ==,
				min_baths: >=,
				max_baths: <=,

				order_by: <meta key>:<'asc' or 'desc'>

				return: 'unit_data'  || 'property_ids' || 'floorplan_ids'	
		*/

		public function __construct($args = []) {
			global $wpdb;

			$this->options = new rentPress_Options();

			$this->query_args = (object) array_merge(
				['return' => null],
				(array) $args
			);

			$this->mysql_query_select='';
			$this->mysql_query_from='';
			$this->mysql_query_where=[];
			$this->mysql_query_where_ors=[];
			$this->mysql_query_order=[];
			$this->mysql_query_group_by=[];

			// QUERY SELECT
			// If we are provided a specific return strategy, make sure to apply the appropriate select logic
			if ( isset($this->query_args->return) ) {
				if ( in_array($this->query_args->return, ['floorplan_ids', 'property_ids']) ) {
					$this->mysql_query_select = "SELECT $wpdb->postmeta.post_id ";
				} elseif ( $this->query_args->return == '*' ) {
					$this->mysql_query_select = "SELECT * ";
				}
			} else {
				// If we are defaulting, then just return the unit data set from TLC
				$this->mysql_query_select = "SELECT $wpdb->rp_units.tpl_data ";
			}

			// QUERY FROM
			$this->mysql_query_from = " FROM $wpdb->rp_units ";

			// WHERE FILTERS FROM args			
			if (isset($this->query_args->post_id)) {

				switch (get_post_type($this->query_args->post_id)) {

					case 'properties':
						$this->query_args->prop_code=get_post_meta($this->query_args->post_id, 'prop_code', true);
						break;

					case 'floorplans':
						$this->query_args->fpID=get_post_meta($this->query_args->post_id, 'fpID', true);
						break;

				}

			}

			if ( isset($this->query_args->prop_code) && rentPress_searchHelpers::if_query_var_check($this->query_args->prop_code)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.prop_code = %s ", $this->query_args->prop_code);
			}
			elseif ( isset($this->query_args->property_code) && rentPress_searchHelpers::if_query_var_check($this->query_args->property_code)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.prop_code = %s ", $this->query_args->property_code);
			}
			elseif ( isset($this->query_args->fpID) && rentPress_searchHelpers::if_query_var_check($this->query_args->fpID)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.fpID = %s ", $this->query_args->fpID);
			}			

			// UnitCode
			if ( isset($this->query_args->unit_code) ) {
				if (is_array( $this->query_args->unit_code )) {

					$this->query_args->unit_code = array_filter(
						$this->query_args->unit_code, 
						function($unit_code) { return rentPress_searchHelpers::if_query_var_check($unit_code); }
					);
					
					$this->query_args->unit_code = array_map(
						function($unit_code) use ($wpdb) { return $wpdb->prepare('%s', $unit_code); },
						$this->query_args->unit_code
					);

					$this->mysql_query_where[] = " $wpdb->rp_units.unit_code IN (". join(',', $this->query_args->unit_code) .")";

				}
				elseif (rentPress_searchHelpers::if_query_var_check($this->query_args->unit_code) ) {
					$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.unit_code = %s ", $this->query_args->unit_code);
				}
			}

			// IS_AVAILABLE AND AVAILABLE_BY
			if ( isset($this->query_args->available_by) && rentPress_searchHelpers::if_query_var_check($this->query_args->available_by)) {
				$this->mysql_query_where[] = $wpdb->prepare(
					" $wpdb->rp_units.is_available_on <= %s ", 
					date('Y-m-d', strtotime( $this->query_args->available_by )) 
				);
			}
			elseif ( isset($this->query_args->is_available_soon) && rentPress_searchHelpers::if_query_var_check($this->query_args->is_available_soon)) {
				$days = $this->options->getOption('use_avail_units_before_this_date');

				$this->mysql_query_where[] = $wpdb->prepare(
					" $wpdb->rp_units.is_available_on <= %s ", 
					date('Y-m-d', strtotime('+'. $days .' days')) 
				);
			}
			elseif ( isset($this->query_args->is_available) && rentPress_searchHelpers::if_query_var_check($this->query_args->is_available)) {
				$this->mysql_query_where[] = " $wpdb->rp_units.is_available = TRUE ";
			}
			elseif (isset($this->query_args->is_available) && ($this->query_args->is_available === false || $this->query_args->is_available == 'false')) {
				$this->mysql_query_where[] = " $wpdb->rp_units.is_available = FALSE ";
			}
			
			if (isset($this->query_args->dont_show_1970) && rentPress_searchHelpers::if_query_var_check($this->query_args->dont_show_1970)) {
				$this->mysql_query_where[] = $wpdb->prepare(
					" $wpdb->rp_units.is_available_on > %s ", 
					'1970-01-01'
				);
			}

			// RENT
			if ( isset($this->query_args->min_rent) && rentPress_searchHelpers::if_query_var_check($this->query_args->min_rent)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.rent >= %f ", $this->query_args->min_rent);
			}

			if ( isset($this->query_args->max_rent) && rentPress_searchHelpers::if_query_var_check($this->query_args->max_rent)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.rent <= %f ", $this->query_args->max_rent);
			}

			if ( isset($this->query_args->rent_ors) && rentPress_searchHelpers::if_query_var_check($this->query_args->rent_ors) && is_array($this->query_args->rent_ors)) {

				foreach ($this->query_args->rent_ors as $rent_or_statement) {
					$this->mysql_query_where_ors[]=$rent_or_statement;
				}

			}

			// BEDS
			if ( isset($this->query_args->beds) && is_array($this->query_args->beds)) {
				$this->query_args->beds = array_filter($this->query_args->beds, function($bed) {return rentPress_searchHelpers::if_query_var_check_allow_zero($bed);});

				$this->query_args->beds=array_map(
					function($bed) use ($wpdb) {return $wpdb->prepare(' %f ', $bed);},
					$this->query_args->beds
				);

				$this->mysql_query_where[] = " $wpdb->rp_units.beds IN (". join(',', $this->query_args->beds) .")";
			}
			elseif ( isset($this->query_args->beds) && rentPress_searchHelpers::if_query_var_check_allow_zero($this->query_args->beds)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.beds = %f ", $this->query_args->beds);
			}

			if ( isset($this->query_args->min_beds) && rentPress_searchHelpers::if_query_var_check_allow_zero($this->query_args->min_beds)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.beds >= %f ", $this->query_args->min_beds);
			}

			if ( isset($this->query_args->min_beds) && rentPress_searchHelpers::if_query_var_check_allow_zero($this->query_args->max_beds)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.beds <= %f ", $this->query_args->max_beds);
			}	

			// BATHS
			if ( isset($this->query_args->baths) ) {
				if ( is_array($this->query_args->baths)) {				
					$this->query_args->baths=array_filter($this->query_args->baths, function($bath) {return rentPress_searchHelpers::if_query_var_check_allow_zero($bath);});
					
					$this->query_args->baths=array_map(
						function($bath) use ($wpdb) {return $wpdb->prepare('%f', $bath);},
						$this->query_args->baths
					);

					$this->mysql_query_where[] = " $wpdb->rp_units.baths IN (". join(',', $this->query_args->baths) .")";
				}
				elseif (rentPress_searchHelpers::if_query_var_check_allow_zero($this->query_args->baths)) {
					$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.baths = %f ", $this->query_args->baths);
				}
			}

			if ( isset($this->query_args->min_baths) && rentPress_searchHelpers::if_query_var_check_allow_zero($this->query_args->min_baths)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.baths >= %f ", $this->query_args->min_baths);
			}

			if ( isset($this->query_args->min_baths) && rentPress_searchHelpers::if_query_var_check_allow_zero($this->query_args->max_baths)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.baths <= %f ", $this->query_args->max_baths);
			}	

			// SQFT
			if ( isset($this->query_args->min_baths) && rentPress_searchHelpers::if_query_var_check_allow_zero($this->query_args->min_sqft)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.sqft >= %f ", $this->query_args->min_sqft);
			}

			if ( isset($this->query_args->min_baths) && rentPress_searchHelpers::if_query_var_check($this->query_args->max_sqft)) {
				$this->mysql_query_where[] = $wpdb->prepare(" $wpdb->rp_units.sqft <= %f ", $this->query_args->max_sqft);
			}

			// Rent
			$this->mysql_query_where[]=" $wpdb->rp_units.rent > 0 ";
			
			// Helpers From SELECT
			if (rentPress_searchHelpers::if_query_var_check($this->query_args->return)) {
				switch ($this->query_args->return) {
					case 'property_ids':
						$this->mysql_query_from .= " INNER JOIN $wpdb->postmeta ON $wpdb->rp_units.prop_code = $wpdb->postmeta.meta_value ";
						$this->mysql_query_where[] = " $wpdb->postmeta.meta_key = 'prop_code' ";

						break;

					case 'floorplan_ids':
						$this->mysql_query_from .= " INNER JOIN $wpdb->postmeta ON $wpdb->rp_units.fpID = $wpdb->postmeta.meta_value ";
						$this->mysql_query_where[] = " $wpdb->postmeta.meta_key = 'fpID' ";

						break;
				}
			}
			
			// QUERY ORDER BY
			if ( isset($this->query_args->order_by) && rentPress_searchHelpers::if_query_var_check($this->query_args->order_by)) {
				$sortby=explode(':', $this->query_args->order_by);
				$sortby[1]=strtoupper($sortby[1]);

				switch ($sortby[0]) {
					case 'available_units':
						if (isset($this->query_args->return) && ( $this->query_args->return == 'property_ids' || $this->query_args->return == 'floorplan_ids' )) {
							$this->mysql_query_order[] = " COUNT($wpdb->rp_units.tpl_data) ". $sortby[1];	
							$this->mysql_query_group_by[] = " $wpdb->rp_units.prop_code, $wpdb->postmeta.post_id ";
						}

						break;
					
					case 'available_date':
						if (isset($this->query_args->return) && ( $this->query_args->return == 'property_ids' || $this->query_args->return == 'floorplan_ids' )) {
							$this->mysql_query_order[] = " $wpdb->rp_units.is_available_on ".$sortby[1];	
							$this->mysql_query_group_by[] = " $wpdb->rp_units.prop_code, $wpdb->postmeta.post_id ";
						}

						break;

					case 'rent':
						$this->mysql_query_order[] = " $wpdb->rp_units.rent ".$sortby[1];	
						break;

					default: 
						$this->mysql_query_order[] = " $wpdb->rp_units.". $sortby[0] ." ".$sortby[1];
						break;
				}			
			}

			$this->mysql_query = $this->mysql_query_select . $this->mysql_query_from;
			
			if (count($this->mysql_query_where) > 0 ) {
				$this->mysql_query .= " WHERE ". join(' AND ', $this->mysql_query_where);

				if (count($this->mysql_query_where_ors) > 0) {
					$this->mysql_query .= "AND ( ". join(' OR ', $this->mysql_query_where_ors) ." )";
				}
			} 

			if (isset($this->query_args->not_in) && is_array($this->query_args->not_in) ) {
				$this->query_args->not_in=array_map(function($nin) use ($wpdb) {

					return $wpdb->prepare('%s', $nin);

				}, $this->query_args->not_in);

				$excludedUnits = join(',', $this->query_args->not_in);

				$this->mysql_query .= " AND $wpdb->rp_units.unit_code NOT IN ($excludedUnits)";
			}

			if (count($this->mysql_query_group_by) > 0 ) {
				$this->mysql_query .= " GROUP BY ". join(',', $this->mysql_query_group_by);
			} 

			if (count($this->mysql_query_order) > 0 ) {
				$this->mysql_query .= " ORDER BY ". join(' , ', $this->mysql_query_order);
			}

			if ( isset($this->query_args->offset) && isset($this->query_args->limit) ) {
				if (
					rentPress_searchHelpers::if_query_var_check_allow_zero($this->query_args->offset) 
					&& 
					rentPress_searchHelpers::if_query_var_check($this->query_args->limit)
				) {
					$this->mysql_query.= $wpdb->prepare(
						" LIMIT %d , %d ", 
						$this->query_args->offset,
						$this->query_args->limit
					);
				}
				elseif (rentPress_searchHelpers::if_query_var_check($this->query_args->limit)) {
					$this->mysql_query.= $wpdb->prepare(
						" LIMIT 0 , %d", 
						$this->query_args->limit
					);
				}
			}
		}

		public function run_query() {
			global $wpdb;

			$db_results = $wpdb->get_col($this->mysql_query);

			if ($this->query_args->return != 'floorplan_ids' || $this->query_args->return != 'property_ids') {
				$db_results = array_map(
					function($json_string) {
						return json_decode($json_string);
					},
					$db_results
				);
			
				$db_results = apply_filters('rentPressUnitsJsonFromDB', $db_results, $this->query_args);
			}

			return $db_results;
		}
	}
