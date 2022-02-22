<?php

class rentPress_ShortCodes_Social_PropertySocials extends rentPress_ShortCodes_Base {

    public function handleShortcode($atts, $content = '') {

    global $wpdb;
    
        $attributes = shortcode_atts( array(
            'property_code' => false,
            'show_names'    => false,
        ), $atts );

        ob_start();

        if ( $attributes['property_code'] ) {

            global $rentPress_Service;

            $propertyCode           = $attributes['property_code'];
            $showNetworkNames       = $attributes['show_names'];

            $args = array (
                'post_type'         => array( 'properties' ),
                'post_status'       => array( 'publish' ),
                'numberposts'       => -1,
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

                $propertyFacebook     = $propertyData['prop_facebook'][0];
                $propertyTwitter      = $propertyData['prop_twitter'][0];
                $propertyInstagram    = $propertyData['prop_instagram'][0];

                if ($showNetworkNames == false) :
                    $nameStyles = 'style="display:none" class="rp-visually-hidden"';
                else:
                    $nameStyles = '';
                endif;

                if( (!(empty($propertyFacebook || $propertyTwitter || $propertyInstagram))) ) {

                include RENTPRESS_PLUGIN_DIR . '/src/rentPress/ShortCodes/Social/social-icons-template.php';

            } else {
                echo 'No social links found';
            }

            wp_reset_postdata();

            return ob_get_clean();
        } else {
            echo "Property code is required";
        }
        
    }
}

}