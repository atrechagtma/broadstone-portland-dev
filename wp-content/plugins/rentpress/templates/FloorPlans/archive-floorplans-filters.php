<aside class="rp-archive-fp-sidebar rp-is-open" id="rp-archive-fp-sidebar">
	<form id="rp-archive-fp-filters">	
		<input type="hidden" name="page_id" value="<?php echo $post->ID; ?>">

		<section class="rp-archive-fp-is-filter-module">
			<h4 class="rp-archive-fp-filters-title">
				Bedrooms
			</h4>
			<div class="rp-module-wrapper">
				<section class="rp-is-filter-wrapper" id="rp-archive-fp-bed-filter">
					<!-- This section is constructed in the constructBedroomsFilter function of the archive-floorplans-basic.js file -->
				</section>
			</div>
		</section>		

		<section  id="rp-archive-fp-feature-filter-section" class="rp-archive-fp-is-filter-module">
			<h4 class="rp-archive-fp-filters-title">
				Features
			</h4>
			<div class="rp-module-wrapper">
				<section class="rp-is-filter-wrapper" id="rp-archive-fp-feature-filter">
					<!-- This section is constructed in the constructFeaturesFilter function of the archive-floorplans-basic.js file -->
				</section>
			</div>
		</section>

		<?php if ($all_floorplans_data['data']['rent_range']->max != 1) : ?>
				<section class="rp-archive-fp-is-filter-module rp-range-wrapper-container" id="filter-module-section">
				
				<h4 class="rp-archive-fp-filters-title">Max Price / Month</h4>

				<div class="rp-module-wrapper">

					<input type="hidden" id="floorplans_min_rent" name="floorplans_min_rent" value="">
					<input type="hidden" id="floorplans_max_rent" name="floorplans_max_rent" value="">

					<div class="rp-range-wrapper">
						<div class="rp-range-slider" id="rp-archive-fp-price-range"></div>
						<!-- This section is constructed by an external library noUIslider called in the constructPriceSlider function of the archive-floorplans-basic.js file -->
					</div>
				</div>
			</section>
		<?php endif; ?>

		<section class="rp-archive-fp-is-filter-module">
			<h4 class="rp-archive-fp-filters-title">Move-in Date</h4>
			<div class="rp-module-wrapper">
				
				<select id="floorplans_available_filter" name="floorplans_available_by" class="rp-select">
					<option value="" selected="selected">Show it all</option>
					<option value="Next Two Weeks">Next Two Weeks</option>
					<option value="Next Month">Next Month</option>
					<option value="Next Two Months">Next Two Months</option>
				</select>

			</div>
		</section>
		<!-- These button event listeners are created in the archive-floorplans-basic.js file -->

		<section class="rp-apply-filters">
			<button id="apply_filters_button" type="button" class="rp-button apply-filters-button" style="width: 100%;">Apply Filters</button>
		</section>

		<section class="text-center rp-filter-reset">
				<button id="reset_button" type="button" class="rp-button-alt" style="width: 100%;">Reset</button>
		</section>
	</form>
</aside>