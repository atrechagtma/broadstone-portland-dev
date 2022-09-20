<?php

/*
 *  Register the Property Post Type
 */
function rentpress_property_init()
{
    $labels = array(
        'name' => _x('Properties', 'Post type general name', 'textdomain'),
        'singular_name' => _x('Property', 'Post type singular name', 'textdomain'),
        'menu_name' => _x('Properties', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('Property', 'Add New on Toolbar', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'add_new_item' => __('Add New Property', 'textdomain'),
        'new_item' => __('New Property', 'textdomain'),
        'edit_item' => __('Edit Property', 'textdomain'),
        'view_item' => __('View Property', 'textdomain'),
        'all_items' => __('All Properties', 'textdomain'),
        'search_items' => __('Search Properties', 'textdomain'),
        'parent_item_colon' => __('Parent Properties:', 'textdomain'),
        'not_found' => __('No properties found.', 'textdomain'),
        'not_found_in_trash' => __('No properties found in Trash.', 'textdomain'),
        'featured_image' => _x('Property Featured Image', 'Overrides the “Featured Image” phrase for this post type.', 'textdomain'),
        'set_featured_image' => _x('Set featured image', 'Overrides the “Set featured image” phrase for this post type.', 'textdomain'),
        'remove_featured_image' => _x('Remove featured image', 'Overrides the “Remove featured image” phrase for this post type.', 'textdomain'),
        'use_featured_image' => _x('Use as featured image', 'Overrides the “Use as featured image” phrase for this post type.', 'textdomain'),
        'archives' => _x('Property archives', 'The post type archive label used in nav menus. Default “Post Archives”.', 'textdomain'),
        'insert_into_item' => _x('Insert into property', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post).', 'textdomain'),
        'uploaded_to_this_item' => _x('Uploaded to this property', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post).', 'textdomain'),
        'filter_items_list' => _x('Filter properties list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”.', 'textdomain'),
        'items_list_navigation' => _x('Properties list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”.', 'textdomain'),
        'items_list' => _x('Properties list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”.', 'textdomain'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'apartments'),
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => RENTPRESS_MENU_POSITION + 1,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-building',
    );

    register_post_type('rentpress_property', $args);
}
add_action('init', 'rentpress_property_init', 10);

// Catch property delete hook and delete that same property from the refresh_model table
function rentpress_removeTrashedPropertyFromRefreshDatabase($post_id)
{
    // Since this hook catches all trashed posts, gotta check to see if it is the right type
    if ('rentpress_property' != get_post_type($post_id)) {
        return;
    }
    $property_code = get_post_meta($post_id, 'rentpress_custom_field_property_code', true);
    if (!empty($property_code)) {
        require_once RENTPRESS_PLUGIN_DATA_MODEL . 'refresh_model.php';
        rentpress_deleteRefreshDataForProperty($property_code);
    }
}
add_action('trashed_post', 'rentpress_removeTrashedPropertyFromRefreshDatabase');

// post list fields for properties
rentpress_add_custom_column_filters([
    'post_type' => 'rentpress_property',
    'field_name' => 'city',
    'has_column' => false,
    'column_config' => [
        'column_sort' => 'sort',
        'column_order' => 3,
        'column_header' => 'City',
    ],
    'field_config' => [
        'field_type' => 'term_select',
        'data_type' => 'term',
        'field_sort' => SORT_STRING,
        'taxonomy' => 'city',
        'data_compare' => '=',
        'field_placeholder' => 'Filter by City',
    ],
]);

rentpress_add_custom_column_filters([
    'post_type' => 'rentpress_property',
    'field_name' => 'neighborhood',
    'has_column' => true,
    'column_config' => [
        'allow_false' => true,
        'column_capitalization' => 'words',
        'column_header' => 'Primary Neighborhood',
        'column_display' => [
            [
                'compair_type' => '==',
                'compair_value' => '',
                'style' => 'background-color: red;',
                'replace' => 'No Neighborhood Set',
            ],
        ],
    ],
    'field_config' => [
        'field_type' => 'select',
        'data_type' => 'neighborhood',
        'field_sort' => SORT_STRING,
        'data_compare' => '=',
        'meta_key' => 'property_neighborhood_post_ids',
        'field_placeholder' => 'Neighborhood',
    ],
]);

rentpress_add_custom_column('Starting Price', 'price', 'rentpress_property', function ($post_id) {
    if (!count($GLOBALS['rentpressPostData']['properties'])) {
        require_once (RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php');
        $AllProperties = rentpress_getAllProperties();
        foreach ($AllProperties as $property) {
            $GLOBALS['rentpressPostData']['properties'][$property->property_post_id] = $property;
        }
    }
    $thisProp = $GLOBALS['rentpressPostData']['properties'][$post_id];
    $propRent = isset($thisProp->property_rent_type_selection_cost) ? $thisProp->property_rent_type_selection_cost : '';
    $rentDisplay = ($propRent > 100 && !empty($propRent)) ? '$' . esc_attr($propRent) : 'Bad Data';
    if (!empty($thisProp->property_disable_pricing)) {
        echo '<strong style="color: orange;">Disabled</strong>';
    } elseif ($rentDisplay == 'Bad Data') {
        echo '<strong style="color: red;">' . esc_html($rentDisplay) . '</strong><br /> Selected: ' . $thisProp->property_rent_type_selection;
        esc_attr_e($propRent);
    } else {
        esc_attr_e($rentDisplay);
    }
});