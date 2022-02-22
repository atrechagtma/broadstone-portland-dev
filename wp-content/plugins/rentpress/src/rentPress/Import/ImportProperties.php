<?php 

/**
* Import properties from RentPress Connect
*/
class rentPress_Import_ImportProperties extends rentPress_Base_Import
{

	public function import($nextRoundUrl = null, $count = 0)
	{
		global $wpdb;

		// Fetch first or next page of properties
		$propertyRequest = $this->requestPropertiesByRound($nextRoundUrl);

		// Check if response is object
		if ( ! is_object($propertyRequest) ) {
			$this->log->error('Property request returned invalid object. Response: '.json_encode($propertyRequest));
			$this->notifications->errorResponse('During import, property request returned invalid object. Check the logs.', true);
			die();
		}

		// Check for an error in the property request response
		if ( isset($propertyRequest->error) ) {
			$this->log->error('Property import error: '.$propertyRequest->error->message);
			$this->notifications->errorResponse($propertyRequest->error->message, true);
			die();
		}

		// Extract meta and property listing data from request response
		$responseMeta = $propertyRequest->ResponseMeta;
		$properties = $propertyRequest->ResponseData->data;

		$RP_rentType = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->options->getOption('unit_rent_type')))) ?: 'MarketRent';
        $RP_leaseTermSelection = $this->options->getOption('unit_lease_term') ?: '12';

		foreach ($properties as $property) {
			$prop_floorplans = $property->floorplans->data;
			
			foreach ($prop_floorplans as $floorplan) {
				$units = $floorplan->units->data;

				if ( isset($units) && count($units) > 0 ) {
					foreach ($units as $unit) {
						$unit->Image = new stdClass;
						$unit->Image->FloorPlanImage = isset($floorplan->Information->FloorPlanImage) ? $floorplan->Information->FloorPlanImage : 'https://placehold.it/400x200';
					}

					$this->storeUnits(
						$units, 
						$RP_rentType,
						$RP_leaseTermSelection,
						$floorplan->Identification->FloorPlanCode
					);
				}
				else {
					$unitRemoval = $wpdb->query(
						$wpdb->prepare(
							"DELETE FROM $wpdb->rp_units WHERE fpID = %s AND prop_code = %s",
							$floorplan->Identification->FloorPlanCode,
							$floorplan->Identification->ParentPropertyCode
						)
					);
				}
			}

			// Store floorplans as post
			$this->storeFloorPlans($prop_floorplans, $property->Identification->PropertyCode);

			// Store property floor plans as posts, returns void
			$newProperty = $this->persist(new rentPress_Repositories_Properties(), $property);

			if ( is_object($newProperty) && isset($newProperty->error) ) {
				$this->log->error('Error creating or updating new property: '.$newProperty->error->message);
				continue;
			}
			
			$this->log->event('Property updated/created: '.$property->Information->PropertyName);
			
			$this->propertyCount++;
		}

		// If there are more pages in the request, recursively call this method to continue iteration
		if ( $responseMeta->total_pages > 1 && $responseMeta->total_pages !== $responseMeta->current_page ) {
			return $this->import($responseMeta->next_link, $this->propertyCount);
		}

		// If there were no properties brought back, let them know
		if ( $this->propertyCount == 0 ) {
			$this->log->warning('There were no properties found to import.');
			$this->notifications->errorResponse('There were no properties found to import.', true);
		} else {
			$this->successNotification();
		}
	}

	private function storeProperties($properties) {

		foreach ($properties as $property) {



		}

	}

	private function storeUnits($units, $rentType, $leaseTermSelection, $fpID = null) {
		global $wpdb;

		/*$purgeUnitsSQL = $wpdb->prepare("DELETE FROM $wpdb->rp_units WHERE fpID = %s", $fpID);

        $runUnitsPurge = $wpdb->query($sql);*/

		// Grab all codes of available units
		if ( isset($fpID) && $fpID !== '' && count($units) > 0 ) {
			$units_codes = array_map(function($unit) {
				return esc_html(trim($unit->Identification->UnitCode));
			}, $units);

			// Remove excess units that may not be present in the new list of units 
			if ( count($units_codes) > 0 ) {
				// Grab property code for query specificity... will be the same on all units so just grab the first one in the list. 
				$propCode = $units[0]->Identification->ParentPropertyCode;
				// Prepare the SQL statement for wpdb prepare so we can dynamically add to the NOT IN clause
				$sql = "DELETE FROM $wpdb->rp_units WHERE fpID = '{$fpID}' AND prop_code = '{$propCode}' AND unit_code NOT IN (".implode(', ', array_fill(0, count($units_codes), '%s')).")";
				// Prepare the sql query with wpdb so we can add all the unit codes to the NOT IN clause
				$sql = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $units_codes));
				$unitRemoval = $wpdb->query($sql);
			}
		} else {
                $sql = "DELETE FROM $wpdb->rp_units WHERE fpID = '{$fpID}';";
              
                // Prepare the sql query with wpdb so we can add all the unit codes to the NOT IN clause
                $unitRemoval = $wpdb->query($sql);
        } // endif

        if ( count($units) > 0 ) {
			foreach ( $units as $unit ) {
				$newUnit = $this->persist(new rentPress_Repositories_Units($rentType, $leaseTermSelection), $unit);
				
				if ( is_object($newUnit) && isset($newUnit->error) ) {
					$this->log->event('Unit could not be created. Unit Code: ' . $unit->Identification->UnitCode);
					continue;
				}
			}
        }
	}

	/**
	 * Store the floor plans that are included on an imported property
	 * @param  stdObject $floorPlans   [Array of floor plans on imported property]
	 * @param  [string] $propertyCode  [Property code of the imported property]
	 * @return VOID                    [No return values]
	 */
	private function storeFloorPlans($floorPlans, $propertyCode)
	{
		$floorPlanIDs = [];
		foreach ($floorPlans as $floorPlan) {
			$floorPlanIDs[] = sanitize_text_field($floorPlan->Identification->FloorPlanCode);

			$newFloorPlan = $this->persist(new rentPress_Repositories_FloorPlans(), $floorPlan);
			
			if ( is_object($newFloorPlan) && isset($newFloorPlan->error) ) {
				$this->log->error('Error creating or updating new floor plan: '.$newFloorPlan->error->message);
				continue;
			}
		}

		$floorPlanCleanse = $this->cleanUpFloorPlans($floorPlanIDs, $propertyCode);
	}

	private function cleanUpExcessUnits($allStoredUnitCodes) {
		global $wpdb;

		$sql_query="DELETE FROM $wpdb->rp_units WHERE fpID = '{$fpID}' AND prop_code = '{$propCode}' AND unit_code NOT IN (".implode(', ', array_fill(0, count($units_codes), '%s')).")";



	}

	private function cleanUpFloorPlans($floorPlanIDsInImport, $propertyCode)
	{

		//if (count($floorPlanIDsInImport) > 0) {

			$floorPlansToRemove = new WP_Query([
				'post_type' => 'floorplans', 
				'post_status' => ['draft', 'publish'],
				'nopaging' => true,
				'meta_query' => [
					[
						'key' => 'fpID',
						'value' => $floorPlanIDsInImport,
						'compare' => 'NOT IN'
					],
					[
						'key' => 'parent_property_code',
						'value' => $propertyCode,
						'compare' => '='
					]
				]
			]);

			if ( $floorPlansToRemove->have_posts() ) : 
				global $wpdb;
				while ( $floorPlansToRemove->have_posts() ) : $floorPlansToRemove->the_post();
					$fpID = get_post_meta($floorPlansToRemove->post->ID, 'fpID', true);		

					$unitRemoval = $wpdb->query(
						$wpdb->prepare(
							"DELETE FROM $wpdb->rp_units WHERE prop_code = %s AND fpID = %s",
							$propertyCode,
							$fpID
						)
					);

					$this->persistFloorplansCaluMetaData($floorPlansToRemove->post->ID);
				endwhile; 

				wp_reset_postdata();
			else :
				return 'No floor plans to remove';
			endif;
		
		//}
	
		return 'success';
	}

	public function persistFloorplansCaluMetaData($floorplanPost) {
		global $wpdb;

		$availableUnits = $wpdb->get_col(
        	$wpdb->prepare(
        		"SELECT COUNT(*) FROM $wpdb->rp_units WHERE is_available = TRUE AND prop_code = %s AND fpID = %s AND rent > 0",
				get_post_meta($floorplanPost, 'parent_property_code', true),
				get_post_meta($floorplanPost, 'fpID', true)
        	)	
        )[0];

      	$numberOfCapturedUnits=$wpdb->get_var(
      		$wpdb->prepare(
      			"SELECT COUNT(*) as count FROM $wpdb->rp_units WHERE prop_code = %s AND fpID = %s and rent > 0",
				get_post_meta($floorplanPost, 'parent_property_code', true),
				get_post_meta($floorplanPost, 'fpID', true)
      		)
      	);

		$floorplan_meta_data=[
			'fpUnitsCaptured' => $numberOfCapturedUnits,
			'fpAvailUnitCount'=> isset($availableUnits) && ! is_null($availableUnits) ? $availableUnits : null,
            //'fpAvailableUnitsInThirty'=> isset($floorPlan->Information->UnitsAvailable30) ? $floorPlan->Information->UnitsAvailable30 : null,
            //'fpAvailableUnitsInSixty'=> isset($floorPlan->Information->UnitsAvailable60) ? $floorPlan->Information->UnitsAvailable60 : null,
		];

		foreach ($floorplan_meta_data as $key => $value) {
			update_post_meta($floorplanPost, $key, $value);
		}
	}

	/**
	 * Import and update a single property
	 * @param  string  $postID       [Post ID of property to update]
	 * @param  boolean $kill 		 [Whether or not a failed check should kill the process]
	 * @return [Success]             [Success response]
	 */
	public function importSinglePropertyByPostId($postID, $postType = null, $kill = true)
	{
		global $wpdb;
		$postType = $postType ?: $_REQUEST['current_post_type'];
		$public = $kill ? true : false;

		// Check if the current_post_type key/value pair exists in $_REQUEST 
		if (! isset($postType) ) {
			$this->notifications->errorResponse('Post type not found in request.', $public, $kill);
			if ( $kill ) die(); // Kill process
		}

		// Check if the post type value is a string
		if (! is_string($postType) ) {
			$this->notifications->errorResponse('Post type received is not a string, instead got: '.$postType, $public, $kill);
			if ( $kill ) die(); // Kill process
		}

		if (! intval($postID) ) {
			$this->notifications->errorResponse('Post ID format is invalid.', $public, $kill);
			if ( $kill ) die(); // Kill process
		}

		// Decide between using meta key for property post or floor plan post
		$propCodeMetaKey =  $postType == 'properties' ? 'prop_code' : 'parent_property_code';
		$propertyCode = get_post_meta($postID, $propCodeMetaKey, true);
		
		self::importSinglePropertyByPropCode($propertyCode, $postID, $kill);
	}
	/**
	 * Import and update a single property 
	 * @param  string  $prop_code    [Property code of property to update]
	 * @param  boolean $kill 		 [Whether or not a failed check should kill the process]
	 * @return [Success]             [Success response]
	 */
	public function importSinglePropertyByPropCode($prop_code, $post_id = null, $kill = true)
	{
		global $wpdb;
		$public = $kill ? true : false;

		$property = json_decode($this->fetchPropertyByCode($prop_code));

		// Check if there was an error in fetching the property. Spit out error if there was
		if ( isset($property->error) ) {
			$this->notifications->errorResponse($property->error->message, $public, $kill);
			if ( $kill ) die();
		}

		if ( empty($property) ) {
			$this->notifications->errorResponse('Empty response received from Top Line Connect for property with code: '.$prop_code, $public, $kill);
			if ( $kill ) die();
		}

		// Check if there was an error in fetching the property. Spit out error if there was
		if (! is_object($property) ) {
			$this->notifications->errorResponse('Invalid response object from Top Line Connect for property with code: '.$prop_code, $public, $kill);
			if ( $kill ) die();
		}

		$RP_rentType = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->options->getOption('unit_rent_type')))) ?: 'MarketRent';
        $RP_leaseTermSelection = $this->options->getOption('unit_lease_term') ?: '12';

        if (isset($property->ResponseData->data->Identification->PropertyCode)) {

			// Should only ever return one property, but will use as an array for processing
			$properties = [ $property->ResponseData->data ];
			$propertyName = '';

			foreach ($properties as $property) {
				if (is_object($property)) {				
					$propertyName = $property->Information->PropertyName;
					$prop_floorplans = $property->floorplans->data;
					
					// Storing Units In DB Table
					foreach ($prop_floorplans as $floorplan) {
						$units = $floorplan->units->data;

						if ( isset($units) && count($units) > 0 ) {
							foreach ( $units as $unit ) {
								$unit->Image = new stdClass;
								$unit->Image->FloorPlanImage = isset($floorplan->Information->FloorPlanImage) ? $floorplan->Information->FloorPlanImage : 'https://placehold.it/400x200';
							}
						
							$this->storeUnits(
								$units, 
								$RP_rentType, 
								$RP_leaseTermSelection,
								$floorplan->Identification->FloorPlanCode
							);
						}
						else { // no units in feed, so remove units for this floorplan/prop in our unit table.
							$unitRemoval = $wpdb->query(
								$wpdb->prepare(
									"DELETE FROM $wpdb->rp_units WHERE fpID = %s AND prop_code = %s",
									$floorplan->Identification->FloorPlanCode,
									$floorplan->Identification->ParentPropertyCode
								)
							);
						} // end if
					} // end floor plan foreach
	
					// Store floorplans as post
					$this->storeFloorPlans($prop_floorplans, $property->Identification->PropertyCode);									

					$newProperty = $this->persist(new rentPress_Repositories_Properties(), $property);
					
					if ( is_object($newProperty) && isset($newProperty->error) ) {
						$this->log->error('Error creating or updating new property: '.$newProperty->error->message);
						
						if ( $kill ) {
							$this->notifications->errorResponse('Error updating '.$propertyName.': '.$newProperty->error->message, $public, $kill);
							die();
						}
					}

				}
			}

		}

		$this->successNotification(0, "Successfully resynced {$propertyName}.", $kill);
	}

	/**
	 * Return success notification, or log it
	 * @return [echo/log write]         [Either returns success notification for AJAX or logs success]
	 */
	public function successNotification($count = 0, $messageOverride = null, $kill = true)
	{
		// If we are performing an automatic refresh, just log the success message
		if ( isset($this->isAutoRefresh) && $this->isAutoRefresh ) {
			$message = $this->successMessage ?: 'RentPress has finished re-syncing imported properties.';
			$this->log->event($message);
		} else { // otherwise, return success notification to page
			$message = isset($messageOverride) ? $messageOverride : 'Successfully imported '.$this->propertyCount.' properties.';
			$this->log->event('[Manual Refresh] '.$message);
			if ( $kill ) {
				echo $this->notifications->successResponse($message);
				die();
			}
		}
	}

	/**
	 * Request properties from specific page
	 * @param  string $nextRoundUrl [URL needed to get next round of current property pages]
	 * @return JSON                 [JSON Response of properties]
	 */
	public function requestPropertiesByRound($nextRoundUrl)
	{
		$properties = isset($nextRoundUrl) ?
						json_decode($this->setUrlOverride($nextRoundUrl)->fetchProperties()) : 
						json_decode($this->fetchProperties());
		return $properties;
	}

	/**
	 * Fetch properties from RentPress Connect
	 * @return JSON [TLC JSON Response]
	 */
	public function fetchProperties($page = 1, $limit = 10)
	{
		$parameters = [
            'propertyLimit' => $limit,
            'getPage' => $page,
            'include' => 'floorplans.units'
		];
		return $this->get('/properties', $parameters);
	}

	/**
	 * Fetch properties from RentPress Connect
	 * @return JSON [TLC JSON Response]
	 */
	public function fetchPropertyByCode($propertyCode, $page = 1, $limit = 10)
	{
		$parameters = [
            'propertyLimit' => $limit,
            'getPage' => $page,
            'include' => 'floorplans.units'
		];
		return $this->get("/properties/{$propertyCode}", $parameters);
	}

	public function fetchPropertyCodes() {

		$parameters=[];

		return $this->get("/property_codes", $parameters);

	}

}