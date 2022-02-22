<?php 
/**
 * Manage floor plan information
 * @author  Derek Foster <derek@30lines.com>
 */

class rentPress_Units_Units
{
    /** Refers to a single instance of this class. */
    private static $instance = null;
    /** @var stdClass [Object array of units] */
    public $units;
    /** @var string [ID of the current target unit] */
    public $currentUnitName;
    /** @var string [Parent floor plan post ID so we can request the units -- required] */
    public $floorPlanPostID;
    public $availableRentTypes = ['EffectiveRent', 'TermRent', 'Amount', 'MarketRent', 'MinRent', 'MaxRent', 'BestPrice'];

    /**
     * Initialize
     */
    final private function __construct() {
        $this->floorPlanPostID = null; 
        $this->currentUnitName = null;
        $this->units = new stdClass();
        $this->log = new rentPress_Logging_Log();
        $this->options = new rentPress_Options();
    } // end constructor

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

    public function all()
    {
        rentPress_SlackBot::send_deprecation_message('all', 'rentPress_Units_Units');
        return $this->units;
    }

    public function fetchAvailableNow()
    {
        rentPress_SlackBot::send_deprecation_message('fetchAvailableNow', 'rentPress_Units_Units');

        if ( ! isset($this->units) || count($this->units) == 0 ) {
            $this->log->warning('No units provided for processing in fetchAvailableNow().');
            return 'No units provided for processing.';
        }
        $results = [];
        foreach ( $this->units as $unit ) {
            $availableOn = strtotime($unit->Information->AvailableOn);
            if ( $availableOn < time() || $unit->isAvailable ) $results[] = $unit;
        }
        return $results;
    }

