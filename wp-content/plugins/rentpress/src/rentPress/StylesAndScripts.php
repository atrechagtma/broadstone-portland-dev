<?php

class rentPress_StylesAndScripts {

	public function __construct() {

		$this->options = new rentPress_Options();

		if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' ) {
			add_action('admin_print_scripts', [$this, 'enqueueGoogleMaps'], 40);
		}

		add_action('admin_enqueue_scripts', [$this, 'enqueueAdminStylesAndScripts'], 10, 1);

		add_action( 'wp_enqueue_scripts' , [$this, 'enqueueGlobalTemplateStylesAndScripts'] );
		add_action( 'wp_enqueue_scripts' , [$this, 'templates_accent_colors'] );

		if ($this->options->getOption('enable_google_analytics') == true){
			add_action('wp_head',[$this, 'setUpGoogleAnalytics'], 1000);
		}

		
	}	

	public function getGoogleMapsApiKey() {
		$googleJsApiKey = $this->options->getOption('google_api_token');

		if (empty($googleJsApiKey)) {

			$googleMapProSettings=get_option('gmb_settings');

			if (! empty($googleMapProSettings['gmb_maps_api_key'])) {

				$googleJsApiKey=$googleMapProSettings['gmb_maps_api_key'];

			}

		}

		if (empty($googleJsApiKey)) {

			$wpgmp_settings=get_option("wpgmp_settings");

			if (! empty($wpgmp_settings['wpgmp_api_key'])) {

				$googleJsApiKey=$wpgmp_settings['wpgmp_api_key'];

			}

		}

		return $googleJsApiKey;
	}

	public function enqueueGoogleMaps()
	{
		$googleJsApiKey=self::getGoogleMapsApiKey();
		
		if ( ! empty($googleJsApiKey) ) {
			wp_enqueue_script('rentPress-gm-s', "https://maps.googleapis.com/maps/api/js?key={$googleJsApiKey}&libraries=places,geometry", ['jquery'], null, false);
		} 
	}

	public function setUpGoogleAnalytics() {
		global $rentPress_Service;
		echo "<!-- Google Analytics -->
		<script>
			window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
			ga('create', '".$this->options->getOption('rentPress_google_analytics_id')."', 'auto');
			ga('send', 'pageview');
		</script>
		<script async src='https://www.google-analytics.com/analytics.js'></script>
	<!-- End Google Analytics -->";
		wp_enqueue_script('rentPress-google-analytics', RENTPRESS_PLUGIN_ASSETS.'js/client/google-analytics.js', false, null, false);
      	$accentColor = $this->options->getOption('templates_accent_color');
      	wp_localize_script( 'rentPress-google-analytics', 'options', array(
			'accentColor' => $accentColor
      	));
	}

	public function enqueueAdminStylesAndScripts($hook)
	{
		if (is_admin()) {
			wp_enqueue_style('rentPress-admin-css', RENTPRESS_PLUGIN_ASSETS.'build/css/admin-side.css', false, null, false);
			
			// if (defined('RENTPRESS_CORE_TO_USE_MINIFIED') && RENTPRESS_CORE_TO_USE_MINIFIED === false) {	
				wp_enqueue_script('rentPress-admin-js', RENTPRESS_PLUGIN_ASSETS.'build/js/admin-side.js', ['jquery'], null, false);
			// }

			wp_localize_script( 'rentPress-admin-js', 'rentPressOptions', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			));

		}
	}

	public function enqueueGlobalTemplateStylesAndScripts()
	{
		wp_register_style('rentpress-client-side', RENTPRESS_PLUGIN_ASSETS.'build/css/client-side.css', false, null, false);

		//only register the needed scripts
		if ($this->options->getOption('is_site_about_a_single_property') === 'true' && is_page('floorplans') && $this->options->getOption('override_archive_floorplans_template_file') != 'current-theme') {
			//this script in enqueued in archive-floorplans-basic-data.php file
		}
		elseif (defined('RENTPRESS_CORE_TO_USE_MINIFIED') && RENTPRESS_CORE_TO_USE_MINIFIED === false) {	
			wp_register_script('rentpress-client-side', RENTPRESS_PLUGIN_ASSETS.'build/js/client-side.js', ['jquery'], null, false);
		} 
		else {
			wp_register_script('rentpress-client-side', RENTPRESS_PLUGIN_ASSETS.'build/js/client-side.noMaps.js', ['jquery'], null, false);
		}

		if ($this->options->getOption('is_site_about_a_single_property') === 'true') {

			$the_property=get_posts([
				'post_type' => 'properties',
				'post_status' => ['draft', 'publish'],
				'posts_per_page' => 1,
				'fields' => 'ids',
			]);


			if (count($the_property) == 1) {

				wp_localize_script(
					'rentpress-client-side', 
					'rp_thisWebisteIsAboutASingleProperty', 
					(string) $the_property[0]
				);	
				
			}

		}
	
        wp_localize_script('rentpress-client-side', 'wp_ajax_url', admin_url( 'admin-ajax.php' )); 

        $client_side_ranges=[];

        if ($this->options->getOption('is_site_about_a_single_property') === 'true') {
        	$client_side_ranges['rent_range']=rentPress_searchHelpers::get_min_and_max_meta_values_from('floorplans', 'fpMinRent', 'fpMaxRent', false, 'int');
        }
       	else {
	        $client_side_ranges['rent_range']=rentPress_searchHelpers::get_min_and_max_meta_values_from('properties', 'propMinRent', 'propMaxRent', false, 'int');
       	}

        wp_localize_script('rentpress-client-side', 'rentPressRanges', $client_side_ranges);

      	if (is_singular('properties')) {

			$property_id=get_the_ID();

		}
		elseif (is_singular('floorplans')) {
			global $wpdb;

			$property_id=$wpdb->get_row($wpdb->prepare("
				SELECT pm.post_id FROM $wpdb->postmeta pm
				WHERE  pm.meta_key = 'prop_code' AND pm.meta_value IN (
					SELECT pm.meta_value FROM $wpdb->postmeta pm
					WHERE  pm.meta_key = 'parent_property_code' AND pm.post_id = %d
				)
				LIMIT 1
			", get_the_ID()))->post_id;
		}

		$isPricingDisabled=(string) (int) (
			$this->options->getOption('disable_pricing') === 'true'
			||
			( isset($property_id) && get_post_meta($property_id, 'propDisablePricing', true) === 'true')
		);

		wp_localize_script('rentpress-client-side', 'rp_must_disable_pricing', $isPricingDisabled); 
		wp_localize_script('rentpress-client-side', 'rp_replacementMessage', $this->options->getOption('disable_pricing_message'));
		wp_localize_script('rentpress-client-side', 'rp_replacementURL', $this->options->getOption('disable_pricing_url'));

		wp_enqueue_script('rentpress-client-side');

		wp_enqueue_style('rentpress-client-side');
		wp_enqueue_script( 'prefix-font-awesome', 'https://kit.fontawesome.com/b485f10715.js' );

	}


	
	public function templates_accent_colors() {

		/*		
		if (
			$this->options->getOption('override_single_floorplan_template_file') != 'current-theme'
			||
			$this->options->getOption('override_single_property_template_file') != 'current-theme'
		) {
		*/

		$accentColor = $this->options->getOption('templates_accent_color');
	
		$darker = darken_color($accentColor, $darker=1.1);

		$theStylesheet="";

		/********* Global Styles *********/
		$theStylesheet.="
.rentpress-core-container .rp-primary-accent {
	color: {$accentColor} !important;
}
.rentpress-core-container .rp-primary-accent-border {
	border-color: {$accentColor} !important;
}

.rp-archive-fp-card-icon {
	color: {$accentColor};
}

.rentpress-core-container .rp-button {
	background-color:{$accentColor};
	border-color:{$accentColor};
}
.rentpress-core-container .rp-button-alt:first-child {
    background-color: {$accentColor};
    color: #fff;
    border-color: {$accentColor};
}
.rentpress-core-container .rp-button:hover {
	background-color:{$darker};
}

.rentpress-core-container .rp-button-alt {
	border-color: {$accentColor};
}
.rentpress-core-container .rp-button-alt:hover {
	background-color: {$accentColor};
}
.autocomplete-active {
	background-color: {$accentColor} !important;
}
.rp-fp-has-special {
	background-color: {$accentColor};
}
.autocomplete-items div:hover {
	background-color: {$accentColor};
}
";


		/********* Archive Floorplan Styles *********/
		$theStylesheet.="
.rentpress-core-container #rp-archive-fp-filters .noUi-connect {
    background: {$accentColor};
}

.rentpress-core-container #rp-archive-fp-filters .rp-archive-fp-is-filter-module .rp-module-wrapper select {
    border-color: {$accentColor};
}";		

/********* Archive Properties Styles *********/
		$theStylesheet.="
.rp-prop-search button {
    background-color: $accentColor;
}

.rp-prop-search button:hover {
	background-color: $darker;
}

.rp-archive-header-search .rp-module-wrapper input[type='checkbox']:checked+label {
    background-color: {$accentColor};
    color: #fff;
}

.is-rp-prop .rp-prop-is-special {
	background-color: {$accentColor};
}

.rentpress-core-container #rp-archive-fp-filters .rp-archive-fp-is-filter-module .rp-module-wrapper select {
    border-color: {$accentColor};
}";

		/********* Single Floorplan Styles *********/
		$theStylesheet.="
