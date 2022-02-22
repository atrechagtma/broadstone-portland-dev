<?php
/**
 * Property Repository
 * @author foster30lines
 * Provides a way to easily access top line properties imported or created in Wordpress in an efficient way.
 * Also provides us with some helper methods that perform calculations on the full spread of imported properties. These
 * things are useful for search information or setting filters on archive pages.
 */
class rentPress_Caching_Properties extends rentPress_Base_Caching
{

    /**
     * Fetch list of all available bath counts on all property floor plans
     * @param array $floorplans // List of all available floor plans
     * @return array
     */
    public function incrementalBathRange( $floorplans = array() ) {
        // Init string formatter class
        $rentPressFormatter = new rentPress_Helpers_StringFormatter();
        $floorplans = $this->rentPressMakeSureWeHaveFloorPlans($floorplans);
        $maxBath = [];
        if ( isset($floorplans) ) {
            foreach ($floorplans as $floorplan) {
                if ($floorplan['fpBaths'] === '') continue;
                $maxBath[] = $rentPressFormatter->removeDecimalPointsIfZeroValues($floorplan['fpBaths']);
            }
            $maxBath = array_unique($maxBath);
            sort($maxBath, SORT_NUMERIC);
        }
        return $maxBath;
    }

    /**
     *  Fetch all available bed counts
     *  @param array $floorplans // List of all available floor plans
     *  @return array
     */
    public function incrementalBedRange( $floorplans = array() ) {
        $floorplans = $this->rentPressMakeSureWeHaveFloorPlans($floorplans);
        $maxBeds = [];
        if( isset($floorplans) ) {
            foreach ( $floorplans as $floorplan ) {
                $tmp = null;
                if ( 
                  $floorplan['fpBeds'] === ''|| 
                  !isset($floorplan['fpBeds']) || 
                  $floorplan['fpBeds'] === $tmp 
                ) {
                  continue;
                }
                $maxBeds[] = (integer) $floorplan['fpBeds'] == 0 ? 'Studio' : $floorplan['fpBeds'];
                $tmp = $floorplan['fpBeds'];
            }
            $maxBeds = array_unique($maxBeds);
            sort($maxBeds, SORT_NUMERIC);
        }
        return $maxBeds;
    }

    /**
     * Create array of sqft ranges across all properties for search dropdown
     * @param array $floorplans // List of all available floor plans
     * @return array
     */
    public function incrementalSqftRange( $startAt = 500, $increment = 500, $cap = 2000 ) {
        // retrieve all properties
        $properties = $this->all();

        // array for all min and max sqftg values from all properties
        $allSqftValues = [];

        // loop through each property and add sqftg values to array
        foreach ($properties as $property) {
          if ($property['propMinSQFT'] > 100) $allSqftValues[] = $property['propMinSQFT'];
          if ($property['propMaxSQFT'] > 100) $allSqftValues[] = $property['propMaxSQFT'];
        }

        $returnRange = $this->processRanges($startAt, $increment, $cap, $allSqftValues);

        return array_unique($returnRange);
    }

    /**
     * Return string representation of the range for all floor plan rent prices
     * @param array $floorplans // List of all floor plans
     * @return string
     */
    public function rentPressRentRange( $floorplans = array() ) {
        $floorplans = $this->rentPressMakeSureWeHaveFloorPlans($floorplans);
        $minRent = $this->incrementalLowTierRent(1500, $floorpans);
        $maxRent = $this->incrementalHighTierRent(1500, $floorplans);
        return ($minRent === $maxRent) ? '$'. number_format($minRent) : '$' . number_format($minRent) . ' - $' . number_format($maxRent);
    }

    /**
     * Fetch list of high tier rent prices, re-calculate if they don't exist
     * @param integer $startAt // This tells us where we should start the pricing list
     * @param array $floorplans // List of all floor plans
     * @return array
     */
    public function incrementalHighTierRent($startAt = 1000, $increment = 250, $cap = 4000, $floorplans = array()) {
        $floorplans = $this->rentPressMakeSureWeHaveFloorPlans($floorplans);
        $maxRent = [];
        if ( isset($floorplans) ) {
            foreach($floorplans as $floorplan) {
                $divisibleByOneHundred = (integer) $floorplan['fpMaxRent'] / 100;
                if (
                    !isset($floorplan['fpMaxRent'])
                    || $floorplan['fpMaxRent'] == ''
                    || (integer) $floorplan['fpMaxRent'] < $startAt
                    || $divisibleByOneHundred <= 0
                ) continue;
                $maxRent[] = $floorplan['fpMaxRent'];
            }
            $maxRent = $this->processRanges($startAt, $increment, $cap, $maxRent);
        }
        return $maxRent;
    }

    /**
     * Fetches list of low tier rent from all available properties
     * @param integer $priceCap // This will tell us how high the list should go
     * @param array $floorplans // array of available floor plans
     * @return string
     */
    public function incrementalLowTierRent($startAt = 500, $increment = 250, $cap = 1500, $floorplans = array()) {
        $minRent = [];
        $floorplans = $this->rentPressMakeSureWeHaveFloorPlans($floorplans);
        if ( isset($floorplans) ) {
            foreach ($floorplans as $floorplan) {
                $divisibleByOneHundred = (integer) $floorplan['fpMinRent'] / 100;
                if (
                    !isset($floorplan['fpMinRent'])
                    || $floorplan['fpMinRent'] == ''
                    || (integer) $floorplan['fpMinRent'] >= $cap
                    || $divisibleByOneHundred <= 0
                ) continue;
                $minRent[] = $floorplan['fpMinRent'];
            }
            $minRent = $this->processRanges($startAt, $increment, $cap, $minRent);
        }
        return $minRent;
    }

