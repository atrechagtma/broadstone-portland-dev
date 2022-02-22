<?php 
    if (!$isShortcode) {
        echo rp_archive_fp_wordpressLoop($show_content_top);
    }

    if ($rentPressOptions->getOption('hide_floorplan_availability_counter') == true) : ?>
        <style type="text/css">
            .rp-num-avail {
                display: none;
            }
        </style>
<?php endif; ?>

<section class="rp-archive-floorplans rentpress-core-container">

    <section class="rp-archive-fp-container clearfix">

        <h1 align="center"><?= get_the_title(); ?></h1>
        <nav class="rp-archive-fp-mobile-filter-header">
        </nav>

        <div class="rp-archive-fp-main-section">

            <div class="rp-is-open" id="rp-archive-fp-data">

                <section id="floorplan_cards-noscript" class="rp-archive-fp-loop-noscript">
                     <?php foreach($all_floorplans as $floorplan): ?>
                        <div class='is-rp-fp'>
                            <a href="<?php echo $floorplan->post_url; ?>">
                            <figure>
                                <img src="<?php echo $floorplan->fpImg['image'] ?>" alt="<?php echo $floorplan->fpImg['alt']; ?>" >
                            </figure>
                                <footer class='rp-fp-details'>
                                    <h4 style="margin: 0px;">
                                        <?php echo $floorplan->post_title; ?> 
                                    </h4>
                                    <p>
                                        <?php
                                        $floorplan_bedrooms = (int)$floorplan->units[0]->beds;
                                        if ($floorplan_bedrooms == 1 ) {
                                            echo $floorplan_bedrooms . " Bedroom";
                                        } elseif ($floorplan_bedrooms == 0 ) {
                                            echo "Studio";
                                        } else {
                                            echo $floorplan_bedrooms . " Bedrooms";
                                        } ?>
                                         | 
                                        <?php echo $floorplan->sqft; ?> Sq. ft.
                                    </p>
                                    <p class='rp-starting-at'>
                                        <span>
                                            Starting at $<?php echo $floorplan->fpMinRent; ?>
                                        </span>
                                    </p>
                                    <p>
                                        <span class='rp-num-avail rp-primary-accent'>
                                            <?php if ($rentPressOptions->getOption('show_waitlist_ctas') == 'true' && ($floorplan->fpAvailUnitCount == '0') ) {
                                                echo "Join Waitlist";
                                            } else {
                                            echo $floorplan->fpAvailUnitCount; ?> Available <?php
                                            } ?>
                                        </span>
                                    </p>
                                </footer>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </section>

            </div>

        </div>

    </section>

</section>

<?php 
    if (!$isShortcode) {
        rp_archive_fp_wordpressLoop(!$show_content_top);
    }