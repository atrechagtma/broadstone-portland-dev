<?php

class rentPress_SettingsTabs_IntegrationsConfig extends rentPress_Base_WpSettingsSubPage {

	public static $integrationsConfigSectionID = 'rentPress_integrations_configuration';

	public static $headerLinks = 'rentPress_integrations_header_links';

	public static $optionGroup = 'rentPress_integrations_config_option_group';

	public static $googleConfigSectionID = 'rentPress_google_configuration';

	public static $googleAnalyticsSectionID = 'rentPress_google__analytics_configuration';

	public static $otherIntegrationsSectionID = 'rentPress_other_integrations';

	// public static $gravityFormsSectionID = 'rentPress_gravity_forms';

	public static $reviewsSectionID = 'rentPress_reviews_addon';

	public static $yardiSectionID = 'rentPress_yardi_toolkit';

	public function __construct() {
		$this->wp_menu_args = [
			'menu_title' => 'Integrations',
			'page_title' => 'Integrations',
			'page_slug' => 'rentpress_integrations_settings'
		];

		$this->fields_keys =[

			'is_appointments_installed',
			'tour_cta_button',
			'override_cta_link',
			'single_floorplan_schedule_a_tour_url',
			'is_display_reviews_addon_installed',
			'google_api_token',
			'google_js_default_map_center_latitude',
			'google_js_default_map_center_longitude',
			'google_js_map_unit_of_distance',
			'enable_google_analytics',
			'google_analytics_id',
			// 'gravity_forms_add_on',
			'reviews_add_on',
			'yardi_toolkit_installed'
		];

		parent::__construct();
	}

