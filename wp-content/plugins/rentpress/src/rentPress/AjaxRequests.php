<?php 

class rentPress_AjaxRequests {

	public function __construct() {

		$this->options = new rentPress_Options();

		$this->importer = new rentPress_Import_ImportProperties();

		// add_action( 'wp_ajax_nopriv_save_rentpress_option_settings', [$this, 'save_rentpress_option_settings_callback'] );
        // add_action( 'wp_ajax_save_rentpress_option_settings', [$this, 'save_rentpress_option_settings_callback'] );
		
        // add_action("wp_ajax_nopriv_fetch_property_codes", [$this, 'fetch_property_codes_callback']);
        add_action('wp_ajax_fetch_property_codes', [$this, 'fetch_property_codes_callback'] );

        // add_action( 'wp_ajax_nopriv_import_properties_from_service', [$this, 'import_properties_from_service_callback'] );
        add_action( 'wp_ajax_import_properties_from_service', [$this, 'import_properties_from_service_callback'] );
       
        // add_action( 'wp_ajax_nopriv_resync_single_property', [$this, 'resync_single_property_callback'] );
        add_action( 'wp_ajax_resync_single_property', [$this, 'resync_single_property_callback'] );

        // add_action( 'wp_ajax_nopriv_resync_single_property_by_prop_code', [$this, 'resync_single_property_by_prop_code_callback'] );
        add_action( 'wp_ajax_resync_single_property_by_prop_code', [$this, 'resync_single_property_by_prop_code_callback'] );
       
        // add_action( 'wp_ajax_nopriv_update_unit_lease_term_price_option', [$this, 'update_unit_lease_term_price_option_callback'] );

	}	

	public function save_rentpress_option_settings_callback()
	{
		$this->options->saveOrUpdate();
		die();
	}

    public function fetch_property_codes_callback() {
    	echo $this->importer->fetchPropertyCodes();

    	die();
    }

	public function import_properties_from_service_callback()
	{
		$this->importer->import();
		die();
	}

	public function resync_single_property_callback()
    {

    	$postID = $_REQUEST['property_post_id'];
		if ( isset($postID) && (! is_integer(intval($postID)) || is_array($postID)) ) {
			die('Property post ID data type is invalid.');
		}
		$this->importer->importSinglePropertyByPostId($postID);
		die();
    }
	
	public function resync_single_property_by_prop_code_callback()
    {

		$this->importer->importSinglePropertyByPropCode($_REQUEST['prop_code']);
		die();
    }

}