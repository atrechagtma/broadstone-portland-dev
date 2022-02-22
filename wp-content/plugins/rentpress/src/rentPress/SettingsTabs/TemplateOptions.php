<?php

class rentPress_SettingsTabs_TemplateOptions extends rentPress_Base_WpSettingsSubPage{

	public static $headerLinks = 'rentPress_templates_header_links';

	public static $optionGroup = 'rentPress_templates_options';

	public static $globalOption = 'rentPress_template_global_option_section';

	public static $singleFloorplanTemplateOverrideSectionID = 'sfp_rentPress_override_setting';

	public static $defaultFeaturedImageSectionID = 'rentPress_default_featured_image_section';

	public static $floorplanGridTemplateOverrideSectionID = 'fg_rentPress_override_setting';

	public static $singlePropertyTemplateOverrideSectionID = 'sp_rentPress_override_setting';

	public static $propertyGridTemplateOverrideSectionID = 'pg_rentPress_override_setting';

	public function __construct() {
		$this->wp_menu_args = [
			'menu_title' => 'Template Options',
			'page_title' => 'Template Options',
			'page_slug' => 'rentpress_template_options'
		];

		$this->fields_keys =[
			'templates_accent_color',
			'override_unit_visibility',
			'archive_floorplans_default_sort',
			'archive_property_default_sort',
			'override_how_floorplan_pricing_is_display',
			'hide_floorplan_availability_counter',
			'hide_floorplans_without_availability',
			'override_single_floorplan_template_title',
			'override_apply_links_targets',
			'override_request_link',
			'single_floorplan_request_more_info_url',
			'show_waitlist_ctas',
			'show_waitlist_override_url',
			'override_single_floorplan_template_file',
			'single_floorplan_content_position',
			'override_archive_floorplans_template_file',
			'archive_floorplan_content_position',
			'show_floorplans_grid_featured_image',
			'floorplans_grid_featured_image_text',
			'override_single_property_template_file',
			'override_archive_properties_template_file',
			'choose_archive_properties_template_file',
			'archive_properties_min_cluster',
			'archive_properties_cluster_grid',
			'archive_properties_content_position',
			'show_property_grid_featured_image',
			'property_grid_featured_image_text',
			'properties_default_featured_image',
			'floorplans_default_featured_image',
			'cities_default_featured_image'
		];

		parent::__construct();
	}

	public function render_settings_page() {
		$common_html=new rentPress_SettingsTabs_CommonHtml();

		$common_html->openingOptionsWrapper(); ?>
		    <h1>RentPress: Template Options</h1>
		    <?php
		    settings_errors();
		    $common_html->displayOptionsTabs(); ?>

		    <form method="post" action="options.php" enctype="multipart/form-data">
		        <?php settings_fields(self::$optionGroup); ?>

		        <?php do_settings_sections($this->wp_menu_args->page_slug);?>
		        <?php submit_button();?>
		    </form>
		<?php $common_html->closingOptionsWrapper();
	}

