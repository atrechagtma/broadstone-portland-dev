<?php 
    if (!$isShortcode) :
        if ($rentPressOptions->getOption('show_floorplans_grid_featured_image') === 'true') : 

            $featuredImageText = $rentPressOptions->getOption('floorplans_grid_featured_image_text');

            if(has_post_thumbnail()) {
                $img = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
                $featuredImg = $img[0];
            } else {
                $featuredImg = "https://placehold.it/1920x538?text=Featured%20Img";
            }?>

            <div class="rp-featured-image" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo $featuredImg ?>');">

                <div class="rp-featured-text">
                    <h1><?php echo $featuredImageText; ?></h1>
                </div>

            </div>

        <?php endif; 

        echo rp_archive_fp_wordpressLoop($show_content_top);
        
    endif;

    if ($rentPressOptions->getOption('hide_floorplan_availability_counter') == true) : ?>
        <style type="text/css">
            .rp-num-avail {
                display: none;
            }
        </style>
<?php endif; ?>

<section class="rp-archive-floorplans rentpress-core-container" id="rp_archive_floorplans">

    <section class="rp-archive-fp-container clearfix">

        <nav class="rp-archive-fp-nav">

            <div class="rp-archive-fp-filter-nav-section">
                <span class="rp-is-filter">
                    <span class="rp-is-toggle rp-is-open" id="rp-archive-fp-toggle-filters"> Hide Filters</span>
                </span>
            </div>
            
            <div class="rp-archive-fp-nav-section">
                <span class="is-matching" style="text-align:center"><strong id="floorplans_count" ></strong> Matching Floor Plans</span>
                <span class="is-sort">Sort 
                    <select name="" id="floorplan_sort" onchange="floorplan_sort()">
                        <option value="avail:asc">Soonest Available</option>
                        <option value="rent:asc" <?php echo $hide_pricing; ?>>Rent: Low to High</option>
                        <option value="rent:desc" <?php echo $hide_pricing; ?>>Rent: High to Low</option>
                        <option value="sqft:asc">SQFT: Low To High</option>
                        <option value="sqft:desc">SQFT: High to Low</option>
                        <option value="beds:asc">Bedrooms</option>
                    </select>
                </span>
            </div>
        </nav>
        
        <nav class="rp-archive-fp-mobile-filter-header">
            <span class="rp-archive-fp-mobile-filter-title rp-button" id="rp-archive-fp-open-mobile-open-filters"><i class="rp-icon-equalizer"></i>Filter & Sort</span>
        </nav>

        <div class="rp-archive-fp-main-section">

            <?php require('archive-floorplans-filters.php'); ?>

            <div class="rp-is-open rp-archive-fp-data" id="rp-archive-fp-data">

                <section id="floorplan_cards" class="rp-archive-fp-loop">test
                    <!-- This section is constructed in the makeFpCard function of the archive-floorplans-basic.js file -->

                    <section id="rp-fp-load-more-dest"></section>

                    <footer class="rp-load-more-row" id="rp-fp-load-more-row">
                        <button class="rp-load-more-btn rp-button" id="rp-fp-load-more-btn">Load More</button>
                    </footer>

                </section>

            </div>

        </div>

    </section>

</section>

<?php
    if (!$isShortcode) {
        rp_archive_fp_wordpressLoop(!$show_content_top);
    }



