<!-- property search card schema -->
<script type="application/ld+json">
{
	"@context": "http://schema.org/",
	"@type": "ApartmentComplex",
		"address": {
			"@type": "PostalAddress",
			"addressLocality":"<?php echo $property['propCity'][0]; ?>",
			"addressRegion":"<?php echo $property['propState'][0] ?>",
			"postalCode":"<?php echo $property['propZip'][0]; ?>",
			"streetAddress":"<?php echo $property['propAddress'][0]; ?>"
		},
	"image":"<?php echo $property['image']; ?>",
	"name":"<?php echo $property['propName'][0]; ?>",
	"petsAllowed":<?php if ($property['pet_restrictions']) {
                                        echo 'true,
';} else {
                                        echo 'false,
';} ?>
	"url":"<?php echo $property['url']; ?>"
}
</script>

