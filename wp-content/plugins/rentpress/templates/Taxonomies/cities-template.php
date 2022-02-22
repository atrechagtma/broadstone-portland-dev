<?php
	get_header();
	include RENTPRESS_PLUGIN_DIR . 'templates/Taxonomies/cities-template-data.php';
?>

<div class="rp-main-hero has-bg-img" style="background-image: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.35)), url(<?php 
	if( get_the_post_thumbnail($post->ID, 'full') ) { 
		echo get_the_post_thumbnail_url($post->ID, 'full');
	} else {
		echo $globalTaxCityImage;
	}
	?>);">	
	<h1  class="rp-main-hero-title">
		<?php echo the_title(); ?>
	</h1>
</div>

<section class="rp-property-archive rentpress-core-container" id="rp_property_archive">

	<?php if ( have_posts() && '' !== get_post()->post_content ) : ?>

		<section class="rp-section-block-light">

			<section>
				<div>
					<div class="rp-city-archive-desc">
						<p>
							<?php 
							    while ( have_posts() ) : the_post(); 
							        the_content();
							    endwhile; 
							?>
						</p>
					</div>
				</div>
			</section>

		</section>

	<?php endif; ?>

	<div class="rp-archive-cities-main-section rp-background-grey">
	    <div class="rp-archive-cities-data" id="rp-archive-cities-data">

	        <section id="city_cards" class="rp-archive-cities-loop flex-grid">

				<?php foreach ($citiesTerms as $city) : if( $city->count ) : ?>
					<div class="is-rp-city flex-grid-thirds">
						<a href="<?php echo get_term_link($city->term_id, 'prop_city'); ?>">
							<figure class="rp-city-figure">
								<?php if (wp_get_attachment_image_url( get_term_meta($city->term_id)['showcase-taxonomy-image-id'][0],'full')) : ?>
									<img class="rp-city-image" src="<?php echo wp_get_attachment_image_url( get_term_meta($city->term_id)['showcase-taxonomy-image-id'][0],'full') ?>">
								<?php else : ?>
									<img class="rp-city-image" src="<?php echo $globalTaxCityImage ?>">
								<?php endif ?>
							</figure>
							 <section class="rp-city-details">
			                    <div class="rp-city-top">
			                    	<center>
			                        	<h2 class="rp-city-name" style="font-weight: bold; color: <?php echo $templateAccentColor; ?>;"><?php echo $city->name; ?></h2>
			                        </center>
			                    </div> 
			                </section>
			            </a>
			        </div>
				<?php endif; endforeach; ?>

			</section>

			<?php if ( $searchTemplateIsActive != '' ) : ?>
				<a href="<?php echo get_home_url(); ?>/search/" class="rp-button rp-button-alt rp-city-archive-search-cta">Search Apartments</a>
			<?php else: ?>
				<a href="<?php echo $requestInfoURL; ?> " class="rp-button rp-button-alt rp-city-archive-search-cta">Request Info</a>	
			<?php endif; ?>
		</div>
	</div>


</section>

<?php get_footer(); ?>