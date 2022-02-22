<?php 

class rentPress_SavePostsAndMetaFilters {

	public function __construct() {

		$this->options = new rentPress_Options();
		$this->importer = new rentPress_Import_ImportProperties();
		$this->postTypes = new rentPress_Posts_PostTypes();
		$this->caching = new rentPress_Base_Caching();

		/* Override saving properties post type meta data */
        add_action('save_post_properties', [$this, 'save_custom_post_meta_for_properties']);

        /* Override saving floorplans post type meta data */
        add_action('save_post_floorplans', [$this, 'save_custom_post_meta_for_floor_plans']);

        // Making Sure Delete Units
        add_action('before_delete_post', [$this, 'delete_units_of_posts']);

       	if (! is_admin()) {
	       	add_filter( 'get_post_metadata', [$this, 'filter_properties_metadata'], 15, 4);
	       	add_filter( 'get_post_metadata', [$this, 'filter_floorplans_metadata'], 15, 4);       		
       	}       

       	add_filter('get_post_metadata', [$this, 'filter_units_metadata'], 15, 4);

       	add_filter('rentPressUnitsJsonFromDB', [$this, 'filter_units_data'], 10, 2);

       	$this->remove_saved_nonces();
	}	

	public function resetTransients()
    {
        $this->caching->cacheReset();
    }

	/**
    * Save post metadata when a property is saved.
    *
    * @param int $post_id The post ID.
    */
    public function save_custom_post_meta_for_properties( $post_id ) {

		// return if autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// reset our transients to make sure any new information gets put into our cache
		$this->resetTransients();

		// Clean the action in the request
		$cleanAction = sanitize_text_field($_REQUEST['action']);

		// Make sure Post ID is accurate
		if ( isset($post_ID) && (! is_integer(intval($post_id)) || is_array($post_id)) ) {
			wp_die('Post ID data type is invalid.');
		}

		// Determine action being taken: Bulk update, quick edit, or regular update from edit page are supported
		switch ( $cleanAction ) {
			case 'edit': // edit is bulk edit from list view
				foreach ($_REQUEST['post'] as $postID) {
					if ( ! is_integer(intval($postID)) || is_array($postID) ) {
						wp_die('Post ID data type is invalid.');
					}
					$this->bulkEditCustomProperties($postID);
				}
				break;

			case 'inline-save': // inline-save is quick edit in list view
				$this->quickEditCustomProperty($post_id);
				break;

			case 'editpost': // editpost is updating post from edit screen
				$this->updatePropertyMetaFromEditScreen($post_id);
				break;
			
			default:
				break;
		}
		return;
    }

