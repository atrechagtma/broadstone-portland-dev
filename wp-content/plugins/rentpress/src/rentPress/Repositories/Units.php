<?php
class rentPress_Repositories_Units implements rentPress_Base_Repository {
	public static $requiredFields  = ['unit_code', 'prop_code', 'fpID', 'beds', 'baths', 'sqft'];

	public function __construct($rentType = 'MarketRent', $leaseTermSelection = '12')
	{
		$this->rentType = $rentType;
		$this->leaseTermSelection = $leaseTermSelection;
		$this->log = new rentPress_Logging_Log();
		$this->respond = new rentPress_Helpers_Responder();
		$this->unit_meta = rentPress_Posts_Meta_Units::get_instance();
		$this->options = new rentPress_Options();
	}

	public function persist($unit)
	{
		return $this->updateOrCreateUnit($unit);
	}

	public function updateOrCreateUnit($unit) {	
		global $wpdb;

		$Unit = $this->unit_meta->fromUnit( $unit );

		$rent=$Unit->rent();
    	
    	$unit=apply_filters('rentPressSync_unitData', $unit);

    	// Fields DB
    	$db_cols = [ 
			'unit_code' => $unit->Identification->UnitCode, 
			'prop_code' => $unit->Identification->ParentPropertyCode,
			'fpID' => $unit->Identification->ParentFloorPlanCode,
			'is_available_on' => date('Y-m-d', strtotime($unit->Information->AvailableOn)),
			'is_available' => (int) $unit->Information->isAvailable,
			'rent' => $rent,
			'beds' => isset($unit->Rooms->Bedrooms) ? $unit->Rooms->Bedrooms : 0,
			'baths' => isset($unit->Rooms->Bathrooms) ? $unit->Rooms->Bathrooms : 0,
			'sqft' => $unit->SquareFeet->Max,
			'tpl_data' => json_encode($unit), 
		];
		
		// Check if required fields are provided and not null/empty
		$requirementsMet = false;
		$db_cols_without_empty_values = array_filter($db_cols, 'strlen');
	
    	// Unit Validation Before Updating And Inserting Into DB TABLE
    	if ($rent <= 99 || $unit->SquareFeet->Max <= 99) {
			$this->log->error('Could not persist unit #'. $unit->Identification->UnitCode.' on property with code: '.$unit->Identification->ParentPropertyCode. ' because of invalid rent or square feet data');
    	}
    	elseif ($unit->Information->isAvailable == false && ($unit->Information->AvailableOn != '' || $unit->Information->AvailableOn != null)) {
			$this->log->error('Could not persist unit #'. $unit->Identification->UnitCode.' on property with code: '.$unit->Identification->ParentPropertyCode. ' because of invalid availableOn field');
    	}

		if (
			(count(array_intersect(array_keys($db_cols_without_empty_values), self::$requiredFields)) == count(self::$requiredFields))
			&&
			preg_match("/((\d*\,\d*)|(\d{3}))/", (string) $rent)
			&&
			$unit->SquareFeet->Max > 99
			// ToDo: Ask Joshua what this is here for, and for what use case/edge case is it necessary
			// &&
			// (
			// 	($unit->Information->isAvailable == false && ($unit->Information->AvailableOn != '' || $unit->Information->AvailableOn != null))
			// 	||
			// 	$unit->Information->isAvailable == true
			// )
		) { 
			$requirementsMet = true;
		}
		
		if ($requirementsMet) {
			$did_unit_update = $wpdb->update($wpdb->rp_units, $db_cols, [
				'unit_code' => $unit->Identification->UnitCode,
				'fpID'      => $unit->Identification->ParentFloorPlanCode,
				'prop_code' => $unit->Identification->ParentPropertyCode
			]);

			if (! $did_unit_update) {
				$unit_exists = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT unit_id, unit_code FROM $wpdb->rp_units WHERE unit_code = %s AND fpID = %s AND prop_code = %s LIMIT 1", 
						$unit->Identification->UnitCode,
						$unit->Identification->ParentFloorPlanCode,
						$unit->Identification->ParentPropertyCode
					)
				);

				if (! isset($unit_exists->unit_code)) {
					$inserted_unit = $wpdb->insert( $wpdb->rp_units, $db_cols);
					
					if ( ! $inserted_unit ) {
						$this->log->error('Could not persist unit #'.$unit->Identification->UnitCode.' on property with code: '.$unit->Identification->ParentPropertyCode);
					}
				}
			}	
		}
		else {
			$this->log->error('Could not save unit with code #'.$unit->Identification->UnitCode.' because required parameters are missing.');
		}
	}
}
