<?php 

/**
* WP Floor plan repository
*/
class rentPress_Repositories_FloorPlans implements rentPress_Base_Repository
{
	protected $options;

	public function __construct()
	{
		$this->options = new rentPress_Options();
		$this->notifications = new rentPress_Notifications_Notification();
		$this->log = new rentPress_Logging_Log();
		$this->respond = new rentPress_Helpers_Responder();
	}

	/**
	 * Initiate floor plan persistance logic
	 * @param  stdClass $floorPlan [Floor plan object array]
	 * @return mixed               [Success/failure message]
	 */
	public function persist($floorPlan)
	{
		return $this->updateOrCreateFloorPlan($floorPlan);
	}

	public function isFloorplansDataGood($floorPlan) 
	{
		return ($floorPlan['fpMinSQFT'] > 99 && $floorPlan['fpMinRent'] > 99);
	}

	/**
	 * Determine whether to update or create floor plan, then do so
	 * @param  stdClass $floorPlan [Floor plan object]
	 * @return array               [Success/Failure message]
	 */
	public function updateOrCreateFloorPlan($floorPlan)
	{
		// Check to see if we're getting the right type of data for floor plan update/create
		if ( ! is_object($floorPlan) ) {
			$this->log->error('Invalid floor plan object. Actual value: '.json_encode($floorPlan));
			return $this->respond->error('Invalid floor plan object on update/create.', 406); // 406 'Not acceptable'
		}

		// Reconstruct response into acceptable meta-data key/value structure for Wordpress consumption
		$floorPlan = $this->reformatFloorPlanForInsert($floorPlan);
		
		$postID = (int) $this->floorPlanExists($floorPlan['fpID']);

		if ( $postID ) {
			if ( ! is_int($postID) ) return $this->respond->insufficientData('Invalid post ID: '.$postID);
			return $this->updateFloorPlan($postID, $floorPlan);
		}
	
		return $this->createFloorPlan($floorPlan);

	}

