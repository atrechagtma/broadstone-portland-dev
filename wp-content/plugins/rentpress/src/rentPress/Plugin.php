<?php 

/**
* Main RentPress processes
*/
class rentPress_Plugin implements ArrayAccess {
	protected $contents;
	protected static $indicateActivated;


	public function __construct() 
	{
		$this->contents = array();
		$this->installer = new rentPress_InstallrentPress();
		$this->options = new rentPress_Options();
		$this->postTypes = new rentPress_Posts_PostTypes();
		$this->importer = new rentPress_Import_ImportProperties();
		$this->caching = new rentPress_Base_Caching();
	}

	public function offsetSet( $offset, $value ) 
	{
		$this->contents[$offset] = $value;
	}

	public function offsetExists($offset) 
	{
		return isset( $this->contents[$offset] );
	}

	public function offsetUnset($offset) 
	{
		unset( $this->contents[$offset] );
	}

	public function offsetGet($offset) 
	{
		if( is_callable($this->contents[$offset]) ){
			return call_user_func( $this->contents[$offset], $this );
		}
		return isset( $this->contents[$offset] ) ? $this->contents[$offset] : null;
	}

	public function install()
	{
		$this->installer->install();
	}

	public function isInstalled()
	{
		return $this->options->isInstalled();
	}

	public function upgrade()
	{
		# Cleanup and additions/subtractions pertaining to the version upgrade
		$this->installer->installDatabaseTables();
	}

	public function activate()
	{
		// activation logic
		self::$indicateActivated = $this->options->addOption(rentPress_Helpers_StringLiterals::$activeStateKey, 1);

		$this->installer->install();
		$this->installer->installDatabaseTables();
	}

	public function deactivate()
	{
		self::$indicateActivated = $this->options->deleteOption(rentPress_Helpers_StringLiterals::$activeStateKey);

		// Remove transients
		delete_transient('rentPress_refresh_feed');

		// Remove transients set up by "cache" classes
		foreach ($this->caching->cacheKeys as $transientKey) {
			delete_transient($transientKey);
		}

	}

	public function addActionsAndFilters()
	{
		global $wpdb;
		
		$wpdb->rp_units=$wpdb->prefix.'rp_units';

		// Init custom post types
		$this->postTypes->setUpCustomPostTypes();

        // Property auto-magic refresh 
		add_action(rentPress_Helpers_StringLiterals::$refreshPropertiesKey, [$this, 'refreshProperties']);

		// Get the transient for the next event.
		$refreshTransient = get_transient(rentPress_Helpers_StringLiterals::$refreshPropertiesKey);
		$isInstalled = $this->options->getOption(rentPress_Helpers_StringLiterals::$activeStateKey);
		if ( ! $refreshTransient && $isInstalled ) {
			// Push cron to refresh feed in background process
			wp_schedule_single_event( time(), rentPress_Helpers_StringLiterals::$refreshPropertiesKey );
			// Put the results in a transient. Default expire after an hour.
			set_transient( rentPress_Helpers_StringLiterals::$refreshPropertiesKey, 'CRON_HAS_RAN', RENTPRESS_REFRESH_FREQUENCY );
		}

        // Begin setting preset feed configuration options, if none are present
        foreach ( $this->options->defaultOptionValues as $optionKey => $optionValue) {
        	$storeOtionValue = $this->options->getOption($optionKey);

	        if ( ! $storeOtionValue || empty($storeOtionValue)) {
		        $this->options->addOption($optionKey, $optionValue);
	        }

	        // for backwards compat... making sure RentPress uses true/false instead of 'on' for checkbox values
	        if ( $optionValue == 'on' ) {
	        	$this->options->updateOption($optionKey, $optionValue);
	        }
        }

        // Core Actions And Filters
        $this->actions_and_filters=[
        	new rentPress_SavePostsAndMetaFilters(),
        	new rentPress_StylesAndScripts(),
        	new rentPress_PagesAndTemplates(),
        	new rentPress_AjaxRequests(),
        ];      
	}

	public function addShortCodes()
	{
		/* Displays a grid of floor plans, bedroom filters are optional */
        $floorplanShortcode = new rentPress_ShortCodes_FloorPlans_Grid();
        $floorplanShortcode->register('floorplan_grid');

        /* Displays a single featured floor plan */
        $floorplanShortcode = new rentPress_ShortCodes_FloorPlans_Featured();
        $floorplanShortcode->register('featured_floor_plan');

        /* Unit Table Shortcode */
        $unitsShortcode=new rentPress_ShortCodes_Units();
        $unitsShortcode->register('units_table');

        /* Displays a grid of properties */
        $propertyGridShortcode = new rentPress_ShortCodes_Properties_PropertyGrid();
        $propertyGridShortcode->register('property_grid');

        $dvancedPropertyGridShortcode = new rentPress_ShortCodes_Properties_AdvancedPropertyGrid();
        $dvancedPropertyGridShortcode->register('advanced_property_grid');

        // Display office hours
        $propertyHoursShortcode = new rentPress_ShortCodes_Hours_OfficeHours();
        $propertyHoursShortcode->register('property_hours');

        // Display phone number
        $propertyPhoneShortcode = new rentPress_ShortCodes_Phone_PhoneNumber();
        $propertyPhoneShortcode->register('property_phone');

		// Display address
        $propertyAddresShortcode = new rentPress_ShortCodes_Address_PropertyAddress();
        $propertyAddresShortcode->register('property_address');

        // Display equal housing logos
        $equalHousingShortcode = new rentPress_ShortCodes_EqualHousing_EqualHousing();
        $equalHousingShortcode->register('equal_housing');

        // Display social links
		$propertySocialLinks = new rentPress_ShortCodes_Social_PropertySocials();
		$propertySocialLinks->register('property_socials');

		// Display nearby properties
		$propertyNearbyShortcode = new rentPress_ShortCodes_Properties_NearbyProperties_NearbyProperties();
		$propertyNearbyShortcode->register('property_nearby');

	}

	public function refreshProperties()
	{
		global $wpdb;
		
		$numOfPublishedPoroperties = $wpdb->query("SELECT p.ID FROM $wpdb->posts p WHERE p.post_type = RENTPRESS_PROPERTIES_CPT AND p.post_status = 'publish'");

		if ( $numOfPublishedPoroperties > 0 ) {
			$this->importer->log->event('Properties found for auto-magic refresh: beginning refresh...');
			// Refresh user property feed
			$update = $this->importer->setIsAutoRefresh(true)->import();
		} else {
			$this->importer->log->warning('No properties found to refresh for the auto-magic import.');
		}

		// For some reason the scheduled chron gets fired off twice, let's remove the secondary queue object
		// so it doesn't do that.
		$timestamp = wp_next_scheduled( rentPress_Helpers_StringLiterals::$refreshPropertiesKey );
		wp_unschedule_event( $timestamp, rentPress_Helpers_StringLiterals::$refreshPropertiesKey );
	}
	
	public function run()
	{ 
		foreach ( $this->contents as $key => $content ) { // Loop on contents
			if ( is_callable($content) ) {
				$content = $this[$key];
			}
			if ( is_object( $content ) ) {
				$reflection = new ReflectionClass( $content );
				if ( $reflection->hasMethod( 'run' ) ) {
					$content->run(); // Call run method on object
				}
			}
		}
	}


}