    public function filter_properties_metadata($value, $object_id, $meta_key, $is_single) {
		if ( isset($meta_key) && get_post_type($object_id) === RENTPRESS_PROPERTIES_CPT && in_array($meta_key, ['propMinRent', 'propMaxRent']) ) {
			global $wpdb;
			
			$property_disable_pricing=$wpdb->get_col($wpdb->prepare("
		        SELECT pm.meta_value 
		        FROM $wpdb->postmeta pm
		        WHERE  pm.meta_key = 'propDisablePricing' AND pm.post_id = %d
		        LIMIT 1
		    ", $object_id));
		    
		    if (
				$this->options->getOption('disable_pricing') == "true"
				||
				(isset($property_disable_pricing[0]) && $property_disable_pricing[0] == "true")
			) {	
				return 0;
			}
		}

		return $value;
    }

    /**
    * Save post metadata when a floor plan is saved.
    *
    * @param int $post_id The post ID.
    */
    public function save_custom_post_meta_for_floor_plans( $post_id ) {

		// return if autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// reset our transients to make sure any new information gets put into our cache
		$this->resetTransients();

		// Determine action being taken: Bulk update, quick edit, or regular update from edit page are supported
		switch ( $_REQUEST['action'] ) {
			case 'edit': // edit is bulk edit from list view
				foreach ($_REQUEST['post'] as $postID) {
					$this->bulkEditCustomProperties($postID);
				}
				break;

			case 'inline-save': // inline-save is quick edit in list view
				$this->quickEditCustomProperty($post_id);
				break;

			case 'editpost': // editpost is updating post from edit screen
				$this->updateFloorPlanMetaFromEditScreen($post_id);
				break;
			
			default:
				break;
		}
		return;
    }

    public function filter_floorplans_metadata($value, $object_id, $meta_key, $is_single) {
		if ( isset($meta_key) && get_post_type($object_id) == RENTPRESS_FLOORPLANS_CPT) {

			if (in_array($meta_key, ['fpMinRent', 'fpMaxRent', 'fpMinDeposit', 'fpMaxDeposit'])) {
	    		global $wpdb;
				
				$property_disable_pricing=$wpdb->get_col($wpdb->prepare("
			    	SELECT pm.meta_value 
			    	FROM $wpdb->postmeta pm
					WHERE  pm.meta_key = 'propDisablePricing' AND pm.post_id IN (
						SELECT pm.post_id FROM $wpdb->postmeta pm
						WHERE  pm.meta_key = 'prop_code' AND pm.meta_value IN (
							SELECT pm.meta_value FROM $wpdb->postmeta pm
							WHERE  pm.meta_key = 'parent_property_code' AND pm.post_id = %d
						)
					)
					LIMIT 1 
			    ", $object_id));

				if (
					$this->options->getOption('disable_pricing') == rentPress_Helpers_StringLiterals::$rp_true 
					||
					( isset($property_disable_pricing[0]) && $property_disable_pricing[0] == rentPress_Helpers_StringLiterals::$rp_true)
					||
					(
						$this->options->getOption('disable_pricing_on_floorplan_with_no_available_units') === 'true'
						&&
						get_post_meta($object_id, 'fpAvailUnitCount', true) == 0
					)
				) {
					return 0;
				}				
			}
			elseif ($meta_key == "fpAvailURL" && ! empty($this->options->getOption('override_apply_url')) ) {

				return $this->options->getOption('override_apply_url');

			}

		}			

		return $value;
    }

    public function delete_units_of_posts($postID) {
    	global $wpdb;

    	switch (get_post_type($postID)) {
    		case RENTPRESS_PROPERTIES_CPT:
    			$prop_code=get_post_meta($postID, 'prop_code', true);
    			$deleted=$wpdb->delete( $wpdb->rp_units, ['prop_code' => $prop_code,]);
    			break;

    		case RENTPRESS_FLOORPLANS_CPT:
    			$fpID=get_post_meta($postID, 'fpID', true);
    			$deleted=$wpdb->delete( $wpdb->rp_units, ['fpID' => $fpID,]);
    			break;

    		default: 
    			// Do nothing cause we need a post type 
				$this->importer->log->scream()->warning("Could not delete units for post #{$postID} because no post type was returned!");
    			break;
    	}
    }

    public function bulkEditCustomProperties($postID)
    {
		if ( isset($_REQUEST['tax_input']) ) {
			foreach ($_REQUEST['tax_input'] as $metaKey => $metaValue ) {
				$metaValue = sanitize_text_field($metaValue);
				update_post_meta($postID, $metaKey, $metaValue);
			}
		}
    }

    public function quickEditCustomProperty($postID)
    {
		if ( isset($_REQUEST['tax_input']) ) {
			foreach ($_REQUEST['tax_input'] as $metaKey => $metaValue ) {
				$metaValue = sanitize_text_field($metaValue);
				update_post_meta($postID, $metaKey, $metaValue);
			}
		}
    }

    public function updatePropertyMetaFromEditScreen($postID)
    {
    	$rentPressMeta = $this->postTypes->metaBoxes;
    	
    	$allPropertyMetaFields = array_merge(
    		$rentPressMeta::$propertyGeneralFields,
    		$rentPressMeta::$propertyRoomFields,
    		// $rentPressMeta::$propertyImageFields, Need To Create MetaBoxes!
    		$rentPressMeta::$propertyRangeFields,
    		$rentPressMeta::$propertyCoordinateMeta,
    		$rentPressMeta::$propertyExtras,
    		$rentPressMeta::$propertyDisable
		);

		$metaToUpdate = array_unique( array_merge( array_keys($allPropertyMetaFields), array_keys($_REQUEST) ) );

		foreach ($metaToUpdate as $metaKey) {
			if ( isset($_REQUEST[$metaKey]) ) {

				//var_dump($metaKey);

				$_REQUEST[$metaKey] = sanitize_text_field($_REQUEST[$metaKey]);
				update_post_meta($postID, $metaKey, $_REQUEST[$metaKey]);
			}
			else {
				delete_post_meta($postID, $metaKey);
			}
		}
    }

    public function updateFloorPlanMetaFromEditScreen($postID)
    {
    	$rentPressMeta = $this->postTypes->metaBoxes;
    	$allFloorPlanMetaFields = array_merge(
    		array_keys($rentPressMeta::$floorPlanMeta),
    		array_keys($rentPressMeta::$floorPlanListMeta),
    		array_keys($rentPressMeta::$floorPlanSpecialMeta)

		);
		foreach ($allFloorPlanMetaFields as $metaKey) {
			if ( isset($_REQUEST[$metaKey]) ) {
				$_REQUEST[$metaKey] = $metaKey !== 'fpUnits' ? sanitize_text_field($_REQUEST[$metaKey]) : $_REQUEST[$metaKey];
				$_REQUEST[$metaKey] = $metaKey !== 'fpUnits' ? esc_html($_REQUEST[$metaKey]) : $_REQUEST[$metaKey];

				if ( isset($_REQUEST[rentPress_Helpers_StringLiterals::$overrideMetaPrefix.$metaKey]) ) {
					update_post_meta($postID, rentPress_Helpers_StringLiterals::$overrideMetaPrefix.$metaKey, $_REQUEST[rentPress_Helpers_StringLiterals::$overrideMetaPrefix.$metaKey]);
				}
				else {
					update_post_meta($postID, rentPress_Helpers_StringLiterals::$overrideMetaPrefix.$metaKey, null);
				}
				
				update_post_meta($postID, $metaKey, trim($_REQUEST[$metaKey]));
			}
		}
    }

	public function filter_units_metadata($value, $object_id, $meta_key, $is_single) {
		if (isset($meta_key) && ($meta_key == 'propUnits' || $meta_key == 'fpUnits')) {
			$units_query=new rentPress_Units_Query([
				'post_id' => $object_id,
			]);

			return json_encode($units_query->run_query());
		}

		return $value;
	}

	public  function remove_saved_nonces() {
		global $wpdb;
		
		if ( ! $this->options->getOption('has_tried_to_remove_nonces_1') ) {

			$wpdb->query( 
				$wpdb->prepare(
					"DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s",
					'%_nonce%' 
				)
			);

			$this->options->addOption('has_tried_to_remove_nonces_1', 'true');

        }
	}

	public function filter_units_data($units, $args) {

		if (! empty($this->options->getOption('override_apply_url'))) {

			foreach ($units as $unit) {
				if (isset($unit->Information)) {

					$unit->Information->AvailabilityURL=$this->options->getOption('override_apply_url');

				}
			}

		}

		return $units;
	} 

}