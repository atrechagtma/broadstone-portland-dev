<?php	
	class rentPress_Units_WpAdminAddUnit{
		function __construct() {
			$this->options=new rentPress_Options();
		}	

		public function run()
		{
			// Add main menu page
			if ($this->options->getOption('api_token') == '') {
				add_action( 'admin_menu', [$this, 'WpAdminAddUnit'], 99 );
			}

			add_action('admin_post_add_unit', [$this, 'post_of_adding_unit']);
		}

		public function WpAdminAddUnit()
		{
			add_submenu_page(

				'rp_units_viewer',
				'Add New Units',
				'Add New Units',
				'manage_options',
				'rp_unit_add',
				[ $this, 'render_page' ],
				7,
				'dashicons-screenoptions'
			);
		}

		public function render_page() {
			global $wpdb;

			$properties=get_posts([
				'post_type' => 'properties', 
				'post_status' => ['publish', 'draft'], 
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_query' => [
					[
						'key' => 'prop_code',
						'compare' => 'EXISTS',
					],
				],
				'nopaging' => true, 
			]);

			ob_start(); ?>

			<div id="rp-add-unit-wrapper">
				<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
					<h2>Add New Unit</h2>
					<p>Enter unit data below.</p>
					<table class="form-table">
						<tbody>
							<input type="hidden" name="action" value="add_unit">
							<tr class="add-unit-field">
								<th scope="row">Property and Floorplan</th>
								<td>
									<select name="parent_floorplan_code" required="">
							        	<option value="" selected disabled>Select Floorplan from Property</option>

										<?php
											$current_v = isset($_REQUEST['query']['post_id'])?$_REQUEST['query']['post_id']:'';

											foreach ($properties as $property) {
												echo "<option disabled>". $property->post_title ."</option>";

												$floorplans=get_posts([
													'post_type' => 'floorplans',
													'post_status' => ['publish', 'draft'], 
													'floorplans_of_property' => $property->ID,
													'orderby' => 'title',
													'order' => 'ASC',
													'nopaging' => true,
												]);

												foreach ($floorplans as $floorplan) {
													echo "<option value='". get_post_meta($floorplan->ID, 'fpID', true) ."' ". selected($floorplan->ID, $current_v) .">&nbsp;&nbsp;&nbsp;". $floorplan->post_title ."</option>";
												}
											} 
										?>
									</select>
								</td>
							</tr>
							<tr class="add-unit-field">
								<th scope="row">Unit Number</th>
								<td>
									<input type="text" name="unit_number" placeholder="" required>
								</td>
							</tr>																			
							<tr class="add-unit-field">
								<th scope="row">Rent</th>
								<td>
									<input type="number" name="unit_rent" min='100' placeholder="" required>
								</td>
							</tr>														
							<tr class="add-unit-field">
								<th scope="row">Application Url</th>
								<td>
									<input type="url" name="application_url" placeholder="https://example.com" required>
								</td>
							</tr>										
						</tbody>
						
					</table>
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Save New Unit">
					</p>
				</form>
			</div>

			<?php
			echo ob_get_clean();
		}

		public function post_of_adding_unit() {
        	
        	$units_repo=new rentPress_Repositories_Units();
        	$fp_repo=new rentPress_Repositories_FloorPlans();

        	$fp_post_id=$fp_repo->floorPlanExists( $_POST['parent_floorplan_code'] );
        	$property_code=get_post_meta($fp_post_id, 'parent_property_code', true);
        	$beds=get_post_meta($fp_post_id, 'fpBeds', true);
        	$baths=get_post_meta($fp_post_id, 'fpBaths', true);
        	$sqr_ft=get_post_meta($fp_post_id, 'fpMinSQFT', true);

			$tpl_data=[
	            'Identification' => [
	                'UnitCode' => $_POST['unit_number'],
	                'BuildingNumber' => null,
	                'UnitSpaceID' => null,
	                'ParentPropertyCode' => $property_code,
	                'ParentFloorPlanCode' => $_POST['parent_floorplan_code'],
	            ],
	            'Information' => [
	                'Name' => null,
	                'AvailableOn' => date('Y-m-d', strtotime('now')),
	                'AvailabilityURL' => $_POST['application_url'],
	                'ReadyDate' => null,
	                'UnitType' => null,
	                // 'isAvailable' => (boolean) $unit['unit_occupancy_status'],
	                'isAvailable' => true,
	                'SpecialsMessage' => null,
	                'FloorPlanImage' => null
	            ],
	            'QuickLinks' => [
	                'ScheduleTourUrl' => null,
	                'QuoteUrl' => null,
	                'MatterportUrl' => null,
	                'Application' => $_POST['application_url']
	            ],
	            'Rent' => [
	                'Amount' => $_POST['unit_rent'],
	                'EffectiveRent' => $_POST['unit_rent'],
	                'MarketRent' => $_POST['unit_rent'],
	                'MinRent' => $_POST['unit_rent'],
	                'MaxRent' => $_POST['unit_rent'],
	                'TermRent' => $_POST['unit_rent'],
	                'BestPrice' => $_POST['unit_rent']
	            ],
	            'Rooms' => [
	                'Bedrooms' => $beds,
	                'Bathrooms' => $baths,
	                'FloorLevel' => null
	            ],
	            'SquareFeet' => [
	                'Min' => $sqr_ft,
	                'Max' => $sqr_ft
	            ],
	            'Amenities' => null, // Soon we will store unit based amenities
	            'Images' => null,
	            'Videos' => null
        	];

        	$tpl_data=json_encode($tpl_data);
        	$tpl_data=json_decode($tpl_data, false);

        	$units_repo->persist($tpl_data);

        	$fp_ranges=$fp_repo->ranges( $_POST['parent_floorplan_code'] );
        	
        	$fp_metas=[
        		'fpUnitsCaptured' => $fp_ranges->units_captured,
        		'fpAvailUnitCount' => isset($fp_ranges->units_available) && ! is_null($fp_ranges->units_available) ? $fp_ranges->units_available : 0,	
        	];
        	
	      	if (! is_null($fp_ranges->rent ) && isset($fp_ranges->rent->minRent)) {
	      		$fp_metas['fpMinRent'] = $fp_ranges->rent->minRent;
           		$fp_metas['fpMaxRent'] = $fp_ranges->rent->maxRent;
	      	}

        	$fp_repo->updateFloorPlanMeta($fp_post_id, $fp_metas);

	        wp_safe_redirect(admin_url('admin.php?page=rp_units_viewer'));

		}
	}