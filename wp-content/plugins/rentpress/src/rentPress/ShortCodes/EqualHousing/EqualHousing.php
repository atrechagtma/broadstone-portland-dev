<?php

class rentPress_ShortCodes_EqualHousing_EqualHousing extends rentPress_ShortCodes_Base {

    public function handleShortcode($atts, $content = '') {

    // global $wpdb;

        $attributes = shortcode_atts( array(
            'color' => 'white',
            'label' => false
        ), $atts );

        ob_start();

        if ( $attributes ) {

            $houseColor           = $attributes['color'];
            $showHouseLabel       = $attributes['label'];

            include RENTPRESS_PLUGIN_DIR . '/src/rentPress/ShortCodes/EqualHousing/equal-housing-image-template.php';

        } else { ?>
            <img class="rp-equal-housing rp-lazy" width="75px" alt="Equal Housing Opportunity" data-src="<?php echo (RENTPRESS_PLUGIN_ASSETS.'images/EqualHousing/equal-housing-white.png');?>"></img>
            <?php
        }

        return ob_get_clean();

    }

}