footer.rp-single-fp-sub-details h5 {
    color: {$accentColor};
}
.rp-unit-card.rp-active{
	border-color: {$accentColor};
	background-color: {$accentColor};
}

.rp-unit-card:hover {
	border-color: {$accentColor};
}

#rpUnitCards .rp-unit-card .rp-radio-unit-number:checked + label,
.unit-modal .rp-unit-card .rp-radio-unit-number:checked + label {
	background-color: {$accentColor};
	border-color: {$accentColor};
}

#rpUnitCards .rp-unit-card .rp-card-avail,
.unit-modal .rp-unit-card .rp-card-avail {
	color: {$accentColor};
}

#rpUnitCards .rp-unit-card:hover .rp-card-avail,
.unit-modal .rp-unit-card:hover .rp-card-avail {
	color: {$darker};
}

footer#rp-single-fp-form-buttons .rp-button-alt:first-child {
    background-color: {$accentColor};
    color: #fff;
    border-color: {$accentColor};
}

.rentpress-core-container .rp-single-fp-share-nav a:hover {
	color: {$accentColor};
}

.rp-single-special {
	background-color: {$accentColor};
}

.rp-archive-special {
	background-color: {$accentColor};
}

.rp-fp-card-special {
	color: {$accentColor};
}

.rp-3d-tour-link,
.rp-3d-tour-link:hover, 
.rp-3d-tour-link:active {
	color: {$accentColor};
}

#rp-archive-fp-toggle-filters {
	color: {$accentColor};
}";

		/********* Single Property Styles *********/
		$theStylesheet.="
.rp-single-prop-on-page-nav {
    background-color: {$accentColor};
}

.rp-single-special {
    background-color: {$accentColor};
}

.rp-archive-special {
    background-color: {$accentColor};
}

.rp-fp-card-special {
	color: {$accentColor};
}

.rp-single-prop-on-page-nav a {
	color: #fff;
}

.rp-single-prop-on-page-nav a:hover {
	color: #fff;
}

.rp-single-prop-details-links .rp-prop-cta-buttons .rp-button {
	border: 2px solid {$accentColor} !important;
}

.rp-single-prop-details-links .rp-prop-cta-buttons .rp-button-alt {
	background-color: #fff;
	color: {$accentColor};
}

.rp-single-prop-details-links .rp-prop-cta-buttons .rp-button-alt:hover {
	background-color: {$accentColor};
	color: #fff;
}


.rp-single-prop-details-wrapper a:not(.rp-button) {
	color: {$accentColor};
}";

		wp_add_inline_style('rentpress-client-side', $theStylesheet);


	}

}