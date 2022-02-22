<?php

class rentPress_ShortCodes_Properties_NearbyProperties_NearbyProperties extends rentPress_ShortCodes_Base {

    public function handleShortcode($atts, $content = '') {

    global $wpdb;
    
        $attributes = shortcode_atts( array(
            'property_code' => false,
            'city' => false,
            'min_count' => '1'
        ), $atts );

        ob_start();

            if ( $attributes['property_code'] ) {

                global $rentPress_Service;

                $propertyCode         = $attributes['property_code'];
                $minPropCount         = $attributes['min_count'];

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

                    $rentPressOptions    = new rentPress_Options();
                    $templateAccentColor = $rentPressOptions->getOption('templates_accent_color');
                    $currentProperty     = $rentPress_Service['properties_meta']->setPostID($query->post->ID);
                    $propertyData        = get_post_meta($query->post->ID);
                    $cityID              = wp_get_post_terms($query->post->ID, 'prop_city' )[0]->term_id;

                    // Find three other properties in the same city
                    $city_args = array(
                      'post_type' => 'properties',
                      'post_status' => 'publish',
                      'orderby' => 'rand',
                      'posts_per_page' => 3,
                      'post__not_in' => array($query->post->ID),
                      'tax_query' => array(
                        'relation' => 'AND',
                        array(
                          'taxonomy' => 'prop_city',
                          'field' => 'term_id',
                          'terms' => $cityID,
                        ),
                      ),
                    );
                    $prop_qry = new WP_Query($city_args);
                    $numNearProps = $prop_qry->found_posts;

                    if ($minPropCount <= $numNearProps) {
                        include RENTPRESS_PLUGIN_DIR . '/src/rentPress/ShortCodes/Properties/NearbyProperties/nearby-properties-template.php';
                    } else {
                        echo 'Did not find at least '.$minPropCount.'  nearby properties.';
                    }

                }

                } else {
                    echo 'Property code not provided';
                }

        wp_reset_postdata();

        return ob_get_clean();
        
    }

}