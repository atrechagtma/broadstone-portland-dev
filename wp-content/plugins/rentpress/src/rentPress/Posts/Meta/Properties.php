<?php 

/**
* Fetch meta information for Properties
*/
class rentPress_Posts_Meta_Properties extends rentPress_Base_CptMeta
{
    private static $instance = null;

    public function fromPropCode($prop_code = null) {
    	if (! is_null($prop_code)) {
    		return self::setPostID( 
				get_posts([
					'fields' => 'ids',
					'post_status' => 'any',
					'posts_per_page' => 1,

					'post_type' => 'properties',
					'property_code' => $prop_code,
				])[0]
			);
    	}
    }

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
		return $this->fetchMeta($postID, 'propName');
	}

	public static function format_phone_number ( $mynum, $mask ) {
	    /*********************************************************************/
	    /*   Purpose: Return either masked phone number or false             */
	    /*     Masks: Val=1 or xxx xxx xxxx                                             */
	    /*            Val=2 or xxx xxx.xxxx                                             */
	    /*            Val=3 or xxx.xxx.xxxx                                             */
	    /*            Val=4 or (xxx) xxx xxxx                                           */
	    /*            Val=5 or (xxx) xxx.xxxx                                           */
	    /*            Val=6 or (xxx).xxx.xxxx                                           */
	    /*            Val=7 or (xxx) xxx-xxxx                                           */
	    /*            Val=8 or (xxx)-xxx-xxxx                                           */
	    /*********************************************************************/         
	    if ( ( $mask == 1 ) || ( $mask == 'xxx xxx xxxx' ) ) { 
	        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', 
	                '$1 $2 $3'." \n", $mynum);
	        return $phone;
	    }   // end if $mask == 1
	    if ( ( $mask == 2 ) || ( $mask == 'xxx xxx.xxxx' ) ) { 
	        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', 
	                '$1 $2.$3'." \n", $mynum);
	        return $phone;
	    }   // end if $mask == 2
	    if ( ( $mask == 3 ) || ( $mask == 'xxx.xxx.xxxx' ) ) { 
	        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', 
	                '$1.$2.$3'." \n", $mynum);
	        return $phone;
	    }   // end if $mask == 3
	    if ( ( $mask == 4 ) || ( $mask == '(xxx) xxx xxxx' ) ) { 
	        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', 
	                '($1) $2 $3'." \n", $mynum);
	        return $phone;
	    }   // end if $mask == 4
	    if ( ( $mask == 5 ) || ( $mask == '(xxx) xxx.xxxx' ) ) { 
	        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', 
	                '($1) $2.$3'." \n", $mynum);
	        return $phone;
	    }   // end if $mask == 5
	    if ( ( $mask == 6 ) || ( $mask == '(xxx).xxx.xxxx' ) ) { 
	        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', 
	                '($1).$2.$3'." \n", $mynum);
	        return $phone;
	    }   // end if $mask == 6
	    if ( ( $mask == 7 ) || ( $mask == '(xxx) xxx-xxxx' ) ) { 
	        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', 
	                '($1) $2-$3'." \n", $mynum);
	        return $phone;
	    }   // end if $mask == 7
	    if ( ( $mask == 8 ) || ( $mask == '(xxx)-xxx-xxxx' ) ) { 
	        $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', 
	                '($1)-$2-$3'." \n", $mynum);
	        return $phone;
	    }   // end if $mask == 8
	    return false;       // Returns false if no conditions meet or input
	}  // end function format_phone_number

	//@TODO Charles, remove this $formatNumber functionality. It is obsolete. Need to know which site you used this on.
	public function phone($postID = null, $formatNumber = false, $separator = '')
	{
		$phoneNumber = $this->fetchMeta($postID, 'propPhoneNumber');
		return $phoneNumber;
	}

	public function formatPhone($n, $format, $noFormat) {
        if ($format == 'noPhoneFormat') {
            $formattedNumber = $noFormat;
        } else {
            $n = str_split($n);
            $format = str_split($format);
            $formattedNumber = '';
            $x = 0;
            for ($i=0; $i < count($format); $i++) { 
                if ($format[$i] == 'x') {
                    $formattedNumber .= $n[$x];
                    $x++;
                } else {
                    $formattedNumber .= $format[$i];
                }
            }
        }
        return $formattedNumber;
    }

    public function isExpired($date) {
        $currentDate   = new DateTime(); 
        $formattedDate = new DateTime($date); 

        if ($date !='' && $formattedDate < $currentDate) {
            $isExpired = true;
        } else {
            $isExpired = false;
        }
        return $isExpired;
    }

	public function availabilityUrl($postID = null)
	{
		return $this->fetchMeta($postID, 'propAvailUrl');
	}

	public function address($postID = null, $fullAddress = false)
	{
		if ( $fullAddress ) {
			return $this->fetchMeta($postID, 'propAddress').' '.
					$this->fetchMeta($postID, 'propCity').', '.
					$this->fetchMeta($postID, 'propState').' '.
					$this->fetchMeta($postID, 'propZip');
		}
		return $this->fetchMeta($postID, 'propAddress');
	}

	public function slug($postID = null)
	{
		return sanitize_title($this->fetchMeta($postID, 'propName'));
	}

	public function description($postID = null, $type = null)
	{
		if ( isset($type) ) return $this->fetchMeta($postID, 'prop'.ucwords($type).'Description');
		return $this->fetchMeta($postID, 'propDescription');
	}

	public function city($postID = null)
	{
		return $this->fetchMeta($postID, 'propCity');
	}

	public function state($postID = null)
	{
		return $this->fetchMeta($postID, 'propState');
	}

	public function zip($postID = null)
	{
		return $this->fetchMeta($postID, 'propZip');
	}

	public function website($postID = null)
	{
		return $this->fetchMeta($postID, 'propURL');
	}

	public function email($postID = null)
	{
		return $this->fetchMeta($postID, 'propEmail');
	}

	public function latitude($postID = null)
	{
		return $this->fetchMeta($postID, 'propLatitude');
	}

	public function longitude($postID = null)
	{
		return $this->fetchMeta($postID, 'propLongitude');
	}

	public function amenities($postID = null)
	{
		return $this->fetchJsonMeta($postID, 'amenities');
	}

	public function communityAmenities($postID = null)
	{
		return $this->fetchJsonMeta($postID, 'propCommunityAmenities');
	}

	public function units($postID = null, $args = [])
	{
        $args = array_merge(['post_id' => isset($postID) ? $postID : $this->postID ],$args);
		$units_query=new rentPress_Units_Query($args);
		return $units_query->run_query();
	}

	public function ilsTrackingCodes($postID = null)
	{
		return $this->fetchJsonMeta($postID, 'propTrackingCodes');
	}

	public function timezone($postID = null)
	{
		return $this->fetchMeta($postID, 'propTimeZone');
	}

	public function officeHours($postID = null, $formatted = false)
	{
		$results = $this->fetchJsonMeta($postID, 'propOfficeHours');
		if ( $formatted ) {
			$officeHours = '<ul class="rentpress-office-hours">';
			foreach ($results as $hours) {
				$officeHours .= '<li>Open '.$hours->day.
								' from '.date( 'g:i A', strtotime( $hours->openTime.':00' ) ).' - '.
								date( 'g:i A', strtotime( $hours->closeTime.':00' ) ).'</li>';
			}
			$officeHours .= '</ul>';
			return $officeHours;
		}
		return $results;
	}

	public function rent($postID = null, $type = 'Min')
	{
		return $this->fetchMeta($postID, 'prop'.ucwords($type).'Rent');
	}

	public function beds($postID = null, $type = 'Min')
	{
		return $this->fetchMeta($postID, 'prop'.ucwords($type).'Beds');
	}

	public function sqftg($postID = null, $type = 'Min')
	{
		return $this->fetchMeta($postID, 'prop'.ucwords($type).'SQFT');
	}

    /**
     * Fetch image for property with featured image and placeholder as fallbacks
     * @param  string $postID      [ID of property post]
     * @param  string $placeholder [url of placeholder image if one does not come back]
     * @return string              [Property image URL]
     */
    public function image($postID = null, $placeholder = 'http://placehold.it/400x350?text=Property+Img')
    {
    	$defaultImageOverride = $this->options->getOption('properties_default_featured_image');
    	$placeholder = ! empty($defaultImageOverride) ? $defaultImageOverride : $placeholder;
        $image = $this->fetchFeaturedImageUrl($postID); 
        return $image ? $image : $placeholder;
    }

    /**
     * Fetch the general images imported by the feed
     * @param  [integer] $postID [Post ID of property]
     * @return [mixed]           [Array or stdObject with general property images]
     */
    public function generalImages($postID = null)
    {
    	return $this->fetchJsonMeta($postID, 'propGeneralPhotos');
    }

    /**
     * Fetch staff members of property brought in by the feed import, if any
     * @param  [mixed] $postID [Integer or string of property post ID]
     * @return [mixed]         [stdObject or array of decoded staff member JSON]
     */
    public function staffMembers($postID = null)
    {
    	return $this->fetchJsonMeta($postID, 'propertyStaff');
    }

    /**
     * Return the Specials Message that a property has ( Currently only supported in Encasa feed clients )
     * @param  int    $postID [ID of the post in WP]
     * @return string         [Specials message paragraph in string format]
     */
    public function specialsMessage($postID = null)
    {
		return $this->fetchMeta($postID, 'propSpecialsMessage');
    }

    public function fetchAvailableAtEachBedLevel()
    {
    	global $wpdb;
    	
		$availableAtEachBedLevelFromDB=$wpdb->get_results($wpdb->prepare(
	    	"
	    		SELECT beds, COUNT(*) as count, MIN(rent) as minRent, MAX(rent) as maxRent
	    		FROM $wpdb->rp_units
	    		INNER JOIN $wpdb->postmeta ON $wpdb->rp_units.prop_code = $wpdb->postmeta.meta_value
	    		WHERE 
	    			$wpdb->postmeta.meta_key = 'prop_code' 
	    			AND $wpdb->postmeta.post_id = %d 
	    			AND $wpdb->rp_units.is_available = TRUE 
	    			AND $wpdb->rp_units.rent > 0
 	    		GROUP BY prop_code, beds
	    		ORDER BY beds ASC
	    	",
	    	$this->postID
	    ));

		$availableAtEachBedLevel=[];

		foreach ($availableAtEachBedLevelFromDB as $db_row) {
			$availableAtEachBedLevel[ $db_row->beds ]=$db_row;
		}

		return $availableAtEachBedLevel;
    }

    public function fetchAvailableBeforeAtEachBedLevel( $days_ahead = null, $includeUnavailableFloorPlans = false )
    {
    	global $wpdb;

    	if (! isset($days_ahead) || is_null($days_ahead)) {
    		$days_ahead = $this->options->getOption('use_avail_units_before_this_date');
    	}
    	
		$availableAtEachBedLevelFromDB = $wpdb->get_results($wpdb->prepare(
	    	"
	    		SELECT beds, prop_code, COUNT(*) as count, MIN(rent) as minRent, MAX(rent) as maxRent
	    		FROM $wpdb->rp_units
	    		INNER JOIN $wpdb->postmeta ON $wpdb->rp_units.prop_code = $wpdb->postmeta.meta_value
	    		WHERE 
	    			$wpdb->postmeta.meta_key = 'prop_code' 
	    			AND $wpdb->postmeta.post_id = %d 
	    			AND $wpdb->rp_units.is_available_on <= %s 
	    			AND $wpdb->rp_units.rent > 0
 	    		GROUP BY prop_code, beds
	    		ORDER BY beds ASC
	    	",
	    	$this->postID,
			date('Y-m-d', strtotime('+'. $days_ahead .' days')) 
	    ));

		$availableAtEachBedLevel=[];

		if ( count($availableAtEachBedLevelFromDB) > 0 ) {
			foreach ($availableAtEachBedLevelFromDB as $db_row) {
				$availableAtEachBedLevel[ $db_row->beds ]=$db_row;
			}

			if ( $includeUnavailableFloorPlans ) {
				$propertyCode = end($availableAtEachBedLevelFromDB)->prop_code;

				$unavailFloorPlans = get_posts([
					'fields' => 'ids',
					'post_type' => 'floorplans',
					'floorplans_of_property' => $propertyCode,
					'floorplans_with_available_units' => false
				]);

				$rent = [];
				$floorPlansWithNoAvailability = [];
				foreach ($unavailFloorPlans as $fpPostID) {
					$minRent = get_post_meta($fpPostID, 'fpMinRent', true);
					if ( in_array($minRent, ['-1', '0', 0, -1]) ) continue;
					$floorPlansWithNoAvailability[get_post_meta($fpPostID, 'fpBeds', true)][] = intval($minRent);
				}

				foreach ( $floorPlansWithNoAvailability as $bedCount => $rentRange ) { 
					$availableAtEachBedLevel[$bedCount] = new stdClass();
					$availableAtEachBedLevel[$bedCount]->beds = (string) $bedCount;
					$availableAtEachBedLevel[$bedCount]->prop_code = $propertyCode;
					$availableAtEachBedLevel[$bedCount]->count = count($rentRange);
					$availableAtEachBedLevel[$bedCount]->minRent = min($rentRange);
					$availableAtEachBedLevel[$bedCount]->maxRent = max($rentRange);
				}
			}
		}

		return $availableAtEachBedLevel;
    }

    public function fetchAvailableOnAtEachBedLevel($date = null) {
	   	global $wpdb;

    	if ( ! isset($date) || is_null($date) ) {
    		$date = $this->options->getOption('use_avail_units_before_this_date');
    	}
    	
		$availableAtEachBedLevelFromDB=$wpdb->get_results($wpdb->prepare(
	    	"
	    		SELECT beds, COUNT(*) as count, MIN(rent) as minRent, MAX(rent) as maxRent
	    		FROM $wpdb->rp_units
	    		INNER JOIN $wpdb->postmeta ON $wpdb->rp_units.prop_code = $wpdb->postmeta.meta_value
	    		WHERE 
	    			$wpdb->postmeta.meta_key = 'prop_code' 
	    			AND $wpdb->postmeta.post_id = %d 
	    			AND $wpdb->rp_units.is_available_on <= %s 
	    			AND $wpdb->rp_units.rent > 0
 	    		GROUP BY prop_code, beds
	    		ORDER BY beds ASC
	    	",
	    	$this->postID,
			date('Y-m-d', strtotime($date)) 
	    ));

		$availableAtEachBedLevel=[];

		foreach ($availableAtEachBedLevelFromDB as $db_row) {
			$availableAtEachBedLevel[ $db_row->beds ]=$db_row;
		}

		return $availableAtEachBedLevel;	
    }

}