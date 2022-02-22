<?php

class rentPress_ShortCodes_Phone_PhoneNumber extends rentPress_ShortCodes_Base {

    public function handleShortcode($atts, $content = '') {

    global $wpdb;
    
        $attributes = shortcode_atts( array(
            'property_code' => false,
            'show_phone_icon' => true,
            'number_is_link' => true,
        ), $atts );

        ob_start();

        if ( $attributes['property_code'] ) {

            global $rentPress_Service;

            $propertyCode         = $attributes['property_code'];
            $showPhoneIcon        = $attributes['show_phone_icon'];
            $phoneIsLink         = $attributes['number_is_link'];

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

            if ( $query->have_posts() ) {
                $query->the_post();

                $rentPressOptions     = new rentPress_Options();
                $currentProperty      = $rentPress_Service['properties_meta']->setPostID($query->post->ID);
                $propertyData         = get_post_meta($query->post->ID);

                $noFormatNumber       = $propertyData['propPhoneNumber'][0]; //number from sync
                $trackingNumber       = $propertyData['property_trackingphone'][0]; //user entered number
                $propertyPhoneNumber  = preg_replace('/[^0-9]/', '', $noFormatNumber); //isolate digits out from synced number
                $formattedPhoneNumber = $currentProperty->formatPhone($propertyPhoneNumber, $rentPressOptions->getOption('phone_number_format'), $noFormatNumber); //send synced number digits to formater function by user choice

                // format the phone number
                if ( (preg_match('/[0-9]/', $noFormatNumber) || (preg_match('/[0-9]/', $trackingNumber))) ) : //check if either value is number
                  if ( $trackingNumber) :
                      $displayedPhoneNumber  = $trackingNumber; //if tracking number entered use that
                    else :
                      $displayedPhoneNumber  = $formattedPhoneNumber; //otherwise use formatted number from sync
                    endif;
                    $displayedPhoneNumberUri = 'tel:'.str_replace(' ', '', $displayedPhoneNumber); //transform number from above into a link
                endif;

                if ($displayedPhoneNumber) { //if we got a number we can display it
                    include RENTPRESS_PLUGIN_DIR . '/src/rentPress/ShortCodes/Phone/phone-template.php'; 
                } else {
                echo 'No phone number for this property'; //the property doesn't have a phone number
            }

            } else {
                echo 'No phone number found'; //there was no property or something else went wrong
            }

            wp_reset_postdata();

            return ob_get_clean();
        } else {
            echo "Property code is required";
        }
        
    }

}