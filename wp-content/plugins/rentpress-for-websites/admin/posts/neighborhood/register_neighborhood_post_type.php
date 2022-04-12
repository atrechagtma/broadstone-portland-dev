<?php

/*
 *  Register the Neighborhood Post Type
 */
function rentpress_neighborhood_init()
{
    $labels = array(
        'name'                  => _x( 'Neighborhoods', 'Post type general name', 'textdomain' ),
        'singular_name'         => _x( 'Neighborhood', 'Post type singular name', 'textdomain' ),
        'menu_name'             => _x( 'Neighborhoods', 'Admin Menu text', 'textdomain' ),
        'name_admin_bar'        => _x( 'Neighborhood', 'Add New on Toolbar', 'textdomain' ),
        'add_new'               => __( 'Add New', 'textdomain' ),
        'add_new_item'          => __( 'Add New Neighborhood', 'textdomain' ),
        'new_item'              => __( 'New Neighborhood', 'textdomain' ),
        'edit_item'             => __( 'Edit Neighborhood', 'textdomain' ),
        'view_item'             => __( 'View Neighborhood', 'textdomain' ),
        'all_items'             => __( 'All Neighborhoods', 'textdomain' ),
        'search_items'          => __( 'Search Neighborhoods', 'textdomain' ),
        'parent_item_colon'     => __( 'Parent Neighborhoods:', 'textdomain' ),
        'not_found'             => __( 'No neighborhoods found.', 'textdomain' ),
        'not_found_in_trash'    => __( 'No neighborhoods found in Trash.', 'textdomain' ),
        'featured_image'        => _x( 'Neighborhood Featured Image', 'Overrides the “Featured Image” phrase for this post type.', 'textdomain' ),
        'set_featured_image'    => _x( 'Set featured image', 'Overrides the “Set featured image” phrase for this post type.', 'textdomain' ),
        'remove_featured_image' => _x( 'Remove featured image', 'Overrides the “Remove featured image” phrase for this post type.', 'textdomain' ),
        'use_featured_image'    => _x( 'Use as featured image', 'Overrides the “Use as featured image” phrase for this post type.', 'textdomain' ),
        'archives'              => _x( 'Neighborhood archives', 'The post type archive label used in nav menus. Default “Post Archives”.', 'textdomain' ),
        'insert_into_item'      => _x( 'Insert into neighborhood', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post).', 'textdomain' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this neighborhood', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post).', 'textdomain' ),
        'filter_items_list'     => _x( 'Filter neighborhoods list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”.', 'textdomain' ),
        'items_list_navigation' => _x( 'Neighborhoods list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”.', 'textdomain' ),
        'items_list'            => _x( 'Neighborhoods list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”.', 'textdomain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_rest'       => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'neighborhood' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => RENTPRESS_MENU_POSITION+3,
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
        'menu_icon'          => 'dashicons-location-alt',
    );

    register_post_type( 'rentpress_hood', $args );
}
add_action( 'init', 'rentpress_neighborhood_init', 10 );


// post list fields for neighborhood

rentpress_add_custom_column_filters([
  'post_type' => 'rentpress_hood',
  'field_name' => 'Properties',
  'has_column' => true,
  'field_config' => [
    'field_type' => 'select',
    'data_type' => 'prop_codes_nh',
    'meta_key' => 'rentpress_custom_field_neighborhood_property_codes',
    'data_compare' => 'LIKE',
    'field_placeholder' => 'Properties',
  ],
]);