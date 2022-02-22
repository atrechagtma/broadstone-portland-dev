<?php
/**
 * The template for a single floor plan product page.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package 30_Lines_Properties
 */

include RENTPRESS_PLUGIN_DIR . 'templates/FloorPlans/single-floorplan-data.php';

get_header();
while ( have_posts() ) : the_post(); ?>
<header class="entry-header" style="background-image: url(https://bsp-staging.local/wp-content/uploads/2021/11/Broadstone-Portland-detail25.jpg);">
    <h1 class="entry-title">Floor Plans</h1>
</header>

<section class="rentpress-core-container">
    <?php
    if($floorPlanSpecial && $floorPlanSpecialExpiration !== true) { 
        if ($floorPlanSpecialLink) {
        $specialSection = '<div class="rp-single-special"><a href="'.$floorPlanSpecialLink.'" target="_blank"><span style="font-size: 1.25rem;">&#x2605</span> Special - '.$floorPlanSpecial.'</a></div>';
        } else {
            $specialSection = '<div class="rp-single-special"><span style="font-size: 1.25rem;">&#x2605</span> Special - '.$floorPlanSpecial .'</div>';
        }
        echo $specialSection;
    } ?>
    <section id="post-<?php the_ID(); ?>" <?php post_class('floorplans-wrapper'); ?>>
        <header id="rp-single-fp-header-details">
            <div class="rp-row">
                <section class="rp-col-6 rp-single-fp-gallery">
                    <figure id="rp-single-fp-image" class="rp-single-fp-image">
                        <a href="<?php echo $image; ?>" data-mfp-src="<?php echo $fpImage; ?>" id="rp-single-fp-open-image-popup">
                            <img src="<?php echo $fpImage; ?>" alt="<?php echo $post->post_title; ?> - <?php echo esc_attr($fpProperty->post_title); ?>">
                        </a>
                    </figure>

                    <?php if (isset($fpMatterport) && $fpMatterport != "") : ?>
                        <a class="rp-3d-tour-link" href="#view-3d-tour"><div class='rp-3d-link-icon'><i class="far fa-play-circle"></i></div>Explore Floor Plan</a>
                    <?php endif; ?>

                </section>
                <aside class="rp-single-fp-content rp-col-6">
                    <div class="rp-single-fp-sub-details">

                    <?php if ($useFPMarketingName == true) : ?>
                        <h2><?php echo $post->post_title; ?></h2>
                        <span id="rp-fp-name"><?php echo rp_single_fp_getBedBathString($bedrooms, rp_single_fp_getBathroomString($bathrooms)); ?></span><br />
                    <?php else : ?>
                        <h2>
                            <span id="rp-fp-name"><?php echo rp_single_fp_getBedBathString($bedrooms, rp_single_fp_getBathroomString($bathrooms)); ?></span>
                        </h2>
                    <?php endif; ?>

                        <span><?php echo number_format($sqft); ?> Sq. Ft.</span> |
                        <span class="rp-dp-here"><?php echo $currentFloorPlan->displayRentForTemplate(); ?></span>
                    </div>
                    <form id="rp-single-fp-all-the-unit-things" class="rp-single-fp-all-the-unit-things">

                    <?php if (count($units) > 0 && count($distinctTermRents) > 0 && $disableLeaseTermPricing !== 'true' && $globalPriceDisable !== 'true') : ?>
                        <div class="rp-form-legend">
                            <h4>Choose a lease term</h4>
                            <select name="rentTerm" id="">
                            <option value="">Choose...</option>

                            <?php foreach ($distinctTermRents as $termRent) : ?>
                            <option value="<?php echo $termRent; ?>"><?php echo $termRent; ?> Months</option>
                            <?php endforeach; ?>

                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="rp-form-legend">
                    <?php if (!empty($units)) : ?>
                        <h4>Choose an Apartment</h4>
                        <section id="rpUnitCards" class="rp-unit-cards">

                        <?php
                            foreach ($units as $unit) :
                                $Unit = $rentPress_Service['unit_meta']->fromUnit($unit);
                                $rent = $Unit->rent();
                                $unitName = $unit->Information->Name;
                                $unitCode = $unit->Identification->UnitCode;
                                $name = $unitName ? $unitName : $unitCode;
                        ?>

                            <div class="rp-unit-card" data-unit-code="<?php echo $unitCode; ?>" >
                                <input type="radio" name="unit" class="rp-radio-unit-number" id="<?php echo $unitCode; ?>" data-unit-avail-link="<?php echo $unit->Information->AvailabilityURL; ?>">
                                <label class="unit-selector" role="button" for="<?php echo $unitCode; ?>">
                                    <h5 class="rp-card-title"><?php echo $name; ?></h5>
                                    <p class="rp-card-rent rp-dp-here">
                                    
                                    <?php if ($globalPriceDisable !== 'true') { ?>

                                        $<span data-is-price data-defualt-rent="<?php echo $rent; ?>" data-rent-terms="<?php echo esc_attr(json_encode((array)$unit->Rent->TermRent)); ?>">
                                        <?php echo $rent; ?>
                                        </span>/mo

                                    <?php } else { echo $priceDisableMsg; } ?>
                                        
                                    </p>
                                    <p class="rp-card-sqft"><?php echo $unit->SquareFeet->Max; ?> sq. ft.</p>
                                    <p class="rp-card-avail">

                                    <?php if ($unit->Information->isAvailable) {
                                        echo "Available Now";
                                    } else {
                                        echo "Available ". $unit->Information->AvailableOn;
                                    } ?>

                                    </p>
                                </label>
                            </div>

                        <?php endforeach; ?>

                        </section>
                        <!-- rp-unit-cards -->

                        <?php else : ?>
                            <div class='rp-single-no-units-wrapper'>
                                <h4 class='rp-single-no-units-headline'>No Apartments Available</h4>
                                <p class='rp-single-no-units-text'>This is one of our most popular layouts. No apartments are currently available. You can browse our <a class='rp-primary-accent' href='/floorplans/'>other options</a>.</p>
                                <br>
                            </div>  
                        <?php endif; ?>

                    </div>
                    <!-- CTAs -->
                    <?php if (count($units) == 0 && $show_waitlist_ctas == "true") : ?>

                        <!-- If no available units, show Waitlist, Tour, and Floorplans buttons -->
                        <footer id="rp-single-fp-form-buttons" class="<?php if (isset($schedule_a_tour_url) && ($showTourCTAs)) { echo 'rp-3-btn'; }; ?>">
                            <a id="rp-fp-waitlist" href="<?= $waitlist_url ?>" class="rp-button-alt rp-waitlist-button">Join Our Waitlist</a>

                        <?php if (isset($schedule_a_tour_url) && ($showTourCTAs)) : ?>

                            <a id="rp-fp-schedule-tour" href="<?php echo esc_url($schedule_a_tour_url); ?>" class="rp-button-alt rp-tour-button">Schedule Tour</a>

                        <?php endif; ?>

                            <a href="/floorplans/" class="rp-button-alt rp-floorplans-back-button>">Browse Floorplans</a>
                        </footer>
                    
                    <!-- If available units, show optional Tour, Info, and Apply buttons -->
                    <?php else : ?>

                        <footer id="rp-single-fp-form-buttons" class="<?php if (isset($schedule_a_tour_url) && ($showTourCTAs)) { echo 'rp-3-btn'; }; ?>">

                        <?php if (isset($schedule_a_tour_url) && ($showTourCTAs)) : ?>
                            <a id="rp-fp-schedule-tour" href="<?php echo esc_url($schedule_a_tour_url); ?>" class="rp-button-alt">Schedule Tour</a>
                        <?php endif; 

                        if (isset($requestMoreInfoUrl)) : ?>
                            <a id="rp-fp-request-info" href="<?php echo esc_url($requestMoreInfoUrl); ?>" class="rp-button-alt">Request Info</a>
                        <?php endif; ?>

                        <?php if ($globalApplyOverride !== '') : ?>
                            <a href="<?php echo $availabilityUrl; ?>" target="<?php echo $rentPressOptions->getOption('override_apply_links_targets'); ?>" class="rp-button-alt">Apply Now</a>
                        <?php else : ?>
                            <a id="rp-fp-apply-now" href="<?php echo $availabilityUrl; ?>" target="<?php echo $rentPressOptions->getOption('override_apply_links_targets'); ?>" class="rp-button-alt more-info-button">Apply Now</a>
                        <?php endif; ?>
                        </footer>

                    <?php endif; ?>
                    <!-- CTAs -->
                    
                    </form>
                    <div id="rp-single-fp-share-section">
                        <h4>Share This Floor Plan</h4>
                        <nav class="rp-single-fp-share-nav">
                            <a id="rp-twitter" href="<?php echo esc_url('https://twitter.com/share?url='. get_permalink().'&amp;text='. $post->post_title .'&amp;hashtags=apartments'); ?>" target="_blank"><span class="icomoon rp-icon-twitter"></span></a>

                            <a id="rp-facebook" href="<?php echo esc_url('http://www.facebook.com/sharer.php?u='. get_permalink()); ?>" target="_blank"><span class="icomoon rp-icon-facebook"></span></a>
                            
                            <a id="rp-email" href="mailto:?subject=Check Out This Apartment&body=I liked this apartment at <?php echo esc_attr($fpProperty->post_title); ?>. What do you think? <?php echo esc_attr(get_the_permalink()); ?>">
                                <span class="icomoon rp-icon-envelope"></span></a>
                        </nav>
                    </div>
                </aside>
            </div>
        </header>

        <!-- Tour Section -->
        <?php if ($fpMatterport != '') : ?>

        <section id="view-3d-tour">
            <h3 class="text-center rp-padded-y rp-tour-header">Take A Look Around</h3>
            <iframe id="rp-fp-matterport" class="rp-tour-frame rp-lazy" data-src="<?php echo $fpMatterport; ?>" width="100%" height="600" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
            <div class="rp-2-btn">
                <footer id="rp-single-fp-form-buttons" style="display: flex; justify-content: center;">

                <?php if (isset($schedule_a_tour_url) && ($showTourCTAs == 'true')) : ?>
                    <a id="rp-fp-schedule-tour-2" href="<?php echo esc_url($schedule_a_tour_url); ?>" class="rp-button-alt" style="min-width: 12em;">Schedule A Tour</a>&nbsp;
                <?php endif; ?>

                <?php if (isset($requestMoreInfoUrl)) : ?>
                    <a id="rp-fp-request-info-2" href="<?php echo esc_url($requestMoreInfoUrl); ?>" class="rp-button-alt">Request Info</a>&nbsp;<?php endif; ?> 

                    <a id="rp-fp-apply-now-2" href="<?php echo $availabilityUrl; ?>" target="<?php echo $rentPressOptions->getOption('override_apply_links_targets'); ?>" id="rp-fp-app-link" style="min-width: 12em;" class="rp-button-alt button-alt more-info-button">Apply Now</a>
                </footer>
                <br>
            </div>
        </section>

        <?php endif; 

        if ( ($propDescription) || ($fpDescription) || (!empty($amenities_filtered)) ) { 
            if ( ($propDescription || $fpDescription) && (empty($amenities_filtered)) ) {
                $class1 = "is-solo";
            } elseif ( (!empty($amenities_filtered)) && !($propDescription) && !($fpDescription) ) {
                $class2 = "is-centered";
            } else {
                $class1 = "";
                $class2 = "";
            } ?>

        <section id="rp-single-fp-features" class="rp-padded-y rp-floorplan-about">
            <div class="rp-row">

            <?php if( $fpDescription ) { ?>
                <aside class="rp-col-6 rp-single-text <?php echo $class1; ?>">
                    <p><strong>About This Floor Plan at <?php echo esc_attr($fpProperty->post_title); ?></strong></p>
                    <p class="meta-desc-text"><?php echo $fpDescription; ?></p>
                </aside>
            <?php  } elseif( $propDescription ) { ?>
                <aside class="rp-col-6 rp-single-text <?php echo $class1; ?>">
                    <p><strong>About Our Community - <?php echo esc_attr($fpProperty->post_title); ?></strong></p>
                    <p class="meta-desc-text"><?php echo $propDescription; ?></p>
                </aside>
            <?php } if (!empty($amenities_filtered)) { ?>

                <section class="rp-col-6 rp-single-feat-list <?php echo $class2; ?>">
                    <p><strong>Amenities & Features</strong></p>
                    <ul>

                    <?php
                        foreach ($amenities_filtered as $amenity) {
                            echo "<li>". $amenity->name ."</li>";
                        } ?>

                    </ul>
                </section>

            <?php } ?>

            </div>
        </section>

        <?php }; ?>

        <section class="rp-row rp-similar-floorplans">
            <h3 class="text-center rp-padded-y">Similar Floorplans</h3>
            <div class="rp-single-list rp-col-12">

            <?php foreach ( $similarFloorPlans as $floorPlan ) :
                $similarFloorPlan = $rentPress_Service['floorplans_meta']->setPostID($floorPlan->ID);
                $fp = get_post_meta($floorPlan->ID);
                $fpName = sanitize_text_field($similarFloorPlan->name());
                $bedrooms = esc_html__($similarFloorPlan->beds(), RENTPRESS_LANG_KEY);
                $bathrooms = esc_html__($similarFloorPlan->baths(), RENTPRESS_LANG_KEY);
                $sqft = esc_html__($similarFloorPlan->sqftg(), RENTPRESS_LANG_KEY);
                $fpRent = $similarFloorPlan->rent();
                $similar_fp_units = $similarFloorPlan->units();
                $available_units = 0;
                $apartments = 0;
                $specialText = $fp['fp_special_text'][0];
                $specialExpiration = $fp['fp_special_expiration'][0];
                $isExpired = isExpired($specialExpiration);

                if ($specialText && $specialText !== "" && $isExpired !== true){
                    $hasSpecial = 'true';
                } else {
                    $hasSpecial = 'false';
                }

                foreach ($similar_fp_units as $unit) {
                    //set up a usable timestamp
                    $unit->availableDateTS = ($unit->Information->AvailableOn) ? strtotime($unit->Information->AvailableOn) : null ;
                    if ($availability_type != 'unit_visibility_5' && $unit->Information->AvailableOn != '1970-01-01' && rp_single_fp_isAvailable($unit, $availability_type, $today, $lookahead)) {
                        $available_units++;
                    }
                    $apartments++;
                }

                if ($rentPressOptions->getOption('hide_floorplans_without_availability') == true && $available_units == 0) {
                    continue;
                } ?>

            <div class="rp-single-list-floorplan avail-now">
                <a href="<?php echo get_permalink($floorPlan->ID); ?>">
                    <figure>
                        <img class="rp-lazy" src="" data-src="<?php echo $similarFloorPlan->image(); ?>" alt="<?php echo $post->post_title; ?> - <?php echo esc_attr($fpProperty->post_title); ?>">
                    <?php if ($hasSpecial == 'true') { ?>
                        <div class='rp-fp-has-special'><h6><span>&#x2605</span> Special</h6></div>
                    <?php } ?>
                        
                    </figure>
                    <footer class="rp-fp-details">
                    
                    <?php if ($rentPressOptions->getOption('override_single_floorplan_template_title') === 'true') : ?>
                        <h5><?php echo $floorPlan->post_title; ?></h5>
                    <?php else : ?>
                        <h5><span><?php echo rp_single_fp_getBedBathString($bedrooms, rp_single_fp_getBathroomString($bathrooms)); ?></span></h5>
                    <?php endif; ?>

                    <span><?php echo number_format($sqft); ?> Sq. Ft.</span> | <span class=""><?php echo $similarFloorPlan->displayRentForTemplate(); ?></span>

                    <?php if ($show_waitlist_ctas == "true" && $available_units == 0 && $availability_type != 'unit_visibility_5') : ?>
                        <div class="rp-num-avail rp-primary-accent">Join Waitlist</span></div>
                    <?php elseif ($availability_type != 'unit_visibility_5') : ?>
                        <div class="rp-num-avail rp-primary-accent"><?php echo $available_units; ?> Available</div>
                    <?php else : ?>
                        <div class="rp-num-avail rp-primary-accent"><?php echo $apartments; ?> Apartments</span></div>
                    <?php endif; ?>

                    </footer>
                </a>
            </div> 
            <?php endforeach; ?>

            </div>
        </section>

        <div id="floorplans-back-btn" class="rp-footer-back-btn">
            <a class="rp-button-alt" href="<?php echo site_url(); ?>/floorplans">Back to Floor Plans</a>
        </div>
        <br>
    <!-- floorplans-wrapper -->
    </section>
<!-- rentpress-core-container -->
</section>

<?php if ($rentPressOptions->getOption('single_floorplan_content_position') == 'single_floorplan_content_bottom') { ?>
    <div <?php post_class('rp-default-wp-content'); ?>>
        <?php the_content(); ?>
    </div>
<?php } 

include RENTPRESS_PLUGIN_DIR . 'misc/template-schema/single-floorplan-schema.php';

get_footer();
endwhile;