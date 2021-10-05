<?php

/**
 * Content Split Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'content-split-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'content-split-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$left = get_field('left_content') ?: '<h3>Here Goes your left side content</h3><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce eleifend, mi eu gravida vehicula, purus nisi feugiat mauris, sed sollicitudin ipsum turpis non odio. In pulvinar arcu ut sem hendrerit, et pretium urna mattis.</p>';
$right = get_field('right_content') ?: '<h3>Here Goes your right side content</h3><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce eleifend, mi eu gravida vehicula, purus nisi feugiat mauris, sed sollicitudin ipsum turpis non odio. In pulvinar arcu ut sem hendrerit, et pretium urna mattis.</p>';
$layout = get_field('layout') ?: 6;
$bg = get_field('background_color');
$color = get_field('text_color');
?>

<section class="block <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-md-<?php echo $layout; ?>">
				<div class="the-content">
					<?php echo $left; ?>
				</div>
			</div>
			<div class="col-md-<?php echo 12 - $layout; ?>">
				<div class="the-content">
					<?php echo $right; ?>
				</div>
			</div>
		</div>
	</div>
    <style type="text/css">
        #<?php echo $id; ?> {
            background-color: <?php echo $bg; ?>;
			color: <?php echo $color; ?>;
        }
		.editor-styles-wrapper .content-split-block {
			padding: 80px;
		}
		.editor-styles-wrapper .row {
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
			align-items: center;
		}
		.editor-styles-wrapper .row .col-md-1 {
			flex: 0 0 auto;
			width: 8.33333333%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-2 {
			flex: 0 0 auto;
			width: 16.66666667%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-3 {
			flex: 0 0 auto;
			width: 25%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-4 {
			flex: 0 0 auto;
			width: 33.33333333%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-5 {
			flex: 0 0 auto;
			width: 41.66666667%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-6 {
			flex: 0 0 auto;
			width: 50%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-7 {
			flex: 0 0 auto;
			width: 58.33333333%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-8 {
			flex: 0 0 auto;
			width: 66.66666667%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-9 {
			flex: 0 0 auto;
			width: 75%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-10 {
			flex: 0 0 auto;
			width: 83.33333333%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-11 {
			flex: 0 0 auto;
			width: 91.66666667%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .row .col-md-12 {
			flex: 0 0 auto;
			width: 100%;
			padding: 0 30px;
		}
		.editor-styles-wrapper .the-content h3 {
			font-size: min(4vw, 72px);
			text-transform: uppercase;
			font-weight: lighter;
		}
        #<?php echo $id; ?> .the-content a {
			background-color: #294954;
			color: white;
			text-transform: uppercase;
			padding: 15px 55px;
			font-weight: bold;
			border-radius: 12px;
			margin-top: 60px;
			display: inline-block;
			text-decoration: none;
			box-shadow: 0px 3px 6px #00000029;
		}
        #<?php echo $id; ?> .the-content a:hover {
			color: rgba(255, 255, 255, 0.5);
		}
        #<?php echo $id; ?> .the-content a:active {
			box-shadow: 0px 0px 0px #00000029;
		}
    </style>
</section>