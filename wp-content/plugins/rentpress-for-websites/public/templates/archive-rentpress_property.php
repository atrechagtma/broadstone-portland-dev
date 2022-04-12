<?php
/**
 * Template Name: RentPress Property Search
 *
 * @package RentPress Templates
 */
get_header(); ?>
</header>

<main class="main-content" role="main">
	<section class="clearfix">
		<?php while ( have_posts() ) : the_post(); 
			echo do_shortcode('[rentpress_property_search][/rentpress_property_search]');
		endwhile; ?>
	</section>
</main>

<?php get_footer(); ?>