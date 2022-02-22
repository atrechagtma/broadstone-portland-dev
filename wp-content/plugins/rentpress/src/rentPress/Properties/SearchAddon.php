<?php
/**
 * Manage Properties Search Querys
 * @author  Joshua
 */

class rentPress_Properties_SearchAddon {

	static public $orderby_same_same=[
		'available_units' => 'meta_data.propUnitsAvailable',
		'available_date' => 'from_available_units.soonestAvailablity',

		'title' => '_title',
		'date' => '_date',
		'modified' => '_modified',

		'rent' => 'rent',
		
		'beds' => 'meta_data.propMaxBeds',
		'sqft' => 'meta_data.propMaxSQFT',
		
		'city' => 'meta_data.propCity',
		// 'state' => 'meta_data.propState', // need to convert the state to full words or somekind index
		'zip' => 'meta_data.propZip',
	];

	public function __construct() {
		//$this->property_meta = rentPress_Posts_Meta_Properties::get_instance();
		$this->options = new rentPress_Options();
	}

	public function run() {
		add_filter('query_vars', [$this, 'addQueryVars'], 30, 1);
		add_action('pre_get_posts', [$this, 'pre_get_properties'], 30, 1);
	}

	public function addQueryVars($vars) {

		$vars[] = 'property_code';
		$vars[] = 'property_email';

		$vars[] = 'properties_allows_pets';
			/*foreach (rentPress_Base_Meta::$proproperty_codepertiesAllowPetsFields as $meta_key => $meta_title) {
				$vars[] = 'properties_allows_'. strtolower($meta_title);
			}*/

		$vars[] = 'properties_min_rent';
		$vars[] = 'properties_max_rent';
		$vars[] = 'properties_rent_ors';

		$vars[] = 'properties_beds';
		$vars[] = 'properties_min_beds';
		$vars[] = 'properties_max_beds';

		$vars[] = 'properties_baths';
		$vars[] = 'properties_min_baths';
		$vars[] = 'properties_max_baths';

		$vars[] = 'properties_min_sqft';
		$vars[] = 'properties_max_sqft';
		$vars[] = 'properties_sqft_ors';

		$vars[] = 'properties_with_available_units';
		$vars[] = 'properties_with_soon_available_units';
		$vars[] = 'properties_available_by';

		$vars[] = 'properties_within_radius';
		$vars[] = 'properties_within_distance_unit';
		$vars[] = 'properties_within_distance_from';

		$vars[] = 'properties_wildcard_name_and_places';
		$vars[] = 'properties_from_neighborhood';
		$vars[] = 'properties_city';
		$vars[] = 'properties_near_city';
		$vars[] = 'properties_state';
		$vars[] = 'properties_zipcode';

		$vars[] = 'properties_with_specials';

		$vars[] = 'properties_sortedby';

		return $vars;
		
	}

