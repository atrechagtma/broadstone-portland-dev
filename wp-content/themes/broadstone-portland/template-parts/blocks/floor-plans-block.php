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
$plans = get_field('plan');
?>

<section class="block <?php echo esc_attr($className); ?> has-bg-img <?php echo empty($bg_image) ? 'theme-dark' : 'theme-light'; ?>" id="<?php echo esc_attr($id); ?>">
    <div> 
        <ul class="tabs nav nav-tabs" role="tablist"> 
        <?php
        $count = 0;
        foreach ($plans as $plan) {
        $count++; ?>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" role="tab" href="#tab-index-<?php echo $count; ?>"><?php echo $plan['slider_title']; ?></a></li>
        <?php
        }
        ?>
        </ul>
        <div class="tab-content">
        <?php
        $count = 0;
        foreach ($plans as $plan) {
        $count++; ?>
            <div class="tab-item tap-pane swiper-container" data-index="tab-index-<?php echo $count; ?>">
                <div class="swiper-wrapper">
                <?php
                    $floor_plans = $plan['floor_plans'];
                    foreach ($floor_plans as $k => $fp) :
                ?>
                    <div class="slider-item swiper-slide" role="tabpanel">
                        <div class="slider-column slider-col-left">
                            <div class="slider-header">
                                <h3><?php echo $fp['title']; ?></h3>
                                <hr />
                            </div>
                            <div class="the-content">
                                <?php echo $fp['content']; ?>
                            </div>
                        </div>
                        <div class="slider-column slider-col-right">
            
                            <!-- Floorplan Carousel -->
                            <div id="floorplanCarousel-<?php echo $k; ?>-<?php echo $count; ?>" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">

                                <?php if ($fp['floor_plan_images']) :?>
                                    <?php $i=0; foreach ($fp['floor_plan_images'] as $fpimage) :?>
                                        <?php if($fpimage['floor_plan_image']['sizes']['large']) :?>
                                            <div class="carousel-item <?php if( $i==0 ){ echo 'active'; };?>">
                                                <a role="button" class="gallery_image" href="<?php echo esc_url($fpimage['floor_plan_image']['sizes']['large']); ?>" data-bs-toggle="modal" data-bs-target="#floorplan-modal-<?php if( $i==0 ){ echo $i; };?>">
                                                    <i class="fas fa-expand-arrows-alt"></i>
                                                    <img src="<?php echo esc_url($fpimage['floor_plan_image']['sizes']['large']); ?>" alt="<?php echo esc_attr($fpimage['floor_plan_image']['alt']); ?>" />
                                                </a>
                                            </div>
                                            <?php $i++; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if ($i > 1) : ?>
                                    <div class="carousel-indicators">
                                    <?php $j=0; foreach ($fp['floor_plan_images'] as $fpimage) :?>
                                        <button type="button" data-bs-target="#floorplanCarousel-<?php echo $k; ?>-<?php echo $count; ?>"
                                        data-bs-slide-to="<?php echo $j; ?>" class="<?php if( $j==0 ){ echo 'active'; };?>" aria-current="<?php if( $k==0 ){ echo 'active'; };?>"
                                        ></button>
                                    <?php $j++; endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <!--/ Floorplan Carousel -->

                        </div><!-- .slider-col-right -->
                    </div><!-- .slider-item -->

                <?php endforeach; ?>
                </div><!-- .swiper-wrapper -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div><!-- .tab-item -->
        <?php
        }
        ?>
        </div><!-- .tab-content -->
    </div><!-- div -->
</section>

 <!-- <?php var_dump ($fpimage['floor_plan_image']); ?> -->
<script>
    window.onload = () => {
        const tabs = document.querySelectorAll('#<?php echo $id; ?> .tabs a');
        const tabsArr = Array.from(tabs)
        const allTabs = document.querySelectorAll('#<?php echo $id; ?> .tab-item')
        const allTabsArr = Array.from(allTabs)

        // initialize swiper
        new Swiper('.tab-item', {
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

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
    .slider-column .the-content {
        text-align: left;
    }

    .swiper-container {
        margin: auto;
        overflow: visible;
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
        /* background-color: #294954; */
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
    #<?php echo $id; ?> .slider-item .slider-column {
        flex: 1;
    }

    #<?php echo $id; ?> .slider-item .slider-col-left {
        max-width: 60%;
        text-align: center;
    }

    #<?php echo $id; ?> .slider-item .slider-col-right {
        max-width: 40%;
    }

    #<?php echo $id; ?> .slider-item .slider-column img {
        width: 100%;
        height: auto;
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
    #<?php echo $id; ?> .the-content a {
        margin-top: 20px;
        border: solid 3px #294954;
        border-radius: 12px;
        padding: 13px 1.5vw;
        color: #294954;
        text-transform: uppercase;
        text-decoration: none;
        font-weight: bold;
        font-size: 16px;
        display: inline-block;
        background-color: #fff;
        
    }

    #<?php echo $id; ?> .the-content a:after {
        /* content: " \2794"; */
    }

    #<?php echo $id; ?> .the-content a:hover {
        color: rgba(41, 73, 84, .5);
        text-decoration: underline;
    }
    #<?php echo $id; ?> .the-content a:active {
        background-color: #294954;
        color: white;
    }
    #<?php echo $id; ?> .tab-item.inactive {
        display: none;
    }
    #<?php echo $id; ?> .tab-item.active {
        display: block;
    }

    /* Carousel Styles */
    .swiper-wrapper .carousel-indicators [data-bs-target] {
        background-color: transparent;
        border: 1px solid #294954;
        border-radius: 50%;
        width: 12px;
        height: 12px;
    }

    .swiper-wrapper .active[data-bs-target] {
        background: #294954;
    }

    .swiper-wrapper .carousel-indicators {
        bottom: -80px;
    }

    .swiper-wrapper .carousel-item .fas {
        font-size: 2.5em;
        position: absolute;
        bottom: 10px;
        right: 10px;
        color: #ccc;
        opacity: .6;
    }

    .floorplan-modal {
        max-width: inherit;
    }

    .editor-styles-wrapper #<?php echo $id; ?> .tab-item:not(:first-child),
    .editor-styles-wrapper #<?php echo $id; ?> .slider-item:not(:first-child) {
        display: none;
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
        #<?php echo $id; ?> .the-content a {
            font-size: 20px;
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