<?php

if ($rentPressOptions->getOption('disable_pricing') !== 'true' && $available_units > 0) {

    if ($floorplan_post->fpMinRent < 100) {
        $fpOfferText = '';
    } else {
        $fpOfferText = '"offers": 
      	{
        	"@type": "aggregateOffer",
        	"lowPrice": "'. $floorplan_post->fpMinRent .'",
        	"highPrice": "'. $floorplan_post->fpMaxRent .'",
        	"priceCurrency": "USD",
        	"offerCount": "'. $available_units .'"
      	},';
    }

    if ($fpDescription !== null && $fpDescription !== "") {
        $fpDescriptionText = '"description": "' . esc_html($fp->fpDescription) .'",';
    } else {
        $fpDescriptionText = '"description": "' . esc_html(get_post_meta($fpProperty->ID, 'propDescription', true)) .'",';
    }

    // property image check 
    if ( get_the_post_thumbnail_url($fpProperty->ID) ) {
        $fpPropImgURL = get_the_post_thumbnail_url($fpProperty->ID); 
    } else {
        $fpPropImgURL = $rentPressOptions->getOption('rentPress_properties_default_featured_image');
    } ?>

    <!-- floorplan card schema -->
    <script type="application/ld+json">
    {
    	"@context": "https://schema.org/", 
    	"@type": "Product", 
    	"name": "<?php echo $floorplan_post->fpName; ?>",
    	<?php echo $fpDescriptionText; ?>
    	"image": "<?php echo $floorplan_post->fpImg['image']; ?>",
    	"brand": "<?php echo get_bloginfo( $show = 'name'); ?>",
    	"sku": "<?php echo $floorplan_post->ID; ?>",
    	<?php echo $fpOfferText; ?>
      	"about":
          		{
          			"@type": "FloorPlan",
                	"image": "<?php echo $floorplan_post->fpImg['image']; ?>",
                	"name": "<?php echo $floorplan_post->fpName; ?>",
                	"url": "<?php echo $floorplan_post->post_url; ?>",
                	"floorsize": "<?php echo $floorplan_post->sqft; ?>",
                	"isPlanForApartment": "<?php echo get_the_title($fpProperty); ?>",
                	"numberOfBedrooms": "<?php echo $floorplan_post->bedCount; ?>",
                	"numberOfBathroomsTotal": "<?php echo $floorplan_post->bathCount; ?>",
                    "containedInPlace": 
                    {
                        "@type": "LocalBusiness",
                        "name": "<?php echo get_the_title($fpProperty->ID); ?>",
                        "description": "<?php echo esc_html(get_post_meta($fpProperty->ID, 'propDescription', true)); ?>",
                        "priceRange": "<?php echo '$' .get_post_meta($fpProperty->ID, 'wpPropMinRent', true). '-$' .get_post_meta($fpProperty->ID, 'wpPropMaxRent', true); ?>",
                        "image": "<?php echo $fpPropImgURL; ?>",
                        "telephone": "<?php echo get_post_meta($fpProperty->ID, 'propPhoneNumber', true);?>",
                        "address": 
                            {
                                "@type": "PostalAddress",
                                "streetAddress": "<?php echo get_post_meta($fpProperty->ID, 'propAddress', true); ?>",
                                "addressLocality": "<?php echo get_post_meta($fpProperty->ID, 'propCity', true);?>",
                                "addressRegion": "<?php echo get_post_meta($fpProperty->ID, 'propState', true);?>",
                                "postalCode": "<?php echo get_post_meta($fpProperty->ID, 'propZip', true);?>"
                            },
                        "geo": 
                            { 
                                "@type": "GeoCoordinates",
                                "latitude": "<?php echo get_post_meta($fpProperty->ID, 'propLatitude', true);?>",
                                "longitude": "<?php echo get_post_meta($fpProperty->ID, 'propLongitude', true);?>"
                            },
                        "@id": "<?php echo get_permalink(); ?>"
                    }
          		}
            }

    </script>

<?php } ?>