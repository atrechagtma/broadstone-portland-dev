<?php 
/**
 * Manage floor plan information
 * @author  Derek Foster <derek@30lines.com>
 */

class rentPress_FloorPlans_FloorPlan
{
    /** Refers to a single instance of this class. */
    private static $instance = null;
    protected $floorplans;

    /**
     * Creates or returns an instance of this class.
     *
     * @return  rentPress_FloorPlans_FloorPlan A single instance of this class.
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    } // end get_instance;
 
    /**
     * Initialize
     */
    private function __construct() {        
        $this->floorplans = new rentPress_Caching_FloorPlans();
    } // end constructor
 
    /**
     * Get all properties in the system
     * @return array [Array of all available properties]
     */
    public function all($arguments = array())
    {
        rentPress_SlackBot::send_deprecation_message('ALL', 'rentPress_FloorPlans_FloorPlan');
        return $this->floorplans->all($arguments);
    }

    public function byPropertyTaxonomy($propertyTaxonomy, $arguments = array())
    {
        rentPress_SlackBot::send_deprecation_message('byPropertyTaxonomy', 'rentPress_FloorPlans_FloorPlan');
        return $this->floorplans->byProperty($propertyTaxonomy, $arguments);
    }

    /**
     * Get any number of random floor plans
     * @return array [Array of random floor plans]
     */
    public function fetchRandom($amount = 1)
    {
        rentPress_SlackBot::send_deprecation_message('fetchRandom', 'rentPress_FloorPlans_FloorPlan');
 
        $similarFloorPlans = new WP_Query([
            'post_type' => 'floorplans',
            'post_status' => 'publish', 
            'posts_per_page' => $amount,
            'orderby' => 'rand'
        ]);
        $results = [];
        if ( $similarFloorPlans->have_posts() ) {
            while ( $similarFloorPlans->have_posts() ) {
                $similarFloorPlans->the_post();
                $image = wp_get_attachment_url( get_post_thumbnail_id($similarFloorPlans->post->ID) ); 
                $image = $image ? $image : 'https://placehold.it/270x300?text=Floor+Plan'; 
                $results[] = [
                    'fpName' => get_post_meta($similarFloorPlans->post->ID, 'fpName', true),
                    'fpAvailUnitCount' => get_post_meta($similarFloorPlans->post->ID, 'fpAvailUnitCount', true),
                    'fpBeds' => get_post_meta($similarFloorPlans->post->ID, 'fpBeds', true),
                    'fpBaths' => get_post_meta($similarFloorPlans->post->ID, 'fpBaths', true),
                    'fpMinSQFT' => get_post_meta($similarFloorPlans->post->ID, 'fpMinSQFT', true), 
                    'fpMinRent' => get_post_meta($similarFloorPlans->post->ID, 'fpMinRent', true),
                    'permalink' => get_the_permalink($similarFloorPlans->post->ID),
                    'fpImg' => $image,
                    'fpPostID' => $similarFloorPlans->post->ID
                ];
            }
        }
        return $results;
    }

    /**
     * Fetch image for floor plan with featured image and placeholder as fallbacks
     * @param  mixed  $floorPlanPostID [ID of floor plan post]
     * @return string                  [Floor plan image URL]
     */
    public function fetchFloorPlanImage($floorPlanPostID)
    {
        rentPress_SlackBot::send_deprecation_message('fetchFloorPlanImage', 'rentPress_FloorPlans_FloorPlan');
        
        $image = get_post_meta(intval($floorPlanPostID), 'fpImg', true);
        $image = $image ? $image : wp_get_attachment_url( get_post_thumbnail_id($floorPlanPostID) ); 
        return $image ? $image : 'https://placehold.it/400x350?text=Floor+Plan+Img';
    }

} // end class