	public function wp_setting_sections_and_fields() {

		add_settings_section(
			self::$headerLinks,
			'Jump to an options section:',
			function() { echo '<p style="font-size: 16px;">
		    	<a href="#template-globals">Global Options</a> | <a href="#single-floor-plan">Single Floor Plan</a> | <a href="#floorplans-grid">Floorplans Grid</a> | <a href="#property-listing">Property Listing</a> | <a href="#properties-search">Properties Search</a> | <a href="#placeholders">Placeholders</a>
		    	<br /><br /><br />
		    	<b>Need Help? </b>Check the support site for assistance setting up page templates: <a href="https://via.30lines.com/tFezGUBS" target="_blank" rel="noopener noreferrer">How to Set Up and Customize RentPress Templates</a>.
		    	</p><br />'; },
		    $this->wp_menu_args->page_slug
		);

		// RentPress Template Globals Section

		add_settings_section(
	        self::$globalOption,
	        '<div id="template-globals">RentPress Templates Global Options</div>',
			function() { echo '<p>These settings configure options across all included RentPress Templates.</p>'; },
	        $this->wp_menu_args->page_slug
    	);

    	add_settings_field(
	        $this->fields->templates_accent_color->name,
	        'Accent Color',
	        [$this, 'rentPress_templates_accent_color'],
	        $this->wp_menu_args->page_slug,
	        self::$globalOption
   		);

	    add_settings_field(
			$this->fields->override_unit_visibility->name,
			'Unit Visibility',
			[$this, 'rentPress_override_unit_visibility'],
			$this->wp_menu_args->page_slug,
			self::$globalOption
		);

		add_settings_field(
			$this->fields->archive_floorplans_default_sort->name,
			'Default Floor Plan Sort',
			[$this, 'rentPress_floorplans_default_sort'],
			$this->wp_menu_args->page_slug,
			self::$globalOption
		);

	    add_settings_field(
	        $this->fields->override_how_floorplan_pricing_is_display->name,
	        'Show Pricing As',
	        [$this, 'rentPress_override_how_floorplan_pricing_is_display_field'],
	        $this->wp_menu_args->page_slug,
	        self::$globalOption
    	);

		add_settings_field(
			$this->fields->hide_floorplan_availability_counter->name,
			'Floor Plan Availability Counter',
			[$this, 'rentPress_hide_floorplan_availability_counter'],
			$this->wp_menu_args->page_slug,
			self::$globalOption
		);

		add_settings_field(
			$this->fields->hide_floorplans_without_availability->name,
			'Floor Plans with No Availability',
			[$this, 'rentPress_hide_floorplans_without_availability'],
			$this->wp_menu_args->page_slug,
			self::$globalOption
		);

		add_settings_field(
			$this->fields->override_single_floorplan_template_title->name,
			'Floor Plan Title',
			[$this, 'rentPress_override_single_floorplan_title'],
			$this->wp_menu_args->page_slug,
			self::$globalOption
		);

		add_settings_field(
			$this->fields->override_apply_links_targets->name,
			"Apply Link Opens In",
			[$this, 'rentPress_override_apply_links_targets_field'],
			$this->wp_menu_args->page_slug,
			self::$globalOption
		);

		add_settings_field(
			$this->fields->override_request_link->name,
			'“Request More Info” CTA',
			[$this, 'override_request_link_field'],
			$this->wp_menu_args->page_slug,
			self::$globalOption
		);

    	add_settings_field(
	        $this->fields->single_floorplan_request_more_info_url->name,
	        '"Request More Info" URL Override',
	        [$this, 'request_info_url_field'],
			$this->wp_menu_args->page_slug,
			self::$globalOption,
	        [
	        	'class' => 'field-group-2'
	    	]
	    );
	    add_settings_field(
			$this->fields->show_waitlist_ctas->name,
			'Waitlist CTAs',
			[$this, 'show_waitlist_ctas_field'],
			$this->wp_menu_args->page_slug,
			self::$globalOption
		);

    	add_settings_field(
	        $this->fields->show_waitlist_override_url->name,
	        '"Join Waitlist" URL Override',
	        [$this, 'show_waitlist_override_url_field'],
			$this->wp_menu_args->page_slug,
			self::$globalOption,
	        [
	        	'class' => 'field-group-6'
	    	]
	    );

		register_setting(self::$optionGroup, $this->fields->override_how_floorplan_pricing_is_display->name);
	    register_setting(self::$optionGroup, $this->fields->templates_accent_color->name);
		register_setting(self::$optionGroup, $this->fields->override_unit_visibility->name);
		register_setting(self::$optionGroup, $this->fields->archive_floorplans_default_sort->name);
	    register_setting(self::$optionGroup, $this->fields->hide_floorplan_availability_counter->name);
		register_setting(self::$optionGroup, $this->fields->hide_floorplans_without_availability->name);
		register_setting(self::$optionGroup, $this->fields->override_single_floorplan_template_title->name);
		register_setting(self::$optionGroup, $this->fields->override_request_link->name);
		register_setting(self::$optionGroup, $this->fields->single_floorplan_request_more_info_url->name);
		register_setting(self::$optionGroup, $this->fields->override_apply_links_targets->name);
		register_setting(self::$optionGroup, $this->fields->show_waitlist_ctas->name);
		register_setting(self::$optionGroup, $this->fields->show_waitlist_override_url->name);

	
		/* Single floor plan template options */

		add_settings_section(
			self::$singleFloorplanTemplateOverrideSectionID,
			'<div id="single-floor-plan">Single Floor Plan Template</div>',
			[$this, 'single_floorplan_template_section_description'],
			$this->wp_menu_args->page_slug
		);

		add_settings_field(
			$this->fields->override_single_floorplan_template_file->name,
			'Single Floor Plan Template',
			[$this, 'rentPress_override_single_floorplan_file'],
			$this->wp_menu_args->page_slug,
			self::$singleFloorplanTemplateOverrideSectionID
		);

		add_settings_field(
			$this->fields->single_floorplan_content_position->name,
			'Single Floor Plan Content Position',
			[$this, 'rentPress_single_floorplan_content_position'],
			$this->wp_menu_args->page_slug,
			self::$singleFloorplanTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-3'
	    	]
		);	

		register_setting(self::$optionGroup, $this->fields->override_single_floorplan_template_file->name);
		register_setting(self::$optionGroup, $this->fields->single_floorplan_content_position->name);

		/* Floor plan grid template options */

		add_settings_section(
			self::$floorplanGridTemplateOverrideSectionID,
			'<div id="floorplans-grid">Floorplans Grid Template</div>',
			[$this, 'floorplan_grid_section_description'],
			$this->wp_menu_args->page_slug
		);

		add_settings_field(
			$this->fields->override_archive_floorplans_template_file->name,
			'Floorplans Grid Template',
			[$this, 'rentPress_override_archive_floorplans_file'],
			$this->wp_menu_args->page_slug,
			self::$floorplanGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-4'
	    	]
		);	

