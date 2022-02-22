<?php
    
class rentPress_ShortCodes_FloorPlans_Featured extends rentPress_ShortCodes_Base
{
    public function formatPricing($cost)
    {
        $fpRentSummary = $cost === 0 ? '<i>No pricing available</i>' : 'Starting at $'.number_format($cost).'/month';
        $fpRentSummary = $cost === -1 ? 'Contact us for pricing' : $fpRentSummary;
        return $fpRentSummary;
    }

    public function rp_featured_fp_getFutureTime($rentPressOptions)
    {
        $date = $rentPressOptions->getOption('use_avail_units_before_this_date');
        return strtotime( "+$date days", time());
    }

    public function rp_featured_fp_isAvailable($unit, $option, $today, $lookahead)
    {
        $isAvailable = false;

        switch ($option) {
            // Show all with date and/or availability status
            case 'unit_visibility_1':
                $isAvailable = ($unit->Information->isAvailable || $unit->Information->AvailableOn) ? true : false ;
                break;
            // Show all with availability status
            case 'unit_visibility_2':
                $isAvailable = ($unit->Information->isAvailable) ? true : false ;
                break;
            // Show units only available today
            case 'unit_visibility_3':
                $isAvailable = ($unit->availableDateTS && $unit->availableDateTS < $today) ? true : false ;
                break;
            // Show available today and soon
            case 'unit_visibility_4':
                $isAvailable = ($unit->availableDateTS && $unit->availableDateTS < $lookahead) ? true : false ;
                break;
        }

        return $isAvailable;
    }

    public function handleShortcode($atts, $content = '')
    {
        global $wpdb;

        $rentPressOptions       = new rentPress_Options();
        $useFPMarketingName     = $rentPressOptions->getOption('override_single_floorplan_template_title');
        $globalPriceDisable     = $rentPressOptions->getOption('rentPress_disable_pricing');
        $priceDisableMsg        = $rentPressOptions->getOption('rentPress_disable_pricing_message');
        $hideAvailableCount     = $rentPressOptions->getOption('rentPress_hide_floorplan_availability_counter');
        $availability_type      = $rentPressOptions->getOption('override_unit_visibility');
        $today                  = strtotime( "+1 days", time());
        $lookahead              = $this->rp_featured_fp_getFutureTime($rentPressOptions);

        $attributes = shortcode_atts( array(
            'floor_plan_code' => false,
            'link_to_availability' => false,
            'intro_title' => false,
            'intro_content' => false
        ), $atts );

        if ( $attributes['floor_plan_code'] ) {
            $wpQueryArgs = [
                'post_type' => 'floorplans',
                'post_status' => 'publish',
                'floorplan_code' => $attributes['floor_plan_code'],
                'posts_per_page' => 1 // Cause this is for a single featured floor plan
            ];

            $floorPlans = new WP_Query($wpQueryArgs);

            ob_start();

            echo '<div id="rp-featured-floor-plan-intro">';
                if ( $attributes['intro_title'] ) {
                    $title = esc_html($attributes['intro_title']);
                    echo "<h1>{$title}</h1>";
                }
                if ( $attributes['intro_content'] ) {
                    $content = esc_html($attributes['intro_content']);
                    echo "<p>{$content}</p>";
                }
            echo '</div>';

            if ( $floorPlans->have_posts() ) {
                global $rentPress_Service;
                echo '<section id="rp-featured-floor-plan-container" class="rp-short-code fp-list">';
                while ( $floorPlans->have_posts() ) {
                    $floorPlans->the_post();
                    $floorPlanService = $rentPress_Service['floorplans_meta']->setPostID($floorPlans->post->ID);
                    $units = json_decode($floorPlans->post->fpUnits);
                    $available_units = 0;

                    foreach ($units as $unit) {
                        //set up a usable timestamp
                        $unit->availableDateTS = ($unit->Information->AvailableOn) ? strtotime($unit->Information->AvailableOn) : null ;

                        if ($unit->Information->AvailableOn != '1970-01-01' && $this->rp_featured_fp_isAvailable($unit, $availability_type, $today, $lookahead)) {
                            $available_units++;
                        }
                        $apartments++;
                    }

                    //set up the displayed unit count based on client settings
                    $fpAvailUnitCountDisplay = $available_units." Available" ;

                    if ($show_waitlist && $available_units == 0) {
                        $fpAvailUnitCountDisplay = "Join Waitlist" ;
                    }
                    if ($availability_type == 'unit_visibility_5' && $apartments == 1) {
                        $fpAvailUnitCountDisplay = $apartments." Apartment";
                    }
                    if ($availability_type == 'unit_visibility_5' && $apartments > 1) {
                        $fpAvailUnitCountDisplay .= "s";
                    }

                    ?>
                        <div class="rp-featured-floorplan">
                            <?php $imgLink = $attributes['link_to_availability'] !== false ? $floorPlanService->availabilityUrl() : get_the_permalink(); ?>

                            <figure>
                                <a href="<?= $imgLink ?>">
                                    <img src="<?= $floorPlanService->image() ?>">
                                </a>
                            </figure>

                            <div class="rp-featured-floorplan-details">
                                <?php if ($useFPMarketingName == true) : ?>
                                <h3>
                                    <span><?php echo $floorPlans->post->fpName; ?></span>
                                </h3>
                                <?php else : ?>
                                <h3>
                                    <span><?= intval($floorPlans->post->fpBeds) == 0 ? 'Studio' : $floorPlans->post->fpBeds . ' Bed' ?></span> |
                                    <span><?php echo number_format($floorPlans->post->fpBaths) ?>&nbsp;Bath</span>
                                </h3>
                                <?php endif; ?>

                                <h5>
                                    <span><?= number_format($floorPlans->post->fpMinSQFT) ?> Sq. Ft.</span><br>
                                    <?php if ($globalPriceDisable !== 'true') : ?>
                                        <?php if ((number_format($floorPlans->post->fpMinRent)) !== '0') : ?>
                                            <span>Starting at $<?= number_format($floorPlans->post->fpMinRent) ?></span>
                                        <?php else: ?>
                                            <span><?= $priceDisableMsg; ?></span>
                                        <?php endif;?>
                                    <?php endif; ?>
                                </h5>
                                <aside>
                                    <?php if ($useFPMarketingName == true) : ?>
                                    <span><?= intval($floorPlans->post->fpBeds) == 0 ? 'Studio' : $floorPlans->post->fpBeds . ' Bed' ?></span> |
                                    <span><?php echo number_format($floorPlans->post->fpBaths) ?>&nbsp;Bath</span>
                                    <?php endif; ?>
                                    <?php if ($hideAvailableCount != true) : ?>
                                    <div class="units-avail"><?= $fpAvailUnitCountDisplay ?></div>
                                    <?php endif; ?>
                                    <?php if ( $attributes['link_to_availability'] !== false ) : ?>
                                        <a target="_blank" class="button btn" href="<?php echo $floorPlanService->availabilityUrl(); ?>">View Availability</a>
                                    <?php else : ?>
                                        <a class="button btn" href="<?php the_permalink(); ?>">View Available Apartments</a>
                                    <?php endif; ?>
                                </aside>
                            </div>
                        </div> <!-- .featured-floorplan -->
                    <?php
                }
                echo '</section>';
            } else {
                echo 'No floor plans match the provided criteria. Please check your shortcode parameters.';
            }
            wp_reset_postdata();
        } else {
            echo '<div class="no-featured-fp-found">Sorry! We could not fetch the floor plan because the floor_plan_code short code parameter was not provided</div>';
        }

        return ob_get_clean();
    }
}