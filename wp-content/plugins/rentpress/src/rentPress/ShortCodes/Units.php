<?php

class rentPress_ShortCodes_Units extends rentPress_ShortCodes_Base 
{
    public function handleShortcode($atts, $content = '') 
    {
        global $wpdb;

        $attributes = shortcode_atts( array(
            'property_code' => false,
          	'fp_id' => false
        ), $atts );

        ob_start();

        if ( $attributes['property_code'] || $attributes['fp_id'] ) {

            global $rentPress_Service;

            $propertyCode         = $attributes['property_code'];
            $fpID                 = $attributes['fp_id'];
            $rentPressOptions     = new rentPress_Options();

            $args = array (
                'post_type'         => array( 'properties' ),
                'post_status'       => array( 'publish' ),
                'meta_query'        => array(
                    array(
                        'key'       => 'prop_code',
                        'value'     =>  $propertyCode,
                    ),
                ),
            );
            $query = new WP_Query( $args );

            if($query->have_posts()) :
                $disableLeaseTermPricing = $rentPressOptions->getOption('disbale_all_units_lt_pricing');
                $waitlist_url            = ($rentPressOptions->getOption('show_waitlist_override_url') == '/waitlist') 
                    ? site_url().'/waitlist/'
                    : $rentPressOptions->getOption('show_waitlist_override_url') ;
                $show_waitlist_ctas      = $rentPressOptions->getOption('show_waitlist_ctas');
                $override_request_link   = $rentPressOptions->getOption('override_request_link');
                $requestURL              = $rentPressOptions->getOption('single_floorplan_request_more_info_url');
                $propertyPostLink        = (get_permalink($query->posts[0]->ID));
                $propertyTitle           = ($query->posts[0]->post_title);
                $globalApplyOverride     = $rentPressOptions->getOption('rentPress_override_apply_url');
                $propertyApplyOverrrideURL = (get_post_meta($query->posts[0]->ID)['prop_apply']);
                $propertyApplyLinkIsOverridden = get_post_meta($query->posts[0]->ID)['prop_apply_unit'];
                $globalPriceDisable      = $rentPressOptions->getOption('rentPress_disable_pricing');
                $disabledPricing         = get_post_meta($query->posts[0]->ID)['propDisablePricing'];
            endif;

            // Units Query
            $queryUnitsArgs=[];

            if (isset($propertyCode) && $propertyCode != false) {
            	$queryUnitsArgs['prop_code']=$propertyCode;
            }

            if ( isset($fpID) && $fpID != false ) {
            	$queryUnitsArgs['fpID']=$fpID;
            }

            $queryUnits=new rentPress_Units_Query($queryUnitsArgs);

            $queriedUnits=$queryUnits->run_query();

            // Gathering Rent Terms Available
            $distinctTermRents=[];

            $unitsTermRents=array_filter(
            	array_map(
            		function($unit) {
            			return $unit->Rent->TermRent;
            		}, 
            		$queriedUnits
            	), 
            	function($termRents) {
            		return is_array($termRents) || is_array($termRents);
        	    }
        	);

        	foreach ($unitsTermRents as $unitTermRents) {
    			foreach ($unitTermRents as $termRent) {
    				$distinctTermRents[]=$termRent->Term;    			
    			}
        	}

    	   $distinctTermRents=array_unique($distinctTermRents);

        	// Template

            include __DIR__.'/Units_Template.php';

            wp_reset_postdata();

            return ob_get_clean();

        } else {
            echo "Property code is required";
        }
    }
}