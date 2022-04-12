<?php
/**
 * Template Name: RentPress Floorplan Archive
 *
 * @package RentPress Templates
 */
get_header(); ?>
</header>

<main class="main-content" role="main">
	<section class="clearfix">
		<?php while ( have_posts() ) : the_post(); 
			echo do_shortcode('[rentpress_floorplan_search sidebarfilters][/rentpress_floorplan_search]');
		endwhile; ?>
	</section>
</main>

<?php get_footer(); ?>