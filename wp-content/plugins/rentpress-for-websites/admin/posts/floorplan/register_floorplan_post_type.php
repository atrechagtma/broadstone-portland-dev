<?php

/*
 *  Register the Floor Plan Post Type
 */
function rentpress_floorplan_init()
{
    $labels = array(
        'name'                  => _x( 'Floor Plans', 'Post type general name', 'textdomain' ),
        'singular_name'         => _x( 'Floor Plan', 'Post type singular name', 'textdomain' ),
        'menu_name'             => _x( 'Floor Plans', 'Admin Menu text', 'textdomain' ),
        'name_admin_bar'        => _x( 'Floor Plan', 'Add New on Toolbar', 'textdomain' ),
        'add_new'               => __( 'Add New', 'textdomain' ),
        'add_new_item'          => __( 'Add New Floor Plan', 'textdomain' ),
        'new_item'              => __( 'New Floor Plan', 'textdomain' ),
        'edit_item'             => __( 'Edit Floor Plan', 'textdomain' ),
        'view_item'             => __( 'View Floor Plan', 'textdomain' ),
        'all_items'             => __( 'All Floor Plans', 'textdomain' ),
        'search_items'          => __( 'Search Floor Plans', 'textdomain' ),
        'parent_item_colon'     => __( 'Parent Floor Plans:', 'textdomain' ),
        'not_found'             => __( 'No floor plans found.', 'textdomain' ),
        'not_found_in_trash'    => __( 'No floor plans found in Trash.', 'textdomain' ),
        'featured_image'        => _x( 'Floor Plan Featured Image', 'Overrides the “Featured Image” phrase for this post type.', 'textdomain' ),
        'set_featured_image'    => _x( 'Set featured image', 'Overrides the “Set featured image” phrase for this post type.', 'textdomain' ),
        'remove_featured_image' => _x( 'Remove featured image', 'Overrides the “Remove featured image” phrase for this post type.', 'textdomain' ),
        'use_featured_image'    => _x( 'Use as featured image', 'Overrides the “Use as featured image” phrase for this post type.', 'textdomain' ),
        'archives'              => _x( 'Floor Plan archives', 'The post type archive label used in nav menus. Default “Post Archives”.', 'textdomain' ),
        'insert_into_item'      => _x( 'Insert into floor plan', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post).', 'textdomain' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this floor plan', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post).', 'textdomain' ),
        'filter_items_list'     => _x( 'Filter floor plans list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”.', 'textdomain' ),
        'items_list_navigation' => _x( 'Floor Plans list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”.', 'textdomain' ),
        'items_list'            => _x( 'Floor Plans list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”.', 'textdomain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_rest'       => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'floorplans' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => RENTPRESS_MENU_POSITION+2,
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
        'menu_icon'          => 'dashicons-layout',
    );

    register_post_type( 'rentpress_floorplan', $args );
}
add_action( 'init', 'rentpress_floorplan_init', 10 );

// Catch floorplan delete hook and delete that same floorplan from the refresh_model table
function rentpress_removeTrashedFloorplanFromRefreshDatabase( $post_id ) {
  // Since this hook catches all trashed posts, gotta check to see if it is the right type
  if ( 'rentpress_floorplan' != get_post_type( $post_id ) ) {
      return;
  }

  require_once( RENTPRESS_PLUGIN_DATA_MODEL . 'refresh_model.php' );
  rentpress_deleteRefreshData();
}
add_action( 'trashed_post', 'rentpress_removeTrashedFloorplanFromRefreshDatabase' );

// post list fields for floorplans

rentpress_add_custom_column_filters([
  'post_type' => 'rentpress_floorplan',
  'field_name' => 'property',
  'has_column' => true,
  'column_config' => [
    'column_header' => 'Parent Property',
  ],
  'field_config' => [
    'field_type' => 'select',
    'data_type' => 'prop_codes_fp',
    'meta_key' => 'rentpress_custom_field_floorplan_parent_property_code',
    'field_sort' => SORT_STRING,
    'data_compare' => '=',
    'field_placeholder' => 'Property',
  ],
]);

rentpress_add_custom_column_filters([
  'post_type' => 'rentpress_floorplan',
  'field_name' => 'bedrooms',
  'has_column' => true,
  'column_config' => [
    'allow_false' => true,
    'column_header' => 'Beds & Baths',
    'column_sort' => 'sort',
    'column_order' => 3,
    'column_capitalization' => 'words',
    'column_display' => [
      [
          'compare_type' => '==',
          'compare_value' => '',
          'replace' => 'no beds set',
      ],
      [
          'compare_type' => '==',
          'compare_value' => '0',
          'replace' => 'Studio',
      ],
    ],
  ],
  'sort_config' => [
    'sort_type' => 'number',
    'sort_order' => 'asc',
  ],
  'field_config' => [
    'field_type' => 'select',
    'data_type' => 'bedbath',
    'field_sort' => SORT_NATURAL,
    'meta_key' => 'rentpress_custom_field_floorplan_bedroom_count',
    'data_compare' => '=',
    'field_placeholder' => 'Bedrooms',
  ],
]);

rentpress_add_custom_column_filters([
  'post_type' => 'rentpress_floorplan',
  'field_name' => 'availability',
  'has_column' => true,
  'sort_config' => [
    'sort_type' => 'number',
    'sort_order' => 'asc',
  ],
  'column_config' => [
    'allow_false' => true,
    'column_header' => 'Availability',
    'column_sort' => 'sort',
    'column_display' => [
      [
          'compare_type' => '==',
          'compare_value' => '',
          'replace' => 'No Units',
      ],
    ],
  ],
  'field_config' => [
    'field_type' => 'checkbox',
    'data_type' => 'availability',
    'meta_key' => 'rentpress_custom_field_floorplan_unit_count_available',
    'data_compare' => '<',
    'field_placeholder' => 'Available Units',
  ],
]);

// custom collumns
rentpress_add_custom_column('SQFT', 'sqft', 'rentpress_floorplan', function($post_id) {
  $thisMeta = get_post_meta($post_id);
  $fpSqFt = isset($thisMeta['rentpress_custom_field_floorplan_min_sqft'][0]) ? $thisMeta['rentpress_custom_field_floorplan_min_sqft'][0] : '';
  $sqftDisplay = ( $fpSqFt > 100 && !empty($fpSqFt)) ? $fpSqFt : 'Bad Data';
  if ($sqftDisplay  == 'Bad Data') {
      echo '<strong style="color: red;">Bad Data</strong><br/>';
      esc_attr_e($fpSqFt);
  } else {
      esc_attr_e($sqftDisplay);
  }
});

rentpress_add_custom_column('Starting Price', 'price', 'rentpress_floorplan', function($post_id) {
  $thisMeta = get_post_meta($post_id);
  $fpRent = isset($thisMeta['rentpress_custom_field_floorplan_rent_type_selection_cost'][0]) ? $thisMeta['rentpress_custom_field_floorplan_rent_type_selection_cost'][0] : '';
  $rentDisplay = ($fpRent > 100 && !empty($fpRent)) ? '$' . $fpRent : 'Bad Data';
  if (!empty($thisMeta['rentpress_custom_field_floorplan_rent_type_selection'][0]) && $thisMeta['rentpress_custom_field_floorplan_rent_type_selection'][0] == 'Disabled') {
      echo '<strong style="color: orange;">Disabled</strong>';
  } elseif ($rentDisplay  == 'Bad Data') {
      echo '<strong style="color: red;">Bad Data</strong><br/>';
      esc_attr_e($fpRent);
  } else {
      esc_attr_e($rentDisplay);
  }
});
