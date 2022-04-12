<?php
/**
 * Template Name: RentPress Single Floorplan
 *
 * @package RentPress Templates
 */
get_header(); ?>
</header>

<main class="main-content" role="main">
	<section class="clearfix">
		<?php while ( have_posts() ) : the_post(); 
			// the_content(); 	
			echo do_shortcode('[rentpress_single_floorplan id='. get_the_ID() .'][/rentpress_single_floorplan]');
		endwhile; ?>
	</section>
</main>

<?php get_footer(); ?>