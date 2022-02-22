<?php

class rentPress_ShortCodes_Hours_OfficeHours extends rentPress_ShortCodes_Base {

    public function handleShortcode($atts, $content = '') {

    global $wpdb;
    
        $attributes = shortcode_atts( array(
            'property_code' => false,
            // 'show_open' => true
        ), $atts );

        ob_start();

        if ( $attributes['property_code'] ) {

            global $rentPress_Service;

            $propertyCode           = $attributes['property_code'];

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

                $currentProperty  = $rentPress_Service['properties_meta']->setPostID($query->post->ID);
                $propertyData         = get_post_meta($query->post->ID);
                $propertyImportSource = $propertyData['propSource'][0];

                if ($propertyImportSource == 'entrata' || $propertyImportSource == 'realpage') :
                     echo "No office hours found";                    
                else:
                    if (!function_exists('dayByNumber'))   {
                        function dayByNumber($dayNum = 1) {
                            $arrWeek = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
                            return $arrWeek[$dayNum] ?: $arrWeek[0];
                        }
                    }
                    $propertyHours        = $propertyData['propOfficeHours'][0];
                    $officeHours          = json_decode($propertyHours);
                    // $showOpen             = $attributes['show_open'];
                    include RENTPRESS_PLUGIN_DIR . '/src/rentPress/ShortCodes/Hours/hours-template.php';
                endif;

            } else {
                echo 'No office hours found';
            }

            wp_reset_postdata();

            return ob_get_clean();
        } else {
            echo "Property code is required";
        }
        
    }

}