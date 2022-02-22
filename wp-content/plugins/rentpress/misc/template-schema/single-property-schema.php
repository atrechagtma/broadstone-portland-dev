<?php 

if (($rentPressOptions->getOption('disable_pricing') !== 'true')  && (get_post_meta(get_the_ID(), 'propDisablePricing')[0] !== 'true')) {

	if ($propertyMinRent < 100) {
		$propOffersText = '';
		$propPriceRangeText =  '';
		} else {
		$propOffersText = '"offers": 
							{
								"@type": "aggregateOffer",
								"lowPrice": "'. $propertyMinRent .'",
								"highPrice": "'. $propertyMaxRent .'",
								"priceCurrency": "USD",
								"offerCount": "'. $numberAvailableUnits .'"
							},';
		$propPriceRangeText = '"priceRange": "$' .$propertyMinRent. '-$' .$propertyMaxRent .'",';
	}

} ?>

<!-- property schema -->
<script type="application/ld+json" id="schema" style="display: none;">
    {
        "@type": "ItemPage",
        "primaryImageOfPage": "<?php echo $currentProperty->image(); ?>",
        "significantLink": "<?php echo $contactLeasingLink; ?>",
        "keywords": "<?php echo $propertyKeywords; ?>",
        "about":
            {
                "@type": "Product",
                <?php echo $ratingSchemaText; ?>
                "image": "<?php echo $currentProperty->image(); ?>",
                "description": "<?php echo esc_html($propertyDescription); ?>",
                "brand": "<?php echo get_bloginfo( $show = 'name'); ?>",
                <?php echo $propOffersText; ?>
                "name": "<?php echo get_the_title(); ?>",
                "sku": "<?php echo $propertyData['prop_code'][0]; ?>"
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
	                                    "name": "Search Apartments",
	                                    "@id": "<?php echo get_bloginfo( $show = 'wpurl'). '/search/'; ?>"
	                                }
	                        },
	                        {
	                            "@type": "ListItem",
	                            "position":2,
	                            "item": 
	                                {
	                                    "@type": "Thing",
	                                    "name": "<?php echo $propertyCity; ?>",
	                                    "@id": "<?php echo $cityLink; ?>"
	                                }
	                        },
	                        {
	                            "@type": "ListItem",
	                            "position":3,
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
	                "url": "<?php echo get_permalink(get_the_ID()); ?>",
	                "telephone": "<?php echo $displayedPhoneNumber; ?>",
	                "description": "<?php echo esc_html($propertyDescription); ?>",
	                "address":
	                    {
	                        "@type": "PostalAddress",
	                        "streetAddress": "<?php echo $propertyAddress; ?>",
	                        "addressLocality": "<?php echo $propertyCity; ?>",
	                        "addressRegion": "<?php echo $propertyState; ?>",
	                        "postalCode": "<?php echo $propertyZip; ?>"
	                    },
	                "geo": 
	                    { 
	                        "@type": "GeoCoordinates",
	                        "latitude": "<?php echo $propertyData['propLatitude'][0]; ?>",
	                        "longitude": "<?php echo $propertyData['propLongitude'][0]; ?>"
	                    },
	                "image":
	                    [
	                    	"<?php echo $currentProperty->image(); ?>"
	                	],
	                "containsPlace":
	                    {
	                        "@type": "Apartment",
	                        "petsAllowed":<?php if (has_term('', 'prop_pet_restrictions')) {
	                                        echo 'true' ;
	                                    } else {
	                                        echo 'false'; } ?>
	                    },
	                    "containedInPlace": 
	                        {
	                            "@type": "LocalBusiness",
	                            <?php echo $propPriceRangeText; ?>
	                            "telephone": "<?php echo $displayedPhoneNumber; ?>",
	                            "image": "<?php echo $currentProperty->image(); ?>",
	                            "address": 
	                                {
	                                    "@type":"PostalAddress",
	                                    "streetAddress": "<?php echo $propertyAddress; ?>",
	                                    "addressLocality": "<?php echo $propertyCity; ?>",
	                                    "addressRegion": "<?php echo $propertyState; ?>",
	                                    "postalCode": "<?php echo $propertyZip; ?>"
	                                },
	                            "name": "<?php echo get_the_title(); ?>",
	                            "@id": "<?php echo get_permalink(get_the_ID()); ?>"
	                        },
	                "@type": "ApartmentComplex",
	                "name": "<?php echo get_the_title(); ?>",
                	"tourBookingPage": "<?php echo $tourLink; ?>",
	                "hasMap": "<?php echo 'https://www.google.com/maps/dir/'. $googleMapAddress; ?>",
	                "logo": "<?php echo $propertyLogoImg; ?>",
	                "slogan": "<?php echo $propertyTagline; ?>",
	                "numberOfAvailableAccommodationUnits": "<?php echo $numberAvailableUnits; ?>",
	                "amenityFeature": "<?php 
	                    global $post;
	                    $terms = wp_get_post_terms($post->ID, 'prop_amenities');
	                    if ($terms) {
	                        $output = array();
	                        foreach ($terms as $term) {
	                            echo $term->name.',';
	                        }
	                    } ?>",
	                "@id": "<?php echo get_permalink(get_the_ID()); ?>"
	            }
	        ],
        "@context":"http://schema.org"
    	}
	</script>