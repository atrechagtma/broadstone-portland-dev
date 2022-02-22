<?php

if ($fp->fpMinRent < 100) {
    $fpModalOfferText = '';
} else  {
    $fpModalOfferText = '"offers": 
  	{
    	"@type": "aggregateOffer",
    	"lowPrice": "'. $fp->fpMinRent .'",
    	"highPrice": "'. $fp->fpMaxRent .'",
    	"priceCurrency": "USD",
    	"offerCount": "'. $available_units_count .'"
  	},';
}

if ($fpDescription !== null && $fpDescription !== "") {
    $fpDescriptionText = '"description": "' . esc_html($fp->fpDescription) .'",';
} else {
    $fpDescriptionText = '"description": "' . esc_html(get_post_meta(get_the_ID(), 'propDescription', true)) .'",';
}

if ($fpModalOfferText !== null &&  $fpModalOfferText !== '') { ?>
<!-- floorplan modal schema -->
<script type="application/ld+json">
{
	"@context": "https://schema.org/", 
	"@type": "Product", 
	"name": "<?php echo $fp->post_title; ?>",
	<?php echo $fpDescriptionText; ?>
	"image": "<?php echo $fp->fpImg['image']; ?>",
	"brand": "<?php echo get_bloginfo( $show = 'name'); ?>",
	"sku": "<?php echo $fp->ID; ?>",
	<?php echo $fpModalOfferText; ?>
  	"about":
  	{
		"@type": "FloorPlan",
    	"image": "<?php echo $fp->fpImg['image']; ?>",
    	"name": "<?php echo $fp->post_title; ?>",
    	"url": "<?php echo get_permalink(get_the_ID()) .'#floorplans'; ?>",
    	"floorsize": "<?php echo $fp->displaySqft; ?>",
    	"isPlanForApartment": "<?php echo get_the_title($fpProperty); ?>",
    	"numberOfBedrooms": "<?php echo $fp->bedCount; ?>",
    	"numberOfBathroomsTotal": "<?php echo $fp->bathCount; ?>",
    	"petsAllowed": <?php if (has_term('', 'prop_pet_restrictions')) {
                                echo 'true';
                            } else {
                                echo 'false';
                            } ?> 
    },
	"containedInPlace": 
    {
        "@type": "LocalBusiness",
        "name": "<?php echo get_the_title(); ?>",
        "description": "<?php echo esc_html(get_post_meta(get_the_ID(), 'propDescription', true)); ?>",
        "priceRange": "<?php echo '$' .get_post_meta(get_the_ID(), 'wpPropMinRent', true). '-$' .get_post_meta(get_the_ID(), 'wpPropMaxRent', true); ?>",
        "image": "<?php echo get_the_post_thumbnail_url($fpProperty->ID); ?>",
        "address": 
            {
            "@type": "PostalAddress",
            "streetAddress": "<?php echo get_post_meta(get_the_ID(), 'propAddress', true); ?>",
            "addressLocality": "<?php echo get_post_meta(get_the_ID(), 'propCity', true);?>",
            "addressRegion": "<?php echo get_post_meta(get_the_ID(), 'propState', true);?>",
            "postalCode": "<?php echo get_post_meta(get_the_ID(), 'propZip', true);?>"
            },
        "geo": 
            { 
            "@type": "GeoCoordinates",
            "latitude": "<?php echo get_post_meta(get_the_ID(), 'propLatitude', true);?>",
            "longitude": "<?php echo get_post_meta(get_the_ID(), 'propLongitude', true);?>"
            },
        "@id": "<?php echo get_permalink(); ?>"
    }
}
</script>

<?php } ?>