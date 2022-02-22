<!-- property card schema -->
<script type="application/ld+json">
{
	"@context": "http://schema.org/",
	"@type": "ApartmentComplex",
		"address": {
			"@type": "PostalAddress",
			"addressLocality":"<?php echo $prop->propCity; ?>",
			"addressRegion":"<?php echo $prop->propState; ?>",
			"postalCode":"<?php echo $prop->propZip; ?>",
			"streetAddress":"<?php echo $prop->propAddress; ?>"
		},
		"image":"<?php echo $currentProperty->image(); ?>",
		"name":"<?php echo $prop->post_title; ?>",
		"petsAllowed":<?php if (has_term('', 'prop_pet_restrictions')) {
                                        echo 'true,
';} else {
                                        echo 'false,
';} ?>
		"url":"<?php echo get_permalink($prop->ID); ?>"
}
</script>