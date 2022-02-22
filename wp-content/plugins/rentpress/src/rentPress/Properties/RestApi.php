<?php 
class rentPress_Properties_RestApi{

	public function run() {
		add_action('rest_api_init', [$this, 'register_rest_routes']);
	}

	public function register_rest_routes() {

		register_rest_route( 'properties', '/for_mvc', array(
	        'callback' => [$this, 'rest_mvc_data'],
	        'methods'  => [WP_REST_Server::READABLE, WP_REST_Server::CREATABLE],
	        'args'     => array(
	            'q_args' => array(
	                'required' => false,
	                'default' => [],
	            ),
	        )
	    ) );
		
		register_rest_route( 'properties', '/ids_from_query', array(
	        'callback' => [$this, 'rest_filtered_ids'],
	        'methods'  => [WP_REST_Server::READABLE, WP_REST_Server::CREATABLE],
	        'args'     => array(
	            'q_args' => array(
	                'required' => false,
	                'default' => [],
	            ),
	        )
	    ) );

	}

	public static function filter_for_mvc($properties = [], $search_args = []) {
		global $rentPress_Service, $wpdb;

		foreach ($properties as $property) {
			// Only The Data We Need
			$property=rentPress_searchHelpers::mvc_post_format($property);

			$Property = $rentPress_Service['properties_meta']->setPostID($property->ID);
			
			// Simplifing The Data
			$property->rent = floatval($Property->rent(null));
			$property->image = $Property->image(null, 'https://placehold.it/600x400');
		
			if ( 
				isset($property->meta_data['post_type_connected_to_neighborhoods']) 
				&& 
				! is_numeric($property->meta_data['post_type_connected_to_neighborhoods'])
				&&
				(int) $property->meta_data['post_type_connected_to_neighborhoods'] != 0
			) { 
				$theNeighborhood=$wpdb->get_row("
			        SELECT ID, post_title, guid FROM {$wpdb->posts} p
			        WHERE p.ID = ". $property->meta_data['post_type_connected_to_neighborhoods'] ."
			        LIMIT 1
			    ");

				$property->neighborhood_id=(int)$theNeighborhood->ID;
				$property->neighborhood_link=htmlspecialchars_decode($theNeighborhood->guid);
				$property->neighborhood_title=$theNeighborhood->post_title;
			}

			if (isset($property->meta_data['propLatitude']) && isset($property->meta_data['propLongitude'])) {

				$property->latitude = $property->meta_data['propLatitude'];
				$property->longitude = $property->meta_data['propLongitude'];

				unset($property->meta_data['propLatitude']);
				unset($property->meta_data['propLongitude']);
			}

			elseif (isset($property->meta_data['prop_coords'])) {
				$property->meta_data['prop_coords']=substr($property->meta_data['prop_coords'], 1, -1);
				$property->meta_data['prop_coords']=str_replace(' ', '', $property->meta_data['prop_coords']);
			
				$explode_position=explode(',', $property->meta_data['prop_coords']);

				$property->latitude=$explode_position[0];
				$property->longitude=$explode_position[1];

				unset($property->meta_data['prop_coords']);
			}


			// Distance 
			if (
				isset($search_args['properties_within_radius'])
				&&
				isset($search_args['properties_within_distance_from']['lat'])
				&&
				isset($search_args['properties_within_distance_from']['lng'])
			) {
				$property->distance=rentPress_searchHelpers::get_distance_between(
					$search_args['properties_within_distance_from']['lat'], 
					$search_args['properties_within_distance_from']['lng'], 

					$property->latitude,
					$property->longitude,
					
					'M'									
					// @ToDo $search_args['properties_within_distance_unit'] , ALSO NEEDS DEFAULT VALUES TOO
				);
			}

			//$property->meta_data['propUnits']=$Property->units();

			// Units Metas
			$mysql_available_unit_data_points=$wpdb->get_row(
				$wpdb->prepare("
					SELECT 
						MIN(rent) as minRent, MAX(rent) as maxRent,
						MIN(beds) as minBeds, MAX(beds) as maxBeds,
						MIN(UNIX_TIMESTAMP(is_available_on)) as soonestAvailablity,
						COUNT(tpl_data) as numOfAvailable

					FROM $wpdb->rp_units 
	
					WHERE prop_code = %s AND is_available = TRUE AND rent > 0", 

					$property->meta_data['prop_code']
				)
			);

			$mysql_unit_data_points=$wpdb->get_row(
				$wpdb->prepare(
					"
						SELECT MIN(UNIX_TIMESTAMP(is_available_on)) as soonestAvailablity
						FROM $wpdb->rp_units 	
						WHERE prop_code = %s AND rent > 0
					", 

					$property->meta_data['prop_code']
				)
			);
			
			$property->availableAtEachBedLevel=$Property->fetchAvailableAtEachBedLevel();				
			$property->soonestAvailableUnitDate=(int) $mysql_unit_data_points->soonestAvailablity;

			$property->from_available_units=new stdClass();
				$property->from_available_units->minRent = (int) $mysql_available_unit_data_points->minRent;
				$property->from_available_units->maxRent = (int) $mysql_available_unit_data_points->maxRent;
				
				$property->from_available_units->minBeds = (int) $mysql_available_unit_data_points->minBeds;
				$property->from_available_units->maxBeds = (int) $mysql_available_unit_data_points->maxBeds;

				$property->from_available_units->soonestAvailablity = (int) $mysql_available_unit_data_points->soonestAvailablity;
				$property->from_available_units->numOfAvailable = (int) $mysql_available_unit_data_points->numOfAvailable;
		
			$property=apply_filters('rentPressPropertyDataForMvc', $property, $search_args);
		}
	
		return $properties;
	}


	public function rest_filtered_ids(WP_REST_Request $request) {
		$search_args=[
			'fields' => 'ids',
			'post_type' => 'properties',
			'post_status' => 'publish'
		];

		if ( $request->get_param( 'q_args' ) ) {
			$search_args=array_merge($request->get_param( 'q_args' ), $search_args);
		}

		$search_args=array_merge(
			[
				'orderby' => 'title',
				'order' => "ASC",
			],
			$search_args
		);
		
		$search_args=array_merge(
			[
				'posts_per_page' => -1,
			],
			$search_args
		);
		
		return get_posts($search_args);
	}

	public function rest_mvc_data(WP_REST_Request $request) {
		$search_args=[
			'post_type' => 'properties',
			'post_status' => 'publish',
		];
	
		$param_args=$request->get_param('q_args');
		
		if ( isset($param_args) && is_array($param_args) ) {
			$search_args=(array) array_merge($param_args, $search_args);

			if (isset($param_args['the_filtered_args'])) {
				$filtering_by_distance=( 
					rentPress_searchHelpers::if_query_var_check( $param_args['the_filtered_args']['properties_within_radius'] ) 
					&& 
					$param_args['the_filtered_args']['properties_within_distance_from']
					&& 
					isset($param_args['the_filtered_args']['properties_within_distance_from']['lat']) 
					&& 
					isset($param_args['the_filtered_args']['properties_within_distance_from']['lng'])
				);

				if ($filtering_by_distance && isset($param_args['post__in'])) {
					$search_args['orderby']='post__in';				
				}
				elseif (isset($param_args['the_filtered_args']['properties_sortedby'])) {
					$search_args['properties_sortedby']=$param_args['the_filtered_args']['properties_sortedby'];
				}
				elseif (isset($param_args['the_filtered_args']['orderby'])) {
					$search_args['orderby']=$param_args['the_filtered_args']['orderby'];

					if (isset($param_args['the_filtered_args']['order'])) {
						$search_args['order']=$param_args['the_filtered_args']['order'];
					}
				}

				unset($search_args['the_filtered_args']);
			}
		}

		$search_args=array_merge(
			[
				'posts_per_page' => -1,
			],
			$search_args
		);

		$propertiesQuery=new WP_Query($search_args);

		$properties=$propertiesQuery->posts;

		$search_args=$propertiesQuery->query_vars;

		if (isset($param_args['the_filtered_args'])) {
			$search_args=array_merge($param_args['the_filtered_args'], $search_args);
		}

		$data=self::filter_for_mvc($properties, $search_args);

		return $data;
	}

}