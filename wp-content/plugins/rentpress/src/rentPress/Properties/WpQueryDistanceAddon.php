<?php 
class rentPress_Properties_WpQueryDistanceAddon{
	public function __construct() {
		$this->options = new rentPress_Options();
	}

	public function run() {
		add_action('pre_get_posts', [$this, 'pre_get_properties'], 30, 1);

		add_filter('posts_join', [$this, 'wp_query_inner_joins'], 40, 2);
		add_filter('posts_where', [$this, 'wp_query_where'], 40, 2);
		add_filter('posts_orderby', [$this, 'wp_query_orderby'], 40, 2);
	}

	public static function allow_distance_search($query) {

		return (
			rentPress_searchHelpers::if_query_var_check( $query->get('properties_within_radius') ) 
			&& 
			is_array($query->get('properties_within_distance_from'))
			&& 
			isset($query->get('properties_within_distance_from')['lat']) 
			&& 
			isset($query->get('properties_within_distance_from')['lng'])
		);

	}

	public function pre_get_properties($query) {
		if ( $query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {

			// Google Places Via Maps Radius Search
			if ($query->get('properties_near_city') && rentPress_searchHelpers::if_query_var_check( $query->get('properties_within_radius') )) {

				$ch = curl_init();

				$url="https://maps.googleapis.com/maps/api/geocode/json";

				$url_params="?address=". urlencode($query->get('properties_near_city')) ."&sensor=false&key=". $this->options->getOption('google_api_token');

				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				
				curl_setopt($ch, CURLOPT_URL, $url.$url_params);

				$geoloc = json_decode(curl_exec($ch), true);

				$step1 = $geoloc['results'];
				$step2 = $step1[0]['geometry'];
				$coords = $step2['location'];

				$query->set('properties_within_distance_from', [
					'lat' => $coords['lat'],
					'lng' => $coords['lng'],
				]);

				$query->set('properties_sortedby', 'distance:asc');

				curl_close($ch);
			}
		}

		return $query;
	}

	public function wp_query_inner_joins($join, $query) {
		global $wpdb;

		if ( $query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {

			// Distance Data
			if (self::allow_distance_search($query)) { 
				$join .= $wpdb->prepare(
					"
						LEFT JOIN (
							SELECT 
								ID, 
								( %d * acos( cos( radians(%f) ) * cos( radians( prop_locations.lat ) ) 
								* cos( radians(prop_locations.lng) - radians(%f)) + sin(radians(%f)) 
								* sin( radians(prop_locations.lat)))) AS distance 
							FROM (
								SELECT DISTINCT latTable.post_id as ID, latTable.meta_value as lat, lngTable.meta_value as lng
								FROM {$wpdb->postmeta} latTable
								INNER JOIN {$wpdb->postmeta} lngTable ON latTable.post_id = lngTable.post_id
								WHERE latTable.meta_key = 'propLatitude' AND lngTable.meta_key = 'propLongitude'
							) prop_locations
						) prop_distance ON prop_distance.ID = {$wpdb->posts}.ID
					",
					$query->get('properties_within_distance_unit') == 'K'?6371:3959,
					$query->get('properties_within_distance_from')['lat'], 
					$query->get('properties_within_distance_from')['lng'], 
					$query->get('properties_within_distance_from')['lat']
				);
			}
		}

		return $join;
	}
	
	public function wp_query_where($where, $query) {
		global $wpdb;

		if ( $query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {
			// Has Distance Of 
			if ( self::allow_distance_search($query) ) { 
				$where .= $wpdb->prepare(
					" AND prop_distance.distance <= %f ",
					$query->get('properties_within_radius')
				);
			}	
		}

		return $where;
	}

	public function wp_query_orderby($orderby, $query) {
		if ( $query->get('post_type') == RENTPRESS_PROPERTIES_CPT && ! $query->is_singular) {
			if ( self::allow_distance_search($query) ) { 
				return "prop_distance.distance ASC";
			}
			
			$sort_by=rentPress_searchHelpers::parse_sortby(
				$query->get('properties_sortedby'), 
				rentPress_Properties_SearchAddon::$orderby_same_same
			);

			if (self::allow_distance_search($query) && $sort_by[0] == 'distance') {
				return "prop_distance.distance ".strtoupper($sort_by[1]);
			}
		}

		return $orderby;
	}

}