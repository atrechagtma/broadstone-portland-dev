<?php

class rentPress_SettingsTabs_FeedConfig extends rentPress_Base_WpSettingsSubPage{

	public static $optionGroup = 'rentPress_feed_config_option_group';

	public static $useAvailableUnitsSectionID = 'rentPress_available_units_only';

	public static $unitTypeSectionID = 'rentPress_unit_rent_type_section';

	public static $phoneNumberFormatSectionID = 'rentPress_phone_number_format_section';

	public static $disablePricingSectionID = 'rentPress_disable_pricing_section';

	public static $disableLeaseTermPricingSectionID = 'rentPress_disable_lease_term_pricing_section';

	public static $overrideApplyUrlSectionID = 'rentPress_override_appply_url_section';

	public static $headerLinks = 'rentpress_header_links_feed_config';

	public function __construct() {
		$this->wp_menu_args =  [
			'menu_title' => 'Feed Configuration',
			'page_title' => 'RentPress: Feed Configuration',
			'page_slug' => 'rentpress_feed_config'
		];

		$this->fields_keys = [
			'use_avail_units_for_property_rent',
			'use_avail_units_for_floor_plan_rent',
			'use_avail_units_before_this_date',
			'unit_rent_type',
			'unit_lease_term',
			'disable_pricing',
			'disable_pricing_message',
			'disable_pricing_url',
			'disable_pricing_on_floorplan_with_no_available_units',
			'disbale_all_units_lt_pricing',
			'disable_units_lt_pricing',
			'phone_number_format',
			'override_apply_url',
		];

		parent::__construct();
	}

	public function render_settings_page()
	{
		$common_html=new rentPress_SettingsTabs_CommonHtml();

		$common_html->openingOptionsWrapper(); ?>
		    <h1>RentPress: Pricing and Availability Feed Configuration</h1>
		    <?php
		    settings_errors();
		    $common_html->displayOptionsTabs(); ?>

		    <p><i>**Applying changes to pricing and availability settings will require a manual re-sync to see changes immediately. Otherwise, the hourly auto-resync will take care of it for you.</i></p>

		    <form method="post" action="options.php" enctype="multipart/form-data">
		        <?php settings_fields(self::$optionGroup); ?>
		        <?php do_settings_sections($this->wp_menu_args->page_slug); ?>
		        <?php submit_button(); ?>
		    </form>
		<?php
		$common_html->closingOptionsWrapper();
	}