		add_settings_field(
			$this->fields->archive_floorplan_content_position->name,
			'Floorplans Grid Content Position',
			[$this, 'rentPress_archive_floorplan_content_position'],
			$this->wp_menu_args->page_slug,
			self::$floorplanGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-4'
	    	]
		);

		add_settings_field(
			$this->fields->show_floorplans_grid_featured_image->name,
			'Featured Image Banner',
			[$this, 'show_floorplans_featured_image'],
			$this->wp_menu_args->page_slug,
			self::$floorplanGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-4'
	    	]
		);

		add_settings_field(
	        $this->fields->floorplans_grid_featured_image_text->name,
	        'Featured Image Text',
	        [$this, 'floorplans_grid_image_text'],
	        $this->wp_menu_args->page_slug,
	        self::$floorplanGridTemplateOverrideSectionID,
	        [
	        	'class' => 'field-group-4'
	    	]
	    );

	    register_setting(self::$optionGroup, $this->fields->override_archive_floorplans_template_file->name);
	    register_setting(self::$optionGroup, $this->fields->archive_floorplan_content_position->name);
	    register_setting(self::$optionGroup, $this->fields->show_floorplans_grid_featured_image->name);
	    register_setting(self::$optionGroup, $this->fields->floorplans_grid_featured_image_text->name);

		/* Property listing template options */

		add_settings_section(
				self::$singlePropertyTemplateOverrideSectionID,
				'<div id="property-listing">Property Listing Template</div>',
				[$this, 'single_property_section_description'],
				$this->wp_menu_args->page_slug
		);

		add_settings_field(
			$this->fields->override_single_property_template_file->name,
			'Single Property Template',
			[$this, 'rentPress_override_single_property_file'],
			$this->wp_menu_args->page_slug,
			self::$singlePropertyTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-7'
	    	]
		);	

		register_setting(self::$optionGroup, $this->fields->override_single_property_template_file->name);

		/* Property search template options */

		add_settings_section(
				self::$propertyGridTemplateOverrideSectionID,
				'<div id="properties-search">Properties Search Template</div>',
				[$this, 'property_grid_section_description'],
				$this->wp_menu_args->page_slug
		);

		add_settings_field(
			$this->fields->override_archive_properties_template_file->name,
			'Properties Search Template',
			[$this, 'rentPress_override_archive_properties_file'],
			$this->wp_menu_args->page_slug,
			self::$propertyGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-8'
	    	]
		);

		add_settings_field(
			$this->fields->choose_archive_properties_template_file->name,
			'Select Search Template',
			[$this, 'rentPress_choose_archive_properties_file'],
			$this->wp_menu_args->page_slug,
			self::$propertyGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-8'
	    	]
		);

		add_settings_field(
			$this->fields->archive_properties_min_cluster->name,
			'Map Min Cluster Count',
			[$this, 'rentPress_archive_properties_min_cluster'],
			$this->wp_menu_args->page_slug,
			self::$propertyGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-8'
	    	]
		);

		add_settings_field(
			$this->fields->archive_properties_cluster_grid->name,
			'Map Marker Grid Size',
			[$this, 'rentPress_archive_properties_cluster_grid'],
			$this->wp_menu_args->page_slug,
			self::$propertyGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-8'
	    	]
		);

		add_settings_field(
			$this->fields->archive_property_default_sort->name,
			'Default Property Sort',
			[$this, 'rentPress_archive_property_default_sort'],
			$this->wp_menu_args->page_slug,
			self::$propertyGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-8'
	    	]
		);

		add_settings_field(
			$this->fields->archive_properties_content_position->name,
			'Properties Search Content Position',
			[$this, 'rentPress_archive_properties_content_position'],
			$this->wp_menu_args->page_slug,
			self::$propertyGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-8'
	    	]
		);

		add_settings_field(
			$this->fields->show_property_grid_featured_image->name,
			'Featured Image Banner',
			[$this, 'show_property_featured_image'],
			$this->wp_menu_args->page_slug,
			self::$propertyGridTemplateOverrideSectionID,
			[
	        	'class' => 'field-group-8'
	    	]
		);

		add_settings_field(
	        $this->fields->property_grid_featured_image_text->name,
	        'Featured Image Text',
	        [$this, 'property_grid_image_text'],
	        $this->wp_menu_args->page_slug,
	        self::$propertyGridTemplateOverrideSectionID,
	        [
	        	'class' => 'field-group-8'
	    	]
	    );

		register_setting(self::$optionGroup, $this->fields->override_archive_properties_template_file->name);
		register_setting(self::$optionGroup, $this->fields->choose_archive_properties_template_file->name);
		register_setting(self::$optionGroup, $this->fields->archive_properties_min_cluster->name);
		register_setting(self::$optionGroup, $this->fields->archive_properties_cluster_grid->name);
		register_setting(self::$optionGroup, $this->fields->archive_property_default_sort->name);
		register_setting(self::$optionGroup, $this->fields->archive_properties_content_position->name);
		register_setting(self::$optionGroup, $this->fields->show_property_grid_featured_image->name);
		register_setting(self::$optionGroup, $this->fields->property_grid_featured_image_text->name);

		/* Property and floor plan default featured image fallback */

	    add_settings_section(
	        self::$defaultFeaturedImageSectionID,
	        '<div id="placeholders">Placeholder Images</div>',
			function() { echo '<p>
			You can upload images for properties and floor plans that do not already have images defined in the feed.
			</p>'; },
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	        $this->fields->properties_default_featured_image->name,
	        'Placeholder Property Image',
	        [$this, 'rentPress_properties_default_featured_image'],
	        $this->wp_menu_args->page_slug,
	        self::$defaultFeaturedImageSectionID
	    );

	    add_settings_field(
	        $this->fields->floorplans_default_featured_image->name,
	        'Placeholder Floor Plan Image',
	        [$this, 'rentPress_floorplans_default_featured_image'],
	        $this->wp_menu_args->page_slug,
	        self::$defaultFeaturedImageSectionID
	    );

	    add_settings_field(
	        $this->fields->cities_default_featured_image->name,
	        'Placeholder City Image',
	        [$this, 'rentPress_cities_default_featured_image'],
	        $this->wp_menu_args->page_slug,
	        self::$defaultFeaturedImageSectionID
	    );


	    register_setting(
	    	self::$optionGroup,
	    	$this->fields->properties_default_featured_image->name,
	    	[$this, 'handle_file_upload_for_properties_featured_image']
	    );

	    register_setting(
	    	self::$optionGroup,
	    	$this->fields->floorplans_default_featured_image->name,
	    	[$this, 'handle_file_upload_for_floorplans_featured_image']
	    );

	    register_setting(
	    	self::$optionGroup,
	    	$this->fields->cities_default_featured_image->name,
	    	[$this, 'handle_file_upload_for_cities_featured_image']
	    );

	}

	public function single_floorplan_template_section_description()
	{
		echo '<p>Options specific to the Single Floor Plan template.</p>';
	}	

	public function floorplan_grid_section_description()
	{
		echo '<p>Options specific to the Floorplans Grid template.</p>';
	}	

	public function single_property_section_description()
	{
		echo '<p>Options specific to the Property Listing template.</p>';
	}	

	public function property_grid_section_description()
	{
		echo '<p>Options specific to the Properties Search template. When using the Advanced Search with Map template, make sure you have a maps JS API key entered in the <a href="/wp-admin/admin.php?page=rentpress_integrations_settings">Integrations settings tab</a>.</p>';
	}

	public function rentPress_override_single_floorplan_file()
	{
		$isChecked = checked( $this->fields->override_single_floorplan_template_file->value, RENTPRESS_PLUGIN_DIR . 'templates/FloorPlans/single-floorplan-basic.php', false );
		echo "<label>
				<input id='rentpress_single_floorplan_setting' type='checkbox' name='". $this->fields->override_single_floorplan_template_file->name ."' value='". RENTPRESS_PLUGIN_DIR . "templates/FloorPlans/single-floorplan-basic.php' {$isChecked}>
			Enable single floor plan template
			</label>
		";
	}

	public function rentPress_templates_accent_color() 
	{

		echo "<input type='color' name='{$this->fields->templates_accent_color->name}' value='{$this->fields->templates_accent_color->value}'>";

	}

	public function rentPress_override_archive_floorplans_file()
	{

		$isChecked = checked( $this->fields->override_archive_floorplans_template_file->value, RENTPRESS_PLUGIN_DIR . 'templates/FloorPlans/archive-floorplans-basic.php', false );
		echo "
			<label>
				<input id='rentPress_archive_floorplan_setting' type='checkbox' label='Testing' name='". $this->fields->override_archive_floorplans_template_file->name ."' value='". RENTPRESS_PLUGIN_DIR . "templates/FloorPlans/archive-floorplans-basic.php' {$isChecked}>
			Enable floorplans grid template
			</label>
		";
	}

	public function rentPress_override_unit_visibility()
	{
		$options=[
			"Show units with date and/or availability status" => 'unit_visibility_1',
			"Show units with availability status" => 'unit_visibility_2',
			"Show units only available as of today" => 'unit_visibility_3',
			"Show units available as of today plus Lookahead" => 'unit_visibility_4',
			"Show all units" => 'unit_visibility_5'
		];

		echo '<select name="'. $this->fields->override_unit_visibility->name .'">';

			foreach ($options as $text => $optValue) {

				$isSelected=selected($this->fields->override_unit_visibility->value, $optValue, false);

				echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

			}

		echo '</select>';
	}

	public function rentPress_floorplans_default_sort()
	{
		$options=[
			"Soonest Available" => 'avail:asc',
			"Rent: Low to High" => 'rent:asc',
			"Rent: High to Low" => 'rent:desc',
			"SQFT: Low To High" => 'sqft:asc',
			"SQFT: High to Low" => 'sqft:desc',
			"Bedrooms" => 'beds:asc'
		];

		echo '<select name="'. $this->fields->archive_floorplans_default_sort->name .'">';

			foreach ($options as $text => $optValue) {

				$isSelected=selected($this->fields->archive_floorplans_default_sort->value, $optValue, false);

				echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

			}

		echo '</select>';
	}	

	public function rentPress_archive_property_default_sort()
	{
		$options=[
			"Soonest Available" => 'avail:asc',
			"Specials" => 'specials:first',
			"Rent: Low to High" => 'price:asc',
			"Rent: High to Low" => 'price:desc',
			"Property: A to Z" => 'prop:a-z',
			"City: A to Z" => 'city:a-z'
		];

		echo '<select name="'. $this->fields->archive_property_default_sort->name .'">';

			foreach ($options as $text => $optValue) {

				$isSelected=selected($this->fields->archive_property_default_sort->value, $optValue, false);

				echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

			}

		echo '</select>';
	}

	public function rentPress_override_how_floorplan_pricing_is_display_field() 
	{
		$options=[
			"Starting At" => 'starting-at',
			'Range' => 'range',
		];

		echo '<select name="'. $this->fields->override_how_floorplan_pricing_is_display->name .'">';

			foreach ($options as $text => $optValue) {

				$isSelected=selected($this->fields->override_how_floorplan_pricing_is_display->value, $optValue, false);

				echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

			}

		echo '</select>';

	}

	public function rentPress_hide_floorplan_availability_counter () 
	{

		$isChecked = checked( $this->fields->hide_floorplan_availability_counter->value, 'true', false );
		echo "
			<label>
				<input type='checkbox' name='{$this->fields->hide_floorplan_availability_counter->name}' value='true' {$isChecked}>
				Hide floor plan available units counter
			</label>
		";

	}	

	public function rentPress_hide_floorplans_without_availability () 
	{

		$isChecked = checked( $this->fields->hide_floorplans_without_availability->value, 'true', false );
		echo "
			<label>
				<input type='checkbox' name='{$this->fields->hide_floorplans_without_availability->name}' value='true' {$isChecked}>
				Hide floor plans with no available units
			</label>
		";

	}

	public function rentPress_override_single_floorplan_title () 
	{

		$isChecked = checked( $this->fields->override_single_floorplan_template_title->value, 'true', false );
		echo "
			<label>
				<input type='checkbox' name='{$this->fields->override_single_floorplan_template_title->name}' value='true' {$isChecked}>
				Use floor plan marketing names
			</label>
		";

	}

	public function rentPress_archive_properties_content_position()
	{
		$options=[
			"Top of Page" => 'archive_properties_content_top',
			"Bottom of Page" => 'archive_properties_content_bottom'
		];

		echo '<select name="'. $this->fields->archive_properties_content_position->name .'">';

			foreach ($options as $text => $optValue) {

				$isSelected=selected($this->fields->archive_properties_content_position->value, $optValue, false);

				echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

			}

		echo '</select><p><small>Choose where you would like to show content on the properties search template.</small></p>';
	}

	public function rentPress_archive_floorplan_content_position()
	{
		$options=[
			"Top of Page" => 'archive_floorplan_content_top',
			"Bottom of Page" => 'archive_floorplan_content_bottom'
		];

		echo '<select name="'. $this->fields->archive_floorplan_content_position->name .'">';

			foreach ($options as $text => $optValue) {

				$isSelected=selected($this->fields->archive_floorplan_content_position->value, $optValue, false);

				echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

			}

		echo '</select>';
	}

	public function rentPress_override_apply_links_targets_field() 
	{
		$options=[
			"Same Window" => '_self',
			'New Window' => '_blank',
		];

		echo '<select name="'. $this->fields->override_apply_links_targets->name .'">';

			foreach ($options as $text => $optValue) {

				$isSelected=selected($this->fields->override_apply_links_targets->value, $optValue, false);

				echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

			}

		echo '</select>';
	}

	public function show_floorplans_featured_image()
	{
		$isChecked = checked( $this->fields->show_floorplans_grid_featured_image->value, 'true', false );

		echo '<label for="show_floorplans_grid_featured_image">';
			echo "<input id='show_floorplans_grid_featured_image' type='checkbox' name='{$this->fields->show_floorplans_grid_featured_image->name}' value='true' {$isChecked}>";
			echo "Show featured image";
		echo '</label>';
	}

	public function override_request_link_field()
	{
		$isChecked = checked( $this->fields->override_request_link->value, 'true', false );

		echo '<label for="override_request_link">';
			echo "<input id='override_request_link' type='checkbox' name='{$this->fields->override_request_link->name}' value='true' {$isChecked}>";

			echo '<i>Override /contact/ link for "Request More Info" and "Contact Leasing" buttons</i>';
		echo '</label>';
	}

	public function property_grid_image_text() 
	{

		echo "<input type='text' name='{$this->fields->property_grid_featured_image_text->name}' value='{$this->fields->property_grid_featured_image_text->value}' placeholder='Find Your Home'>";
		echo '<p><small>Enter the text that you would like to appear over the featured image.</small></p>';
	}

	public function show_property_featured_image()
	{
		$isChecked = checked( $this->fields->show_property_grid_featured_image->value, 'true', false );

		echo '<label for="show_property_grid_featured_image">';
			echo "<input id='show_property_grid_featured_image' type='checkbox' name='{$this->fields->show_property_grid_featured_image->name}' value='true' {$isChecked}>";
			echo "Show featured image";
		echo '</label>';
	}	

	public function request_info_url_field() 
	{
		$default_url = site_url().'/contact';
		echo "<input class='field-group-2-input' type='url' name='{$this->fields->single_floorplan_request_more_info_url->name}' value='{$this->fields->single_floorplan_request_more_info_url->value}' placeholder='$default_url'>";
		echo '<p><small>Override /contact/ link for "Request More Info” button by entering the entire URL</small></p>';
	}	

	public function rentPress_override_single_property_file()
	{
		$isChecked = checked( $this->fields->override_single_property_template_file->value, RENTPRESS_PLUGIN_DIR . 'templates/Properties/single-property-basic.php', false );
		echo "<label>
				<input id='rentpress_single_property_setting' type='checkbox' name='". $this->fields->override_single_property_template_file->name ."' value='". RENTPRESS_PLUGIN_DIR . "templates/Properties/single-property-basic.php' {$isChecked}>
				Enable single property template
			</label>
		";
	}

	public function show_waitlist_ctas_field()
	{
		$isChecked = checked( $this->fields->show_waitlist_ctas->value, 'true', false );

		echo '<label for="show_waitlist_ctas">';
			echo "<input id='show_waitlist_ctas' type='checkbox' name='{$this->fields->show_waitlist_ctas->name}' value='true' {$isChecked}>";
			echo "Show Waitlist CTAs";
		echo '</label>';
	}

	public function show_waitlist_override_url_field() 
	{

		$default_url = site_url().'/waitlist/';
		echo "<input class='field-group-2-input' type='url' name='{$this->fields->show_waitlist_override_url->name}' value='{$this->fields->show_waitlist_override_url->value}' placeholder='$default_url'>";
		echo '<p><small>Override /waitlist/ link for "Join Waitlist” button by entering the entire URL.</small></p>';
	}

	public function rentPress_override_archive_properties_file()
	{

		$isChecked = checked( $this->fields->override_archive_properties_template_file->value, RENTPRESS_PLUGIN_DIR . 'templates/Properties/archive-properties-basic.php', false );
		echo "
			<label>
				<input id='rentPress_archive_property_setting' type='checkbox' name='". $this->fields->override_archive_properties_template_file->name ."' value='". RENTPRESS_PLUGIN_DIR . "templates/Properties/archive-properties-basic.php' {$isChecked}>
			Enable properties search template
			</label>
		";
	}

	public function rentPress_choose_archive_properties_file()
	{
	$options=[
	  "Basic Search" => 'rentPress_choose_archive_basic',
	  "Advanced Search With Map" => 'rentPress_choose_archive_advanced' 
	];

	echo '<select name="'. $this->fields->choose_archive_properties_template_file->name .'">';

	foreach ($options as $text => $optValue) {

		$isSelected=selected($this->fields->choose_archive_properties_template_file->value, $optValue, false);

		echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

	}

	echo '</select>';
	}

	public function rentPress_archive_properties_min_cluster()
	{
		echo "<input type='number' name='{$this->fields->archive_properties_min_cluster->name}' value='{$this->fields->archive_properties_min_cluster->value}' placeholder='Min cluster count' min='1'>";
		echo '<p><small>The minimum number of pins necessary to make a cluster.</small></p>';
	}

	public function rentPress_archive_properties_cluster_grid()
	{
		echo "<input type='number' name='{$this->fields->archive_properties_cluster_grid->name}' value='{$this->fields->archive_properties_cluster_grid->value}' placeholder='Grid size' min='1'>";
		echo '<p><small>Larger grid sizes will bring pins into clusters more frequently.</small></p>';
	}

	public function rentPress_single_floorplan_content_position()
	{
		$options=[
			"Top of Page" => 'single_floorplan_content_top',
			"Bottom of Page" => 'single_floorplan_content_bottom'
		];

		echo '<select name="'. $this->fields->single_floorplan_content_position->name .'">';

			foreach ($options as $text => $optValue) {

				$isSelected=selected($this->fields->single_floorplan_content_position->value, $optValue, false);

				echo '<option value="'. esc_attr($optValue) .'" '. $isSelected .'>'. $text .'</option>';

			}

		echo '</select><p><small>Choose where you would like to show content on the single floor plan template.</small></p>';
	}

	public function handle_file_upload_for_properties_featured_image($option)
	{
		$fileKey = $this->fields->properties_default_featured_image->name;

		//check if user had uploaded a file and clicked save changes button
        if(!empty($_FILES[$fileKey]["tmp_name"]))
        {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
		        require_once( ABSPATH . 'wp-admin/includes/file.php' );
		    }

            $urls = wp_handle_upload($_FILES[$fileKey], array('test_form' => FALSE));

            if ( isset($urls['error']) && $urls['error'] ) {
				add_settings_error(
			        'error_uploading_rentpress_override_image',
			        esc_attr( 'error_image_upload' ),
			        __($urls['error'], RENTPRESS_LANG_KEY),
			        'error' // Type
			    );
		        return get_option($fileKey);
            }
            if ( isset($urls['url']) ) {
	            $this->options->updateOption($fileKey, $urls['url']);
	            return $urls["url"];
            }
        }

        //no upload. old file url is the new value.
        return get_option($fileKey);
	}

	public function handle_file_upload_for_floorplans_featured_image($option)
	{
		$fileKey = $this->fields->floorplans_default_featured_image->name;

		//check if user had uploaded a file and clicked save changes button
        if (!empty($_FILES[$fileKey]["tmp_name"]))
        {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

            $urls = wp_handle_upload($_FILES[$fileKey], array('test_form' => FALSE));

            if (isset($url['error']) && $urls['error'] ) {
				add_settings_error(
			        'error_uploading_rentpress_override_image',
			        esc_attr( 'error_image_upload' ),
			        __($urls['error'], RENTPRESS_LANG_KEY),
			        'error' // Type
			    );

		        return get_option($fileKey);
            }
            if ( isset($urls['url']) ) {
	            $this->options->updateOption($fileKey, $urls['url']);
	            return $urls["url"];
            }
        }

        //no upload. old file url is the new value.
        return get_option($fileKey);
	}

	public function handle_file_upload_for_cities_featured_image($option)
	{
		$fileKey = $this->fields->cities_default_featured_image->name;

		//check if user had uploaded a file and clicked save changes button
        if (!empty($_FILES[$fileKey]["tmp_name"]))
        {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

            $urls = wp_handle_upload($_FILES[$fileKey], array('test_form' => FALSE));

            if (isset($url['error']) && $urls['error'] ) {
				add_settings_error(
			        'error_uploading_rentpress_override_image',
			        esc_attr( 'error_image_upload' ),
			        __($urls['error'], RENTPRESS_LANG_KEY),
			        'error' // Type
			    );

		        return get_option($fileKey);
            }
            if ( isset($urls['url']) ) {
	            $this->options->updateOption($fileKey, $urls['url']);
	            return $urls["url"];
            }
        }

        //no upload. old file url is the new value.
        return get_option($fileKey);
	}

	public function floorplans_grid_image_text() 
	{

		echo "<input type='text' name='{$this->fields->floorplans_grid_featured_image_text->name}' value='{$this->fields->floorplans_grid_featured_image_text->value}' placeholder='Find Your Home'>";
		echo '<p><small>Enter the text that you would like to appear over the featured image.</small></p>';
	}

	public function rentPress_properties_default_featured_image()
	{
		$imageUploadedProperty = $this->fields->properties_default_featured_image->value;

		?>
		<input type="file"
			name="<?php echo $this->fields->properties_default_featured_image->name; ?>"
			value="<?php echo $this->fields->properties_default_featured_image->value; ?>" />

		<span><i><?php echo '<b>Current:</b> '.$imageUploadedProperty; ?></i></span>
		<?php
	}

	public function rentPress_floorplans_default_featured_image()
	{
		$imageUploadedFloorplan = $this->fields->floorplans_default_featured_image->value;

		?>
		<input type="file"
			name="<?php echo $this->fields->floorplans_default_featured_image->name; ?>"
			value="<?php echo $this->fields->floorplans_default_featured_image->value; ?>" />

		<span><i><?php echo '<b>Current:</b> '.$imageUploadedFloorplan; ?></i></span>
		<?php
	}

	public function rentPress_cities_default_featured_image()
	{
		$imageUploadedCity = $this->fields->cities_default_featured_image->value;

		?>
		<input type="file"
			name="<?php echo $this->fields->cities_default_featured_image->name; ?>"
			value="<?php echo $this->fields->cities_default_featured_image->value; ?>" />

		<span><i><?php echo '<b>Current:</b> '.$imageUploadedCity; ?></i></span>
		<?php
	}
}