	public function pre_get_properties ($query) {
		if ($query->get('post_type') == 'properties' && ! $query->is_singular) {			
			global $wpdb;

			// Defualts
			if ($query->get('orderby') == "") {
				$query->set('orderby', 'title');
				$query->set('order', 'ASC');
			}

			$query->set('suppress_filters', false);

			// Start Of Meta And Tax Queries
			$properties_tax_query=[];
			$properties_meta_query=[];

			// Property Code
			if (rentPress_searchHelpers::if_query_var_check($query->get('property_code'))) {
				$properties_meta_query[]=[
					'key' => 'prop_code',
					'value' => $query->get('property_code'),
				];

				/*
					@ToDo Maybe Add 
						$query->set('per_posts_page', 1);
				*/
			}
			
			// Property Email
			if (rentPress_searchHelpers::if_query_var_check($query->get('property_email'))) {
				$properties_meta_query[]=[
					'key' => 'propEmail',
					'value' => $query->get('property_email'),
				];
			}

			// With Specials
			if (rentPress_searchHelpers::if_query_var_check($query->get('properties_with_specials'))) {
				$properties_meta_query[]=[
					'key' => 'propSpecialsMessage',
					'compare' => '!=',
					'value' => '',
				];
			}

			// Pets
			if ($query->get('properties_allows_pets') === true || $query->get('properties_allows_pets') === 'true') {
				
				$properties_tax_query[]=[
					'taxonomy' => 'prop_pet_restrictions',
					'operator' => 'EXISTS',
				];

			}

			// Meta Field Locations
			if (rentPress_searchHelpers::if_query_var_check($query->get('properties_wildcard_name_and_places'))) {
				$names_to_short=array_flip(rentPress_searchHelpers::$states);

          		$state_shortname=$names_to_short[ ucwords( $query->get('properties_wildcard_name_and_places') ) ];

          		if (! isset($state_shortname) && strlen($query->get('properties_wildcard_name_and_places')) > 2) {
          			$maybeStates=[];

					foreach ($names_to_short as $longName => $shortName) {
						if (strpos($longName, ucwords($query->get('properties_wildcard_name_and_places'))) !== false) {
							$maybeStates[]=$shortName;
						}
         			}       			
          		}

				$properties_meta_query['wildcard_name_and_places']=[
	                'relation' => 'OR',
	                
	                'propName' => [
	                     'key' => 'propName',
	                    'compare' => 'LIKE',
	                    'value' => $query->get('properties_wildcard_name_and_places'),
	                ],

	                'city' => [
	                    'key' => 'propCity',
	                    'compare' => 'LIKE',
	                    'value' => $query->get('properties_wildcard_name_and_places'),
	                ],

	                'zip' => [
	                    'key' => 'propZip',
	                    'compare' => 'LIKE',
	                    'value' => $query->get('properties_wildcard_name_and_places'),
	                    'type' => 'NUMERIC',
	                ],

	                'state' => [
	                    'key' => 'propState',
	                    'compare' => 'LIKE',
	                    'value' => $query->get('properties_wildcard_name_and_places'),
	                ],
	            ];

	            if (isset($state_shortname)) {
	                $properties_meta_query['wildcard_name_and_places']['state_with_shortname']=[
	                    'key' => 'propState',
	                    'value' => $state_shortname,
	                ];
	            }

	            if (isset($maybeStates) && count($maybeStates) > 0) {
	            	$properties_meta_query['wildcard_name_and_places']["state"]=[
						'key' => 'propState',
						'compare' => 'IN',
						'value' => $maybeStates,
					];
	            }
			}

			// PostToPostConnection For Neighborhoods
			if ($query->get('properties_from_neighborhood')) {
				$query->set('post_type_connection_to', 'neighborhoods');
				$query->set('post_type_connection_value', $query->get('properties_from_neighborhood'));

				// @ToDo Maybe... Backwards Capitablity For The Old Tax		
			}

			// @ToDo propAddress LIKE % %

			// City
			if (rentPress_searchHelpers::if_query_var_check($query->get('properties_city'))) {
				if (is_array($query->get('properties_city'))) {
					$property_meta_query["city"]=[
						'key' => 'propCity',
						'compare' => "IN",
						'value' => $query->get('properties_city'),
					];
				}

				else {
					$properties_meta_query["city"]=[
						'key' => 'propCity',
						'value' => $query->get('properties_city'),
					];
				}
			}

			// State
			if (rentPress_searchHelpers::if_query_var_check($query->get('properties_state'))) {
				$names_to_short=array_flip(rentPress_searchHelpers::$states);

				if (is_array($query->get('properties_state'))) {
					$array_of_states=$query->get('properties_state');

					$array_of_states = array_map(function($long_state) use ( $names_to_short ) {
						return (isset($names_to_short[ ucwords( $long_state ) ]))?$names_to_short[ ucwords( $long_state ) ]:$long_state;
					}, $array_of_states);

					$properties_meta_query["state"]=[
						'key' => 'propState',
						'compare' => 'IN',
						'value' => $array_of_states,
					];
				}
				else {
					if (isset($names_to_short[ ucwords( $query->get('properties_state') ) ])) {
						$query->set('properties_state', $names_to_short[ ucwords( $query->get('properties_state') ) ]);
					}

					$properties_meta_query["state"]=[
						'key' => 'propState',
						'value' => strtoupper($query->get('properties_state')),
					];
				}
			}			
			
			// Zipcode
			if (rentPress_searchHelpers::if_query_var_check($query->get('properties_zipcode'))) {
				if (is_array($query->get('properties_zipcode'))) {
					$properties_meta_query["zip"]=[
						'key' => 'propZip',
						'compare' => 'IN',
						'value' => $query->get('properties_zipcode'),
					];
				}
				else {
					$properties_meta_query["zip"]=[
						'key' => 'propZip',
						'value' => $query->get('properties_zipcode'),
					];
				}
			}			

			// Meta Field Sorting
			if (rentPress_searchHelpers::if_query_var_is_sortby( $query->get('properties_sortedby') )) {
				$sort_by=rentPress_searchHelpers::parse_sortby(
					$query->get('properties_sortedby'), 
					rentPress_Properties_SearchAddon::$orderby_same_same
				);

				if (strpos($sort_by['orginal'], 'meta_data') !== false) {
					$meta_key=str_replace('meta_data.', '', $sort_by['orginal']);

					$properties_meta_query[]=[
			            'key' => $meta_key,
			            'compare' => 'EXISTS', 
					];

					$query->set('orderby', array(
						$meta_key => $sort_by[1],
					));	

					$query->set('order', $sort_by[1]);												
				}

			}

			rentPress_searchHelpers::apply_meta_query_to_query($query, $properties_meta_query, 'prop_meta');
		
			rentPress_searchHelpers::apply_tax_query_to_query($query, $properties_tax_query);
			
			// The Not In Clase
			rentPress_searchHelpers::the_post_not_in_clause($query);	
		}

		return $query;				
	}
}