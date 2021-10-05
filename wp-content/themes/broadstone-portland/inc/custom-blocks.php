<?php
add_action('acf/init', 'my_acf_init_block_types');
function my_acf_init_block_types()
{

    // Check function exists.
    if (function_exists('acf_register_block_type')) {

        // register a testimonial block.
        acf_register_block_type(array(
            'name'              => 'hero-block',
            'title'             => __('Hero'),
            'description'       => __('Custom Homepage Hero'),
            'render_template'   => 'template-parts/blocks/hero-block.php',
            'category'          => 'formatting',
            'icon'              => 'admin-comments',
        ));
        acf_register_block_type(array(
            'name'              => 'content-split',
            'title'             => __('Content Split'),
            'description'       => __('Content Split'),
            'render_template'   => 'template-parts/blocks/content-split-block.php',
            'category'          => 'formatting',
            'icon'              => 'admin-comments',
        ));
        acf_register_block_type(array(
            'name'              => 'masonry-gallery-block',
            'title'             => __('Masonry Gallery'),
            'description'       => __('Masonry Gallery Hero'),
            'render_template'   => 'template-parts/blocks/gallery-masonry-block.php',
            'category'          => 'formatting',
            'icon'              => 'admin-comments',
        ));
        acf_register_block_type(array(
            'name'              => 'cta-block',
            'title'             => __('Call to Action'),
            'description'       => __('Call to Action'),
            'render_template'   => 'template-parts/blocks/cta-block.php',
            'category'          => 'formatting',
            'icon'              => 'admin-comments',
        ));
        acf_register_block_type(array(
            'name'              => 'properties-map-block',
            'title'             => __('Properties Map'),
            'description'       => __('Properties Map'),
            'render_template'   => 'template-parts/blocks/properties-map-block.php',
            'category'          => 'formatting',
            'icon'              => 'admin-comments',
        ));
        acf_register_block_type(array(
            'name'              => 'floor-plans-block',
            'title'             => __('Floor Plans'),
            'description'       => __('Floor Plans'),
            'render_template'   => 'template-parts/blocks/floor-plans-block.php',
            'category'          => 'formatting',
            'icon'              => 'admin-comments',
        ));
        acf_register_block_type(array(
            'name'              => 'blog-block',
            'title'             => __('Blog Posts'),
            'description'       => __('Blog Posts'),
            'render_template'   => 'template-parts/blocks/blog-block.php',
            'category'          => 'formatting',
            'icon'              => 'admin-comments',
        ));
        acf_register_block_type(array(
            'name'              => 'gallery-block',
            'title'             => __('Gallery'),
            'description'       => __('Gallery'),
            'render_template'   => 'template-parts/blocks/gallery-block.php',
            'category'          => 'formatting',
            'icon'              => 'admin-comments',
        ));
    }
}