    public function is_pricing_disabled() {
        global $wpdb;
        rentPress_SlackBot::send_deprecation_message('is_pricing_disabled', 'rentPress_Units_Units');

        if ( isset($this->units) && count($this->units) > 0 ) {
            if (isset($this->units[0]->Identification) && isset($this->units[0]->Identification->ParentPropertyCode)) {

                $property_disabled_pricing=$wpdb->get_col("
                    SELECT pm.meta_value FROM $wpdb->postmeta pm
                    WHERE  pm.meta_key = 'propDisablePricing' AND pm.post_id IN (
                        SELECT pm.post_id FROM {$wpdb->postmeta} pm
                        WHERE  pm.meta_key = 'prop_code' AND pm.meta_value = '". $this->units[0]->Identification->ParentPropertyCode ."'
                    )
                    LIMIT 1
                ");

                if (
                    $this->options->getOption('disable_pricing') == 'true'
                    ||
                    ( isset($property_disabled_pricing[0]) && $property_disabled_pricing[0] == 'true' )
                ) {
                    return $this->options->getOption('disable_pricing_message');
                }
            }
        }
    }

    public function bestPrice()
    {
        rentPress_SlackBot::send_deprecation_message('bestPrice', 'rentPress_Units_Units');

        if ( isset($this->units) && count($this->units) > 0 ) {
            self::is_pricing_disabled();
            $rent = $this->options->getOption('disable_pricing_message');

            foreach ( $this->units as $unit) {
                if ( trim($unit->Information->Name) == trim($this->currentUnitName) ) {
                    $type = $this->options->getOption('unit_rent_type');
                    $type = str_replace(' ', '', ucwords(str_replace('_', ' ', $type)));
                    if ( ! isset($unit->Rent->{$type}) ) {
                        $message = '`'.$type.'` is an invalid key for rent type. Please choose between the following: '.
                                    implode(', ', $this->availableRentTypes);
                        $this->log->error($message);
                        return $message;
                    }
                    if ( $type == 'TermRent' ) {
                        $globalLeaseTermOption = $this->options->getOption('unit_lease_term');
                        if ( isset($globalLeaseTermOption) ) $leaseTermLength = $globalLeaseTermOption;
                        return $this->fetchRentByTerm($unit->Rent->{$type}, $leaseTermLength);
                    }

                    return $unit->Rent->{$type};
                }
            }
        } else {
            $message = 'No units to select from [origin: bestPrice()]';
            $this->log->error($message);
            return $message;
        }
        return $rent;
    }

    public function rentByType($type = null, $arguments = [])
    {

        rentPress_SlackBot::send_deprecation_message('rentByType', 'rentPress_Units_Units');

        if ( ! isset($type) ) {
            $type = $this->options->getOption('unit_rent_type');
            $type = str_replace(' ', '', ucwords(str_replace('_', ' ', $type)));
        }
        if ( ! isset($this->units) || count($this->units) == 0 ) {
            $this->log->warning('No units provided for processing in rentByType().');
            return 'No units provided for processing in rentByType().';
        }
        if ( ! isset($this->currentUnitName) || $this->currentUnitName == '' ) {
            $message = 'No unit name was provided for getting rentByType(). Please prepend ->fromUnit() to this method and pass through the name of the unit.';
            $this->log->error($message);
            return $message;
        }

        $rent = null;
        
        self::is_pricing_disabled();

        foreach ($this->units as $unit) {
            $hasOverride = get_transient('rentpress_unit_lease_term_price_'.$unit->Identification->UnitCode);
            if ( trim($unit->Information->Name) == trim($this->currentUnitName) ) {

                if ( $type == 'TermRent' || $hasOverride ) {
                    $leaseTermLength = null;
                    if ( isset($arguments['lease_term']) ) $leaseTermLength = $arguments['lease_term'];
                    if ( $hasOverride ) $leaseTermLength = $hasOverride;
                    return $this->fetchRentByTerm($unit->Rent->TermRent, $leaseTermLength);
                }
                if ( $unit->Rent->{$type} == 0 || ! isset($unit->Rent->{$type}) ) return $unit->Rent->MarketRent;
                if ( ! isset($unit->Rent->{$type}) ) {
                    $message = '`'.$type.'` is an invalid key for rent type, or there is no value available. Please choose between the following: '.
                                implode(', ', $this->availableRentTypes);
                    $this->log->error($message);
                    return $unit->Rent->MarketRent;
                }
                return $unit->Rent->{$type};
            }
        }

        return $rent;
    }

    private function fetchRentByTerm($leaseTermOptions, $termOfChoice = null)
    {
        rentPress_SlackBot::send_deprecation_message('fetchRentByTerm', 'rentPress_Units_Units');

        $globalLeaseTermOption = $this->options->getOption('unit_lease_term');
        if ( empty($globalLeaseTermOption) ) $globalLeaseTermOption = '12';
        $leaseTermOptions = isset($leaseTermOptions->data) ? $leaseTermOptions->data : $leaseTermOptions;
        foreach ($leaseTermOptions as $term) {
            // If no lease term length provided, default to the RentPress feed option settings for unit_lease_term
            $termOfChoice = isset($termOfChoice) && $termOfChoice !== '' ? $termOfChoice : $globalLeaseTermOption;
            if ( $term->Term == $termOfChoice ) return $term->Rent;
        }
        return 'No rent for '.$termOfChoice.' lease term';
    }

    private function setUpUnits()
    {
        rentPress_SlackBot::send_deprecation_message('setUpUnits', 'rentPress_Units_Units');

        $results = [];
        if ( ! isset($this->floorPlanPostID) ) 
            wp_die('You need to provide a floor plan post ID to be able to request the units.');
        // Return floor plan units 
        return json_decode(get_post_meta($this->floorPlanPostID, 'fpUnits', true));
    }


    /**
     * Sets the value of floorPlanPostID.
     * @param mixed $floorPlanPostID the floor plan post
     * @return self
     */
    public function fromFloorPlan($floorPlanPostID)
    {
        rentPress_SlackBot::send_deprecation_message('fromFloorPlan', 'rentPress_Units_Units');

        $this->floorPlanPostID = $floorPlanPostID;
        $this->units = $this->setUpUnits();
        return $this;
    }

    /**
     * Sets the value of currentUnitName.
     * @param mixed $currentUnitName the current unit
     * @return self
     */
    public function fromUnit($currentUnitName)
    {
        rentPress_SlackBot::send_deprecation_message('fromUnit', 'rentPress_Units_Units');
        $this->currentUnitName = $currentUnitName;
        return $this;
    }

    /**
     * Sets the value of units.
     * @param mixed $units the units
     * @return self
     */
    public function setUnits($units)
    {
        rentPress_SlackBot::send_deprecation_message('setUnits', 'rentPress_Units_Units');
        $this->units = is_string($units) ? json_decode($units) : $units;
        return $this;
    }

} // end class
