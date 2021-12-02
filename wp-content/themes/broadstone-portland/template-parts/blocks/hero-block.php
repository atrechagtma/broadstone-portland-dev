<?php

/**
 * Hero Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'hero-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'hero-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$logo = get_field('logo');
$bg = get_field('background_image');
$title = get_field('title') ?: 'Your title here...';
$button_text = get_field('cta_button_text');
$link = get_field('cta_button_link');
?>

<section class="block <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
    <div class="text-center">
        <?php echo wp_get_attachment_image($logo, 'full'); ?>
        <h3><?php echo $title; ?></h3>
        <a href="<?php echo $link; ?>" class="btn"><?php echo $button_text; ?></a>

    </div>
    <style type="text/css">
        #<?php echo $id; ?> {
            background-image: url('<?php echo $bg; ?>');
            text-align: center;
            margin-top: 0;
        }
        .editor-styles-wrapper .hero-block{
            position: relative;
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            padding: 200px 140px 140px;
        }
        .editor-styles-wrapper .hero-block::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #f5f4f0;
            opacity: 0.7;
        }
        .editor-styles-wrapper .hero-block .text-center {
            position: relative;
        }
        .editor-styles-wrapper .hero-block h3 {
            font-size: min(4vw, 72px);
            text-transform: uppercase;
            font-weight: lighter;
            margin-bottom: 40px;
        }
        .editor-styles-wrapper .hero-block .btn {
            background-color: #294954;
            color: white;
            text-transform: uppercase;
            padding: 15px 2.8vw;
            font-size: 1vw;
            font-weight: bold;
            border-radius: 12px;
        }
    </style>
</section>