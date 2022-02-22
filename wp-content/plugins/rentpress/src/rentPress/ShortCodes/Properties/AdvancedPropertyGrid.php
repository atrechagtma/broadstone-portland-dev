<?php

class rentPress_ShortCodes_Properties_AdvancedPropertyGrid extends rentPress_ShortCodes_Base {

    public function handleShortcode($atts, $content = '') {

        ob_start();

        global $wpdb;
        global $rentPress_Service;
        $rentPressOptions = new rentPress_Options();

        $attributes = shortcode_atts( array(
            'hide_filters'   => true,
            'min_beds'       => 0,
            'max_beds'       => 100,
            'min_rent'       => 0,
            'max_rent'       => 1000000000,
            'has_special'    => false,
            'load_limit'     => 12,
            'city'           => false,
            'pets'           => false,
            'community_type' => false
        ), $atts );

        require_once RENTPRESS_PLUGIN_DIR."/templates/Properties/advanced-archive-properties-data.php";

        if ( !is_admin() ) {
            require_once RENTPRESS_PLUGIN_DIR."templates/Properties/advanced-archive-properties-search-section.php";
        }

        return ob_get_clean();
        
    }

}