<?php 

// *** This is the RentPress Property Template *** //

include RENTPRESS_PLUGIN_DIR . 'templates/Properties/single-property-data.php';

get_header();

?>

<section class="rentpress-core-container">

    <?php if($propertySpecial && $isExpired != true) { 
        if ($propertySpecialLink) {
        $specialSection = '<div class="rp-single-special"><a href="'.$propertySpecialLink.'" target="_blank"><span style="font-size: 1.25rem;">&#x2605</span> Special - '.$propertySpecial.'</a></div>';
        } else {
            $specialSection = '<div class="rp-single-special"><span style="font-size: 1.25rem;">&#x2605</span> Special - '.$propertySpecial .'</div>';
        }
        echo $specialSection;
    } ?>

    <header class="rp-single-prop-header clearfix">

        <section class="-rp-col-8 rp-single-prop-feat-img">
            <img src="<?php echo $currentProperty->image(); ?>" id="rp-single-prop-heading-image" alt="<?php echo get_the_title(); ?>">
        </section>

        <aside class="-rp-col-4 rp-single-prop-details">

            <div class="rp-single-prop-details-wrapper">

                <?php if ($propertyLogoImg) { ?>
                    <figure class="rp-single-prop-logo">
                            <img class="rp-lazy" data-src="<?php echo $propertyLogoImg ?>" alt="<?php echo get_the_title(); ?>" src="">
                    </figure>
                <?php } ?>
        
                <h1 id="rp-prop-title"><?php echo get_the_title(); ?></h1>
                    <?php if ($propertyTagline != '') { ?>
                        <h3 class="rp-property-tagline"><?php echo $propertyTagline; ?></h3>
                    <?php } ?>

                <div id="rp-single-prop-full-address">
                    <?php echo do_shortcode('[property_address property_code="'. $post->prop_code .'" show_property_name="false"]'); ?>
                </div>

            <p class="rp-single-prop-details-beds">
            <span>
                <?php echo $bedLabel; ?>
            </span>

            <span class=property-pricing>
                <?php echo $priceLabel; ?>
            </span>
            <br class="property-is-pet-friendly"/>
                <?php if ( has_term( '', 'prop_pet_restrictions') ) { 
                    echo '<span> Pet Friendly </span>';
                } ?>
            <br />
            </p>

            <div class="rp-single-prop-details-links">
                    
                    <p class="rp-single-prop-info-links"><?php

                        if($noFormatNumber || $trackingNumber) {
                            echo do_shortcode('[property_phone property_code="'. $post->prop_code .'"]');
                        } ?>
                        
                        <a id="single-prop-email" href="mailto:<?php echo $currentProperty->email(); ?>?subject=Request for Info About <?php echo get_the_title(); ?>"><span class="rp-icon-envelope"></span> Email Us</a>

                        <?php if ($propertyURL =='' || $propertyURL == get_site_url() || $propertyURL =='#' ) {
                            } elseif (!(strpos($propertyURL,'http') !== false)) { ?>
                                <a id="single-prop-website" href="https://<?php echo $propertyURL ?>" target="_blank" rel="noreferrer noopener"><span class="rp-icon-circle-right"></span>  Website</a> <?
                            } else { ?>
                                <a id="single-prop-website" href="<?php echo $propertyURL ?>" target="_blank" rel="noreferrer noopener"><span class="rp-icon-circle-right"></span>  Website</a>
                        <?php } ?>


                <?php if ($showTourButton == 'true') : ?>
                    <div>
                        <a href="<?php echo $tourLink; ?>" class="rp-button rp-button-alt" style="margin:4px">Schedule Tour</a>
                    </div><?php 
                endif; ?>


                <div class="rp-prop-cta-buttons">
                    <?php 
                        echo '<a id="single-prop-contact" href="'.$contactLeasingLink.'"'.$infoButtonClass.'" style="margin:4px"> Contact Leasing </a>'; ?> 
                    <?php if ($globalApplyOverride != '') {
                        echo '<a id="single-prop-apply" href="'.$globalApplyOverride.'"target="_blank" class="rp-button rp-button-alt" style="margin:4px">Apply Now</a>';
                    } elseif ($propertyApplyLink != '') {
                        echo '<a id="single-prop-apply" href="'.$propertyApplyLink.'"target="_blank" class="rp-button rp-button-alt" style="margin:4px">Apply Now</a>';
                    } else {
                        echo '<a id="single-prop-contact" href="'.$contactLeasingLink.'" class="rp-button rp-button-alt" style="margin:4px">Apply Now</a>';
                    } ?>
                </div>

                <p class="rp-share-links">
                    <a id="single-prop-share" href="mailto:?subject=Check Out These Apartments at <?php echo get_the_title(); ?> | <?php echo $currentProperty->city(); ?>, <?php echo $currentProperty->state(); ?>&body=I liked these apartments at <?php echo get_the_title(); ?>. What do you think? Take a look here: <?php echo esc_attr(get_the_permalink()); ?>">Forward to Friend »</a>
                    <?php if ($propertyResidentLink != '') { ?>
                        <a href="<?php echo $propertyResidentLink; ?>">Residents »</span></a>
                    <?php } ?>
                    <br />
                </p>                

                <?php if ( (!($propertyFacebook =='' && $propertyTwitter =='' && $propertyInstagram =='')) ) { ?>
                <p class="rp-single-prop-share-social">
                    <?php echo do_shortcode('[property_socials property_code="'. $post->prop_code .'"]');  ?>
                </p>
                <?php } ?>

            </div> <!-- rp-single-prop-prop-details-wrapper -->

        </aside>

    </header>

    <!-- Sub Nav Section -->
    <nav class="rp-single-prop-on-page-nav" id="rpSinglePropOnPageNav"><?php
        if ($amenities_filtered) { ?>
        <a href="#amenities">Amenities</a><?php
           } else { ?>
        <a href="#amenities">About</a><?php
            }
        if ($propertyPetPolicy !='') { ?>
        <a href="#pet-policy">Pets</a>
        <?php } 
        if ( ($hideHours != 'true') && ($propertyOfficeHours !='null') ) { ?>
        <a href="#office-hours">Office Hours</a>
        <?php } ?>
        <a href="#floorplans">Floor Plans</a>
        <?php if ($propertyGallery != '') { ?>
            <a href="#gallery">Gallery</a>
        <?php } ?>
        <a href="#neighborhood">Neighborhood</a>
        <?php if ($propertyReviews != '') { ?>
            <a href="#reviews">Reviews</a>
        <?php } ?>
    </nav>

    <!-- About - Amenities - Features Section -->

    <main id="post-<?php the_ID(); ?>" <?php post_class('rp-single-prop-main-content'); ?>>

        <section id="amenities" class="rp-single-prop-main-prop-details" style="margin-bottom: 20px;">
            <?php if ($propertyDescription != 'No Description') : ?>
            <div id="rp-single-prop-content" class="rp-col-6 rp-single-text rp-prop-description">
                <h3>About Our Community</h3>
                <span><p><?php echo $propertyDescription; ?></p></span>
            </div>
            <?php endif; ?>

            <?php if ($amenities_filtered) : ?>
            <div class="rp-col-6 rp-single-text rp-prop-amenities-list">
                <h3>Amenities & Features</h3>
                <ul class="rp-list"><?php
                    $output = array();
                    foreach ($amenities_filtered as $amenity) {
                        echo '<li>'.$amenity->name.'</li>';
                    } ?>
                </ul>
            </div><?php 
            endif; ?>

            <?php if ($propertyDescription != 'No Description') : ?>
            <div style="clear:both; width: 100%;"></div>
            <?php endif;
            
            if ($propertyPetPolicy !='') : ?>
                <div class="rp-col-6 rp-single-text " id="pet-policy">
                    <h3><?php echo get_the_title(); ?> Pet Policy</h3>
                    <p><?php echo $propertyPetPolicy; ?><br /></p>
                </div><?php 
            endif;

            if ( ($hideHours != 'true') && ($propertyOfficeHours !='null') ) : ?>
                <div class="rp-col-6 rp-single-text rp-prop-office-hours" id="office-hours">
                    <h3>Office Hours</h3>
                    <?php echo do_shortcode('[property_hours property_code="'. $post->prop_code .'"]'); ?>
                </div><?php
            endif; ?>
        </section>

        <section id="floorplans">
            <div id="rpFloorplansContainer" class="rp-single-prop-floorplans-container"><br />
                <aside class="rp-floorplans-container-subhead"><center><?php echo $fpFeaturedText; ?></center></aside>
                <h3 class="rp-floorplans-container-title"><center><?php echo get_the_title(); ?> Floor Plans</center></h3>
                <div style="margin-top: -100px">
                    <?php echo do_shortcode('[floorplan_grid property_code='. $post->prop_code .'][/floorplan_grid]'); ?>
                </div>
        </section>

        <!-- Gallery Section -->
        <?php if ($propertyGallery != "") { ?>
            <section id="gallery">
            <div id="rp-gallery-container" class="rp-single-prop-gallery-container">
                <aside class="rp-gallery-subhead"><center>Take A Look Around</center></aside>
                <h3 class="rp-gallery-title"><center><?php echo get_the_title(); ?> Photos</center></h3>
                <div class="property-gallery"><?php echo do_shortcode($propertyGallery, $ignore_html = true); ?></div>
            </div>
            </section>
        <?php } ?>
        
        <!-- Neighborhood Section -->
        <section id="neighborhood">
            <div id="rpMapContainer" class="rp-single-prop-map-container" style="background: #F9F9F9;">
            <?php if ($googleApiToken != '') { ?>
                <div class="rp-map-embed">
                    <iframe class="rp-lazy" data-src="https://www.google.com/maps/embed/v1/place?key=<?php echo $googleApiToken .'&q=' . $googleMapAddress ?>" src="" width="100%" height="580px" frameborder="0" style="border:0; margin-bottom: -10px;" allowfullscreen></iframe>
                </div>
            <?php } ?>
            <aside class="rp-map-text-container" style="max-height:550px">
                <div class="rp-map-text">
                    <aside><?php echo get_the_title(); ?></aside>
                    <h3><?php echo $cityObj->name . ', ' . $propertyState; ?></h3><br />
                    <span style="max-height:580px"><p><?php echo $cityDescription; ?></p></span>
                    <a id="neighborhood-learn-more" href='<?php echo get_term_link( $cityObj->term_id, 'prop_city' ) ?>' class='rp-button' style="margin-top:10px">Learn More</a>
                </div>
            </aside>
            </div>
        </section>             
        
        <!-- Reviews Section -->
        <?php if ($propertyReviews != "") : ?>
        <section id="reviews" style="background: white;"><br />
            <div id="rp-reviews-container" class="rp-single-prop-reviews-container">
                <aside><center>What Others Are Saying About Us</center></aside>
                <h3><center><?php echo get_the_title(); ?> Reviews</center></h3>
                <div id="rp-reviews-container" class="rp-single-prop-reviews-container">
                    <?php echo do_shortcode($propertyReviews); ?>
                </div>
            </div>
        </section><?php
        endif; ?>

    </main>

