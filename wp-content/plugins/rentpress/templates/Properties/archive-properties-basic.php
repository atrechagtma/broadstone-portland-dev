<?php 
   /**
    * Template Name: Property Archive
    */

    global $rentPress_Service;

    $rentPressOptions = new rentPress_Options();
  
    get_header(); 

    while ( have_posts() ) :
        the_post(); 

        
        if ($rentPressOptions->getOption('show_property_grid_featured_image') === 'true') : 

            $featuredImageText = $rentPressOptions->getOption('property_grid_featured_image_text');

            if(has_post_thumbnail()) {
                $img = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
                $featuredImg = $img[0];
            } else {
                $featuredImg = "https://placehold.it/1920x538?text=Property%20Search";
            }?>

            <div class="rp-featured-image" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo $featuredImg ?>');">

                <div class="rp-featured-text">
                    <h1><?php echo $featuredImageText; ?></h1>
                </div>

            </div>

        <?php endif; 

        include RENTPRESS_PLUGIN_DIR . 'misc/template-schema/search-results-page-schema.php';

        if ($rentPressOptions->getOption('archive_properties_content_position') == 'archive_properties_content_top') { ?>
            <div <?php post_class('rp-default-wp-content'); ?>>
                <?php the_content(); ?>
            </div>
        <?php } 

        if ( $rentPressOptions->getOption('choose_archive_properties_template_file') == 'rentPress_choose_archive_advanced') {
            echo do_shortcode('[advanced_property_grid]');
        } else {
            echo do_shortcode('[property_grid]');
        }

        if ($rentPressOptions->getOption('archive_properties_content_position') == 'archive_properties_content_bottom') : ?>
            <div <?php post_class('rp-default-wp-content'); ?>>
                <?php the_content(); ?>
            </div>
        <?php endif; 

        
    endwhile; 
get_footer();

?>

