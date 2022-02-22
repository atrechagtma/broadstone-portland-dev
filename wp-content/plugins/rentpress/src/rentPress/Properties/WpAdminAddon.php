<?php 
	class rentPress_Properties_WpAdminAddon{

		public function run() {
			add_action('restrict_manage_posts', [$this, 'admin_list_filters']);
		}

		public function admin_list_filters() {
		    if (isset($_GET['post_type']) && $_GET['post_type'] == 'properties') {
		    	$neighborhoods=get_posts([
		    		'post_type' => 'neighborhoods',
		    		'nopaging' => true,
		    	]);

		    	$distinct_beds = rentPress_searchHelpers::get_distinct_meta_values_from('floorplans', 'fpBeds', true, 'float');

		    	$distinct_baths = rentPress_searchHelpers::get_distinct_meta_values_from('floorplans', 'fpBaths', true, 'float');
		    	?>
		    		<select name="properties_from_neighborhood">
		    			<option value="">Filter By Neighborhood</option>

		    			<?php foreach ($neighborhoods as $neighborhood) { ?>
		    				<option value="<?php echo $neighborhood->ID; ?>" <?php selected($neighborhood->ID, $_GET["properties_from_neighborhood"]); ?>>
		    					<?php echo $neighborhood->post_title; ?>	
		    				</option>
		    			<?php } ?>
		    		</select>

		    		<select name="properties_beds">
		    			<option value="">Filter By Floorplans Beds</option>
		    	
		    			<?php 
			        		$current_v = isset($_GET['properties_beds'])?$_GET['properties_beds']:'';
				           
				            foreach ($distinct_beds as $bed) {
				          		echo "
				          			<option value='". $bed ."' ". selected($bed, $current_v) .">". (($bed == 0)?"Studio":"". $bed ." Bedroom". (($bed > 1)?"s":"") ."") ."</option>
				          		";
				          	} 
			        	?>
		    		</select>

		    		<select name="properties_baths">
		    			<option value="">Filter By Floorplans Baths</option>
		    	
		    			<?php 
			        		$current_v = isset($_GET['properties_baths'])?$_GET['properties_baths']:'';
				           
				            foreach ($distinct_baths as $bath) {
				          		echo "
				          			<option value='". $bath ."' ". selected($bath, $current_v) .">". $bath ." Bathroom". (($bath > 1)?"s":"") ."</option>
				          		";
				          	} 
			        	?>
		    		</select>
		    	<?php
		    }
		} 
		
	}