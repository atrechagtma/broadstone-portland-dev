<!-- tax term schema -->
<script type="application/ld+json">
	{
		"@type": "SearchResultsPage",
		"about":"<?php echo $term_name; ?>",
		"contentLocation": 
			{
				"containedIn": 
					{
						"@type":"State",
						"name":"<?php echo $propertyState; ?>"
					},
				"@type": "City", 
				"name":"<?php echo $term_name; ?>"
			},
		"description":"<?php echo esc_html($term_description); ?>",
		"primaryImageOfPage":"<?php echo $termFeaturedImage; ?>",
		"url":"<?php echo get_term_link($term_id); ?>",
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
	                                    "name": "<?php echo $term_name; ?>",
	                                    "@id": "<?php echo get_term_link($term_id); ?>"
	                                }
	                        }
	                    ],
	                    "@context":"http://schema.org",
	                    "@type":"BreadcrumbList"
	            }
	        ],
        "@context":"http://schema.org"
    }
</script>