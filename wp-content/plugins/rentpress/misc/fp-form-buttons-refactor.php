
<footer id="rp-single-fp-form-buttons" class="<?php if ($appointmentsPluginIsActive && isset($schedule_a_tour_url) && ($rentPressOptions->getOption('tour_cta_button') == 'true')) { echo 'rp-3-btn'; }; ?>">
	<!-- If available units, show Tour, Info, and Apply buttons -->
	<?php if (count($units) == 0 && $show_waitlist_ctas) : ?>

		<a href="<?= $waitlist_url ?>" id="rp-fp-app-link" class="rp-button-alt rp-waitlist-button">Join Our Waitlist</a>

	<?php endif; ?>

	<!-- If no units, show Waitlist, Tour, and floorplans buttons -->
	<?php if ($appointmentsPluginIsActive && isset($schedule_a_tour_url) && ($rentPressOptions->getOption('tour_cta_button') == 'true')) : ?>
		<a href="<?php echo esc_url($schedule_a_tour_url); ?>" class="rp-tour-button rp-button-alt">
				Schedule A Tour
			</a>
	<?php endif; ?>

	<?php if ( isset($requestMoreInfoUrl) && count($units) !== 0 ) : ?>

		<a href="<?php echo esc_url($requestMoreInfoUrl); ?>" class="rp-button-alt">Request More Info</a>

	<?php endif; ?>

	<?php if ( count($units) !== 0 ) : ?>

		<a href="<?php echo $availabilityUrl; ?>" target="<?php echo $rentPressOptions->getOption('override_apply_links_targets'); ?>" id="rp-fp-app-link" class="rp-button-alt more-info-button">Apply Now</a>

	<?php endif; ?>

	<?php if (count($units) == 0 && $show_waitlist_ctas) : ?>

		<a href="/floorplans/" class="rp-button-alt rp-floorplans-back-button>">Browse Floorplans</a>

	<?php endif; ?>

</footer>