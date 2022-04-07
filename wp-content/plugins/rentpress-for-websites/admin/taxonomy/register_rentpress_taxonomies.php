<?php
function rentpress_register_taxonomy_amenity()
{
    $labels = [
        'name' => _x('Amenities', 'taxonomy general name'),
        'singular_name' => _x('Amenity', 'taxonomy singular name'),
        'menu_name' => __('Amenities'),
        'search_items' => __('Search Amenities'),
        'all_items' => __('All Amenities'),
        'parent_item' => __('Parent Amenity'),
        'parent_item_colon' => __('Parent Amenity:'),
        'edit_item' => __('Edit Amenity'),
        'update_item' => __('Update Amenity'),
        'add_new_item' => __('Add New Amenity'),
        'new_item_name' => __('New Amenity Name'),
    ];
    $args = [
        'hierarchical' => true, // make it hierarchical (like categories)
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'rewrite' => ['slug' => 'amenity'],
    ];
    register_taxonomy('amenity', ['rentpress_property'], $args);
}
add_action('init', 'rentpress_register_taxonomy_amenity');

function rentpress_register_taxonomy_feature()
{
    $labels = [
        'name' => _x('Features', 'taxonomy general name'),
        'singular_name' => _x('Feature', 'taxonomy singular name'),
        'menu_name' => __('Features'),
        'all_items' => __('All Features'),
        'edit_item' => __('Edit Feature'),
        'view_item' => __('View Feature'),
        'update_item' => __('Update Feature'),
        'add_new_item' => __('Add New Feature'),
        'new_item_name' => __('New Feature Name'),
        'search_items' => __('Search Features'),
        'parent_item' => __('Parent Feature'),
        'parent_item_colon' => __('Parent Feature:'),
    ];
    $args = [
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'rewrite' => ['slug' => 'feature'],
    ];
    register_taxonomy('feature', ['rentpress_property', 'rentpress_floorplan'], $args);
}
add_action('init', 'rentpress_register_taxonomy_feature');

function rentpress_register_taxonomy_city()
{
    $labels = [
        'name' => _x('Cities', 'taxonomy general name'),
        'singular_name' => _x('City', 'taxonomy singular name'),
        'menu_name' => __('Cities'),
        'all_items' => __('All Cities'),
        'edit_item' => __('Edit City'),
        'view_item' => __('View City'),
        'update_item' => __('Update City'),
        'add_new_item' => __('Add New City'),
        'new_item_name' => __('New City Name'),
        'search_items' => __('Search Cities'),
        'parent_item' => __('Parent City'),
        'parent_item_colon' => __('Parent City:'),
    ];
    $args = [
        'hierarchical' => true, // make it hierarchical (like categories)
        // 'capabilities' => array(
        // 'manage_terms' => '',
        // 'edit_terms' => '',
        // 'delete_terms' => '',
        // 'assign_terms' => '' // disallow assigning cities on properties since it's done via the feed
        // ),
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'city'],
    ];
    register_taxonomy('city', ['rentpress_property'], $args);
}
add_action('init', 'rentpress_register_taxonomy_city');

function rentpress_register_taxonomy_pet()
{
    $labels = [
        'name' => _x('Pets', 'taxonomy general name'),
        'singular_name' => _x('Pet', 'taxonomy singular name'),
        'menu_name' => __('Pet Policy'),
        'search_items' => __('Search Pets'),
        'all_items' => __('All Pets'),
        'parent_item' => __('Parent Pet'),
        'parent_item_colon' => __('Parent Pet:'),
        'edit_item' => __('Edit Pet Policy'),
        'view_item' => __('View Pet'),
        'update_item' => __('Update Pet Policy'),
        'add_new_item' => __('Add New Pet Policy'),
        'new_item_name' => __('New Pet Policy'),

    ];
    $args = [
        'hierarchical' => true, // make it hierarchical (like categories)
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'pets'],
    ];
    register_taxonomy('pet', ['rentpress_property'], $args);
}
add_action('init', 'rentpress_register_taxonomy_pet');

function rentpress_register_taxonomy_property_type()
{
    $labels = [
        'name' => _x('Property Types', 'taxonomy general name'),
        'singular_name' => _x('Property Type', 'taxonomy singular name'),
        'menu_name' => __('Property Types'),
        'search_items' => __('Search Property Types'),
        'all_items' => __('All Property Types'),
        'parent_item' => __('Parent Property Type'),
        'parent_item_colon' => __('Parent Property Type:'),
        'edit_item' => __('Edit Property Type'),
        'update_item' => __('Update Property Type'),
        'add_new_item' => __('Add New Property Type'),
        'new_item_name' => __('New Property Type Name'),
    ];
    $args = [
        'hierarchical' => true, // make it hierarchical (like categories)
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'communities'],
    ];
    register_taxonomy('property_type', ['rentpress_property'], $args);
}
add_action('init', 'rentpress_register_taxonomy_property_type');

require_once( RENTPRESS_PLUGIN_ADMIN_POSTS . 'taxonomy/cities_taxonomy_meta_setup.php' );
require_once( RENTPRESS_PLUGIN_ADMIN_POSTS . 'taxonomy/amenities_taxonomy_meta_setup.php' );
require_once( RENTPRESS_PLUGIN_ADMIN_POSTS . 'taxonomy/features_taxonomy_meta_setup.php' );
require_once( RENTPRESS_PLUGIN_ADMIN_POSTS . 'taxonomy/pets_taxonomy_meta_setup.php' );
require_once( RENTPRESS_PLUGIN_ADMIN_POSTS . 'taxonomy/property_types_taxonomy_meta_setup.php' );