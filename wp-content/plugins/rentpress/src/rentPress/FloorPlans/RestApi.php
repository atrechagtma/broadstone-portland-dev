<?php

class rentPress_FloorPlans_RestApi{

	public function run() {
		add_action('rest_api_init', [$this, 'register_rest_routes']);
	}

	public function register_rest_routes() {

		register_rest_route( 'floorplans', '/for_mvc', array(
	        'callback' => [$this, 'rest_mvc_data'],
	        'methods'  => [WP_REST_Server::READABLE, WP_REST_Server::CREATABLE],
	        'args'     => array(
	            'q_args' => array(
	                'required' => false,
	                'default' => [],
	            ),
	        )
	    ) );
		
		register_rest_route( 'floorplans', '/ids_from_query', array(
	        'callback' => [$this, 'rest_ids_for_query'],
	        'methods'  => [WP_REST_Server::READABLE, WP_REST_Server::CREATABLE],
	        'args'     => array(
	            'q_args' => array(
	                'required' => false,
	                'default' => [],
	            ),
	        )
	    ) );

	}

	public static function filter_for_mvc($floorplans = [], $search_args = []) {
		global $rentPress_Service, $wpdb;
		
		foreach ($floorplans as $floorplan) {
			// Only The Data We Need
			$floorplan=rentPress_searchHelpers::mvc_post_format($floorplan);

			$Floorplan = $rentPress_Service['floorplans_meta']->setPostID($floorplan->ID);
			
			// Simplifing The Data
			$floorplan->image=$Floorplan->image();
			
			// Unit Meta
			$mysql_unit_data_points=$wpdb->get_row(
				$wpdb->prepare("
					SELECT 
						MIN(rent) as minRent, MAX(rent) as maxRent,
						MIN(UNIX_TIMESTAMP(is_available_on)) as soonestAvailablity,
						COUNT(tpl_data) as numOfAvailable

					FROM $wpdb->rp_units 
	
					WHERE fpID = %s AND is_available = TRUE", 

					$floorplan->meta_data['fpID']
				)
			);
			
			$floorplan->from_available_units=new stdClass();
				$floorplan->from_available_units->minRent = (int) $mysql_unit_data_points->minRent;
				$floorplan->from_available_units->maxRent = (int) $mysql_unit_data_points->maxRent;
				
				$floorplan->from_available_units->soonestAvailablity = (int) $mysql_unit_data_points->soonestAvailablity;
				$floorplan->from_available_units->numOfAvailable = (int) $mysql_unit_data_points->numOfAvailable;
			
			$floorplan=apply_filters('rentPressFloorplanDataForMVC', $floorplan, $search_args);
		}

		return $floorplans;
	}

	public function rest_ids_for_query(WP_REST_Request $request) {
		$search_args=[
			'fields' => 'ids',
			'post_type' => 'floorplans',
			'post_status' => 'publish',
		];

		if ( $request->get_param( 'q_args' ) ) {
			$search_args=array_merge($request->get_param( 'q_args' ), $search_args);
		}

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
			'post_type' => 'floorplans',
			'post_status' => 'publish',
		];
	
		$param_args=$request->get_param('q_args');

		if ( isset($param_args) && is_array($param_args) ) {
			$search_args=(array) array_merge($param_args, $search_args);
			
			if (isset($param_args['the_filtered_args'])) {
				if (isset($param_args['the_filtered_args']['floorplans_sortedby'])) {
					$search_args['floorplans_sortby']=$param_args['the_filtered_args']['floorplans_sortedby'];
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
		
		$floorplans=get_posts($search_args);

		if (isset($param_args['the_filtered_args'])) {
			$search_args=array_merge($param_args['the_filtered_args'], $search_args);
		}

		$floorplans=get_posts($search_args);
		
		return self::filter_for_mvc($floorplans, $search_args);		
	}
}