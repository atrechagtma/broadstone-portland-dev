<?php 

class rentPress_PagesAndTemplates {

	/** @var string [string literal with new template override] */
	public static $templateOverride = 'template_override';

	public function __construct() {

		$this->options = new rentPress_Options();

		$this->importer = new rentPress_Import_ImportProperties();

		add_action('admin_init', [$this, 'default_pages']);

		// Singles template override
        add_filter( 'single_template', [$this, 'useCustomSingleTemplate'] );

        add_filter( "page_template", [$this, 'useCustomPageTemplate']);
	}	

	public function resyncPropertyFeedInfo($postID, $postType)
    {
    	$propCode = ($postType == RENTPRESS_PROPERTIES_CPT)?get_post_meta($postID, 'prop_code', true):get_post_meta($postID, 'parent_property_code', true);
    	
    	$propertyResync = get_transient('rentPress_'.$propCode.'_feed_to_be_refreshed');

    	if ( ! $propertyResync ) {
			if ( isset($postID) && (! is_integer(intval($postID)) || is_array($postID)) ) {
				$this->importer->log->error('Property post ID data type is invalid.');
				die();
			}

			// @ToDo, This is not needed we have the id...
	    	$targetName = $postType == RENTPRESS_PROPERTIES_CPT ? get_post_meta($postID, 'propName', true) : get_post_meta($postID, 'fpName', true);
			
			$this->importer
					->setIsAutoRefresh(true)
					->setSuccessMessage('Succesfully refreshed feed for: '.$targetName.' | post id: '.$postID)
					->importSinglePropertyByPostId($postID, $postType, false); // false is saying don't kill process when fail, just log and return;

			
			// Make sure to reset the post data because the single property import performs a wp_query() request
			wp_reset_postdata();

			// Set to refresh every half hour
			set_transient('rentPress_'.$propCode.'_feed_to_be_refreshed', $targetName, HOUR_IN_SECONDS);
    	}
		
		return 'finished'; // This Shuld Be True! Not a String!
    }

	/**
     * Checks if single template override is set to rentPress_Helpers_StringLiterals::$rp_true in the RentPress options meta
     *
     * @param FILELOCATION $single_template
     * @return FILELOCATION
     */
    public function useCustomSingleTemplate($single_template) {
    	/*
			We Should Put These Syncs In Imports Or Something...
    	*/

        global $post, $wp_query;

        
        if ( $wp_query->is_singular ) {
	        if ( $post->post_type == RENTPRESS_PROPERTIES_CPT ) {
	        	// Make sure we update the property information ( runs every hour )
	        	$resyncProperty = $this->resyncPropertyFeedInfo($post->ID, $post->post_type);
		        	$templateToUse = $this->options->getOption('override_single_property_template_file');
		        	$single_template = $templateToUse != 'current-theme' && ! empty($templateToUse) ? $templateToUse : $single_template;
	        } 
	        elseif ( $post->post_type == RENTPRESS_FLOORPLANS_CPT ) {
	        	// Make sure we update the property information ( runs every hour )
	        	$resyncProperty = $this->resyncPropertyFeedInfo($post->ID, $post->post_type);
		        	$templateToUse = $this->options->getOption('override_single_floorplan_template_file');
		        	$single_template = $templateToUse != 'current-theme' && ! empty($templateToUse) ? $templateToUse : $single_template;
	        }
	    }

        return $single_template;
    }

    public function useCustomPageTemplate($page_template) {
       
        global $post, $wp_query;

        if ( $this->options->getOption('is_site_about_a_single_property') === 'true' && $post->post_name === 'floorplans') {

		        $templateToUse = $this->options->getOption('override_archive_floorplans_template_file');

	        	$page_template=$templateToUse != 'current-theme' && ! empty($templateToUse) ? $templateToUse : $page_template;
			     	
        }

        if ( $this->options->getOption('is_site_about_a_single_property') !== 'true' && $post->post_name === 'search') {

		        $templateToUse = $this->options->getOption('override_archive_properties_template_file');

	        	$page_template=$templateToUse != 'current-theme' && ! empty($templateToUse) ? $templateToUse : $page_template;
			     	
        }

        return $page_template;

    }  

    public function default_pages() {

		if ($this->options->getOption('is_site_about_a_single_property') != 'true') {

    		$search_page=get_page_by_path('search');

			if (! isset($search_page->ID)) {

				wp_insert_post([
					'post_type' => 'page',
					'post_title' => 'Search Apartments',
					'post_name' => 'search',
					'post_status' =>  'publish',
					'meta_input' => [
					],		
				]);

			}
		}


		if ($this->options->getOption('is_site_about_a_single_property') === 'true') {

			$floorplans_page=get_page_by_path('floorplans');

			if (! isset($floorplans_page->ID)) {

				wp_insert_post([
					'post_type' => 'page',
					'post_title' => 'Floorplans',
					'post_name' => 'floorplans',
					'post_status' =>  'publish',
					'meta_input' => [
						
					],		
				]);

			}
			
		}

	} 

}