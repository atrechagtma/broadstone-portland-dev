<?php
/**
 * Manage floor plan information
 * @author  Derek Foster <derek@30lines.com>
 */

class rentPress_Properties_Property {
    /** Refers to a single instance of this class. */
    private static $instance = null;
    protected $floorplans;
    protected $targetProperty;

    /**
     * Initialize
     */
    private function __construct() {
    	$this->properties = new rentPress_Caching_Properties();
    	$this->propertyMeta = new rentPress_Posts_Meta_Properties();
        $this->targetProperty = null;
    } // end constructor

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

 	/**
 	 * Get all properties in the system
 	 * @return array [Array of all available properties]
 	 */
    public function all($arguments = array())
    {
        rentPress_SlackBot::send_deprecation_message('all', 'rentPress_Properties_Property');
        return $this->properties->all($arguments);
    }

    /**
     * Get all properties in an array optimized for use with a Google Map ( JS API )
     * @return array [Array of all properties for google JS map]
     */
    public function forGoogleMap()
    {
        rentPress_SlackBot::send_deprecation_message('forGoogleMap', 'rentPress_Properties_Property');

        $mapProperties = [];
        $properties = $this->properties->all();
        foreach ($properties as $property) {

            // @ToDo Upgrade The Neighborhood Relation Here
            $propertyMeta = $this->propertyMeta->setPostID($property['postID']);
            $neighborhoodTerm = '';
            $neighborhoodTerms = get_the_terms( $property['postID'], 'property_neighborhood' ); 
            if ( $neighborhoodTerms && ! is_wp_error( $neighborhoodTerms ) ) : 
                foreach ( $neighborhoodTerms as $term ) $neighborhoodTerm = $term->name;
            endif;
            $mapProperties[] = [
                'name' => $propertyMeta->name(),
                'address' => $propertyMeta->address(null, true),
                'property_link' => get_the_permalink($property['postID']),
                'phone' => $propertyMeta->phone(),
                'image' => $propertyMeta->image(),
                'minrent' => number_format($property['propMinRent']),
                'neighborhood' => $neighborhoodTerm,
                'longitude' => $propertyMeta->longitude(),
                'latitude' => $propertyMeta->latitude()
            ];
        }
        return json_encode($mapProperties);
    }


    public function name($propertyPostID = null)
    {
        rentPress_SlackBot::send_deprecation_message('meta_dp', 'rentPress_Properties_Property');
        if ( isset($this->targetProperty) ) $propertyPostID = $this->targetProperty;
        return $this->propertyMeta->name($propertyPostID);
    }

    public function amenities($propertyPostID = null, $inArrayFormat = false)
    {
        rentPress_SlackBot::send_deprecation_message('meta_dp', 'rentPress_Properties_Property');
        if ( isset($this->targetProperty) ) $propertyPostID = $this->targetProperty;
        return $this->propertyMeta->fetchJsonMeta($propertyPostID, 'amenities', $inArrayFormat);
    }

    public function staff($propertyPostID = null, $inArrayFormat = false)
    {
        rentPress_SlackBot::send_deprecation_message('meta_dp', 'rentPress_Properties_Property');
        if ( isset($this->targetProperty) ) $propertyPostID = $this->targetProperty;
        return $this->propertyMeta->fetchJsonMeta($propertyPostID, 'propertyStaff', $inArrayFormat);
    }

    public function incrementalBathRange()
    {
        rentPress_SlackBot::send_deprecation_message('meta_dp', 'rentPress_Properties_Property');
        return $this->properties->incrementalBathRange();
    }

    public function incrementalBedRange()
    {
        rentPress_SlackBot::send_deprecation_message('meta_dp', 'rentPress_Properties_Property');
        return $this->properties->incrementalBedRange();
    }

    public function incrementalSqftRange()
    {
        rentPress_SlackBot::send_deprecation_message('meta_dp', 'rentPress_Properties_Property');
        return $this->properties->incrementalSqftRange();
    }

    public function incrementalHighTierRent()
    {
        rentPress_SlackBot::send_deprecation_message('meta_dp', 'rentPress_Properties_Property');
      return $this->properties->incrementalHighTierRent();
    }

    public function incrementalLowTierRent()
    {
        rentPress_SlackBot::send_deprecation_message('meta_dp', 'rentPress_Properties_Property');
      return $this->properties->incrementalLowTierRent();
    }


    /**
     * Sets the value of targetProperty.
     *
     * @param mixed $targetProperty // Accepts the property post object or the postID
     *
     * @return self
     */
    public function setTargetProperty($targetProperty)
    {
        $this->targetProperty =  is_object($targetProperty) && isset($targetProperty->ID) ?
                                    $targetProperty->ID :
                                    $targetProperty;
        return $this;
    }
} // end class
