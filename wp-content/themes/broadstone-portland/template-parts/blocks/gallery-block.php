<?php

/**
 * Gallery Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'gallery-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'gallery-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$galleries = get_field('gallery');
?>

<section class="block <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
    <div class="container-fluid">
        <ul class="shuffle-filter">
            <li class='selected' data-target='all'>All</li>
            <?php foreach ($galleries as $gallery) { ?>
            <li data-target='<?php echo sanitize_title($gallery['slide_title']); ?>'>
                <?php echo $gallery['slide_title']; ?></li>
            <?php } ?>
        </ul>


        <ul class="shuffle-container">
            <?php foreach ($galleries as $gallery) : ?>
            <?php $images = $gallery['images']; ?>
            <?php foreach ((array) $images as $img) : ?>
            <li data-groups='["all","<?php echo sanitize_title($gallery['slide_title']); ?>"]'>
            <a href="<?php echo $img['url'] ;?>" data-lightbox="<?php echo $img['url'] ;?>">
            <img src="<?php echo $img['url']; ?>" alt="<?php echo $img['title']; ?>" width="<?php echo $img['width']; ?>" height="<?php echo $img['height']; ?>" />
            </a>
            </li>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>

    </div>

</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Shuffle/5.1.1/shuffle.js"></script>
<script>
    window.onload = function () {
        var Shuffle = window.Shuffle;
        var element = document.querySelector('.shuffle-container');

        var shuffleInstance = new Shuffle(element, {
            itemSelector: 'li'
        });


        $('.shuffle-filter li').on('click', function (e) {
            e.preventDefault();
            $('.shuffle-filter li').removeClass('selected');
            $(this).addClass('selected');
            var keyword = $(this).attr('data-target');
            shuffleInstance.filter(keyword);
        });

    }

</script>

<style>
    ul {
        margin: 0;
        text-align: center;
    }

    ul li {
        list-style: none;
    }

    /*======================*/
    /* ul .shuffle-filter */
    .shuffle-filter {
        padding: 0;
        margin: 0 auto;
        width: 100%;
        max-width: 100% !important;
    }

    @media (min-width: 798px) {
        .shuffle-filter {
            display: flex;
            justify-content: space-evenly;  
        }
    }

    .shuffle-filter li {
        padding: 10px;
        cursor: pointer;
        color: white;
        border-color: rgba(41, 73, 84, 1);
        background-color: rgba(41, 73, 84, 1);
        border-style: solid;
        border-width: 3px;
        border-radius: 12px;
        padding: 13px 1.5vw;
        text-transform: uppercase;
        text-decoration: none;
        font-weight: bold;
        text-align: center;
        box-shadow: 0px 3px 6px #00000029;
        margin-bottom: 15px;
        margin-left: 0;
        flex: 0 0 20%;
    }

    @media (max-width: 797px) {
        .shuffle-filter li {
            display: block;
        }
    }    

    .shuffle-filter li.selected {
        color: rgba(41, 73, 84, 1);
        border-color: rgba(41, 73, 84, 1);
        background-color: #fff;
    }

    /*======================*/
    /* ul shuffle-container*/
    .shuffle-container {
        padding: 0;
        width: 100%;
        max-width: 100% !important;
        text-align: center;
    }

    .shuffle-container li {
        display: inline-block;
        margin-left: 0;
    }

    .shuffle-container li img {
        display: inline-block;
        width: auto;
        max-width: 360px;
        /* height: 40vh; */
        margin: 30px;
    }

    @media (max-width: 797px) {
        .shuffle-container li img {
            max-width: 100%;
            width: 100%;
            margin: 0 0 20px 0;
        }
    }

</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js" integrity="sha512-k2GFCTbp9rQU412BStrcD/rlwv1PYec9SNrkbQlo6RZCf75l6KcC3UwDY8H5n5hl4v77IDtIPwOk9Dqjs/mMBQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.css" integrity="sha512-Woz+DqWYJ51bpVk5Fv0yES/edIMXjj3Ynda+KWTIkGoynAMHrqTcDUQltbipuiaD5ymEo9520lyoVOo9jCQOCA==" crossorigin="anonymous" referrerpolicy="no-referrer" />