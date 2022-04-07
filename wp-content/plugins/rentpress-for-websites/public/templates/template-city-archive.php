<?php /* Template Name: RentPress Cities Archive Template */

add_action( 'wp_enqueue_scripts', 'my_specific_style' );
function my_specific_style(){
  wp_enqueue_style( 'city-archive', RENTPRESS_PLUGIN_PUBLIC_TEMPLATES_DIR . 'template-city-archive-styles.css');
}
get_header();

$options = get_option('rentpress_options');
$city_image = $options['rentpress_default_city_image_section'];
$background = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
$background = isset($background[0]) ? $background[0] : null;
$content = apply_filters('the_content', get_post_field('post_content', $post->ID));
$cities = get_terms([
    'taxonomy' => 'city',
    'hide_empty' => true,
]);
foreach ($cities as $key => $city) {
	$cities[$key]->meta = get_term_meta($city->term_id);
}

?>
	<div class="rentpress-page-hero">
		<header class="rentpress-page-hero-full">
			<h1 class="rentpress-page-title"><?php echo wp_kses_post(get_queried_object()->name); ?></h1>
			<img src="<?php echo esc_url(isset($featured_image[0]->url) ? $featured_image[0]->url : $city_image); ?>" class="lazyload">
		</header>
	</div>

<main id="main" class="clearfix main-content" role="main">
	<?php if ($content) : ?>
		<section class="entry-content">
			<?php echo wp_kses_post($content); ?>
		</section>
	<?php endif ; ?>
	<section class="city-term-grid">
		<?php foreach ($cities as $city) : 
			$image = isset($city->meta['rentpress_custom_field_city_image'][0]) && !empty($city->meta['rentpress_custom_field_city_image'][0]) ? $city->meta['rentpress_custom_field_city_image'][0] : $city_image;
			?>

			<div class="is-rp-city flex-grid-thirds">
				<a href="<?php echo esc_url(get_term_link($city->term_id)); ?>">
					<figure class="rp-city-figure">
							<img class="rp-city-image" src="<?php echo esc_url($image); ?>">
					</figure>
					<section class="rp-city-details">
	                    <div class="rp-city-top">
                        	<h2 class="rp-city-name" style="font-weight: bold; color: #007ff3;"><?php echo wp_kses_post($city->name); ?></h2>
	                    </div>
	                </section>
	            </a>
	        </div>

		<?php endforeach ; ?>
	</section>
</main><!-- #main -->
<?php get_footer(); ?>