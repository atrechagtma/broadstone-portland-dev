<?php
	get_header();
	include RENTPRESS_PLUGIN_DIR . 'templates/Taxonomies/taxonomy-template-data.php';
?>

<div class="rp-main-hero has-bg-img" style="background-image: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.35)), url(<?php echo $termFeaturedImage; ?>);">	
	<h1 class="rp-main-hero-title">
		<?php if ( $tax_name == 'prop_city' ) { 
			echo 'Apartments In <br/>'. $term_name; 
		} 
		if ( $tax_name == 'prop_pet_restrictions' ) { 
			echo $term_name.' <br/>Apartments';
		} 
		if ( $tax_name == 'prop_amenities' ) { 
			echo 'Apartments With <br/>'. $term_name;
		} 
		if ( $tax_name == 'prop_type' ) { 
			echo $term_name;
		} ?>
	</h1>
</div>

<section class="rp-property-archive rentpress-core-container" id="rp_property_archive">

	<section class="rp-section-block-light" style="background: #F9F9F9;">

		<section class="rp-row">
			<div class="rp-col-12">
				<div class="rp-taxonomy-desc">

			<?php if ( $tax_name == 'prop_city' ) { ?>
				<h2 style="color: #<?php echo $accentColorHex; ?>"><?php echo $term_name; ?>, <?php echo $propertyState; ?></h2>
			<?php }

			if ($term_description == $term_name or (str_word_count($term_description) < 3 )) { ?>
					<?php if ( $tax_name == 'prop_city' ) { ?>
						<p>Check out our options for <?php echo $term_name; ?> apartments.</p>
					<?php } 

					if ( $tax_name == 'prop_pet_restrictions' ) { ?>
						<p>Have a four-legged friend? Check out our <?php echo $term_name; ?> apartments.</p>
					<?php }

					if ( $tax_name == 'prop_amenities' ) { ?>
						<p>Check out our options for apartments with <?php echo $term_name; ?>.</p>
					<?php }

					if ( $tax_name == 'prop_type' ) { ?>
						<p>Check out our options for <?php echo $term_name; ?>.</p>
					<?php } ?>
			<?php } else { ?>
				<div align="left" style="margin-right: 20px;"><p><?php echo $term_description; ?></p></div>
			<?php } ?>
			</div>

		</section>

	</section>

	<nav class="rp-archive-fp-nav rp-row" style="margin-top: 30px;">

	    <div class="rp-taxonomy-archive-fp-nav-section">
	        <span class="is-matching">Showing <strong><?php echo count($prop_qry->posts); ?></strong></strong> <?php echo $term_name; ?> Properties</span>
	    </div>

	</nav>

	 <div class="rp-archive-fp-main-section">
	    <div class="rp-archive-fp-data rp-is-open rp-row" id="rp-archive-fp-data">

	        <section id="property_cards" class="rp-archive-fp-loop">

				<?php
			    if($prop_qry->have_posts()) {
			    	foreach ($prop_qry->posts as $prop) {
			    	$propMeta = get_post_meta( $prop->ID );
					$disabledPricing = $propMeta['propDisablePricing'][0];
					$currentProperty = $rentPress_Service['properties_meta']->setPostID($prop->ID);
				?>

				<div class="is-rp-prop">
					<a href="<?php echo get_permalink($prop->ID); ?>">

						<figure class="rp-prop-figure">
							<?php if ( get_the_post_thumbnail_url($prop->ID,'full')) { ?>
								<img src="<?php echo get_the_post_thumbnail_url($prop->ID,'full'); ?>">
							<?php } else { ?>
								<img src="<?php echo $currentProperty->image(); ?>">
							<?php } ?>
						</figure>
						
						 <section class="rp-prop-details">

	                        <div class="rp-prop-top">
	                            <h4 class="rp-prop-name" style="color: <?php echo $templateAccentColor; ?>;"><?php echo $prop->post_title; ?></h4>
	                            <p class="rp-prop-location"><?php echo $prop->propCity; ?>, <?php echo $prop->propState; ?></p>
	                        </div>  

							<div class="rp-prop-bottom">
                                            
	                            <div class="rp-prop-bed-count">
	                            	<span>
	                            		<?php if ($propMeta['wpPropMinBeds'][0] == $propMeta['wpPropMaxBeds'][0] && $propMeta['wpPropMinBeds'][0] == '0' ) {
				                            echo 'Studio';
				                        } elseif ($propMeta['wpPropMinBeds'][0] == $propMeta['wpPropMaxBeds'][0]) {
				                            echo $propMeta['wpPropMinBeds'][0].' Bed';
				                        } elseif ($propMeta['wpPropMinBeds'][0] == '0') {
				                            echo 'Studio - '.$propMeta['wpPropMaxBeds'][0].' Bed';
				                        } else {
				                            echo $propMeta['wpPropMinBeds'][0].' - '.$propMeta['wpPropMaxBeds'][0].' Bed';
				                        }
				                     ?>
	                            	</span>
	                            </div>

	                            <span>
	                            <?php if (($globalPriceDisable == 'true' ) || ($disabledPricing == 'true')) {
			                            echo '';
			                        } elseif ($propMeta['wpPropMinRent'][0] == '' || $propMeta['wpPropMinRent'][0] < '99' ) {
			                            echo '';
			                        } elseif ($priceDisplayMode == 'range' ) {
			                            echo '<div class="rp-prop-price-range">$'.$propMeta['wpPropMinRent'][0].' - $'.$propMeta['wpPropMaxRent'][0]. '</div>'; 
			                        } else {
			                            echo '<div class="rp-prop-price-range">Starting at $'.$propMeta['wpPropMinRent'][0]. '</div>';
			                        } 
			                    ?>
			                    </span>

	                            <div class="rp-pets-welcome">
	                            <?php if( has_term( 'cat-friendly', 'prop_pet_restrictions', $prop->ID )) { ?>
	                                <div class="rp-cat-icon"><span class="rp-visually-hidden">Cat Friendly Apartments</span></div>
	                            <?php }

	                            if( has_term( 'dog-friendly', 'prop_pet_restrictions', $prop->ID )) { ?>
                                 	<div class="rp-dog-icon"><span class="rp-visually-hidden">Dog Friendly Apartments</span></div>
	                            <?php } ?>
	                            </div>
	                            
	                        </div>

						</section>
					</a>
					<?php include RENTPRESS_PLUGIN_DIR . 'misc/template-schema/property-card-schema.php'; ?>
				</div> <!-- is-rp-prop -->

			<?php }}; ?>

			</section>
		</div>
	</div>

