<?php 

/**
* Fetch meta information for Properties
*/
class rentPress_Posts_Meta_Units
{
    public $floorPlanPostID;
    public $unit;

    private $log;
    private $options;
    private static $instance = null;
    public static $disablePricingUrl_key = 'disable_url_pricing';

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
    } // end get_instance

    final public function __construct()
    {
        $this->log = new rentPress_Logging_Log();
        $this->options = new rentPress_Options();
    }

    public function name()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return sanitize_text_field($this->unit->Information->Name);
    }

    public function application()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        
        if ( $this->options->getOption(self::$disablePricingUrl_key) == 'on' ) {
            return $this->options->getOption(self::$disablePricingUrl_key);
        }

        return esc_html($this->unit->QuickLinks->Application);
    }

    public function tourLink()
    {
        if ( ! $this->unit ) return $this->respondNoUnitGiven();
        return esc_html($this->unit->QuickLinks->ScheduleTourUrl);
    }

    public function quoteLink()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
       
        if ( $this->options->getOption(self::$disablePricingUrl_key) == 'on' ) {
            return $this->options->getOption(self::$disablePricingUrl_key);
        }
       
        return esc_html($this->unit->QuickLinks->QuoteUrl);
    }

    public function matterport($inIframe = false)
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        if ( $inIframe ) return '<iframe class="rp-matterport" src="'.$this->unit->QuickLinks->MatterportUrl.'" width="100%" height="500"></iframe>';
        return esc_html($this->unit->QuickLinks->MatterportUrl);
    }

    public function buildingLevel()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return esc_html($this->unit->Rooms->FloorLevel);
    }

    public function hasSpecial()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return empty($this->unit->Information->SpecialsMessage) ? false : true;
    }

    public function special()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return $this->unit->Information->SpecialsMessage;
    }

    public function quickLinks()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return $this->unit->QuickLinks;
    }

    public function videos()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return $this->unit->Videos;
    }

    public function images()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return $this->unit->Images;
    }

    public function amenities()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return $this->unit->Amenities;
    }

    public function beds()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return sanitize_text_field($this->unit->Rooms->Bedrooms);
    }

    public function baths()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return sanitize_text_field($this->unit->Rooms->Bathrooms);
    }

    public function availabilityLink()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        if ( defined('EAUB_PLUGIN_DIR') ) { // For the Entrata application link builder addon
            $entrataManager = new EntrataApplications_OptionsManager();
            return $entrataManager->buildApplicationLinkForShortCode([
                'property_code' => $this->parentPropertyID(),
                'floor_plan_code' => $this->floorPlanID(),
                'unit_code' => $this->unitID()
            ]);
        }

        if($this->options->getOPtion('override_apply_url') && $this->options->getOPtion('override_apply_url') != '') {
            return $this->options->getOPtion('override_apply_url');
        }
        
        return esc_url($this->unit->Information->AvailabilityURL);
    }

    public function sqft($type = 'Min')
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return sanitize_text_field($this->unit->SquareFeet->{$type});
    }

    public function effectiveRent($formatted = false)
    {
        return $this->rentByType('EffectiveRent', $formatted);
    }

    public function marketRent($formatted = false)
    {
        return $this->rentByType('MarketRent', $formatted);
    }

    public function baseRent($formatted = false)
    {
        return $this->rentByType('Amount', $formatted);
    }

    public function bestRent($formatted = false)
    {
        return $this->rentByType('BestPrice', $formatted);
    }

    private function rentByType($type, $formatted = false)
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }

        $rent = $this->unit->Rent->{$type};
        
        if ( $this->disable_pricing === true) {
            $rent = $this->options->getOption('disable_pricing');
        }

        if ( $formatted && $this->disable_pricing === false ) {
            $rent = '$'.number_format($this->unit->Rent->{$type});
        }

        return esc_html($rent);
    }

    public function unitID()
    {
        return $this->fetchID();
    }

    public function parentPropertyID()
    {
        return $this->fetchID('ParentPropertyCode');
    }

    public function floorPlanID()
    {
        return $this->fetchID('ParentFloorPlanCode');
    }

    private function fetchID($key = 'UnitCode')
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        return isset($this->unit->Identification->{$key}) ? esc_html($this->unit->Identification->{$key}) : 'No ID by that key';
    }

    public function availableOn($format = null)
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }

        $date = $this->unit->Information->AvailableOn;

        // If empty date, then return a small notification to call for availability info
        if ( empty($date) ) {
            return 'Call for Availability';
        }

        // Convert to time stamp
        $timestamp = strtotime($date);

        // Format the date if format is provided
        if ( isset($format) ) {
            $date = date($format, $timestamp);
        }

        // If time is before today, we say it is available 'now'
        if ( $timestamp < time() ) {
            $date = 'Now';
        }

        return esc_html__($date, RENTPRESS_LANG_KEY);
    }

    public function rent()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }

        if ( $this->disable_pricing === true ) {
            return $this->options->getOption('disable_pricing');
        }

        // Initially default to Market Rent
        $rent = $this->unit->Rent->MarketRent;

        $unitRentType = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->options->getOption('unit_rent_type'))));

        $globalLeaseTermOption = $this->options->getOption('unit_lease_term');
        
        $hasOverride = get_transient('rentpress_unit_lease_term_price_'.$this->unit->Identification->UnitCode);

        if (
            $hasOverride
            &&
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->UnitCode ) === false 
            &&
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->ParentPropertyCode ) === false 
            &&
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->ParentFloorPlanCode ) === false 
            &&
            $this->options->getOption('disbale_all_units_lt_pricing') !== 'true'
        ) {

            $rent=$this->rentByTerm($hasOverride);

        } elseif (
            $unitRentType === 'TermRent' 
            && 
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->UnitCode ) === false 
            &&
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->ParentPropertyCode ) === false 
            &&
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->ParentFloorPlanCode ) === false 
            &&
            $this->options->getOption('disbale_all_units_lt_pricing') !== 'true'
        ) {
          
            $globalLeaseTermOption = empty($globalLeaseTermOption) ? '12' : $globalLeaseTermOption;
            $rent = $this->rentByTerm($globalLeaseTermOption);
        
        } elseif ( $unitRentType === 'BestPrice' ) {
            // if there is not BestPrice value on the unit, grab the best price overall
            $rent = $this->bestPrice();
        } else { 
            // If not Term or Best Price, then we want to use what the options screen selection is in Feed Config
            $rent = $this->rentByType($unitRentType);
        }

        // If none of the if/else case scenarios give us valid data, then we just try to grab the best price as a fallback
        if ( empty($rent) || $rent === '-1' || $rent === '0' ) {
            $rent = $this->unit->Rent->MarketRent;
        }
        
        return esc_html__($rent, RENTPRESS_LANG_KEY);
    }

    public function fetchBestLeaseTermPrice()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        
        if ( $this->disable_pricing === true) {
            return $this->options->getOption('disable_pricing');
        }

        // If lease term options are available, it will only get best price from that set of data so it works for this method
        return $this->bestPrice();
    }

    public function bestPrice()
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        
        if ( $this->disable_pricing === true ) {
            return $this->options->getOption($rawValue);
        }

        // Add all calculated prices to rent list
        $rentList = [];
        $leaseTermPriceOptions = [];

        if (
            $this->options->getOption('disbale_all_units_lt_pricing') !== 'true'
            && 
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->UnitCode ) === false
            &&
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->ParentPropertyCode ) === false 
            &&
            strpos( $this->options->getOption('disable_units_lt_pricing'), $this->unit->Identification->ParentFloorPlanCode ) === false 
        ) {

            // Add all available lease term rent options to rent list
            $leaseTermOptions = isset($this->unit->Rent->TermRent->data) ? $this->unit->Rent->TermRent->data : $this->unit->Rent->TermRent;
            if ( isset($leaseTermOptions) && (!is_string($leaseTermOptions)) ) {
                foreach ( $leaseTermOptions as $key => $term ) {
                    array_push($leaseTermPriceOptions, intval($term->Rent));
                }
            }

            
        }

        // If there are lease term price options available, then we only want to calculate from those. 
        if ( count($leaseTermPriceOptions) > 0) {
            $rentList = $leaseTermPriceOptions;
        } else {
            // If there are no lease term price options available, then we want to calculate from all other price sources 
            // to find the lowest price available. 
            array_push($rentList,
                intval($this->unit->Rent->MarketRent),
                intval($this->unit->Rent->BestPrice),
                intval($this->unit->Rent->Amount),
                intval($this->unit->Rent->EffectiveRent),
                intval($this->unit->Rent->MinRent),
                intval($this->unit->Rent->MaxRent)
            );
        }

        // Filter out values that qualify as empty
        $rentList = array_filter($rentList); 

        // Sort the rent list
        sort($rentList, SORT_NUMERIC);
        $rent = esc_html__($this->options->getOption('disable_pricing_message'), RENTPRESS_LANG_KEY);

        if ( count($rentList) > 0 ) {
            $rent = esc_html__($rentList[0], RENTPRESS_LANG_KEY);
        }

        return $rent;
    }

    public function rentByTerm($leaseTerm = null)
    {
        if ( ! $this->unit ) {
            return $this->respondNoUnitGiven();
        }
        // Use provided lease term length, default to global setting
        $leaseTerm = isset($leaseTerm) ? $leaseTerm : $this->options->getOption('unit_lease_term');

        // If global and manual fail to provide, manually set to 12 month lease term, as it is most common
        if ( ! isset($leaseTerm) || $leaseTerm == '' ) {
            $leaseTerm = '12';
        }
        // In some cases we don't get the ->data portion of this, just the array of arrays **LOOKING INTO IT**
        if ( ! isset($this->unit->Rent->TermRent->data) && isset($this->unit->Rent->TermRent) ) {
            $termRent = $this->unit->Rent->TermRent;
        } else if ( isset($this->unit->Rent->TermRent->data) ) {
            $termRent = $this->unit->Rent->TermRent->data; 
        } else {
            $termRent = null;
        }
        if ( isset($termRent) ) {
            foreach ($termRent as $key => $term) {
                // If no lease term length provided, default to the RentPress feed option settings for unit_lease_term
                if ( $term->Term == $leaseTerm ) {
                    return esc_html__($term->Rent, RENTPRESS_LANG_KEY);
                }
            }
        }
        // If all else fails, return the rent as defined by the feed config unit rent type option
        $unitRentType = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->options->getOption('unit_rent_type'))));
        return esc_html__($this->rentByType($unitRentType));
    }

    private function respondNoUnitGiven()
    {
        $this->log->warning('No unit was found for processing.');
        return 'No unit was found for processing.';
    }

    /**
     * Sets the value of floorPlanPostID.
     * @param mixed $floorPlanPostID the floor plan post
     * @return self
     */
    public function fromFloorPlan($floorPlanPostID)
    {
        $this->floorPlanPostID = $floorPlanPostID;
        return $this;
    }

    /**
     * Sets the value of unit.
     * @param mixed $unit theunit 
     * @return self
     */
    public function fromUnit($unit)
    {
        global $wpdb;
     
        $this->unit = $unit;

        $property_disabled_pricing=$wpdb->get_col("
            SELECT pm.meta_value FROM $wpdb->postmeta pm
            WHERE  pm.meta_key = 'propDisablePricing' AND pm.post_id IN (
                SELECT pm.post_id FROM {$wpdb->postmeta} pm
                WHERE  pm.meta_key = 'prop_code' AND pm.meta_value = '". self::parentPropertyID() ."'
            )
            LIMIT 1
        ");

        $this->disable_pricing=(
            $this->options->getOption(self::$disablePricingUrl_key) == 'on'
            ||
            ( isset($property_disabled_pricing[0]) && $property_disabled_pricing[0] == 'true' )
        );

        return $this;
    }

    /**
     * __get() is triggered when trying to access a property of the class that may or may not exist
     * @param  [string] $name [Property key]
     * @return [mixed]        [Value of desired property]
     */
    public function __get($name) {
        if ( property_exists($this, $name) ) {
            return $this->$name;
        }
    }    

    /**
     * __call() is triggered when invoking inaccessible methods in an object context.
     * @param  [string] $method    [Name of the method being called]
     * @param  [array]  $arguments [Arguments to be passed to the target method]
     * @return [mixed]             [void if conditions not met, returns response if conditions are met]
     */
    public function __call($method, $arguments)
    {
        // Make sure that a unit object is applied to the class before trying to perform
        // operations on it.
        if ( method_exists($this, $method) ) {
            if ( ! $this->unit ) {
                return $this->respondNoUnitGiven();
            }
            return call_user_func_array(array($this,$method),$arguments);
        }
    }
}