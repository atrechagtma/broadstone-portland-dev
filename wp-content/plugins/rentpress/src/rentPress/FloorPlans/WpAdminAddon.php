<?php 

class rentPress_FloorPlans_WpAdminAddon{
	public function __construct() {

		$this->options = new rentPress_Options();

	}	

	public function run() {
		add_action('pre_get_posts', [$this, 'admin_sorting_pre_get'], 10, 1);
		add_filter('manage_floorplans_posts_columns', [$this, 'admin_list_columns'], 10, 1);
		add_filter('manage_edit-floorplans_sortable_columns', [$this, 'admin_list_sortable_columns'], 10, 1);
		add_action('manage_floorplans_posts_custom_column', [$this, 'admin_list_columns_data'], 10, 2);
		add_action('restrict_manage_posts', [$this, 'admin_list_filters']);
	}

	public function admin_list_columns($columns) {

		if ($this->options->getOption('is_site_about_a_single_property') !== 'true') {
			$columns['property'] = "Property";
		}

		$columns['fp_name'] = 'Floorplan Name';
		$columns['beds'] = 'Bedrooms';
		$columns['baths'] = 'Bathrooms';
		$columns['sqft'] = 'SQ FT';

		unset($columns['date']);
		$columns['date'] = 'Date';

		return $columns;
	}

	public function admin_list_sortable_columns($columns) {
		$columns['fp_name']='fp_name';
		$columns['beds']='beds';
		$columns['baths']='baths';
		$columns['sqft']='sqft';

		return $columns;
	}

	public function admin_list_columns_data($column_name, $post_ID) {
		switch ($column_name) {
			case 'property':
				$parent_property_code=get_post_meta($post_ID, 'parent_property_code', true);
				

				$property=get_posts([
					'post_type' => 'properties',
					'property_code' => $parent_property_code,
					'posts_per_page' => 1,
					'post_status' => 'any',
				])[0];

				$link=get_edit_post_link($property->ID);

				echo "<a href='". $link ."' target='_blank'>". $property->post_title ."</a>";
				break;

			case 'fp_name':
				echo get_post_meta($post_ID, 'fpName', true);
				break;

			case 'beds':
				echo get_post_meta($post_ID, 'fpBeds', true);
				break;

			case 'baths':			
				echo get_post_meta($post_ID, 'fpBaths', true);
				break;

			case 'sqft':			
				echo get_post_meta($post_ID, 'fpMinSQFT', true);
				break;
		}
	} 

	public function admin_sorting_pre_get($query) {
		if (! is_admin()) {
			return $query;
		}

		$orderby=$query->get('orderby');

		switch ($orderby) {
			case 'fp_name':
				$query->set('meta_key', 'fpName');
        		$query->set('orderby', 'meta_value_num');
				break;

			case 'beds':
				$query->set('meta_key', 'fpBeds');
        		$query->set('orderby', 'meta_value_num');
				break;

			case 'baths':
				$query->set('meta_key', 'fpBaths');
        		$query->set('orderby', 'meta_value_num');
				break;

			case 'sqft':
				$query->set('meta_key', 'fpMinSQFT');
        		$query->set('orderby', 'meta_value_num');
				break;			
		}

		return $query;
	}

	public function admin_list_filters() {
		$type = 'post';

	    if (isset($_GET['post_type'])) {
	        $type = $_GET['post_type'];
	    }

	    if ($type == 'floorplans') { 
		    $properties=array_map(
		    	function($rpp) {
			    	$rpp->prop_code=get_post_meta($rpp->ID, 'prop_code', true);

			    	return $rpp;
		    	}, 
		    	get_posts([
	    			'post_type' => 'properties', 
	    			'post_status' => ['publish', 'draft'], 
	    			'orderby' => 'title',
	    			'order' => 'ASC',
	    			'nopaging' => true, 
	    		])
		   	);

		   	$properties=array_filter($properties, function($rpp) {return $rpp->prop_code;});
		
		   	$distinct_beds = rentPress_searchHelpers::get_distinct_meta_values_from('floorplans', 'fpBeds', true, 'float');
	    	
	    	$distinct_baths = rentPress_searchHelpers::get_distinct_meta_values_from('floorplans', 'fpBaths', true, 'float');
	    	?>
	        <select name="floorplans_of_property">
		        <option value="">Filter By Property</option>
		        <?php
		            $current_v = isset($_GET['floorplans_of_property'])?$_GET['floorplans_of_property']:'';
		           
		            foreach ($properties as $property) {
		          		echo "
		          			<option value='". $property->prop_code ."' ". selected($property->prop_code, $current_v, false) .">". $property->post_title ."</option>
		          		";
		          	} 
		        ?>
	        </select>

	        <select name="floorplans_beds">
	        	<option value="">Bedrooms</option>
	        	<?php 
	        		$current_v = isset($_GET['floorplans_beds'])?$_GET['floorplans_beds']:'';
		           
		            foreach ($distinct_beds as $bed) {
		          		echo "
		          			<option value='". $bed ."' ". selected($bed, $current_v, false) .">". (($bed == 0)?"Studio":"". $bed ." Bedroom". (($bed > 1)?"s":"") ."") ."</option>
		          		";
		          	} 
	        	?>
	        </select>

	        <select name="floorplans_baths">
	        	<option value="">Bathrooms</option>
	        	<?php 
	        		$current_v = isset($_GET['floorplans_baths'])?$_GET['floorplans_baths']:'';
		           
		            foreach ($distinct_baths as $bath) {
		          		echo "
		          			<option value='". $bath ."' ". selected($bath, $current_v, false) .">". $bath ." Bathroom". (($bath > 1)?"s":"") ."</option>
		          		";
		          	} 
	        	?>
	        </select>
	        <?php
	    }
	}
}