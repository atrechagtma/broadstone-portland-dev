<?php 
$taxonomy_name = isset(get_queried_object()->taxonomy) ? get_queried_object()->taxonomy : '';
if ($taxonomy_name == 'city' || $taxonomy_name == 'feature' || $taxonomy_name == 'amenity' || $taxonomy_name == 'property_type' || $taxonomy_name == 'pet') {
	include_once RENTPRESS_PLUGIN_DIR . 'public/templates/taxonomy-city.php';
} else {
	include_once get_template_directory() . '/original_taxonomy.php';
}