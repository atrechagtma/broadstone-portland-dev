<?php
	get_header();
	$term_slug = get_queried_object()->slug;
	$term_name = get_queried_object()->name;
	$tax_name = get_queried_object()->taxonomy;
	$term_description = get_queried_object()->description;
	$prop_args = array(
		'post_type' => 'properties', // Your Post type Name that You Registered
		'posts_per_page' => -1,
		'order' => 'ASC',
		'tax_query' => array(
			array(
			    'taxonomy' => get_queried_object()->taxonomy,
			    'field' => 'slug',
			    'terms' => $term_slug
			    )
			)
		);
	$prop_qry = new WP_Query($prop_args);
	$termMeta = get_term_meta( get_queried_object()->term_id );

	function getFloorplanAndUnitMeta($property_code, $wpdb)
	{
		$property_units = $wpdb->get_results($wpdb->prepare( "SELECT * FROM `$wpdb->rp_units` WHERE `prop_code` = %s", $property_code));

		foreach ($property_units as $unit) {
			$unit->tpl_data = json_decode($unit->tpl_data);
			$date = new DateTime($unit->tpl_data->Information->AvailableOn);
			$unit->tpl_data->Information->AvailableStr = $date->format('m/d/Y');
		}

		return $property_units;
	}
?>
<!-- <link rel="stylesheet" type="text/css" href="<?php //echo get_template(); ?>2/wp-content/plugins/rentpress-core/scss/taxonomies/taxonomy-template.css"> -->

<div class="rp-main-hero has-bg-img" style="background-image: url(<?php 
	if( wp_get_attachment_image_url($termMeta['showcase-taxonomy-image-id'][0]) ) { 
		echo wp_get_attachment_image_url($termMeta['showcase-taxonomy-image-id'][0], 'full');
	} else {
		echo "https://via.placeholder.com/2500x1000";
	}
	?>);">
	<h1  class="rp-main-hero-title">
		<?php if ( $tax_name == 'prop_city' ) : ?>
			Apartments In <?= $term_name ?>
		<?php endif ; ?>

		<?php if ( $tax_name == 'prop_pet_restrictions' ) : ?>
			<?= $term_name ?> Apartments
		<?php endif ; ?>

		<?php if ( $tax_name == 'prop_amenities' ) : ?>
			Apartments With <?= $term_name ?>
		<?php endif ; ?></h1>
</div>

<section class="rp-section-block-light">
	<section class="rp-row">
		<div class="rp-col-12">
			<div class="rp-taxonomy-desc">
				<p>
				<?php if ($term_description == $term_name or $term_description == '' ) : ?>
						<?php if ( $tax_name == 'prop_city' ) : ?>
							Check out our options for <?= $term_name ?> apartments.
						<?php endif ; ?>

						<?php if ( $tax_name == 'prop_pet_restrictions' ) : ?>
							Check out our options for <?= $term_name ?> apartments.
						<?php endif ; ?>

						<?php if ( $tax_name == 'prop_amenities' ) : ?>
							Check out our options for apartments with a <?= $term_name ?>.
						<?php endif ; ?>
				<?php else : ?>
					<?= $term_description ?>
				<?php endif ; ?>
				</p>
			</div>
		</div>
	</section>
</section>

<main>
<nav class="rp-archive-fp-nav rp-row">

    <div class="rp-archive-fp-nav-section">
        <span class="is-matching">Showing <strong>1-12</strong> of <strong id="number-of-matching-props"></strong> Matching Properties</span>
        <span class="is-sort">Sort 
            <select name="" id="property_sort" onchange="property_sort()">
                <option value="rent:asc" <?php //echo $hide_pricing; ?>>Rent: Low to High</option>
                <option value="rent:desc" <?php //echo $hide_pricing; ?>>Rent: High to Low</option>
                <option value="">State</option>
                <option value="beds:asc">Bedrooms</option>
            </select>
        </span>
    </div>

</nav>

<div class="rp-archive-fp-main-section">

    <div class="rp-archive-fp-data rp-is-open rp-row" id="rp-archive-fp-data">

        <section id="property_cards" class="rp-archive-fp-loop">

		<?php
		// $iterativeNumber = 1;

		    if($prop_qry->have_posts()) :
		    	foreach ($prop_qry->posts as $prop):
		    	$iterativeNumber++;
		    	$propMeta = get_post_meta( $prop->ID );
		    	$allFeaturedAmenities  = $propMeta['featured_amenities'][0];
				preg_match_all('/"([^"]+)"/', $allFeaturedAmenities, $allFeaturedAmenitiesIDs);
				$units = getFloorplanAndUnitMeta($propMeta['prop_code'][0], $wpdb);		
		?>

		<div class="is-rp-prop">
			<?php if ( get_the_post_thumbnail_url($prop->ID,'full') ) : ?>
				<figure class="is-comm-photo">
					<a href="<?= get_permalink($prop->ID); ?>">
						<img src="<?= get_the_post_thumbnail_url($prop->ID,'full'); ?>">
					</a>
				</figure>
			<?php endif; ?>
			<section class="single-prop-details">
				<div class="single-prop-detail-1">
					<h4>
						<?= $prop->post_title ?>
						<?php if ($propMeta['is_displaying_alert'][0] != 'no') : ?>
							<span class="fa fa-warning property-alert ng-scope" style="color: #ff1744;"></span>
						<?php endif; ?>
					</h4>
					<a href="https://www.google.com/maps/dir/?api=1&destination=<?= $prop->propAddress . $prop->propCity . $prop->propState . $prop->propZip ?>">
						<p class="single-prop-address is-comm-address">
							<?= $prop->propAddress; ?>
							<br>
							<?= $prop->propCity; ?>,
							<?= $prop->propState; ?>
							<?= $prop->propZip; ?>
						</p>
					</a>
					<div class="single-prop-contact">
						<span class="single-prop-phone is-comm-links">
							<a class="rp-phone-link" href="tel: <?= $prop->propPhoneNumber; ?>">
								<span class="rp-phone"><?= $prop->propPhoneNumber; ?></span>
							</a>
						</span>
						<span class="single-prop-email is-comm-links">
							<span class="fa fa-envelope"></span>
							<a href="mailto: <?= $propMeta['propEmail'][0]; ?>">Emails</a>
						</span>
						<ul class="is-comm-amenities">
							<?php 
							foreach ($allFeaturedAmenitiesIDs[1] as $amenitiesID) :
								$featTerm = get_term_by('id', $amenitiesID, 'property_amenities');
								$amenitiesIcon = get_field('icon', 'property_amenities' . '_' . $amenitiesID);
								?>
									<li style="background-image: url(<?= $amenitiesIcon ; ?>);"></li>
							<?php endforeach; ?>
						</ul>		
					</div>
				</div>
				<div class="single-prop-detail-2">
					<div class="single-prop-view-btn">
						<a href="<?= get_permalink($prop->ID); ?>" class="button rp-button-alt">View This Property</a>
					</div>
				</div>
			</section>
		</div>
		<?php 
		// if ($iterativeNumber % 3 == 1) {
		// 	echo '<div class="clearfix clearfix-full-size"></div>';
		// } 
		// if ($iterativeNumber % 2 == 1) {
		// 	echo '<div class="clearfix clearfix-1050px-size"></div>';
		// }
		?>
	<?php
	endforeach;
	endif;

	?>
	
			</section>
		</div>
	</div>
	
	<div class="back-btn">
		<a href="<?php echo site_url(); ?>/search/">
			<button type="button">
				Back To Search
			</button>
		</a>
	</div>
	</div>
</main>
<?php get_footer(); ?>