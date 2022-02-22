<?php

class rentPress_ShortCodes_Address_PropertyAddress extends rentPress_ShortCodes_Base {

    public function handleShortcode($atts, $content = '') {

    global $wpdb;
    
        $attributes = shortcode_atts( array(
            'property_code' => false,
            'show_map_link' => true,
            'show_map_icon' => true,
            'show_property_name' => true,
        ), $atts );

        ob_start();

        if ( $attributes['property_code'] ) {

            global $rentPress_Service;

            $propertyCode         = $attributes['property_code'];
            $showMapLink          = $attributes['show_map_link'];
            $showMapIcon          = $attributes['show_map_icon'];
            $showPropName         = $attributes['show_property_name'];

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

                $propertyName         = get_the_title($query->post->ID);
                $googleApiToken       = $rentPressOptions->getOption('rentPress_google_api_token');
                $googleMapAddress     = $currentProperty->name().', '.$currentProperty->address().','.$currentProperty->city().','.$currentProperty->state().','.$currentProperty->zip();
                $googleMapAddress     = str_replace(' ', '+', $googleMapAddress);

                include RENTPRESS_PLUGIN_DIR . '/src/rentPress/ShortCodes/Address/address-template.php';

            } else {
                echo 'No address found';
            }

            wp_reset_postdata();

            return ob_get_clean();
        } else {
            echo "Property code is required";
        }
        
    }

}