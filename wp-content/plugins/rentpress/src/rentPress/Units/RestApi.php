<?php 
	class rentPress_Units_RestApi {

		public function __construct() {
			$this->property_meta = rentPress_Posts_Meta_Properties::get_instance();
			$this->unit_meta = rentPress_Posts_Meta_Units::get_instance();
		}

		public function run() {
			add_action('rest_api_init', [$this, 'register_rest_routes']);
		}

		public function register_rest_routes() {

			register_rest_route( 'units', '/for_mvc', array(
		        'callback' => [$this, 'rest_mvc_data'],
		        'methods'  => [WP_REST_Server::READABLE, WP_REST_Server::CREATABLE],
		        'args'     => array(
		            'q_args' => array(
		                'required' => false,
		                'default' => [],
		            ),
		        )
		    ) );
		    
		}

		public static function filter_for_mvc($units = [], $search_args = []) {
			global $rentPress_Service;

			foreach ($units as $unit) {
				$Unit = $rentPress_Service['unit_meta']->fromUnit($unit); 
				
				$unit->rent = floatval(number_format($Unit->rent(), 2, '.', ''));

				if (isset($unit->Information->AvailableOn)) {
					$unit->Information->AvailableOnToTime=strtotime($unit->Information->AvailableOn);
				}

				$unit=apply_filters('rentPressUnitDataForMVC', $unit, $search_args);
			}

			return $units;
		}

		public function data_for_the_frontend($search_args = []) {

			$search_args=(object) $search_args;

			$units_query=new rentPress_Units_Query( $search_args );

			$db_results=$units_query->run_query();

			if ($search_args->return != 'floorplan_ids' || $search_args->return != 'property_ids') {
				return self::filter_for_mvc($db_results, $search_args);
			}
			
			return $db_results;
		}

		public function rest_mvc_data(WP_REST_Request $request) {
			$search_args=[];

			if ( $request->get_param( 'q_args' ) ) {
				$search_args=array_merge($request->get_param( 'q_args' ), $search_args);
			}

			return self::data_for_the_frontend($search_args);
		}
	}

