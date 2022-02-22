<?php 

	class rentPress_Neighborhoods_SearchAddon {

		public function run() {
			add_filter('manage_neighborhoods_posts_columns', [$this, 'manage_posts_columns'], 9, 1);
			add_action('manage_neighborhoods_posts_custom_column', [$this, 'columns_content'], 10, 2);
			add_action('rest_api_init', [$this, 'register_rest_routes']);
		}

		public function manage_posts_columns($columns) {
			return array_merge($columns, [
				'numOfProperties' => 'Number Of Properties',
            ]);
		}

		public function columns_content($column, $postId) {

			switch ($column) {
				case 'numOfProperties':
					$propIds=get_posts([
						'post_type' => 'properties',
						'properties_from_neighborhood' => $postId,
						'nopaging' => true,
						'fields' => 'ids',
					]);

					$link=get_admin_url(null, "edit.php?post_type=properties&properties_from_neighborhood=".$postId);

					echo "<a href='". $link ."' target='_blank'>". count($propIds) ."</a>";

					break;

			}

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
				'nopaging' => true,
			];
		
			if ( $request->get_param( 'q_args' ) ) {
				$search_args=array_merge($request->get_param( 'q_args' ), $search_args);
			}

			return self::filter_for_mvc(get_posts($search_args), $search_args);
		}


	}