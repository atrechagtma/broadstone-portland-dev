<?php

/**
 * Properties Map Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'properties-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'properties-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

?>

<section class="block <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
	<?php $location = get_field('location', 'options'); ?>

	<div class="acf-map">
		<div class="marker" data-lat="<?php echo esc_attr($location['lat']); ?>" data-lng="<?php echo esc_attr($location['lng']); ?>" data-address="<?php echo esc_attr($location['address']); ?>">
			<h3><?php the_title(); ?></h3>
			<p><em><?php echo esc_html($location['address']); ?></em></p>
		</div>
	</div>

    <style type="text/css">
        #<?php echo $id; ?> {
			text-align: center;
			margin-top: 0;
        }
		.acf-map {
			width: 100%;
			height: 400px;
			border: #ccc solid 1px;
			margin: 20px 0;
		}

		.acf-map img {
			max-width: inherit !important;
		}
		.marker * {
			opacity: 0;
		}
    </style>
	<script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDAySXaZBcJ55fy_bVzQtdV_h42jPj3iN8&callback"></script>
	<script>
		window.onload = () => {
			let footerMap = document.querySelector('.acf-map');
			let location = document.querySelector('.marker');

			if (footerMap) {
				window.map = new google.maps.Map(footerMap, {
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					center: { lat: parseFloat(location?.dataset?.lat), lng: parseFloat(location?.dataset?.lng)},
					zoom: 14,
				});

				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(parseFloat(location.dataset.lat), parseFloat(location.dataset.lng)),
					map: map
				});
				var listener = google.maps.event.addListener(map, "idle", function () {
					google.maps.event.removeListener(listener);
				});

			}
		}

	</script>
</section>