<?php

/**
 * CTA Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

global $post;
// Create id attribute allowing for custom "anchor" value.
$id = 'cta-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'cta-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$content = get_field('content') ?: '<h3>Your Content goes here!</h3>';
$cta = get_field('cta_button');
$bg_image = get_field('background_image');
$bg_overlay = get_field('background_overlay');
?>

<section class="block <?php echo esc_attr($className); ?> has-bg-img <?php echo empty($bg_image) ? 'theme-dark' : 'theme-light'; ?>" id="<?php echo esc_attr($id); ?>">

	<div class="container-fluid d-flex justify-content-center align-items-center">
		<div class="the-content">
			<?php echo $content; ?>
		</div>
		<a class="btn btn-outline" href="<?php echo esc_url($cta['url']); ?>" target="<?php echo esc_attr($cta['target']); ?>"><?php echo esc_html($cta['title']) ?: 'Button'; ?></a>
	</div>

    <style type="text/css">
        #<?php echo $id; ?> {
            background-image: url('<?php echo $bg_image['url']; ?>');
			margin-top: 0;
			position: relative;
			padding: 140px 0;
        }
        #<?php echo $id; ?>:before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
            background-color: <?php echo $bg_overlay; ?>;
			opacity: .6;
        }
        #<?php echo $id; ?> .container-fluid {
			position: relative;
			z-index: 2;
        }
        #<?php echo $id; ?> .the-content * {
			color: inherit;
		}
		.btn.btn-outline {
			margin-left: 80px;
			text-transform: uppercase;
			text-decoration: none;
			font-weight: 600;
			font-size: 40px;
		}
		.theme-light {
			color: white;
		}
		.theme-light .btn.btn-outline {
			border: solid 3px white;
			background-color: white;
			border-radius: 12px;
			padding: 33px 65px;
            color: <?php echo $bg_overlay; ?>;
			box-shadow: 0px 3px 6px #00000029;
		}

		@media (max-width: 992px) {
			.btn.btn-outline {
				margin-left: 0;
			}
			.theme-light .btn.btn-outline {
				padding: 13px 35px;
				font-size: 18px;
			}
		}

		.theme-light .btn.btn-outline:hover {
            color: rgba(170, 91, 60, 0.5);
			box-shadow: 0px 0px 0px #00000029;
		}
		.theme-light .btn.btn-outline:active {
            color: rgba(170, 91, 60, 0.5);
		}

		.theme-dark .btn.btn-outline {
			border: solid 3px black;
			border-radius: 12px;
			padding: 13px 35px;
			color: black;
		}

        #<?php echo $id; ?> .the-content {
			max-width: 40%;
		}
		.the-content h3 {
			color: inherit;
			margin-bottom: 0;
		}
		@media (max-width: 768px) {
			#<?php echo $id; ?> .the-content {
				max-width: 100%;
			}
			.the-content {
				text-align: center;
				margin-bottom: 20px;
			}
			#<?php echo $id; ?> .container-fluid {
				flex-direction: column;
			}
			#<?php echo $id; ?> {
				padding: 40px;
            }
		}

    </style>
</section>