<?php
if ( $tax_name == 'prop_city' ) { 
	if ($cityRomance !== '') { ?>
	<section class="rp-section-block-light" style="background: #F9F9F9;">
		<section class="rp-row">

		<?php if (!($favContent)) { ?>
			<div class="rp-col-12">
				<div class="rp-taxonomy-desc">
		<?php } else { ?>
			<div class="rp-col-9">
		<?php } 

		if ( $tax_name == 'prop_city' ) { ?>
				<h3 style="color: #<?php echo $accentColorHex; ?>">Welcome to <?php echo $term_name; ?>, <?php echo $propertyState; ?></h3>
		<?php } ?>
			<p>
				<div align="left" style="margin-right: 2rem;"><?php echo $cityRomance; ?></div>
			</p>
			</div>

			<?php if ($favContent) : ?>
			<aside class="rp-col-3">
				<h4>Local Favorites</h4>
				<div style="margin-left: 1rem;"><?php echo $favContent; ?></div>
			</aside>
			<?php endif;

			if (!($favContent)) { ?>
			</div>
			<?php } ?>

		</section>
	</section>
	<div style="margin-bottom: 20px;"></div>
<?php }} ?>

<!-- shortcode section -->
<?php if( isHTML( do_shortcode( $term_map_shortcode ))) { ?>
	<div class="rp-shortcode-section rp-row">
		<?php echo do_shortcode( $term_map_shortcode ); ?>
	</div>
<?php } ?>

