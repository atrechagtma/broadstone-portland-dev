<?php 

	class rentPress_Neighborhoods_RestApi {

		public function run() {
			
			add_action('rest_api_init', [$this, 'register_rest_routes']);
		}

		public static function filter_for_mvc($neighborhoods = [], $search_args = []) {
			foreach ($neighborhoods as $neighborhood) {
				$neighborhood=rentPress_searchHelpers::mvc_post_format($neighborhood);

				$neighborhood=apply_filters('rentPressNeighborhoodDataForMvc', $neighborhood, $search_args);
			}

			return $neighborhoods;
		}

		public function register_rest_routes() {

			register_rest_route( 'neighborhoods', '/for_mvc', array(
		        'callback' => [$this, 'rest_mvc_data'],
		        'methods'  => [WP_REST_Server::READABLE, WP_REST_Server::CREATABLE],
		        'args'     => array(
		            'q_args' => array(
		                'required' => false,
		                'default' => [],
		            ),
		        )
		    ) );
			
			register_rest_route( 'neighborhoods', '/ids_from_query', array(
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

		public function rest_ids_for_query(WP_REST_Request $request) {
			$search_args=[
				'fields' => 'ids',
				'post_type' => 'neighborhoods',
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
				'post_type' => 'neighborhoods',
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
			

			return self::filter_for_mvc(get_posts($search_args), $search_args);
		}


	}