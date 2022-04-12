<?php
/**
 * Template Name: RentPress Amenity Term
 *
 * @package RentPress Templates
 */
add_action( 'wp_enqueue_scripts', 'my_specific_style' );
function my_specific_style(){
  wp_enqueue_style( 'city-archive', RENTPRESS_PLUGIN_PUBLIC_TEMPLATES_DIR . 'template-city-archive-styles.css');
}

get_header();
$options = get_option('rentpress_options');
$city_image = $options['rentpress_default_city_image_section'];
$term_meta = get_term_meta(get_queried_object()->term_id);
$taxonomy_name = get_queried_object()->taxonomy;
$featured_image = isset($term_meta['rentpress_custom_field_'. $taxonomy_name .'_image'][0]) ? $term_meta['rentpress_custom_field_'. $taxonomy_name .'_image'][0] : '';
$short_description = isset($term_meta['rentpress_custom_field_'. $taxonomy_name .'_short_description'][0]) ? $term_meta['rentpress_custom_field_'. $taxonomy_name .'_short_description'][0] : '';
$extended_description = isset($term_meta['rentpress_custom_field_'. $taxonomy_name .'_extended_content'][0]) ? $term_meta['rentpress_custom_field_'. $taxonomy_name .'_extended_content'][0] :'';
$shortcode = isset($term_meta['rentpress_custom_field_'. $taxonomy_name .'_shortcode'][0]) ? $term_meta['rentpress_custom_field_'. $taxonomy_name .'_shortcode'][0] : '';

$allCities = get_terms([
    'taxonomy' => 'city',
    'hide_empty' => true,
]);
$cities = [];
if (isset($allCities) && is_countable($allCities) ? count($allCities) > 3 : '') {
	$citiesInt = array_rand($allCities ,3);
} else {
	$citiesInt = array_keys($allCities);
}
foreach ($citiesInt as $cityInt) {
	$cities[] = $allCities[$cityInt];
}
?>
</header>

<main class="main-content" role="main">
  <div class="rentpress-page-hero">
    <header class="rentpress-page-hero-full">
      <h1 class="rentpress-page-title"><?php echo 'Apartments With<br />' . wp_kses_post(get_queried_object()->name); ?>
      </h1>
      <img src="<?php echo esc_url($featured_image ? $featured_image : $city_image); ?>" class="lazyload" alt="">
    </header>
  </div>

  <section class="clearfix rentpress-term-short-description">
    <div class="container">
      <?php echo wp_kses_post($short_description); ?>
    </div>
  </section>

  <section class="clearfix rentpress-term-properties">
    <?php echo do_shortcode('[rentpress_property_search terms="'. get_queried_object()->name .'" HIDEFILTERS=true][/rentpress_property_search]'); ?>
  </section>

  <section class="clearfix rentpress-extended-term-description">
    <div class="container">
      <?php echo wp_kses_post($extended_description); ?>
    </div>
  </section>

  <?php if($shortcode) : ?>
  <section class="clearfix rentpress-term-shortcode">
    <div class="container">
      <?php echo do_shortcode($shortcode); ?>
    </div>
  </section>
  <?php endif ; ?>

  <section>
    <h3 style="text-align: center;">
      Explore Other Options
    </h3>
    <div class="city-term-grid">
      <?php foreach ($cities as $city) :
					$meta = get_term_meta($city->term_id);
					$image = isset($meta['rentpress_custom_field_city_image'][0]) && !empty($meta['rentpress_custom_field_city_image'][0]) ? $meta['rentpress_custom_field_city_image'][0] : $city_image;
					?>

      <div class="is-rp-city flex-grid-thirds">
        <a href="<?php echo esc_url(get_term_link($city->term_id)); ?>">
          <figure class="rp-city-figure">
            <img class="rp-city-image" src="<?php echo esc_url($image); ?>"
              alt="View of <?php echo wp_kses_post($city->name); ?>">
          </figure>
          <section class="rp-city-details">
            <div class="rp-city-top">
              <h2 class="rp-city-name" style="font-weight: bold; color: #007ff3;">
                <?php echo wp_kses_post($city->name); ?></h2>
            </div>
          </section>
        </a>
      </div>

      <?php endforeach ; ?>
    </div>
  </section>

</main>

<?php get_footer(); ?>