<!-- Cities Section -->
<?php if($citiesTerms[$featuredCities[0]]) { ?>
    <section class="rp-title-header" style="margin-top: 10px;">
	    <p><br /></p>
	    <aside><center>Looking For Something Else?</center></aside>
	    <h3><center>Explore Other Options</center></h3>
    </section>

    <section class="rp-row rp-archive-fp-loop rp-explore-footer">

		
	    	<div class="is-rp-prop">
				<a href="<?php echo get_term_link($citiesTerms[$featuredCities[0]]->term_id, 'prop_city'); ?>">
					<figure class="rp-prop-figure">
						<?php if (wp_get_attachment_image_url( get_term_meta($citiesTerms[$featuredCities[0]]->term_id)['showcase-taxonomy-image-id'][0],'medium')) { ?>
							<img src="<?php echo wp_get_attachment_image_url( get_term_meta($citiesTerms[$featuredCities[0]]->term_id)['showcase-taxonomy-image-id'][0],'medium'); ?>">
						<?php } else { ?>
							<img src="<?php echo $globalTaxCityImage; ?>">
						<?php } ?>
					</figure>
					 <section class="rp-prop-details">
	                    <div class="rp-prop-top">
	                        <h4 class="rp-prop-name" style="color: <?php echo $templateAccentColor; ?>;"><?php echo $citiesTerms[$featuredCities[0]]->name; ?></h4>
	                    </div> 
	                </section>
	            </a>
	        </div>

        <?php if($citiesTerms[$featuredCities[1]]) { ?>
	        <div class="is-rp-prop">
				<a href="<?php echo get_term_link($citiesTerms[$featuredCities[1]]->term_id, 'prop_city'); ?>">
					<figure class="rp-prop-figure">
						<?php if (wp_get_attachment_image_url( get_term_meta($citiesTerms[$featuredCities[1]]->term_id)['showcase-taxonomy-image-id'][0],'medium')) { ?>
							<img src="<?php echo wp_get_attachment_image_url( get_term_meta($citiesTerms[$featuredCities[1]]->term_id)['showcase-taxonomy-image-id'][0],'large'); ?>">
						<?php } else { ?>
							<img src="<?php echo $globalTaxCityImage; ?>">
						<?php } ?>
					</figure>
					 <section class="rp-prop-details">
	                    <div class="rp-prop-top">
	                        <h4 class="rp-prop-name" style="color: <?php echo $templateAccentColor; ?>;"><?php echo $citiesTerms[$featuredCities[1]]->name; ?></h4>
	                    </div> 
	                </section>
	            </a>
	        </div>
        <?php } ?>

        <?php if($citiesTerms[$featuredCities[2]]) { ?>
	        <div class="is-rp-prop">
				<a href="<?php echo get_term_link($citiesTerms[$featuredCities[2]]->term_id, 'prop_city'); ?>">
					<figure class="rp-prop-figure">
						<?php if (wp_get_attachment_image_url( get_term_meta($citiesTerms[$featuredCities[2]]->term_id)['showcase-taxonomy-image-id'][0],'full')) { ?>
							<img src="<?php echo wp_get_attachment_image_url( get_term_meta($citiesTerms[$featuredCities[2]]->term_id)['showcase-taxonomy-image-id'][0],'full'); ?>">
						<?php } else { ?>
							<img src="<?php echo $globalTaxCityImage; ?>">
						<?php } ?>
					</figure>
					 <section class="rp-prop-details">
	                    <div class="rp-prop-top">
	                        <h4 class="rp-prop-name" style="color: <?php echo $templateAccentColor; ?>;"><?php echo $citiesTerms[$featuredCities[2]]->name; ?></h4>
	                    </div> 
	                </section>
	            </a>
	        </div>
    	<?php } ?>

    </section>
<?php } ?>

<?php if ( $searchTemplateIsActive !== '' ) { ?>
	<div class="rp-archive-search-cta-section">
		<a href="<?php echo get_home_url(); ?>/search/" class="rp-button rp-button-alt rp-city-archive-search-cta">More Apartments</a>
	<?php } else { ?>
		<a href="<?php echo $requestInfoURL; ?>" class="rp-button rp-button-alt rp-city-archive-search-cta">Request Info</a>	
	</div>
<?php } ?>

</section>

<!-- <script>
	document.getElementById("number-of-matching-props").innerHTML = properties.length;

</script> -->
<?php include RENTPRESS_PLUGIN_DIR . 'misc/template-schema/taxonomy-term-schema.php'; 
get_footer();