    private function processRanges($startAt, $increment, $cap, $values) {

      $allValues = [];

      // loop through each property and add sqftg values to array
      foreach ($values as $value) {
        if ($value > 100 && $value >= $startAt) $allValues[] = $value;
      }

      $allValues = array_unique($allValues);
      sort($allValues, SORT_NUMERIC);
      $minValue = floor( $allValues[0] / 100 ) * 100;
      $minValue = ( $minValue <= 500 && $minValue > 250 ) ? 250 : $startAt;
      $maxValue = ceil( end($allValues) / 100 ) * 100;

      $initialRange = range($minValue,$maxValue,$increment);
      $returnRange = [];
      foreach ( $initialRange as $returnValue ) {
        if ( $returnValue < $cap ) {
          $returnRange[] = intval( $returnValue );
        }
        elseif ( $returnValue >= $cap ) {
          $returnRange[] = $cap;
        }
      }

      return array_unique($returnRange);
    }

    public function rentPressMakeSureWeHaveFloorPlans($floorplans) {
        if ( count($floorplans) == 0 ) {
            $floorplans = new rentPress_Caching_FloorPlans();
            $floorplans = $floorplans->all();
        }
        return $floorplans;
    }

    /**
     * Fetch cached floor plans or perform a fresh query for them
     * @return array
     */
    public function featured() {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $featuredProperties = new WP_Query([
            'posts_per_page'    => 10,
            'post_type'         => 'properties',
            'post_status'       => 'publish',
            'orderby'           => 'title',
            'order'             => 'ASC',
            'paged'             => $paged
        ]);
        if ( $featuredProperties->have_posts() ) { 
          while ( $featuredProperties->have_posts() ) { 
            $featuredProperties->the_post();
            $getImage = wp_get_attachment_image_src( get_post_thumbnail_id($featuredProperties->post->ID), 'slider', false, '' );
            $image = $getImage[0];
            $slug = str_replace(' ', '-', strtolower(get_post_meta($featuredProperties->post->ID, 'propName', true)));
            $featuredProps[] = [
                'title' => get_post_meta($featuredProperties->post->ID, 'propName', true),
                'image' => [
                    'obj' => $getImage ? $image : 'https://placehold.it/300x300&text='.$slug,
                    'src' => get_permalink($featuredProperties->post->ID)
                ],
                'phone' => get_post_meta($featuredProperties->post->ID, 'propPhoneNumber', true),
                'url'   => get_post_meta($featuredProperties->post->ID, 'propURL', true)
            ];
          }
        }
        wp_reset_postdata();
        return $featuredProps;
    }

    /**
     * Fetch cached properties or perform a fresh query for them
     * @return array
     */
    public function all($arguments = array()) {
        $propertyArgs = [
            'post_type' => 'properties',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ];
        $propertyArgs = is_array($arguments) ? array_merge($propertyArgs, $arguments) : $propertyArgs;
        // Make sure we stick with the 'properties' custom post type for this request
        $propertyArgs['post_type'] = 'properties';
        $properties = new WP_Query($propertyArgs);
        $results = [];
        if ( $properties->have_posts() ) { 
          while( $properties->have_posts() ) { 
            $properties->the_post();
            $results[] = [
                'postID'            => $properties->post->ID,
                'propName'          => get_post_meta($properties->post->ID, 'propName', true),
                'propAddress'       => get_post_meta($properties->post->ID, 'propAddress', true),
                'propCity'          => get_post_meta($properties->post->ID, 'propCity', true),
                'propState'         => get_post_meta($properties->post->ID, 'propState', true),
                'propZip'           => get_post_meta($properties->post->ID, 'propZip', true),
                'propURL'           => get_post_meta($properties->post->ID, 'propURL', true),
                'propDescription'   => get_post_meta($properties->post->ID, 'propDescription', true),
                'propEmail'         => get_post_meta($properties->post->ID, 'propEmail', true),
                'propLatitude'      => get_post_meta($properties->post->ID, 'propLatitude', true),
                'propLongitude'     => get_post_meta($properties->post->ID, 'propLongitude', true),
                'prop_code'         => get_post_meta($properties->post->ID, 'prop_code', true),
                'propMinRent'       => get_post_meta($properties->post->ID, 'propMinRent', true),
                'propMaxRent'       => get_post_meta($properties->post->ID, 'propMaxRent', true),
                'propMinBeds'       => get_post_meta($properties->post->ID, 'propMinBeds', true),
                'propMaxBeds'       => get_post_meta($properties->post->ID, 'propMaxBeds', true),
                'propMinBaths'      => get_post_meta($properties->post->ID, 'propMinBaths', true),
                'propMaxBaths'      => get_post_meta($properties->post->ID, 'propMaxBaths', true),
                'propMinSQFT'       => get_post_meta($properties->post->ID, 'propMinSQFT', true),
                'propMaxSQFT'       => get_post_meta($properties->post->ID, 'propMaxSQFT', true),
                'prop_coords'       => get_post_meta($properties->post->ID, 'prop_coords', true),
                'prop_tagline'      => get_post_meta($properties->post->ID, 'prop_tagline', true),
                'property_searchterms' => get_post_meta($properties->post->ID, 'property_searchterms', true)
            ];
          }
        }
        wp_reset_postdata();
        return $results;
    }

}
