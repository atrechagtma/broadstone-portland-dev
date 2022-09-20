<?php

/**
 * Gallery Masonry Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

global $post;
// Create id attribute allowing for custom "anchor" value.
$id = 'gallery-masonry-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'gallery-masonry-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$content = get_field('content');
$cta = get_field('cta');
$galleries = get_field('galleries');
$bg = get_field('background_color');
$layout = get_field('layout_type');
?>

<section class="block <?php echo esc_attr($className); ?> <?php echo empty($bg) ? 'theme-dark' : 'theme-light'; ?>" id="<?php echo esc_attr($id); ?>">
    <div class="masonry-with-columns <?php echo $layout; ?>">
        <?php if ($galleries): ?>
            <?php $count = 0; ?>
            <?php foreach ($galleries as $post):
                setup_postdata($post);
                $count++;
            ?>
                <div>
                    <a href="" data-lightbox="<?php echo $img['url'] ;?>">
                        <img src="<?php echo $post['url']; ?>" alt="<?php echo $post['title']; ?>" width="<?php echo $post['width']; ?>" height="<?php echo $post['height']; ?>" />
                    </a>    
                </div>
                <?php if (($layout == 'type1' && $count == 4) || $layout == 'type2' && $count == 1): ?>
                    <div class="the-content">
                        <?php echo $content; ?>
                        <a class="btn btn-primary" href="<?php echo esc_url($cta['url']); ?>" target="<?php echo esc_attr($cta['target']); ?>"><?php echo esc_html($cta['title']); ?></a>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>

    <style type="text/css">
        #<?php echo $id; ?> {
            background-color: <?php echo $bg; ?>;
            text-align: center;
            margin-top: 0;
        }

        #<?php echo $id; ?> .the-content h3 {
            color: inherit;
        }
        .the-content .btn.btn-primary {
            background-color: var(--nav-color-secondary);
            color: white;
            text-transform: uppercase;
            padding: 15px 55px;
            font-weight: bold;
            border-radius: 12px;
            margin-top: 60px;
        }
        .theme-light .the-content .btn.btn-primary {
            color: #294954;
            background-color: white;
            border: solid 3px white;
            box-shadow: 0px 3px 6px #00000029;
        }

        .theme-light .the-content .btn.btn-primary:hover {
            color: rgba(41,73,84, .5);
            box-shadow: 0px 0px 0px #00000029;
        }

        .theme-light .the-content .btn.btn-primary:active {
            color: rgba(41,73,84, .5);
            box-shadow: 0px 3px 6px #00000029;
        }

        @media (max-width: 992px) {
            .masonry-with-columns .the-content .btn.btn-primary {
                margin-top: 30px;
            }
        }
    .editor-styles-wrapper .masonry-with-columns.type1 {
        padding: 80px;
        display: grid;
        grid-template-columns: 2fr 3fr 2fr;
        grid-auto-rows: minmax(5vw, auto);
        gap: 80px;
        counter-reset: grid;
    }
    @media (max-width: 992px) {
        .editor-styles-wrapper .masonry-with-columns.type1 {
            display: flex;
            flex-direction: column;
        }
    }
    .editor-styles-wrapper .masonry-with-columns.type1 img {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        object-fit: cover;
    }
    @media (max-width: 992px) {
        .editor-styles-wrapper .masonry-with-columns.type1 img {
            position: static;
            width: 450px;
        }
    }
    .editor-styles-wrapper .masonry-with-columns.type1 > div {
        position: relative;
    }
    .editor-styles-wrapper .masonry-with-columns.type1 > div:nth-child(1) {
        grid-row: 0.1666666667;
    }
    .editor-styles-wrapper .masonry-with-columns.type1 > div:nth-child(2) {
        grid-row: 0.25;
    }
    .editor-styles-wrapper .masonry-with-columns.type1 > div:nth-child(3) {
        grid-row: 0.3333333333;
    }
    .editor-styles-wrapper .masonry-with-columns.type1 > div:nth-child(4) {
        grid-row: 0.6666666667;
    }
    .editor-styles-wrapper .masonry-with-columns.type1 > div:nth-child(5) {
        grid-row: 0.4444444444;
    }
    .editor-styles-wrapper .masonry-with-columns.type1 > div:nth-child(6) {
        grid-row: 0.3333333333;
    }
    .editor-styles-wrapper .masonry-with-columns.type2 {
        padding: 40px;
        display: grid;
        grid-template-columns: 2fr 3fr 2fr;
        grid-template-rows: repeat(4fr);
        gap: 40px;
        counter-reset: grid;
    }
    @media (max-width: 992px) {
        .editor-styles-wrapper .masonry-with-columns.type2 {
            display: flex;
            flex-direction: column;
        }
    }
    .editor-styles-wrapper .masonry-with-columns.type2 img {
        width: 100%;
    }
    @media (max-width: 992px) {
        .editor-styles-wrapper  .masonry-with-columns.type2 img {
            width: 450px;
        }
    }
    .editor-styles-wrapper .masonry-with-columns.type2 > div {
        position: relative;
    }
    .editor-styles-wrapper .masonry-with-columns.type2 > div:nth-child(1) {
        grid-row: 0.3333333333;
    }
    .editor-styles-wrapper .masonry-with-columns.type2 > div:nth-child(2) {
        grid-row: 0.5;
    }
    .editor-styles-wrapper .masonry-with-columns.type2 > div:nth-child(3) {
        grid-row: 0.3333333333;
    }
    .editor-styles-wrapper .masonry-with-columns.type2 > div:nth-child(4) {
        grid-row: 0.6666666667;
        align-self: end;
    }
    @media (max-width: 992px) {
        .editor-styles-wrapper .masonry-with-columns.type2 > div:nth-child(4) {
            align-self: center;
        }
    }
    </style>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js" integrity="sha512-k2GFCTbp9rQU412BStrcD/rlwv1PYec9SNrkbQlo6RZCf75l6KcC3UwDY8H5n5hl4v77IDtIPwOk9Dqjs/mMBQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.css" integrity="sha512-Woz+DqWYJ51bpVk5Fv0yES/edIMXjj3Ynda+KWTIkGoynAMHrqTcDUQltbipuiaD5ymEo9520lyoVOo9jCQOCA==" crossorigin="anonymous" referrerpolicy="no-referrer" />