	/**
	 * Check to see if floor plan exists based on floor plan code given
	 * @param  string $floorPlanCode [Floor plan public code]
	 * @return mixed                 [Either a post ID of the found floor plan or false]
	 */
	public function floorPlanExists($floorPlanCode)
	{
		// Make sure we're getting the right data type for the floor plan code
		if ( ! is_string($floorPlanCode) ) {
			$this->notifications->errorResponse('Incorrect data type for floor plan code used to check if floor plan exists. Expected string. Got ', 402);
			die();
		}
		
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $wpdb->postmeta.post_id as post_id
					FROM $wpdb->posts
					INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
					WHERE $wpdb->postmeta.meta_key = 'fpID' AND $wpdb->postmeta.meta_value = %s AND $wpdb->posts.post_status IN ('publish', 'draft')
					LIMIT 1
				",
				$floorPlanCode
			)
		)->post_id;
	}

	public function createFloorPlan($floorPlan)
	{
		// Make sure we're getting the right data type for the floor plan name
		if ( ! is_array($floorPlan) ) {
			$this->notifications->errorResponse('Incorrect data type for floor plan used to create a new floor plan. Expected array.', 402);
			die();
		}

		// Make sure we're getting the right data type for the floor plan name
		if ( ! is_string($floorPlan['fpName']) ) {
			$this->notifications->errorResponse('Incorrect data type for floor plan name used to create a new floor plan. Expected string.', 402);
			die();
		}

		// Variables used to create information for post
		$postVars = array(
			'post_title'      => sanitize_text_field($floorPlan['fpName']),
			'post_name'       => sanitize_title($floorPlan['fpName']),
			'post_type'       => 'floorplans',
			'post_status'     => 'publish',
		);
		// Store post, store id
		$floorPlanPostID = wp_insert_post($postVars);

		// fetch property taxonomy for floor plan
		$propertyTaxonomy = $this->fetchParentPropertyTaxonomy($floorPlan['parent_property_code']);

        // Set category dynamically to associated property
        wp_set_object_terms( $floorPlanPostID, $propertyTaxonomy, 'property_relationship', false );

		// Update post meta of most recent created floor plan post
		$this->updateFloorPlanMeta($floorPlanPostID, $floorPlan);

		do_action('rentPressSync_additionalFloorplanData', $floorPlanPostID, $floorPlan);
		do_action('rentPressSync_createFloorplan', $floorPlanPostID, $floorPlan);

		return $this->respond->success('Successfully created floor plan!', ['fpPostID' => $floorPlanPostID]);
	}

	public function fetchParentPropertyTaxonomy($parentPropertyCode)
	{
		$parent = new WP_Query([
			'post_type' => 'properties',
			'post_status' => ['draft', 'publish'],
			'meta_key' => 'prop_code',
			'meta_value' => $parentPropertyCode
		]);
		if ( $parent->have_posts() ) : while ( $parent->have_posts() ) : $parent->the_post(); 
			return sanitize_title(get_post_meta($parent->post->ID, 'propName', true));
		endwhile;endif;wp_reset_postdata();
		return false;
	}

	public function updateFloorPlan($floorPlanPostID, $floorPlan)
	{
		// Potentially update floor plan gallery -- commented out until optimized
		// $gallery = $this->updateFloorPlanGallery($floorPlanPostID, $floorPlan);

		// Make sure the floor plans are in an array format
		if ( ! is_array($floorPlan) ) {
			$this->log->error('Imported floor plan meta is not in array format. Actual value: '.json_encode($floorPlan));
			return $this->respond->error('Imported floor plan meta is not in array format.', 422);
		}

		// Make sure the floor plan's parent property code is of a string value, the reason being it could be just integers
		// or a string of characters and integers. Nuance for having a multiple integration system.
		if ( ! is_string($floorPlan['parent_property_code']) ) {
			$this->log->error($floorPlan['fpName'].' parent property code is not in string format.');
			
			return $this->respond->error($floorPlan['fpName'].' parent property code is not a string.', 422);
		}

		// fetch property taxonomy for floor plan
		$propertyTaxonomy = $this->fetchParentPropertyTaxonomy($floorPlan['parent_property_code']);

        // Set category dynamically to associated property
        wp_set_object_terms( $floorPlanPostID, $propertyTaxonomy, 'property_relationship', false );

		// Update post meta of most recent created floorPlan post
		$this->updateFloorPlanMeta($floorPlanPostID, $floorPlan);

		do_action('rentPressSync_additionalFloorplanData', $floorPlanPostID, $floorPlan);
		do_action('rentPressSync_updateFloorplan', $floorPlanPostID, $floorPlan);

		return $this->respond->success('Successfully updated floor plan!');
	}

	public function updateFloorPlanGallery($floorPlanPostID, $floorPlan)
	{
		$images = json_decode($floorPlan['fpGalleryImages']);
		$count = 0;
		if ( ! isset($images) || count($images) == 0 ) return;
		foreach ($images as $image) {
			$filename = sanitize_title($image->title) . '_' . $floorPlanPostID . '.png';
			$uploaddir = wp_upload_dir();
			$uploadfile = $uploaddir['path'] . '/' . $filename;
			// $file = wp_upload_bits( $filename, null, @file_get_contents( $image->url ) );
			if ( ! file_exists($uploadfile) ) {
				$tmp = download_url( $image->url );
				 
				$file_array = array(
				    'name' => $filename,
				    'tmp_name' => $tmp
				);
				 
				/**
				 * Check for download errors
				 * if there are error unlink the temp file name
				 */
				if ( is_wp_error( $tmp ) ) {
		            die($this->notifications->errorResponse('Problem downloading image for floor plan : '.$image->url));
				}

				$attach_id = media_handle_sideload( $file_array, $floorPlanPostID );
				/**
				 * We don't want to pass something to $id
				 * if there were upload errors.
				 * So this checks for errors
				 */
				if ( is_wp_error( $attach_id ) ) {
		            die($this->notifications->errorResponse('Problem attaching image to floor plan post: '.$floorPlanPostID));
				}
				/**
				 * No we can get the url of the sideloaded file
				 * $value now contains the file url in WordPress
				 * $id is the attachment id
				 */
				// $value = wp_get_attachment_url( $attach_id );

			} // end if -- checking if file_exists($uploadfile)
		} // end foreach
	}

	public function ranges($fpCode) {
		global $wpdb;

		$useAvailableUnitsForFloorplanRent = $this->options->getOption('use_avail_units_for_floor_plan_rent');

      	if ( $useAvailableUnitsForFloorplanRent == 'true' ) {
      		$rentRange = $wpdb->get_row(
	        	$wpdb->prepare(
	        		"SELECT MIN(rent) as minRent, MAX(rent) as maxRent FROM $wpdb->rp_units WHERE is_available = TRUE AND fpID = %s AND rent > 0",
	        		$fpCode
	        	) 
	        );

	        if (! is_null($rentRange->minRent) && ! is_null($rentRange->maxRent)) {
	        	//@ToDo Check Rent For -1 And 0 ($floorPlan->Rent->Min And $floorPlan->Rent->Max)
	        }
      	}

      	$availableUnits = $wpdb->get_col(
        	$wpdb->prepare(
        		"SELECT COUNT(*) FROM $wpdb->rp_units WHERE is_available = TRUE AND fpID = %s AND rent > 0",
        		$fpCode
        	)	
        )[0];

      	$numberOfCapturedUnits=$wpdb->get_var(
      		$wpdb->prepare(
      			"SELECT COUNT(*) as count FROM $wpdb->rp_units WHERE fpID = %s and rent > 0",
				$fpCode
      		)
      	);

      	return (object) [
      		'units_captured' => $numberOfCapturedUnits,
      		'units_available' => $availableUnits,
      		'rent' => (isset($rentRange))?$rentRange:null,

      	];
	}

	public function reformatFloorPlanForInsert($floorPlan)
	{	
		global $wpdb;

		$data_ranges=self::ranges($floorPlan->Identification->FloorPlanCode);

        return [
            'fpID'            => $floorPlan->Identification->FloorPlanCode,
            'fpName'          => $floorPlan->Information->FloorPlanName,
            'fpBeds'          => $floorPlan->Rooms->Beds,
            'fpBaths'         => number_format($floorPlan->Rooms->Baths, 2),
            'fpMinSQFT'       => $floorPlan->SquareFeet->Min, 
            'fpMaxSQFT'       => $floorPlan->SquareFeet->Max,
            'fpMinRent'       => (! is_null($data_ranges->rent ) && isset($data_ranges->rent->minRent)) ? $data_ranges->rent->minRent : $floorPlan->Rent->Min,
            'fpMaxRent'       => (! is_null($data_ranges->rent ) && isset($data_ranges->rent->maxRent)) ? $data_ranges->rent->maxRent : $floorPlan->Rent->Max,
            'fpMinDeposit'    => $floorPlan->Deposit->Min,
            'fpMaxDeposit'    => $floorPlan->Deposit->Max,
            'fpAvailUnitCount'=> (! is_null($data_ranges->units_available))? $data_ranges->units_available : $floorPlan->Information->UnitsAvailable ,
            'fpAvailableUnitsInThirty'=> isset($floorPlan->Information->UnitsAvailable30) ? $floorPlan->Information->UnitsAvailable30 : null,
            'fpAvailableUnitsInSixty'=> isset($floorPlan->Information->UnitsAvailable60) ? $floorPlan->Information->UnitsAvailable60 : null,
            'fpAvailURL'      => isset($floorPlan->Information->AvailabilityURL) ? strval($floorPlan->Information->AvailabilityURL): '#',
            'fpImg'           => isset($floorPlan->Information->FloorPlanImage) ? $floorPlan->Information->FloorPlanImage : $this->options->getOption('floorplans_default_featured_image'),
            'fpPhone'         => isset($floorPlan->ContactNumber) ? $floorPlan->ContactNumber : null,
            'fpUnits'         => null,
            'fpUnitsCaptured' => $data_ranges->units_captured,
            'parent_property_code' => $floorPlan->Identification->ParentPropertyCode,
            'fpGalleryImages' => json_encode($floorPlan->Images),
            'fpMaxRoomates' => $floorPlan->Information->MaxRoomates,
            'fpDescription' => $floorPlan->Information->Description,
            'fpVideos'      => isset($floorPlan->Videos) ? json_encode($floorPlan->Videos) : null,
            'fpAmenities'   => isset($floorPlan->Amenities) ? json_encode($floorPlan->Amenities) : null,
            'fpUnitMapping' => $floorPlan->Identification->UnitTypeMapping,
            'fpMatterport'  => $floorPlan->Information->MatterportUrl,
            'fpPDF'  		=> $floorPlan->Information->FloorPlanPDF,
            'fpSpecialsMessage' => "",
        ];
	}

	public function updateFloorPlanMeta($floorPlanPostID, $meta)
	{
		foreach ( $meta as $metaKey => $metaValue ) {
			if ( isset($metaValue) ) {
				$isOverridden = get_post_meta($floorPlanPostID, 'override_meta_'.$metaKey, true);

				if ( $isOverridden ) {
					update_post_meta($floorPlanPostID, 'override_meta_'.$metaKey, get_post_meta($floorPlanPostID, 'override_meta_'.$metaKey, true));
				} else {
					update_post_meta($floorPlanPostID, $metaKey, $metaValue);
				
					update_post_meta($floorPlanPostID, 'override_meta_'.$metaKey, null);
				}
				
				$this->checkForUpdateErrors($floorPlanPostID);
			}
		}
	}

	private function checkForUpdateErrors($post_id) {
		if ( is_wp_error($post_id) ) {
			$errors = $post_id->get_error_messages();
			$errorLog = new rentPress_Base_Import();
			foreach ($errors as $error) $errorLog->logError('RentPress Property Update Error: [WP Error] => '.$error);
		}
	}

}