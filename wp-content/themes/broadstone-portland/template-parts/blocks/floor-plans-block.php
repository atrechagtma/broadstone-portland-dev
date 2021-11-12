<?php

/**
 * Floor Plans Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'floor-plans-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'floor-plans-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$plan_types = get_field('plan');
?>

<section class="block <?php echo esc_attr($className); ?> has-bg-img <?php echo empty($bg_image) ? 'theme-dark' : 'theme-light'; ?>" id="<?php echo esc_attr($id); ?>">
    <div class="container"> 
        <ul class="tabs nav nav-tabs row" role="tablist">
        <?php
        $imgSliders = [];
        foreach ($plan_types as $ptk => $plan_type) : ?>
            <li class="nav-item"><a class="nav-link <?= $ptk == 0 ? 'active' : '' ?>" data-bs-toggle="tab" role="tab" href="#tab-index-<?= $ptk; ?>"><?= esc_html($plan_type['slider_title']) ?></a></li>
        <?php endforeach; ?>
        <?php if (get_field('site_map')) : ?>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" role="tab" href="#tab-site-map">Site Map</a></li>
        <?php endif ?>
        </ul>

        <div class="tab-content">
        <?php
        foreach ($plan_types as $ptk => $plan_type) : ?>
            <div class="tab-item tap-pane swiper swiper-floorplans" data-index="tab-index-<?= $ptk; ?>">
                <div class="swiper-wrapper">
                <?php
                $floor_plans = $plan_type['floor_plans'];
                foreach ($floor_plans as $fpk => $fp) :
                ?>
                    
                    <div class="slider-item swiper-slide" role="tabpanel">
                    <div class="container">
                    <div class="row">
                        <div class="slider-column col-md-6 text-center d-flex flex-column justify-content-center">
                            <div class="slider-header">
                                <h3><?= esc_html($fp['title']) ?></h3>
                                <hr />
                            </div>
                            <div class="the-content">
                                <?= wp_kses_post($fp['content']) ?>
                            </div>
                        </div>
                        <div class="slider-column col-md-6">
            
                            <!-- Floorplan Carousel -->
                            <div class="swiper fp-images swiper-fp-images<?= $ptk.$fpk ?>" >
                                <div class="swiper-wrapper">

                                <?php $fpimages = $fp['floor_plan_images']; ?>
                                <?php if ( $fpimages ) :?>
                                    <?php foreach ($fpimages as $fpik => $fpimage) : ?>

                                        <?php // var_dump($fpimage['floor_plan_image']); ?>

                                            <div class="swiper-slide">
                                                <a href="#" data-featherlight="<?= esc_url($fpimage['floor_plan_image']['url']); ?>">
                                                    <i class="fas fa-expand-arrows-alt"></i>
                                                    <img src="<?= esc_url($fpimage['floor_plan_image']['sizes']['large']) ?>" alt="<?= esc_attr($fpimage['floor_plan_image']['alt']) ?>" />
                                                </a>
                                            </div>

                                            <?php endforeach; ?>
                                            <?php $imgSliders[] = $ptk.$fpk ?>
                                        </div>                                   
                                    <?php endif; ?>
                                    <div class="swiper-pagination dots-<?= $ptk.$fpk ?>"></div>

                                </div>

                            <!--/ Floorplan Carousel -->
                        </div><!-- .slider-col-right -->
                        </div>
                    </div>
                    </div><!-- .slider-item -->

                <?php endforeach ?>
                </div><!-- .swiper-wrapper -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div><!-- .tab-item -->
        <?php endforeach ?>
        <div class="tab-item tap-pane" data-index="tab-site-map">
        <?php $site_map = get_field('site_map') ?>
            <div class="container">
            <div class="row">
            <div class="col-12">
                <a href="#" data-featherlight="<?= $site_map['url'] ?>">
                    <img src="<?= $site_map['url'] ?>" alt="<?= $site_map['alt'] ?>">
                </a>
            </div>
            </div>
        </div>
        </div>
        </div><!-- .tab-content -->
    </div><!-- div -->
</section>

<script>
    window.onload = () => {
        const tabs = document.querySelectorAll('#<?php echo $id; ?> .tabs a');
        const tabsArr = Array.from(tabs)
        const allTabs = document.querySelectorAll('#<?php echo $id; ?> .tab-item')
        const allTabsArr = Array.from(allTabs)

        // initialize swiperTabs
        const swiperTabs = new Swiper('.swiper-floorplans', {
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        <?php foreach ($imgSliders as $imgSlider) : ?>
        const swiperFpImages<?=$imgSlider?> = new Swiper('.swiper-fp-images<?=$imgSlider?>', {
            debugger: true,
            autoheight: true,
            pagination: {
                el: ".dots-<?=$imgSlider?>",
                clickable: true,
            },
        });
        <?php endforeach ?>

        tabsArr.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                let href = e.target.href?.split('#')[1];
                const activeTab = document.querySelector(`div[data-index=${href}`);
                allTabsArr.forEach(tab => {
                    tab.classList.remove('active')
                    tab.classList.add('inactive')
                })
                activeTab.classList.remove('inactive')
                activeTab.classList.add('active')
            })
        })

        allTabsArr.forEach((tab, index) => {
            if (index === 0) {
                allTabsArr[index].classList.add('active')
            }
            else {
                allTabsArr[index].classList.add('inactive')
            }
        })
    }
</script>

<style type="text/css">
.fp-images {
    padding-bottom: 40px;
}
.slider-column .the-content {
        text-align: left;
    }

    .swiper-container {
        /* margin: auto; */
        /* overflow: visible; */
    }

    .swiper-button-next,
    .swiper-container-rtl .swiper-button-prev {
        right: 50px; 
    }

    .swiper-button-prev,
    .swiper-container-rtl .swiper-button-next {
        left: 50px; 
    }

    .floor-plans-block .nav-tabs {
        border: none;
    }

    .swiper-button-next:after, .swiper-button-prev:after {
        font-size: 2.2em;
        font-weight: 700;
        color: #294954;
    }

    .swiper-pagination-bullet {
        background-color: transparent;
        border: 1px solid #294954;
        opacity: 1;
        border-radius: 50%;
        width: 12px;
        height: 12px;
    }

    .swiper-pagination-bullet-active {
        background-color: #294954;
    }

    #<?php echo $id; ?> {
        margin-top: 0;
        position: relative;
        padding: 140px 0;
    }
    #<?php echo $id; ?> ul.tabs {
        list-style: none;
        max-width: 100%;
        padding: 0;
        display: flex;
        justify-content: space-around;
        align-items: center;
        margin-bottom: 60px;
    }
    #<?php echo $id; ?> ul.tabs li {
        margin: 0 0 20px;
        flex: 1 0 25%;
        max-width: 25%;
    }
    #<?php echo $id; ?> ul.tabs li a {
        border: solid 4px #294954;
        background-color: #fff;
        border-radius: 12px;
        padding: 15px 1.5vw;
        color: #294954;
        text-transform: uppercase;
        text-decoration: none;
        font-weight: 600;
        font-size: 20px;
        width: 100%;
        display: inline-block;
        text-align: center;
        box-shadow: 0px 3px 6px #00000029;
        opacity: .6;
        transition: all .2s ease-in-out;
    }
    #<?php echo $id; ?> ul.tabs li a:hover {
        color: #294954;
        box-shadow: 0px 0px 0px #00000029;
        opacity: 1;
    }
    #<?php echo $id; ?> ul.tabs li a.nav-link.active {
        background-color: #294954;
        color: #fff;
        opacity: 1;
    }
    #<?php echo $id; ?> .slider-item {
        display: flex;
        align-items: center;
    }
    .slider-item .slider-column {
        flex: 1;
    }

    .slider-col-left {
        max-width: 60%;
        text-align: center;
    }

    .slider-col-right {
        max-width: 40%;
        padding-right: 100px;
    }
    #<?php echo $id; ?> .slider-header hr {
        border: 0;
        border-bottom: solid 2px #AA5B3C; 
        width: 65%;
        background: transparent;
        margin-left: auto;
        margin-right: auto;
    }
    #<?php echo $id; ?> .slider-header h3 {
        font-size: min(4vw, 72px);
    }
    #<?php echo $id; ?> .the-content {
        font-size: 24px;
        font-weight: 400;
        line-height: 1;
        text-align: center;
    }
    #<?php echo $id; ?> .tab-item.inactive {
        display: none;
    }
    #<?php echo $id; ?> .tab-item.active {
        display: block;
    }

    /* Carousel Styles */
    .swiper-wrapper .fas {
        font-size: 2.5em;
        position: absolute;
        bottom: 10px;
        right: 10px;
        color: #ccc;
        opacity: .6;
    }
    @media (max-width: 992px) {
        
        #<?php echo $id; ?> ul.tabs {
            flex-direction: column;
        }
        #<?php echo $id; ?> ul.tabs li {
            width: 100%;
        }
    }
    @media (max-width: 768px) {
        #<?php echo $id; ?> {
            padding: 40px 0;
        }

        #<?php echo $id; ?> ul.tabs {
            margin: 30px;
        }
        #<?php echo $id; ?> ul.tabs li {
            width: 100%;
            flex-basis: 100%;
            max-width: 100%;
        }
        #<?php echo $id; ?> .slider-header h3 {
            font-size: 46px;
        }
        #<?php echo $id; ?> .slider-item {
            flex-direction: column;
        }
        #<?php echo $id; ?> .slider-item .slider-column {
            max-width: 100%;
        }
        #<?php echo $id; ?> .the-content {
            margin-bottom: 40px;
        }
    }

    @media (min-width: 360px){
        .swiper-container {
            padding-left: 50px;
            padding-right: 50px;
        }
    }

    @media (min-width: 640px){
        .swiper-container {
            padding-left: 50px;
            padding-right: 50px;
        }
    }

    @media (min-width: 768px){
        .swiper-container {
            padding-left: 100px;
            padding-right: 100px;
        }
    }

    @media (min-width: 1024px){
        .swiper-container {
            padding-left: 150px;
            padding-right: 150px;
        }
    }

    @media (min-width: 1200px) {
        .swiper-container {
            padding-left: 200px;
            padding-right: 200px;
        }
    }
</style>