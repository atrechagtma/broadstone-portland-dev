<?php

function rentpress_getAllNeighborhoods()
{

    $all_neighborhoods = array();

    $args = [
        'post_type' => 'rentpress_hood',
        'post_status' => ['publish', 'pending', 'draft'],
        'posts_per_page' => -1,
    ];
    $neighborhoods = get_posts($args);

    foreach ($neighborhoods as $neighborhood) {
        $all_neighborhoods[$neighborhood->post_title] = $neighborhood->ID;
    }

    return $all_neighborhoods;
}