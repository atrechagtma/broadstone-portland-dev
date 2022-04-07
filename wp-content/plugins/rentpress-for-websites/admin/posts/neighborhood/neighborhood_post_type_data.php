<?php

function rentpress_getAllNeighborhoods()
{

    $all_property_codes = array();

    $args = [
        'post_type' => 'rentpress_hood',
        'post_status' => ['publish', 'pending', 'draft'],
        'posts_per_page' => -1
    ];
    $neighborhoods = get_posts( $args );

    foreach ($neighborhoods as $neighborhood) {
        $all_property_codes[] = array('name' => $neighborhood->post_title, 'id' => $neighborhood->ID);
    }

    return $all_property_codes;
}