<section id="property_cards" class="rp-archive-fp-loop">

    <?php if($prop_qry->have_posts()) :
        foreach ($prop_qry->posts as $prop):
        $propMeta = get_post_meta( $prop->ID );
        $disabledPricing = $propMeta['propDisablePricing'][0];
        $priceDisableMsg = $rentPressOptions->getOption('rentPress_disable_pricing_message');
        $currentProperty = $rentPress_Service['properties_meta']->setPostID($prop->ID);
        $specialText = $propMeta['prop_special_text'][0];
        $specialExpiration = $propMeta['prop_special_expiration'][0];
        $specialExpired = $currentProperty->isExpired($specialExpiration);
        if ($specialText && $specialText != '' && $specialExpired != true) : //check for valid special then display
            $propSpecial = '<div class="rp-prop-is-special"><h6><span>&#x2605</span> Special</h6><aside class="rp-prop-is-special-msg">'.$specialText.'</aside></div>';
        else :
            $propSpecial = '';
        endif;
    ?>

    <div class="is-rp-prop">
        <a href="<?php echo get_permalink($prop->ID); ?>">

            <figure class="rp-prop-figure">
                <?php if ( get_the_post_thumbnail_url($prop->ID,'full') ) : ?>
                    <img class="rp-lazy" data-src="<?php echo get_the_post_thumbnail_url($prop->ID,'full'); ?>">
                <?php else : ?>
                    <img class="rp-lazy" data-src="<?php echo $currentProperty->image(); ?>">
                <?php endif;
                echo $propSpecial ?>
            </figure>
            
             <section class="rp-prop-details">

                <div class="rp-prop-top">
                    <h4 class="rp-prop-name" style="color: <?php echo $templateAccentColor; ?>;"><?php echo $prop->post_title; ?></h4>
                    <p class="rp-prop-location"><?= $prop->propCity; ?>, <?= $prop->propState; ?></p>
                </div>  

                <div class="rp-prop-bottom">
                                
                    <div class="rp-prop-bed-count">
                        <span>
                            <?php if ($propMeta['wpPropMinBeds'][0] == $propMeta['wpPropMaxBeds'][0] && $propMeta['wpPropMinBeds'][0] == '0' ) : //check if minbeds and max beds are both 0 then show Studio
                                echo 'Studio';
                            elseif ($propMeta['wpPropMinBeds'][0] == $propMeta['wpPropMaxBeds'][0]) : //if minbeds and maxbeds is same then condense
                                echo $propMeta['wpPropMinBeds'][0].' Bed';
                            elseif ($propMeta['wpPropMinBeds'][0] == '0') : // show range starting with Studio
                                echo 'Studio - '.$propMeta['wpPropMaxBeds'][0].' Bed';
                            else : //show beds range
                                echo $propMeta['wpPropMinBeds'][0].' - '.$propMeta['wpPropMaxBeds'][0].' Bed';
                            endif; ?>
                        </span>
                    </div>

                    <span>
                    <?php if (($globalPriceDisable == 'true' ) || ($disabledPricing == 'true')) : //check if price is disabled across site or on property
                            echo '<div class="rp-prop-price-range"><span>'.$priceDisableMsg.'</span></div>';
                        elseif ($propMeta['wpPropMinRent'][0] == '' || $propMeta['wpPropMinRent'][0] < '99' ) : //check that price is valid
                            echo '<div class="rp-prop-price-range"><span>'.$priceDisableMsg.'</span></div>';
                        elseif ($priceDisplayMode == 'range' ) : //display price as range
                            echo '<div class="rp-prop-price-range"><span>$'.$propMeta['wpPropMinRent'][0].' - $'.$propMeta['wpPropMaxRent'][0]. '</span></div>'; 
                        else : //price as starting at - most common
                            echo '<div class="rp-prop-price-range">Starting at $'.$propMeta['wpPropMinRent'][0]. '</div>';
                        endif; ?>
                    </span>

                    <div class="rp-pets-welcome">
                    <?php if( has_term( 'cat-friendly', 'prop_pet_restrictions', $prop->ID ) ) : ?>
                        <div class="rp-cat-icon"><span class="rp-visually-hidden">Cat Friendly Apartments</span></div>
                    <?php endif;  
                    if( has_term( 'dog-friendly', 'prop_pet_restrictions', $prop->ID ) ) : ?>
                         <div class="rp-dog-icon"><span class="rp-visually-hidden">Dog Friendly Apartments</span></div>
                    <?php endif; ?>
                    </div>
                    
                </div>

            </section>
        </a>
    </div> <!-- is-rp-prop -->

<?php
    	endforeach;
    endif;
?>

</section><!-- rp-archive-fp-loop -->