<!-- search results page schema -->
<script type="application/ld+json">
{
	"@context": "http://schema.org/",
	"@type": "SearchResultsPage",
		"name":"<?php echo get_the_title(); ?>",
		"primaryImageOfPage":"<?php echo $featuredImg; ?>",
		"url":"<?php echo get_page_link($page_id); ?>",
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
	                                    "name": "<?php echo get_bloginfo( $show = 'name' ); ?>",
	                                    "@id": "<?php echo get_bloginfo( $show = 'wpurl'); ?>"
	                                }
	                        },
	                        {
	                            "@type": "ListItem",
	                            "position":1,
	                            "item": 
	                                {
	                                    "@type": "Thing",
	                                    "name": "<?php echo get_bloginfo( $show = 'name' ); ?>",
	                                    "@id": "<?php echo get_permalink(); ?>"
	                                }
	                        }
	                    ],
	                    "@context":"http://schema.org",
	                    "@type":"BreadcrumbList"
	            }
	        ]
}
</script>