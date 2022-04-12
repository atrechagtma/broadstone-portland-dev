<?php
/**
 * Template Name: RentPress Single Property
 *
 * @package RentPress Templates
 */
get_header(); ?>
</header>

<main class="main-content" role="main">
	<section class="clearfix">
		<?php while ( have_posts() ) : the_post(); 
			// the_content(); 	
			echo do_shortcode('[rentpress_single_property id='. get_the_ID() .'][/rentpress_single_property]');
		endwhile; ?>
	</section>
</main>

<?php get_footer(); ?>