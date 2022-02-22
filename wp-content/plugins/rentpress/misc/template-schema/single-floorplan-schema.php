<?php

// check if price is enabled and valid
if( ($rentPressOptions->getOption('rentPress_disable_pricing') !== true) && (get_post_meta($post->ID, 'fpMinRent', true) > 100) ):
	$fpOffersText = '"offers": 
			  	{
			    	"@type": "aggregateOffer",
			    	"lowPrice": "'. get_post_meta($post->ID, 'fpMinRent', true) .'",
			    	"highPrice": "'. get_post_meta($post->ID, 'fpMaxRent', true) .'",
			    	"priceCurrency": "USD",
			    	"offerCount": "'. get_post_meta($post->ID, 'fpAvailUnitCount', true) .'"
			  	},
';
$propPriceRange = '$' .$fpProperty->wpPropMinRent. '-$' .$fpProperty->wpPropMaxRent;
else :
	$fpOffersText = '';
	$propPriceRange = '';
endif; 

// get property image
if (get_the_post_thumbnail_url($fpProperty->ID) == '' || false ) :
	$thePropertyImage = $rentPressOptions->getOption('rentPress_properties_default_featured_image');
else :
	$thePropertyImage = (get_the_post_thumbnail_url($fpProperty->ID));
endif;

?>

<!-- floorplan schema -->
<script type="application/ld+json" id="schema">
{
    "@type": "ItemPage",
    "primaryImageOfPage": "<?php echo $fpImage; ?>",
    "significantLink": "<?php echo $availabilityUrl; ?>",
    "about":
        {
        	"@type": "Product",
            "image": "<?php echo $fpImage; ?>",
            "description": "<?php echo esc_html($propDescription); ?>",
            "brand": "<?php echo get_bloginfo( $show = 'name'); ?>",
            <?php echo $fpOffersText; ?>
	        "name": "<?php echo html_entity_decode(get_the_title()); ?>",
	        "sku": "<?php echo $fpProperty->prop_code; ?>"
        },
    "breadcrumb": 
        [
            {
                "itemListElement":
                    [
                        {
                            "@type": "ListItem",
                            "position":0,
                            "item": 
                                {
                                    "@type": "Thing",
                                    "name": "Home",
                                    "@id": "<?php echo get_bloginfo( $show = 'wpurl'); ?>"
                                }
                        },
                        {
                            "@type": "ListItem",
                            "position":1,
                            "item": 
                                {
                                    "@type": "Thing",
                                    "name": "<?php echo $fpProperty->propName . ' Floor Plans';?> ",
                                    "@id": "<?php echo get_bloginfo( $show = 'wpurl'). '/floorplans/'; ?>"
                                }
                        },
                        {
                            "@type": "ListItem",
                            "position":2,
                            "item": 
                                {
                                   "@type": "Thing",
                                   "name":"<?php echo get_the_title(); ?>",
                                    "@id":"<?php echo get_permalink(get_the_ID()); ?>"
                                }
                        }
                    ],
                    "@context":"http://schema.org",
                    "@type":"BreadcrumbList"
            }
        ],
    "mainEntity":
        [
            {
            	"@type": "FloorPlan",
                "image": "<?php echo $fpImage; ?>",
                "description": "<?php echo $propDescription; ?>",
                "name": "<?php echo get_the_title(); ?>",
                "url": "<?php echo get_permalink(get_the_ID()); ?>",
                "floorsize": "<?php echo $sqft; ?>",
                "isPlanForApartment": "<?php echo get_the_title($fpProperty); ?>",
                "numberOfBedrooms": "<?php echo $bedrooms; ?>",
                "numberOfBathroomsTotal": "<?php echo $bathrooms; ?>",
                "petsAllowed": <?php if (has_term('', 'prop_pet_restrictions')) {
	                                        echo 'true';
	                                    } else {
	                                        echo 'false';
	                                    } ?>
            },
            {
            	"containedInPlace": 
	                {
	                    "@type": "LocalBusiness",
	                    "name": "<?php echo get_the_title($fpProperty); ?>",
	                    "description": "<?php echo $propDescription; ?>", 
	                    "priceRange": "<?php echo $propPriceRange; ?>",
	                    "telephone": "<?php echo $displayedPhoneNumber; ?>",
	                    "image": "<?php echo $thePropertyImage; ?>",
	                    "address": 
	                        {
	                            "@type": "PostalAddress",
		                        "streetAddress": "<?php echo $fpProperty->propAddress; ?>",
		                        "addressLocality": "<?php echo $fpProperty->propCity; ?>",
		                        "addressRegion": "<?php echo $fpProperty->propState; ?>",
		                        "postalCode": "<?php echo $fpProperty->propZip; ?>"
	                        },
                        "geo": 
		                    { 
		                        "@type": "GeoCoordinates",
		                        "latitude": "<?php echo $fpProperty->propLatitude; ?>",
		                        "longitude": "<?php echo $fpProperty->propLongitude; ?>"
		                    },
	                    "@id": "<?php echo get_permalink($fpProperty->ID); ?>"
	                }
            }
        ],
    "@context":"http://schema.org"
}
</script>