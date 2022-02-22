<?php 
	class rentPress_searchHelpers  {

		public static $default_post_to_remove_from_mvc=[
			'post_status',
			//'menu_order',
			'guid',
			'filter',
			'pinged',
			'to_ping',
			'comment_count',
			'comment_status',
			'post_parent',
			'post_type',
			'post_author',
			'ping_status',
			'post_content_filtered',
			//'post_date',
			'post_date_gmt',
			'post_mime_type',
			//'post_modified',
			'post_modified_gmt',
			'post_password',
		];

		public static $states = [ 'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon','PA'=>'Pennsylvania', 'RI'=>'Rhode Island','SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming' ];

		private static function format_array_of_values($theArray, $theFormat = 'string') {
			switch ($theFormat) {
	 			case 'float':
	 				$theArray=array_map(function($value) {return floatval($value);}, $theArray);
	 				break;

	 			case 'int':
	 				$theArray=array_map(function($value) {return (int) $value;}, $theArray);
	 				break;
	 		}

	 		return $theArray;
		}

		public static function get_distinct_meta_values_from($post_type, $meta_key, $allow_zero = false, $format = false) 
		{
			global $wpdb;

			$distance_values=[];

			if (is_array($meta_key)) {
				$distance_values=$wpdb->get_col( $wpdb->prepare("
			        SELECT DISTINCT pm1.meta_value 
			        FROM $wpdb->posts posts
			        LEFT JOIN $wpdb->postmeta pm1 ON posts.ID = pm1.post_id
			        WHERE 
			        	posts.post_type = %s
			        	AND
			        	pm1.meta_key IN (". join(', ', array_map(function($key) use ($wpdb) {return $wpdb->prepare('%s', $key);}, $meta_key)) .") 
			        	AND 
			        	pm1.meta_value != '' AND pm1.meta_value != '-1' ". (($allow_zero == false)?"AND pm1.meta_value != '0'":"") ."
			        ORDER BY pm1.meta_value ASC
			    ", $post_type) );
		 	}
		 	else {
		 		$distance_values=$wpdb->get_col( $wpdb->prepare( "
			        SELECT DISTINCT pm.meta_value 
			        FROM $wpdb->postmeta pm
			        LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id
			        WHERE pm.meta_key = %s AND pm.meta_value != '' AND p.post_type = %s AND pm.meta_value != '-1' ". (($allow_zero == false)?"AND pm.meta_value != '0'":"") ."
			        ORDER BY pm.meta_value ASC
			    ", $meta_key, $post_type ) );
		 	};

		 	if ($format !== false) {

		 		$distance_values=self::format_array_of_values($distance_values, $format);

		 	}

		 	return $distance_values;
		}

		public static function get_min_and_max_meta_values_from($post_type, $min_meta_key, $max_meta_key, $allow_zero = false) 
		{
			global $wpdb;

			$min_and_max = [];

			if ( $min_meta_key === $max_meta_key ) {
				$min_and_max = (array) $wpdb->get_results($wpdb->prepare(
					"
						SELECT MIN(CAST(pm.meta_value as UNSIGNED)) as min, MAX(CAST(pm.meta_value as UNSIGNED)) as max
				        FROM $wpdb->postmeta pm
				        LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id
				        WHERE pm.meta_key = %s AND p.post_type = %s ". (($allow_zero == false)?" AND pm.meta_value > 0 ":"") ." 
					",
					$min_meta_key, $post_type
				))[0];
			}
			else {
				$min_and_max = (array) $wpdb->get_results($wpdb->prepare(
					"
						SELECT MIN(CAST(pm1.meta_value as UNSIGNED)) as min, MAX(CAST(pm2.meta_value as UNSIGNED)) as max
				        FROM $wpdb->posts posts
				        LEFT JOIN $wpdb->postmeta pm1 ON pm1.post_id = posts.ID
				        LEFT JOIN $wpdb->postmeta pm2 ON pm2.post_id = posts.ID
				        WHERE 
				        	posts.post_type = %s
				        	AND
				        	pm1.meta_key = %s ". (($allow_zero == false)?" AND pm1.meta_value > 0 ":"") ."
				        	AND
				        	pm2.meta_key = %s ". (($allow_zero == false)?" AND pm2.meta_value > 0 ":"") ."
					",
					$post_type, $min_meta_key, $max_meta_key
				))[0];
			}

			return (object) $min_and_max;
		}

		public static function get_distance_between($lat1, $lon1, $lat2, $lon2, $unit = "M") {
			$theta = $lon1 - $lon2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			$unit = strtoupper($unit);

			if ($unit == "K") {
				return ($miles * 1.609344);
			} else if ($unit == "N") {
				return ($miles * 0.8684);
			} else {
				return round($miles, 2);
			}
		}

		/**
		 * Advanced data validation check against query vars provided during Units Query building
		 * @param  [rentPress_Units_Query]  $request   [Units Query class]
		 * @param  [string]                 $val       [key name of query var to check]
		 * @param  [boolean] 				$allowZero [Will this check allow zero values through?]
		 * @return [boolean]             			   [True/false dependent on validation pass]
		 */
		public static function is_query_var_value_valid($request, $val, $allowZero = false) {

			// If the requested object key doesn't exist, return false
			if ( ! isset($request->query_args->{$val}) ) {
				return false;
			}

			$val = $request->query_args->{$val};

			if ( ! $allowZero && ( $val == '0' || $val == 0 )  ) {
				return false;
			}

			return (
				$val
				&&
				! empty($val)
				&&
				$val != "false"
				&&
				! is_null($val)
			) || $val === true;

		}	

		public static function if_query_var_check($val) {

			return (
				isset($val)
				&&
				$val
				&&
				! empty($val)
				&&
				$val != "false"
				&&
				$val != "0"
				&&
				! is_null($val)
			) || $val === true;

		}	

		public static function if_query_var_check_allow_zero($val) {

			return (
				$val
				&&
				! empty($val)
				&&
				$val != "false"
				&&
				! is_null($val)
			) || $val === true || $val === 0 || $val == "0";

		}

		public static function if_query_var_is_only_true($query_var) {

			return (
				$query_var === TRUE 
				|| 
				( is_string($query_var) && strtolower($query_var) === 'true' )
			);

		}

		public static function if_query_var_is_only_false($query_var) {

			return (
				$query_var === FALSE
				||
				( is_string($query_var) && strtolower($query_var) === 'false' )
			);

		}

		public static function if_query_is_searching_for_available_units($query) {

			return (
				(
					self::if_query_var_check( $query->get('properties_available_by') )
					||
					self::if_query_var_check( $query->get('properties_with_soon_available_units') )
					||
					self::if_query_var_is_only_true($query->get('properties_with_available_units') )
				)
				||
				(
					self::if_query_var_check( $query->get('floorplans_available_by') )
					||
					self::if_query_var_check( $query->get('floorplans_with_soon_available_units') )
					||
					self::if_query_var_is_only_true( $query->get('floorplans_with_available_units') )
				)
			);

		}

		public static function get_post_meta($post_id, $where_not_in = []) {
			global $wpdb;

			$the_meta_data_query="SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE ";

			$the_meta_data_query.=$wpdb->prepare(" post_id = %d ", $post_id);
			$the_meta_data_query.=" AND meta_key NOT LIKE '\_%' ";
			$the_meta_data_query.=" AND meta_key NOT LIKE 'field_%' ";
			$the_meta_data_query.=" AND meta_value != '' ";
			
			if (count($where_not_in) > 0) {
				$where_not_in=array_map(function($where_not) use($wpdb) {return $wpdb->prepare(' %s ', $where_not); }, $where_not_in);

				$the_meta_data_query.=" AND meta_key NOT IN (". join(',', $where_not_in) .") ";
			}

			$ran_query=$wpdb->get_results($the_meta_data_query);
			$results=[];

			foreach ($ran_query as $meta_query) {
				$results[$meta_query->meta_key]=apply_filters( 
					"get_post_metadata", 
					$meta_query->meta_value, 
					$post_id, 
					$meta_query->meta_key, 
					false
				);
			}

			return $results;
		}

		public static function get_post_acf($post_id, $where_not_in = []) {
			global $wpdb;

			// filter post_id
			$post_id = apply_filters('acf/get_post_id', $post_id );

		 	$fields=[];
			
			$the_acf_query=$wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d and meta_key LIKE %s AND meta_value LIKE %s",
				$post_id,
				'_%',
				'field_%'
			);

			if (count($where_not_in) > 0) {
				$where_not_in=array_map(function($where_not) use($wpdb) {return $wpdb->prepare(' %s ', '_'.$where_not); }, $where_not_in);

				$the_acf_query.=" AND meta_key NOT IN (". join(',', $where_not_in) .") ";
			}

			$keys = $wpdb->get_col($the_acf_query);

			if ( is_array($keys) ) {
				foreach( $keys as $key ) {
					$field = get_field_object( $key, $post_id, ['load_value' => true, 'format_value' => true]);

					if ( is_array($field) ) {					
						$fields[ $field['name'] ] = $field['value'];
					}
				}
		 	}

			return $fields;
		} 

		public static function mvc_meta_data($post_id) {
			$post_meta_data=self::get_post_meta(
				$post_id,
				apply_filters('rentPress_searchHelpers_where_not_in', [])
			);
			
			foreach ($post_meta_data as $meta_key => $meta_value) {
				if (is_array($meta_value) && count($meta_value) === 1) {
					$meta_value=$meta_value[0];
				}

				if ($meta_key[0] == '_') {
					$meta_key_without_=substr($meta_key, 1);

					if (is_string($meta_value) && strpos($meta_value, 'field_') !== false) {			
						unset($post_meta_data[$meta_key]);
						unset($post_meta_data[$meta_key_without_]);

						foreach ($post_meta_data as $_meta_key => $_meta_value) {							
							if (strpos($_meta_key, '_'.$meta_key_without_) !== false) {
								unset($post_meta_data[$_meta_key]);
								break;
							}
						}
					}
					else {
						unset($post_meta_data[$meta_key]);
					}
					
					continue;
				}

				if ($meta_key == 'propLatitude' || $meta_key == 'propLongitude') {
					continue;
				}

				if (is_string($meta_value)) {
					$json_result = json_decode($meta_value, true);

					if (json_last_error() === JSON_ERROR_NONE && is_array($json_result)) {
						$meta_value = $json_result;
					}
				}	

				if (is_numeric($meta_value) && $meta_value[0] != '0') {
					$meta_value=floatval(number_format($meta_value, 2, '.', ''));
				}			

				if ($meta_value === null || $meta_value === 'null') {
					unset($post_meta_data[$meta_key]);
					continue;
				}

				$post_meta_data[$meta_key]=$meta_value;
			}

			if (function_exists('get_fields')) {
				$post_meta_data=array_merge(
					$post_meta_data, 
					(array) self::get_post_acf(
						$post_id,
						apply_filters('rentPress_searchHelpers_where_not_in', [])
					)
				);
			}

			return $post_meta_data;
		}

		public static function mvc_post_format($_post) {

			foreach ((array) $_post as $postVar => $val) {
				if (
					$val === null
					||
					$val === 0
					||
					$val === ''
					||
					in_array($postVar, self::$default_post_to_remove_from_mvc)
				) {
					unset($_post->{$postVar});
				}
				elseif (strpos($postVar, 'post') !== false) {
					$_post->{ str_replace('post', '', $postVar) } = $val;
					unset($_post->{ $postVar });
				}
			}
			
			$_post->permalink=get_permalink($_post->ID);

			// $_post->_excerpt=get_the_excerpt($_post['ID']);

			$_post->image=get_the_post_thumbnail_url($_post->ID, 'full');

			$_post->meta_data=self::mvc_meta_data($_post->ID);

			return $_post;
		}

		public static function apply_meta_query_to_query($query, $meta_query_to_be_added, $extraKey = '') {
			
			if (count($meta_query_to_be_added) > 0) {
				$currentMetaQuery=$query->get('meta_query');
	
				if (is_array($currentMetaQuery)) {
					if (empty($extraKey)) {
						$currentMetaQuery[]=$meta_query_to_be_added;
					}
					else {
						$currentMetaQuery[ $extraKey ]=$meta_query_to_be_added;
					}
				}
				else {
					if (empty($extraKey)) {
						$currentMetaQuery=[ $meta_query_to_be_added ];
					}
					else {
						$currentMetaQuery=[ $extraKey => $meta_query_to_be_added];
					}					
				}
	
				$query->set('meta_query', $currentMetaQuery);
			}

		}

		public static function apply_tax_query_to_query($query, $tax_query_to_be_added) {
			if (count($tax_query_to_be_added) >= 1) {
				$currentTaxQuery=$query->get('tax_query');
				
				if (is_array($currentTaxQuery)) {
					$currentTaxQuery[]=$tax_query_to_be_added;
				}
				else {
					$currentTaxQuery=[ $tax_query_to_be_added ];
				}

				$query->set('tax_query', $currentTaxQuery);
			}

		}

		public static function the_post_not_in_clause($query) {
			$currentPostIn=$query->get('post__in');
				
			$currentPostNotIn=$query->get('post__not_in');

			if (count($currentPostNotIn) > 0 && count($currentPostIn) > 0) {
				$currentPostIn=array_filter($currentPostIn, function($post_id_in) use ($currentPostNotIn) {
					return ! in_array($post_id_in, $currentPostNotIn);
				});

				$query->set('post__in', $currentPostIn);
			}
		}

		public static function if_query_var_is_sortby($query_var) {
			return (
				strpos($query_var, ':') !== false
				&&
				(
					strpos(strtolower($query_var), ':asc')
					||
					strpos(strtolower($query_var), ':desc')
				)
			);
		}

		public static function parse_sortby($unparsed, $same_sames) {
			$sort_by=explode(':', $unparsed);

			if (is_array($sort_by) && count($sort_by) == 2) {
				$orginal_sort_by=$sort_by[0];

				$fliped_same_same=array_flip($same_sames);

				// IFS HERE!!!
				if (isset($fliped_same_same[ $sort_by[0] ])) {
					$sort_by[0]=$fliped_same_same[ $sort_by[0] ];
				}
				
				return [
					'0' => $sort_by[0],
					'1' => strtoupper($sort_by[1]),
					
					'orginal' => $orginal_sort_by,
				];
				
			}
		}

		public static function wpdb_prepare_range_or_min_or_max($mysql_query_field, $raw_value) {
			global $wpdb;

			if (strpos($raw_value, '-') !== false) {
				$pos=strpos($raw_value, '-');

				$lessThen=substr($raw_value, $pos+1);
				$greaterThen=substr($raw_value, 0, $pos);

				return $wpdb->prepare(
					" ( $mysql_query_field <= %f AND $mysql_query_field >= %f ) ", 
					$lessThen, 
					$greaterThen
				);
			}

			elseif (strpos($raw_value, '>') !== false) {
				$pos=strpos($raw_value, '>');

				$greaterThen=substr($raw_value, $pos+1);

				return $wpdb->prepare(" ( $mysql_query_field >= %f ) ", $greaterThen);
			}

			elseif (strpos($raw_value, '<') !== false) {
				$pos=strpos($raw_value, '<');

				$lessThen=substr($raw_value, $pos+1);
			
				return $wpdb->prepare(" ( $mysql_query_field <= %f ) ", $lessThen);
			}
		}

		public static function wp_meta_query_prepare_range_or_min_or_max($meta_field, $rawValue) {
			if (strpos($rawValue, '-') !== false) {
				$pos=strpos($rawValue, '-');

				$lessThen=substr($rawValue, $pos+1);
				$greaterThen=substr($rawValue, 0, $pos);

				return [
					'relation' => 'AND',
					
					[
						'type' => 'NUMERIC',
						'key' => $meta_field,
						'compare' => '<=',
						'value' => $lessThen,
					],
					[
						'type' => 'NUMERIC',
						'key' => $meta_field,
						'compare' => '>=',
						'value' => $greaterThen,
					]
				];
			}

			elseif (strpos($rawValue, '>') !== false) {
				$pos=strpos($rawValue, '>');

				$greaterThen=substr($rawValue, $pos+1);

				return [
					'type' => 'NUMERIC',
					'key' => $meta_field,
					'compare' => '>=',
					'value' => $greaterThen,
				];
			}

			elseif (strpos($rawValue, '<') !== false) {
				$pos=strpos($rawValue, '<');

				$lessThen=substr($rawValue, $pos+1);
				
				return [
					'type' => 'NUMERIC',
					'key' => $meta_field,
					'compare' => '<=',
					'value' => $lessThen,
				];
			}
		}
	}