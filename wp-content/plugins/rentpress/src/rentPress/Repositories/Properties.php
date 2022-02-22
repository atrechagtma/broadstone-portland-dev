<?php

/**
* WP Repository for our imported properties
*/
class rentPress_Repositories_Properties implements rentPress_Base_Repository
{
	protected $options;

	public function __construct()
	{
		$this->options = new rentPress_Options();
		$this->log = new rentPress_Logging_Log();
		$this->respond = new rentPress_Helpers_Responder();

		$this->unit_meta = rentPress_Posts_Meta_Units::get_instance();
	}

	public function persist($property)
	{
		return $this->updateOrCreateProperty($property);
	}

	public function updateOrCreateProperty($property)
	{
		$property = $this->reformatPropertyForInsert($property);

		$postID = (int) $this->propertyExists($property['prop_code']);

		if ( $postID ) {
			return $this->updateProperty($postID, $property);
		}

		return $this->createProperty($property);
	}

	public function propertyExists($propertyCode = "")
	{
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT $wpdb->postmeta.post_id as post_id
					FROM $wpdb->posts 
					INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
					WHERE $wpdb->postmeta.meta_key = 'prop_code' AND $wpdb->postmeta.meta_value = %s AND $wpdb->posts.post_status IN ('publish', 'draft')
					LIMIT 1
				",
				$propertyCode
			)
		)->post_id;
	}

	public function createProperty($property)
	{
		// Get property name, validate that it's a string
		$propertyName = sanitize_text_field($property['propName']);

		// Check to see if it's an actual string
		if ( ! is_string($propertyName) && ! empty($propertyName) ) 
			return $this->respond->insufficientData('Invalid type for property name. String expected, instead got: '.$propertyName);

		// Variables used to create information for post
		$postVars = array(
			'post_title'      => $propertyName,
			'post_name'       => sanitize_title($propertyName),
			'post_type'       => 'properties',
			'post_status'     => 'draft',
		);
		// Store post, store id
		$propertyPostID = wp_insert_post($postVars);

		if ( ! is_int($propertyPostID) || ! $propertyPostID )
			return $this->respond->error('Error importing new property and inserting as wp post.');

		// Update post meta of most recent created property post
		$this->updatePropertyMeta($propertyPostID, $property);
		$this->updatePropertyTerms($propertyPostID, $property);

		do_action('rentPressSync_additionalPropertyData', $propertyPostID, $property);
		do_action('rentPressSync_createProperty', $propertyPostID, $property);

		return $this->respond->success('Successfully created property for '.$property['propName']);
	}

	public function updateProperty($propertyPostID, $property)
	{

		// Update post meta of most recent created property post
		$this->updatePropertyMeta($propertyPostID, $property);
		$this->updatePropertyTerms($propertyPostID, $property);

		do_action('rentPressSync_additionalPropertyData', $propertyPostID, $property);
		do_action('rentPressSync_updateProperty', $propertyPostID, $property);

		return $this->respond->success('Successfully resynced '.$property['propName']);
	}

	public function reformatPropertyForInsert($property)
	{
		global $wpdb;

        $currentZips = $this->options->getOption('property_zipcodes');
        $zip = $property->Location->ZipCode;
		$safeZipCode = intval( $zip );
		if ( ! $safeZipCode ) {
	    	$safeZipCode = '';
		}
		if ( strlen( $safeZipCode ) > 5 ) {
		  $safeZipCode = substr( $safeZipCode, 0, 5 );
		}

        $this->options->updateOption('property_zipcodes', implode(':', [$currentZips, $safeZipCode]));
        $propURL = isset($property->Information->Website) ? $property->Information->Website : '#';
    	$betterRanges = $this->determinePropertyDataRanges( $property->Identification->PropertyCode, $property->floorplans->data);

      	$numberOfCapturedUnits=$wpdb->get_var(
      		$wpdb->prepare(
      			"SELECT COUNT(*) as count FROM $wpdb->rp_units WHERE prop_code = %s and rent > 0",
				$property->Identification->PropertyCode
      		)
      	);

      	$numberOfAvailableUnits=$wpdb->get_var(
      		$wpdb->prepare(
      			"SELECT COUNT(*) as count FROM $wpdb->rp_units WHERE prop_code = %s and is_available = TRUE and rent > 0",
				$property->Identification->PropertyCode
      		)
      	);

        return [
            'propName'          => trim($property->Information->PropertyName),
            'propAddress'       => $property->Location->Address,
            'propCity'          => ucwords(trim($property->Location->City)),
            'propState'         => trim($property->Location->State),
            'propZip'           => trim($property->Location->ZipCode),
            'propURL'           => $propURL,
            'propAvailUrl'      => $property->Information->AvailabilityURL,
            'propTourLink'      => $property->Information->TourUrl,
            'propDescription'   => isset($property->Information->Description) ? $property->Information->Description : 'No Description',
            'propStaffDescription' => isset($property->Information->StaffDescription) ? $property->Information->StaffDescription : 'No Description',
            'propEmail'         => isset($property->Information->Email) ? $property->Information->Email : NULL,
            'propLatitude'      => $property->Location->Latitude,
            'propLongitude'     => $property->Location->Longitude,
            'prop_code'         => $property->Identification->PropertyCode,
            
            'propMinRent'       => $betterRanges->minRent,
            'propMaxRent'       => $betterRanges->maxRent,

            'wpPropMinRent'       => $betterRanges->wpRent->min,
            'wpPropMaxRent'       => $betterRanges->wpRent->max,
            
            'propBedsList'      => json_encode($betterRanges->listOfBeds),
            'wpPropBedsList'      => json_encode($betterRanges->wpBedsList),
            
            'propBedsThatAreUnavailable' => json_encode($betterRanges->unavailableBedCounts),
            
            'propMinBeds'       => $betterRanges->minBeds,
            'propMaxBeds'       => $betterRanges->maxBeds,

            'wpPropMinBeds'     => $betterRanges->wpBeds->min,
            'wpPropMaxBeds'     => $betterRanges->wpBeds->max,

            'propBathsList'     => json_encode($betterRanges->listOfBaths),
            'propMinBaths'      => $betterRanges->minBaths,
            'propMaxBaths'      => $betterRanges->maxBaths,

            'wpPropMinBaths'  	=> $betterRanges->wpBaths->min,
            'wpPropMaxBaths'  	=> $betterRanges->wpBaths->max,

            'propMinSQFT'       => $betterRanges->minSqft,
            'propMaxSQFT'       => $betterRanges->maxSqft,

            'wpPropMinSQFT' 	=> $betterRanges->wpSQFT->min,
            'wpPropMaxSQFT' 	=> $betterRanges->wpSQFT->max,

            'propPhoneNumber' => isset($property->Information->PhoneNumber) ? rentPress_Posts_Meta_Properties::format_phone_number($property->Information->PhoneNumber, $this->options->getOption('phone_number_format')) : null,
            'amenities' => isset($property->Amenities) ? json_encode($property->Amenities) : null, // Get Rid Of In 2018
            'propCommunityAmenities' => isset($property->CommunityAmenities) ? json_encode($property->CommunityAmenities) : null, // Get Rid Of In 2018
            'propertyStaff' => json_encode($property->Staff),
            'propLogo' => $property->Images->PropertyLogo,
            'propFaxNumber' => $property->Information->Fax,
            'propMapPdf' => $property->Information->MapPDF,
            'propMapImage' => $property->Images->MapImage,
            'propGeneralPhotos' => json_encode($property->Images->GeneralPhotos),
            'propMatterportUrl' => ($property->MatterportUrl) ? $property->MatterportUrl : null,
            'propCommunityMatterports' => json_encode($property->CommunityMatterports),
            'propAssetsByNumberOfRooms' => json_encode($property->Rooms->AssetsByRoom),
            'propSchoolRanking' => $property->Analytics->Rankings->SchoolRanking,
            'propCityAverageRating' => $property->Analytics->ApartmentRatings->CityAverage,
            'propNumberOfReviews' => $property->Analytics->ApartmentRatings->NumberOfReviews,
            'propApartmentRatingPctRecommends' => $property->Analytics->ApartmentRatings->PctRecommends,
            'propOtherScores' => json_encode($property->Analytics->Other), 
            'propApplicationFee' => $property->Fees->Amount,
            'propAwards' => json_encode($property->Awards),
            'propVideos' => json_encode($property->Videos),
            'propOfficeHours' => json_encode($property->Information->OfficeHours),
            'propUnits' => null, // Get Rid Of In 2018
            'propUnitsAvailable' => $numberOfAvailableUnits, 
            'propUnitsCaptured' => $numberOfCapturedUnits,
            'propTrackingCodes' => json_encode($property->ILSTrackingCodes),
            'propTimeZone' => $property->Information->TimeZone,
            'propSpecialsMessage' => trim($property->Specials->Message),
            'propSource' => $property->Identification->PropertySource
        ];
	}

	public function determinePropertyDataRanges($prop_code, $floorplans) {
		global $wpdb;

		$shouldUseAvailableUnitsForRent = $this->options->getOption('use_avail_units_for_property_rent');

		$rangesBesidesRent=(object) [];
		$rangesBesidesRent->maxBeds = 0;
		$rangesBesidesRent->maxBaths = 0;
		$rangesBesidesRent->maxSqft = 0;

		$rangesBesidesRent->listOfBeds=[];

		$rangesBesidesRent->unavailableBedCounts=$wpdb->get_col($wpdb->prepare(
			"
				SELECT DISTINCT pm1.meta_value as beds
				FROM {$wpdb->posts} posts
				LEFT JOIN {$wpdb->postmeta} pm1 ON pm1.post_id = posts.ID 
				LEFT JOIN {$wpdb->postmeta} pm2 ON pm2.post_id = posts.ID
				LEFT JOIN {$wpdb->postmeta} pm3 ON pm3.post_id = posts.ID
				WHERE posts.post_type = 'floorplans' AND pm1.meta_key = 'fpBeds' AND pm2.meta_key = 'fpAvailUnitCount' AND pm2.meta_value = 0 AND pm3.meta_key = 'parent_property_code' AND pm3.meta_value = %s
			",
			$prop_code
		));

		$rangesBesidesRent->wpBedsList=$wpdb->get_col($wpdb->prepare(
			"
				SELECT DISTINCT pm1.meta_value as beds
				FROM {$wpdb->posts} posts
				LEFT JOIN {$wpdb->postmeta} pm1 ON pm1.post_id = posts.ID
				LEFT JOIN {$wpdb->postmeta} pm3 ON pm3.post_id = posts.ID
				WHERE posts.post_type = 'floorplans' AND pm1.meta_key = 'fpBeds' AND pm3.meta_key = 'parent_property_code' AND pm3.meta_value = %s
			",
			$prop_code
		));

		$rangesBesidesRent->wpBeds=$wpdb->get_results($wpdb->prepare(
			"
				SELECT MIN(CAST(pm.meta_value as UNSIGNED)) as min, MAX(CAST(pm.meta_value as UNSIGNED)) as max
		        FROM $wpdb->posts p
		        LEFT JOIN  $wpdb->postmeta pm ON pm.post_id = p.ID
		        LEFT JOIN  $wpdb->postmeta prop_code ON prop_code.post_id = p.ID
		        WHERE pm.meta_key = %s AND p.post_type = %s AND prop_code.meta_value = %s
			",
			'fpBeds', 'floorplans', $prop_code
		))[0];

		$rangesBesidesRent->wpBaths=$wpdb->get_results($wpdb->prepare(
			"
				SELECT MIN(CAST(pm.meta_value as UNSIGNED)) as min, MAX(CAST(pm.meta_value as UNSIGNED)) as max
		        FROM $wpdb->posts p
		        LEFT JOIN  $wpdb->postmeta pm ON pm.post_id = p.ID
		        LEFT JOIN  $wpdb->postmeta prop_code ON prop_code.post_id = p.ID
		        WHERE pm.meta_key = %s AND p.post_type = %s AND prop_code.meta_value = %s
			",
			'fpBaths', 'floorplans', $prop_code
		))[0];
		
		$rangesBesidesRent->wpSQFT=$wpdb->get_results($wpdb->prepare(
			"
				SELECT MIN(CAST(pm.meta_value as UNSIGNED)) as min, MAX(CAST(pm.meta_value as UNSIGNED)) as max
		        FROM $wpdb->posts p
		        LEFT JOIN  $wpdb->postmeta pm ON pm.post_id = p.ID
		        LEFT JOIN  $wpdb->postmeta prop_code ON prop_code.post_id = p.ID
		        WHERE pm.meta_key = %s AND p.post_type = %s AND prop_code.meta_value = %s
			",
			'fpMinSQFT', 'floorplans', $prop_code
		))[0];

		$rangesBesidesRent->wpRent=$wpdb->get_results($wpdb->prepare(
			"
				SELECT MIN(CAST(pm.meta_value as UNSIGNED)) as min, MAX(CAST(pm.meta_value as UNSIGNED)) as max
		        FROM $wpdb->posts p
		        LEFT JOIN  $wpdb->postmeta pm ON pm.post_id = p.ID
		        LEFT JOIN  $wpdb->postmeta prop_code ON prop_code.post_id = p.ID
		        WHERE pm.meta_key = %s AND p.post_type = %s AND prop_code.meta_value = %s AND pm.meta_value > 0
			",
			'fpMinRent', 'floorplans', $prop_code
		))[0];

		$rangesBesidesRent->listOfBaths=[];

		foreach ($floorplans as $floorPlan) {
			if ($floorPlan->Rooms->Beds > $rangesBesidesRent->maxBeds) {
				$rangesBesidesRent->maxBeds=$floorPlan->Rooms->Beds;
			}

			if (in_array($floorPlan->Rooms->Beds, $rangesBesidesRent->listOfBeds) == false) {
				$rangesBesidesRent->listOfBeds[]=$floorPlan->Rooms->Beds;
			}

			if ($floorPlan->Rooms->Baths > $rangesBesidesRent->maxBaths) {
				$rangesBesidesRent->maxBaths=$floorPlan->Rooms->Baths;
			}

			if (in_array($floorPlan->Rooms->Baths, $rangesBesidesRent->listOfBaths) == false) {
				$rangesBesidesRent->listOfBaths[]=$floorPlan->Rooms->Baths;
			}

			if ($floorPlan->SquareFeet->Min > $rangesBesidesRent->maxSqft) {
				$rangesBesidesRent->maxSqft=$floorPlan->SquareFeet->Min;
			}

			if ($floorPlan->SquareFeet->Max > $rangesBesidesRent->maxSqft) {
				$rangesBesidesRent->maxSqft=$floorPlan->SquareFeet->Max;
			}				
		}

		$rangesBesidesRent->minBeds = $rangesBesidesRent->maxBeds;
		$rangesBesidesRent->minBaths = $rangesBesidesRent->maxBaths;
		$rangesBesidesRent->minSqft = $rangesBesidesRent->maxSqft;

		foreach ($floorplans as $floorPlan) {
			if ($floorPlan->Rooms->Beds < $rangesBesidesRent->minBeds) {
				$rangesBesidesRent->minBeds=$floorPlan->Rooms->Beds;
			}
			
			if ($floorPlan->Rooms->Baths < $rangesBesidesRent->minBaths) {
				$rangesBesidesRent->minBaths=$floorPlan->Rooms->Baths;
			}

			if ($floorPlan->SquareFeet->Min < $rangesBesidesRent->minSqft) {
				$rangesBesidesRent->minSqft=$floorPlan->SquareFeet->Min;
			}

			if ($floorPlan->SquareFeet->Max < $rangesBesidesRent->minSqft) {
				$rangesBesidesRent->minSqft=$floorPlan->SquareFeet->Max;
			}
		}

		sort($rangesBesidesRent->listOfBeds);
		sort($rangesBesidesRent->listOfBaths);

		if ( $shouldUseAvailableUnitsForRent == 'true' ) {
			$rentRange = $wpdb->get_row(
				$wpdb->prepare("
					SELECT MIN(rent) as minRent, MAX(rent) as maxRent
					FROM $wpdb->rp_units
					WHERE is_available = TRUE AND prop_code = %s AND rent > 0
					GROUP BY prop_code",
					$prop_code
				)
			);
		}
		else {
			/*$rentRange = $wpdb->get_row(
				$wpdb->prepare("
					SELECT MIN(rent) as minRent, MAX(rent) as maxRent
					FROM $wpdb->rp_units
					WHERE prop_code = %s AND rent > 0
					GROUP BY prop_code",
					$prop_code
				)
			);	*/
		}

		if (( ! isset($rentRange) || is_null($rentRange) || is_null($rentRange->minRent)) && count($floorplans) > 0) {
			$rentRange=(object) ['maxRent' => 0];
	
			foreach ($floorplans as $floorPlan) {
				if ($floorPlan->Rent->Min > 99 && $floorPlan->Rent->Min > $rentRange->maxRent) {
					$rentRange->maxRent=$floorPlan->Rent->Min;
				}

				if ($floorPlan->Rent->Max > 99 && $floorPlan->Rent->Max > $rentRange->maxRent) {
					$rentRange->maxRent=$floorPlan->Rent->Max;
				}
			}

			$rentRange->minRent=$rentRange->maxRent;

			foreach ($floorplans as $floorPlan) {
				if ($floorPlan->Rent->Min > 99 && $floorPlan->Rent->Min < $rentRange->minRent) {
					$rentRange->minRent=$floorPlan->Rent->Min;
				}

				if ($floorPlan->Rent->Max > 99 && $floorPlan->Rent->Max < $rentRange->minRent) {
					$rentRange->minRent=$floorPlan->Rent->Max;
				}
				
			}

		}


		
		return (object) array_merge((array) $rangesBesidesRent, (array) $rentRange);
	}

	public function updatePropertyMeta($propertyPostID, $meta)
	{

		if (get_post_meta($propertyPostID, 'override_synced_property_coords_data', true) == '1') {

			unset($meta['propLatitude']);
			unset($meta['propLongitude']);

		}		

		if (get_post_meta($propertyPostID, 'prop_special_text', true) == false) {
			$meta['prop_special_text'] = "";
		}



		foreach ( $meta as $metaKey => $metaValue ) {
			// To be uncommented after optimized
			// if ( $metaKey == 'propGeneralPhotos' ) $response = $this->storePhotos($propertyPostID, $metaValue);
			// if ( $metaKey == 'propAssetsByNumberOfRooms' ) {
			// 	$assets = json_decode($metaValue);
			// 	if ( $assets && count($assets) > 0 ) {
			// 		foreach ($assets as $asset) {
			// 			// $response = $this->storePhotos($propertyPostID, $asset->bedroomImages);
			// 		}
			// 	}
			// }
			if ( isset($metaValue) && $metaValue !== '' ) {
				update_post_meta($propertyPostID, $metaKey, $metaValue);
				$this->checkForUpdateErrors($propertyPostID);
				// if ( $metaKey == 'propertyStaff' ) {
					// var_dump($update);
					// $metaValue = get_post_meta($propertyPostID, $metaKey);
					// die(var_dump($metaValue));
					// var_dump($metaValue[0]);
					// $metaValue = base64_encode(serialize(json_decode($metaValue[0])));
					// update_post_meta($propertyPostID, $metaKey, $metaValue);
					// $newVal = get_post_meta($propertyPostID, $metaKey);
					// var_dump($newVal);
					// var_dump(base64_decode($newVal[0]));
					// $test = unserialize(base64_decode($newVal[0]));
					// die(var_dump($test));
				// }
			}
			else {
				update_post_meta($propertyPostID, $metaKey, '');
			}
		}
		// die('first property images are in ');
	}

	public function updatePropertyTerms($propertyPostID, $meta) {

		// Setting Property Amenities Terms
		$propAmenities=json_decode($meta['amenities']);

		$propCommunityAmenities=json_decode($meta['propCommunityAmenities']);

		$amenities=[];

		$propAmenitiesTerms=[];

		if (is_array($propAmenities)) {
			$amenities=array_merge($amenities, $propAmenities);
		}

		if (is_array($propCommunityAmenities)) {
			$amenities=array_merge($amenities, $propCommunityAmenities);
		}

		foreach ($amenities as $amenity) {
			if ( term_exists( $amenity->Title, 'prop_amenities' ) ) {
				$gotten_term=get_term_by('name', $amenity->Title, 'prop_amenities');

				if (isset($gotten_term->term_id)) {
					$propAmenitiesTerms[]=$gotten_term->term_id;
				}

			}
			else {
				$created_term=wp_insert_term( $amenity->Title, 'prop_amenities', array(
				 	'description'=> $amenity->Description,
    				'slug' => sanitize_title($amenity->Title),
				) );

				$propAmenitiesTerms[] = $created_term->term_id;
			}
		}

		wp_set_post_terms( $propertyPostID, $propAmenitiesTerms, 'prop_amenities', false);

	}

	public function storePhotos($propertyPostID, $photos)
	{
		$images = new rentPress_Images_ImageStorage();
		return $images->storeAndAttach($propertyPostID, $photos);
	}

	private function checkForUpdateErrors($post_id) {
		if ( is_wp_error($post_id) ) {
			$errors = $post_id->get_error_messages();
			$errorLog = new rentPress_Base_Import();
			foreach ($errors as $error) $errorLog->logError('RentPress Property Update Error: [WP Error] => '.$error);
		}
	}

}