<?php if ( $isSiteSingleProp !== 'true' ) : 
    if ($numberInCity > 3) :?>
        <section id="explore-properties"><p>
            <div id="rp-explore-container" class="rp-single-explore-properties-container">
                <p><br /></p>
                <aside><center>Looking For Something Else?</center></aside>
                <h3><center>Explore Other Options</center></h3>

                <div class="rp-archive-fp-main-section">
                <div class="rp-archive-fp-data rp-is-open rp-row" id="rp-archive-fp-data">
                    <?php echo do_shortcode('[property_nearby min_count="3" property_code='. $post->prop_code .' ]'); ?>
                </div>
                </div>

            </div>
        </section>
    <?php endif; ?>

    <section id="search-return" style="background-color: white;" >
        <div id="search-back-btn" class="rp-footer-back-btn">
            <a class="rp-button rp-button-alt" href="<?php echo site_url(); ?>/search/">Back to Search</a>
        </div>
    </section>
<?php endif; ?>

<!-- *** End RentPress Property Template *** -->        

<!-- get reviews schema -->
<?php
if ( class_exists('rentPressDisplayReviews_Shortcodes') && (!empty($propertyReviews)) ) {
    include (RENTPRESS_DISPLAY_REVIEWS_ADDON_DIR."/templates/review-schema.php");
}

include RENTPRESS_PLUGIN_DIR . '/misc/template-schema/single-property-schema.php';

get_footer(); ?>