	public function render_settings_page() {
		$common_html=new rentPress_SettingsTabs_CommonHtml();

		$common_html->openingOptionsWrapper(); ?>
		    <h1>RentPress: Integrations Configuration</h1>
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

		// section jump links
		add_settings_section(
			self::$headerLinks,
			'Jump to an integration section:',
			function() { echo '<p style="font-size: 16px;">
			    	<a href="#google-maps">Google Maps</a> | <a href="#google-analytics">Google Analytics</a> | <a href="#appointments">Schedule a Tour</a> | <a href="#reviews">Reviews Add-on</a> | <a href="#yardi">Yardi Toolkit Add-on
			    	</a>
			    	<br /><br /><br />
			    	<b>Need Help? </b>Check the support site for assistance with RentPress Integrations: <a href="https://via.30lines.com/X7TllTnK" target="_blank" rel="noopener noreferrer">RentPress: Integrations Configuration</a>.
			    	</p><br />'; },
			    $this->wp_menu_args->page_slug
		);

		// google maps section
		add_settings_section(
	        self::$googleConfigSectionID,
	        '<div id="google-maps">Google Maps API</div>',
			function() {
				echo '<p>You can find the API key from your <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/">Google Map Developer console</a>. <br />This API key will be used to retrieve reviews when using the Reviews Add-On, and can be used to display maps on this site.</p>';
			},
	        $this->wp_menu_args->page_slug
	    );

		add_settings_field(
	        $this->fields->google_api_token->name,
	        'Google JS API Key',
	        [$this, 'rentPress_google_js_api_token'],
	        $this->wp_menu_args->page_slug,
	        self::$googleConfigSectionID
	    );

	    add_settings_field(
	        $this->fields->google_js_default_map_center_latitude->name,
	        'Default Center: Latitude',
	        [$this, 'google_js_default_map_center_latitude'],
	        $this->wp_menu_args->page_slug,
	        self::$googleConfigSectionID
	    );

	    add_settings_field(
	        $this->fields->google_js_default_map_center_longitude->name,
	        'Default Center: Longitude',
	        [$this, 'google_js_default_map_center_longitude'],
	        $this->wp_menu_args->page_slug,
	        self::$googleConfigSectionID
	    );

	    add_settings_field(
	        $this->fields->google_js_map_unit_of_distance->name,
	        'Preferred Unit of Measurement',
	        [$this, 'rentPress_google_js_map_unit_of_distance'],
	        $this->wp_menu_args->page_slug,
	        self::$googleConfigSectionID
	    );

	    register_setting(self::$optionGroup, $this->fields->google_api_token->name);
		register_setting(self::$optionGroup, $this->fields->google_js_default_map_center_latitude->name);
		register_setting(self::$optionGroup, $this->fields->google_js_default_map_center_longitude->name);
		register_setting(self::$optionGroup, $this->fields->google_js_map_unit_of_distance->name);

		// google analytics section
	    add_settings_section(
	        self::$googleAnalyticsSectionID,
	        '<div id="google-analytics">Google Analytics</div>',
			function() {
				echo '<p>When connected, shopper clicks and actions on RentPress templates will be automatically reported into your Google Analytics account. To find your tracking ID, go to your Google Analytics account and click on Admin in the sidebar.</p>
					<p>More information can be found at: <a href="https://via.30lines.com/WIoDyQiF  target="_blank" rel=”noopener noreferrer">Understanding RentPress + Google Analytics integration</a>.</p>
				';
			},
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	        $this->fields->enable_google_analytics->name,
	        'Enable Google Analytics',
	        [$this, 'Use_google_analytics'],
	        $this->wp_menu_args->page_slug,
	        self::$googleAnalyticsSectionID
	    );

	    add_settings_field(
	        $this->fields->google_analytics_id->name,
	        'Google Analytics Tracking ID',
	        [$this, 'rentpress_google_analytics_id'],
	        $this->wp_menu_args->page_slug,
	        self::$googleAnalyticsSectionID
	    );

		register_setting(self::$optionGroup, $this->fields->enable_google_analytics->name);
		register_setting(self::$optionGroup, $this->fields->google_analytics_id->name);

		// schedule tour section
		add_settings_section(
	        self::$integrationsConfigSectionID,
	        '<div id="appointments">Schedule a Tour</div>',
			function() { echo '<p>Use the settings below to enable and configure the ability to Schedule a Tour. Default URL is /tour/ on the same site. You can override this URL to send shoppers to any arbitrary page.</p>'; },
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	        $this->fields->tour_cta_button->name,
	        'Tour CTA Button',
	        [$this, 'show_tour_cta_button'],
	        $this->wp_menu_args->page_slug,
	        self::$integrationsConfigSectionID
	    );

	    add_settings_field(
	        $this->fields->override_cta_link->name,
	        'Override Tour URL',
	        [$this, 'override_cta_link_href'],
	        $this->wp_menu_args->page_slug,
	        self::$integrationsConfigSectionID
	    );

    	add_settings_field(
	        $this->fields->single_floorplan_schedule_a_tour_url->name,
	        'Tour URL',
	        [$this, 'tour_url_override_link'],
	        $this->wp_menu_args->page_slug,
	        self::$integrationsConfigSectionID,
	        [
	        	'class' => 'field-group-1'
	    	]
	    );
	    register_setting(self::$optionGroup, $this->fields->tour_cta_button->name);
    	register_setting(self::$optionGroup, $this->fields->override_cta_link->name);
	    register_setting(self::$optionGroup, $this->fields->single_floorplan_schedule_a_tour_url->name);

	    // gravity forms leads addon section
	  //   add_settings_section(
	  //       self::$gravityFormsSectionID,
	  //       '<div id="gf-leads">RentPress: Gravity Forms Leads Add-on</div>',
			// function() { echo '
			// 	<p>
			// 	RentPress: Gravity Forms Leads Add-on connects your contacts forms with your multifamily CRMs and other 3rd party APIs. Seamlessly sends leads to multifamily CRMs and other 3rd party APIs with Gravity Forms. Support for: RentCafe, Entrata. 
			// 	<!-- <a href="https://via.30lines.com/iGMEVKgo" rel=”noopener noreferrer">Click here to get it »</a> <br /><br /> -->
			// 	</p>';

			// 	// echo '<p>More information about available shortcodes is available at <a href="https://via.30lines.com/DOZeNuiQ" target="_blank" rel=”noopener noreferrer">RentPress: Reviews Add-on Documentation</a>.</p>';
			// },
	  //       $this->wp_menu_args->page_slug
	  //   );

	  //   add_settings_field(
	  //       $this->fields->gravity_forms_add_on->name,
	  //       'Gravity Forms Add-On',
	  //       [$this, 'rentPress_gravity_forms_addon_is_installed'],
	  //       $this->wp_menu_args->page_slug,
	  //       self::$gravityFormsSectionID
	  //   );

	  //   register_setting(self::$optionGroup, $this->fields->gravity_forms_add_on->name);

	    // reviews addon section
	    add_settings_section(
	        self::$reviewsSectionID,
	        '<div id="reviews">RentPress: Reviews Add-on</div>',
			function() { echo '
				<p>
				RentPress: Reviews Add-on lets you add shortcodes to your site to display your reviews from Kingsley, Modern Message, and Google Places. <a href="https://via.30lines.com/iGMEVKgo" rel=”noopener noreferrer">Click here to get it »</a> <br /><br />
				</p>';

				echo '<p>More information about available shortcodes is available at <a href="https://via.30lines.com/DOZeNuiQ" target="_blank" rel=”noopener noreferrer">RentPress: Reviews Add-on Documentation</a>.</p>';
			},
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	        $this->fields->reviews_add_on->name,
	        'Reviews Add-On',
	        [$this, 'rentPress_reviews_addon_is_installed'],
	        $this->wp_menu_args->page_slug,
	        self::$reviewsSectionID
	    );

	    register_setting(self::$optionGroup, $this->fields->reviews_add_on->name);

	    // <a href="https://rentpress.io/downloads/?utm_source=rentpress&utm_medium=plugin&utm_campaign=integration_settings" target="_blank" rel=”noopener noreferrer">Click here to get it »</a>

	    // yardi toolkit addon section
	    add_settings_section(
	        self::$yardiSectionID,
	        '<div id="yardi">RentPress: Yardi Toolkit Add-on</div>',
			function() { echo '<p>RentPress: Yardi Toolkit Add-on connects your website to advanced features from the Yardi Marketing API. 
			
			<br /><br />
				More information about available shortcodes is available at <a href="https://via.30lines.com/fpSTxo7t" target="_blank" rel=”noopener noreferrer">RentPress: Yardi Toolkit Documentation</a>.
				</p>';
			},
	        $this->wp_menu_args->page_slug
	    );

	    add_settings_field(
	        $this->fields->yardi_toolkit_installed->name,
	        'Yardi Toolkit Add-on',
	        [$this, 'rentPress_yardi_toolkit_is_installed'],
	        $this->wp_menu_args->page_slug,
	        self::$yardiSectionID
	    );

	    register_setting(self::$optionGroup, $this->fields->yardi_toolkit_installed->name);
	}

	public function show_tour_cta_button()
	{
		$isChecked = checked( $this->fields->tour_cta_button->value, 'true', false );

		echo '<label for="tour_cta_button">';
			echo "<input id='tour_cta_button' type='checkbox' name='{$this->fields->tour_cta_button->name}' value='true' {$isChecked}>";

			echo '<i>Show "Schedule Tour" CTAs</i>';
		echo '</label>';
	}

	public function override_cta_link_href()
	{
		$isChecked = checked( $this->fields->override_cta_link->value, 'true', false );

		echo '<label for="override_cta_link">';
			echo "<input id='override_cta_link' type='checkbox' name='{$this->fields->override_cta_link->name}' value='true' {$isChecked}>";

			echo '<i>Override default /tour/ link for "Schedule Tour" CTA</i>';
		echo '</label>';
	}

	public function Use_google_analytics()
	{
		$isChecked = checked( $this->fields->enable_google_analytics->value, 'true', false );

		echo '<label for="enable_google_analytics">';
			echo "<input id='enable_google_analytics' type='checkbox' name='{$this->fields->enable_google_analytics->name}' value='true' {$isChecked}>";

			echo '<i>Enable Google Analytics Event Tracking</i>';
		echo '</label>';
	}

	public function tour_url_override_link() 
	{
		$default_tour_url = site_url().'/tour/';
		echo "<input class='field-group-1-input' type='url' name='{$this->fields->single_floorplan_schedule_a_tour_url->name}' value='{$this->fields->single_floorplan_schedule_a_tour_url->value}' placeholder='$default_tour_url'>";
		echo '<p><small>Enter the entire URL that you would like the "Schedule Tour" CTA to link to.</small></p>';

	}

	public function rentPress_google_js_api_token()
	{
		echo "<input type='text' size='50' name='{$this->fields->google_api_token->name}' value='{$this->fields->google_api_token->value}' placeholder='e.g. AIzmSyBB2ND1X4K-LBkWS18uF2oKKinMINxFzWA'>";
	}

	public function rentPress_google_analytics_id()
		{
			echo "<input type='text' name='{$this->fields->google_analytics_id->name}' value='{$this->fields->google_analytics_id->value}' placeholder='UA-130303030-1'>";
		}


	public function rentPress_gravity_forms_addon_is_installed()
	{
		if (class_exists( 'rentPressGravityForms_Leads' )) :
			$reviewsIsInstalled = "<b style='color: #64C956;'>Installed</b><br /><br />";
		else :
			$reviewsIsInstalled =  "<b style='color: #EC4D3E;'>Not Installed</b><br /><br />";
		endif;

		echo '<p>' .$reviewsIsInstalled .'</p>';
	}

	public function rentPress_reviews_addon_is_installed()
	{
		if (class_exists( 'rentPressDisplayReviews' )) :
			$reviewsIsInstalled = "<b style='color: #64C956;'>Installed</b><br /><br />";
		else :
			$reviewsIsInstalled =  "<b style='color: #EC4D3E;'>Not Installed</b><br /><br />";
		endif;

		echo '<p>' .$reviewsIsInstalled .'</p>';
	}

	public function rentPress_yardi_toolkit_is_installed()
	{
		if (class_exists( 'rentPressRentCafeIntegration_Settings' )) :
			$yardiIsInstalled = "<b style='color: #64C956;'>Installed</b>";
			$yardiManageMsg = '<p>You can manage RentPress: Yardi Toolkit Add-on <a href="/wp-admin/admin.php?page=rentpress_rentcafe">in its own settings tab</a>.</p>';
		else :
			$yardiIsInstalled =  "<b style='color: #EC4D3E;'>Not Installed</b>";
			$yardiManageMsg = '';

		endif;
		echo '<p>' .$yardiIsInstalled .'</p>';
		echo '<p>' .$yardiManageMsg .'</p>';
	}

	public function google_js_default_map_center_latitude()
	{
		echo "<input type='text' size='20' name='{$this->fields->google_js_default_map_center_latitude->name}' 
		value='{$this->fields->google_js_default_map_center_latitude->value}' placeholder='e.g. 39.9687944'>";
	}

	public function google_js_default_map_center_longitude()
	{
		echo "<input type='text' size='20' name='{$this->fields->google_js_default_map_center_longitude->name}' value='{$this->fields->google_js_default_map_center_longitude->value}' placeholder='e.g. -82.9981827'>";
	}

	public function rentPress_google_js_map_unit_of_distance()
	{
		$distanceOptions = ['M', 'K'];

		echo "<div class='google-map-unit-of-measuremnt-selection-options'>";
			echo "<select name='{$this->fields->google_js_map_unit_of_distance->name}'>";
				foreach ( $distanceOptions as $unitOfMeasurement ) :

					$isSelected = selected($unitOfMeasurement, $this->fields->google_js_map_unit_of_distance->value, false);

					echo "<option value='{$unitOfMeasurement}' {$isSelected}>";
						echo ($unitOfMeasurement == 'K' ? 'Kilometers' : 'Miles');
					echo '</option>';

				endforeach;
			echo '</select>';
		echo '</div>';
	}
}