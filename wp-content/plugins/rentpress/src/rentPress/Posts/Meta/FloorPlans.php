<?php 

/**
* Fetch meta information for Properties
*/
class rentPress_Posts_Meta_FloorPlans extends rentPress_Base_CptMeta
{
    private static $instance = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @return  rentPress_FloorPlans A single instance of this class.
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    } // end get_instance;

	public function name($postID = null)
	{
		return $this->fetchMeta($postID, 'fpName');
	}

    public function beds($postID = null)
    {
        return $this->fetchMeta($postID, 'fpBeds');
    }

    public function baths($postID = null)
    {
        return $this->fetchMeta($postID, 'fpBaths');
    }

    public function sqftg($range = 'Min', $postID = null)
    {
        return $this->fetchMeta($postID, 'fp'.ucwords($range).'SQFT');
    }

    public function rent($range = 'Min', $formatNumber = false, $postID = null)
    {
        if ( $formatNumber ) {
            return number_format($this->fetchMeta($postID, 'fp'.ucwords($range).'Rent'));
        }
        return $this->fetchMeta($postID, 'fp'.ucwords($range).'Rent');
    }

    public function displayRentForTemplate($postID = null) {
        $minRent = number_format($this->fetchMeta($postID, 'fpMinRent'));
        $displayRent = "";

        if ($this->fetchMeta($postID, 'fpMinRent') > 99) {
            switch ($this->options->getOption('override_how_floorplan_pricing_is_display')) {

                case 'starting-at':
                    $displayRent = "Starting at $".$minRent;
                    break;

                case 'range':
                    $maxRent = number_format($this->fetchMeta($postID, 'fpMaxRent'));

                    if ($minRent == $maxRent) {
                        $displayRent = "Starting at $".$minRent;
                    }
                    else {
                        $displayRent = "$".$minRent."-". $maxRent;
                    }

                    break;

                default:    
                    $displayRent = "Starting at $".$minRent;
                    break;

            }
        } else {
            $displayRent = $this->options->getOption('disable_pricing_message');
        }

        return $displayRent;
    }

    public function availableUnitCount($postID = null)
    {
        return $this->fetchMeta($postID, 'fpAvailUnitCount');
    }

    public function availabilityUrl($postID = null)
    {
        if ( defined('EAUB_PLUGIN_DIR') ) { // For the Entrata application link builder addon
            $entrataManager = new EntrataApplications_OptionsManager();
            return $entrataManager->buildApplicationLinkForShortCode([
                'property_code' => $this->fetchMeta($postID, 'prop_code'),
                'floor_plan_code' => $this->fetchMeta($postID, 'parent_property_code')
            ]);
        }
        return $this->fetchMeta($postID, 'fpAvailURL');
    }

    public function pdf($postID = null)
    {
        return $this->fetchMeta($postID, 'fpPDF');
    }

	public function units($postID = null, $args = [], $inArrayFormat = false)
	{
        $args = array_merge(['post_id' => isset($postID) ? $postID : $this->postID ], $args);
        $units_query = new rentPress_Units_Query($args);
        return $units_query->run_query();
	}

    /**
     * Fetch image for floor plan with featured image and placeholder as fallbacks
     * @param  string $floorPlanPostID [ID of floor plan post]
     * @return string                  [Floor plan image URL]
     */
    public function image($floorPlanPostID = null)
    {
        $fallback = 'https://placehold.it/400x350?text=Floor Plan Img';
        $optionsSettingsImageOverride = $this->options->getOption('floorplans_default_featured_image');
        if ( isset($optionsSettingsImageOverride) && strlen($optionsSettingsImageOverride) ) {
            $fallback = $optionsSettingsImageOverride;
        }
        $image = $this->fetchFeaturedImageUrl($floorPlanPostID); 
        if ( ! $image ) {
            $image = $this->fetchMeta($floorPlanPostID, 'fpImg');
            $images = explode(',', $image); 
            if ( count($images) > 1 ) $image = $images[0]; 
        }
        // If there's a placeholder link, override with default fallback
        if ( strpos($image, 'placehold') > 0 && isset($optionsSettingsImageOverride) && strlen($optionsSettingsImageOverride)  ) {
            $image = $fallback;
        }
        return $image ? $image : $fallback;
    }

    /**
     * Get 4 random floor plans
     * @return array [Array of random floor plans]
     */
    public function fetchFourRandom()
    {
        $randomFloorPlans = new WP_Query([
            'post_type' => 'floorplans',
            'post_status' => 'publish', 
            'posts_per_page' => 4,
            'orderby' => 'rand'
        ]);
        $results = [];
        if ( $randomFloorPlans->have_posts() ) {
            while ( $randomFloorPlans->have_posts() ) {
                $randomFloorPlans->the_post();
                $image = $this->fetchFeaturedImageUrl($randomFloorPlans->post->ID); 
                $image = $image ? $image : 'https://placehold.it/350x400?text=Floor+Plan'; 
                $results[] = [
                    'fpName' => get_post_meta($randomFloorPlans->post->ID, 'fpName', true),
                    'fpAvailUnitCount' => get_post_meta($randomFloorPlans->post->ID, 'fpAvailUnitCount', true),
                    'fpBeds' => get_post_meta($randomFloorPlans->post->ID, 'fpBeds', true),
                    'fpBaths' => get_post_meta($randomFloorPlans->post->ID, 'fpBaths', true),
                    'fpMinSQFT' => get_post_meta($randomFloorPlans->post->ID, 'fpMinSQFT', true), 
                    'fpMinRent' => get_post_meta($randomFloorPlans->post->ID, 'fpMinRent', true),
                    'permalink' => get_the_permalink($randomFloorPlans->post->ID),
                    'fpImg' => $image,
                    'fpPostID' => $randomFloorPlans->post->ID
                ];
            }
        }
        return $results;
    }


}