	public function wp_setting_sections_and_fields() {

		add_settings_section(
			self::$headerLinks,
			'Jump to an options section:',
			function() { echo '<p style="font-size: 16px;">
		    	<a href="#unit-availability">Unit Availability</a> | <a href="#unit-rent-type-display">Unit Rent Type Display</a> | <a href="#unit-rent-type-display">Disable Pricing</a> | <a href="#disable-lease-term-pricing">Disable Lease Term Pricing</a> | <a href="#format-phone-numbers">Format Phone Numbers</a> | <a href="#application-links">Application Links</a>
		    	<br /><br /><br />
		    	<b>Need Help? </b>Check the support site for assistance: <a href="https://via.30lines.com/VXg8aM2q" target="_blank" rel="noopener noreferrer">Feed Configuration Settings</a>.
		    	</p><br />'; },
		    $this->wp_menu_args->page_slug
		);

	    add_settings_section(
	        self::$useAvailableUnitsSectionID,
	        '<div id="unit-availability">Unit Availability</div>',
			function() { echo '<p>Options for pricing calculation - if no units are available, it will fall-back to default pricing ranges that come through the feed.</p>'; },
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	        $this->fields->use_avail_units_for_property_rent->name,
	        'Properties',
	        [$this, 'rentPress_available_units_for_properties_only'],
	        $this->wp_menu_args->page_slug,
	        self::$useAvailableUnitsSectionID
	    );

		add_settings_field(
			$this->fields->use_avail_units_for_floor_plan_rent->name,
			'Floor Plans',
			[$this, 'rentPress_available_units_for_floor_plans_only'],
			$this->wp_menu_args->page_slug,
			self::$useAvailableUnitsSectionID
		);

		add_settings_field(
			$this->fields->use_avail_units_before_this_date->name,
			'Lookahead',
			[$this, 'rentPress_use_avail_units_before_this_date'],
			$this->wp_menu_args->page_slug,
			self::$useAvailableUnitsSectionID
		);

		register_setting(self::$optionGroup, $this->fields->use_avail_units_for_property_rent->name);
		register_setting(self::$optionGroup, $this->fields->use_avail_units_for_floor_plan_rent->name);
		register_setting(self::$optionGroup, $this->fields->use_avail_units_before_this_date->name);

		/* Primary unit display rent type selector */

	    add_settings_section(
	        self::$unitTypeSectionID,
	        '<div id="unit-rent-type-display">Unit Rent Type Display</div>',
			function() { echo '<p>Choose which type of rent to show on your website.</p>'; },
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	        $this->fields->unit_rent_type->name,
	        'Unit Rent Price Type',
	        [$this, 'rentPress_unit_rent_type_option'],
	        $this->wp_menu_args->page_slug,
	        self::$unitTypeSectionID
	    );

	    add_settings_field(
	        $this->fields->unit_lease_term->name,
	        'Primary Lease Term',
	        [$this, 'rentPress_unit_lease_term_option'],
	        $this->wp_menu_args->page_slug,
	        self::$unitTypeSectionID,
    		[
	        	'class' => 'lease-term-setting'
	    	]
	    );

		register_setting(self::$optionGroup, $this->fields->unit_rent_type->name);
		register_setting(self::$optionGroup, $this->fields->unit_lease_term->name);

	    add_settings_section(
	        self::$disablePricingSectionID,
	        '<div id="unit-rent-type-display">Disable Pricing</div>',
			function() { echo '<p>You can opt to disable displaying pricing on the website. The results may vary dependent upon how the pricing is being output onto the page.</p>'; },
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	    	$this->fields->disable_pricing_on_floorplan_with_no_available_units->name,
	    	'Disable Pricing by Availability',
	    	[$this, 'rentPress_disable_pricing_on_floorplans_with_no_available_units'],
	    	$this->wp_menu_args->page_slug,
	    	self::$disablePricingSectionID
	    );

	    add_settings_field(
	        $this->fields->disable_pricing->name,
	        'Disable Pricing',
	        [$this, 'rentPress_unit_disable_pricing'],
	        $this->wp_menu_args->page_slug,
	        self::$disablePricingSectionID
	    );

		register_setting(self::$optionGroup, $this->fields->disable_pricing_on_floorplan_with_no_available_units->name);
		register_setting(self::$optionGroup, $this->fields->disable_pricing->name);

	    if ( $this->fields->disable_pricing->value ) :
		    add_settings_field(
		        $this->fields->disable_pricing_message->name,
		        'Disabled Pricing Message',
		        [$this, 'rentPress_unit_disable_pricing_message'],
		        $this->wp_menu_args->page_slug,
		        self::$disablePricingSectionID
		    );

		    add_settings_field(
		        $this->fields->disable_pricing_url->name,
		        'Disabled Pricing URL',
		        [$this, 'rentPress_unit_disable_pricing_url'],
		        $this->wp_menu_args->page_slug,
		        self::$disablePricingSectionID
		    );

			register_setting(self::$optionGroup, $this->fields->disable_pricing_message->name);
			register_setting(self::$optionGroup, $this->fields->disable_pricing_url->name);
	    endif;

	    /* Disbale Lease Term Unit Pricing */

	    add_settings_section(
	        self::$disableLeaseTermPricingSectionID,
	        '<div id="disable-lease-term-pricing">Disable Lease Term Pricing</div>',
			function() { echo '<p>Provide a comma-separated list of unit codes that you want to omit from using pricing matrices from  property and floor plan rent calculations.</p>'; },
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	        $this->fields->disable_units_lt_pricing->name,
	        'Disable Lease Term Pricing For Specific Properties, Floor Plans, And Units',
	        [$this, 'rentPress_unit_disable_lease_term_pricing'],
	        $this->wp_menu_args->page_slug,
	    	self::$disableLeaseTermPricingSectionID
	    );

		add_settings_field(
			$this->fields->disbale_all_units_lt_pricing->name,
			'Disable Lease Term Pricing',
			[$this, 'rentPress_disable_all_units_lt_pricing'],
			$this->wp_menu_args->page_slug,
			self::$disableLeaseTermPricingSectionID
		);

		register_setting(self::$optionGroup, $this->fields->disbale_all_units_lt_pricing->name);
		register_setting(self::$optionGroup, $this->fields->disable_units_lt_pricing->name);

		// Phone number format
		add_settings_section(
			self::$phoneNumberFormatSectionID,
			'<div id="format-phone-numbers">Format Phone Numbers</div>',
			function() {

			},
			$this->wp_menu_args->page_slug
		);

		add_settings_field(
			$this->fields->phone_number_format->name,
			'Select a phone number format',
			[$this, 'rentPress_phone_number_format_selector'],
			$this->wp_menu_args->page_slug,
			self::$phoneNumberFormatSectionID
		);

		register_setting(self::$optionGroup, $this->fields->phone_number_format->name);

		/* Application Links */

		add_settings_section(
			self::$overrideApplyUrlSectionID,
			'<div id="application-links">Application Links</div>',
			function() {echo '<p>You can choose to show a single link for apartment applications. This will override any links provided from your data feed.</p>';},
			$this->wp_menu_args->page_slug
		);

		add_settings_field(
			$this->fields->override_apply_url->name,
			'“Apply Now” URL Override',
			[$this, 'rentPress_override_apply_url_field'],
			$this->wp_menu_args->page_slug,
			self::$overrideApplyUrlSectionID
		);

		register_setting(self::$optionGroup, $this->fields->override_apply_url->name);
	}

	/** Feed config settings field renderings */
	public function rentPress_available_units_for_properties_only()
	{
		$isChecked = checked( $this->fields->use_avail_units_for_property_rent->value, 'true', false );

		echo '<label for="use_avail_units_for_property_rent">';
			echo "<input id='use_avail_units_for_property_rent' type='checkbox' name='{$this->fields->use_avail_units_for_property_rent->name}' value='true' {$isChecked}>";

			echo '<i>Use only available units for property range calculations.</i>';
		echo '</label>';
	}

	public function rentPress_available_units_for_floor_plans_only()
	{
		$isChecked = checked( $this->fields->use_avail_units_for_floor_plan_rent->value, 'true', false );

		echo '<label for="use_avail_units_for_floor_plan_rent">';
			echo "<input id='use_avail_units_for_floor_plan_rent' type='checkbox' name='{$this->fields->use_avail_units_for_floor_plan_rent->name}' value='true' {$isChecked}>";

			echo '<i>Use only available units for floor plan range calculations.</i>';
		echo '</label>';
	}

	public function rentPress_unit_rent_type_option()
	{
		$rentTypeOptions = ['Base', 'Market Rent', 'Effective Rent', 'Term Rent', 'Best Price'];

		foreach ( $rentTypeOptions as $type ) :
			$typeValue = strtolower(str_replace(' ', '_', $type));

			$isChecked = checked($typeValue, $this->fields->unit_rent_type->value, false);

			echo "<label for='{$typeValue}'>";
				echo "<input id='{$typeValue}' type='radio' name='{$this->fields->unit_rent_type->name}' value='{$typeValue}' {$isChecked} > ".$type;
			echo '</label><br/>';
		endforeach;
	}

	public function rentPress_unit_lease_term_option()
	{
		$leaseTermMonths = range(1, 34);
		$leaseTerm = (! empty($this->fields->unit_lease_term->value) ) ? esc_attr($this->fields->unit_lease_term->valueq) : 12;

		// echo"<p>Since you have selected the 'Term Rent' rent type, you must also select the primary length lease term.</p>";
		echo "<div class='term-rent-selection-options'>";
			echo "<select name='{$this->fields->unit_lease_term->name}'>";
				foreach ( $leaseTermMonths as $ltMonth ) :
					$isSelected = selected($ltMonth, $this->fields->unit_lease_term->value, false);
					echo "<option value='{$ltMonth}' {$isSelected}>".$ltMonth.'</option>';
				endforeach;
			echo '</select>';
		echo '</div>';
	}

	public function rentPress_phone_number_format_selector () 
	{
		$options = array(
			'xxx xxx xxxx',
			'xxx.xxx.xxxx',
			'(xxx) xxx xxxx',
			'(xxx) xxx-xxxx',
			'(xxx) xxx.xxxx',
			'xxx-xxx-xxxx',
			'xxx xxx-xxxx',
			'xxxxxxxxxx'
		);
		echo "<select name='{$this->fields->phone_number_format->name}'>";
			echo "<option value='noPhoneFormat'>No number formatting</option>";
			foreach( $options as $option ) {
				$isSelected = selected($option, $this->fields->phone_number_format->value, false);
				echo "<option value='{$option}' {$isSelected}>".$option."</option>";
			}
		echo "</select>";
	}

	public function rentPress_unit_disable_pricing()
	{
		$isChecked = checked( $this->fields->disable_pricing->value, 'true', false );
		echo "
			<label>
				<input type='checkbox' name='{$this->fields->disable_pricing->name}' value='true' {$isChecked}>
				Disable All Pricing
			</label>
		";
	}

	public function rentPress_disable_pricing_on_floorplans_with_no_available_units() {
		$isChecked = checked( $this->fields->disable_pricing_on_floorplan_with_no_available_units->value, 'true', false );
		echo "
			<label>
				<input type='checkbox' name='{$this->fields->disable_pricing_on_floorplan_with_no_available_units->name}' value='true' {$isChecked}>
				Disable Pricing on Floor Plans with no available units
			</label>
		";
	}

	public function rentPress_unit_disable_pricing_message()
	{

		echo "<input type='text' name='{$this->fields->disable_pricing_message->name}' value='{$this->fields->disable_pricing_message->value}' placeholder='Call for pricing'>";
	}

	public function rentPress_unit_disable_pricing_url()
	{
		echo "<input type='text' name='{$this->fields->disable_pricing_url->name}' value='{$this->fields->disable_pricing_url->value}' placeholder='/contact/'>";
	}

	public function rentPress_disable_all_units_lt_pricing() 
	{

		$isChecked = checked( $this->fields->disbale_all_units_lt_pricing->value, 'true', false );
		echo "
			<label>
				<input type='checkbox' name='{$this->fields->disbale_all_units_lt_pricing->name}' value='true' {$isChecked}>
				Disable Lease Term Pricing
			</label>
		";
	}

	public function rentPress_unit_disable_lease_term_pricing() 
	{
		echo "<input type='text' name='{$this->fields->disable_units_lt_pricing->name}' value='{$this->fields->disable_units_lt_pricing->value}'>";

		echo "<p><small>*You may enter ID's for properties, floor plans, and units.</small></p>";
	}

	public function rentPress_use_avail_units_before_this_date()
	{
		$unitsBeforeDateOptions = [30, 45, 60, 75, 90, 365];

		echo "<div class='avail-units-before-date-selection-options'>";
			echo "<select name='{$this->fields->use_avail_units_before_this_date->name}'>";
				foreach ( $unitsBeforeDateOptions as $numberOfDays ) :
					$isSelected = selected($numberOfDays, $this->fields->use_avail_units_before_this_date->value, false);

					$numberOfDaysLabel = $numberOfDays == 365 ? '365 Days' : $numberOfDays .' Days';

					echo "<option value='{$numberOfDays}' {$isSelected}>";
						echo $numberOfDaysLabel;
					echo '</option>';
				endforeach;
			echo '</select>';

			echo '<span><i> Select an option that will decide how far forward to look for available units.</i></span>';
		echo '</div>';
	}

	public function rentPress_override_apply_url_field() 
	{
		echo "<input type='url' name='{$this->fields->override_apply_url->name}' placeholder='https://link-to-application.com/' value='{$this->fields->override_apply_url->value}'>";
	}
}
