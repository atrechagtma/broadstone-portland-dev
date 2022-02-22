<?php 

	global $rentPress_Service;

	$rentPressOptions = new rentPress_Options(); 
	$globalPriceDisable = $rentPressOptions->getOption('rentPress_disable_pricing');
	$googleApiToken = $rentPressOptions->getOption('rentPress_google_api_token');
	$defaultSort = $rentPressOptions->getOption('rentPress_archive_property_default_sort');
	$accentColor = $rentPressOptions->getOption('templates_accent_color');
	$clusterGridSize = $rentPressOptions->getOption('archive_properties_cluster_grid');
	$minClusterSize = $rentPressOptions->getOption('archive_properties_min_cluster');
?>
	<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $googleApiToken; ?>"></script>
	<script src="https://unpkg.com/@google/markerclustererplus@4.0.1/dist/markerclustererplus.min.js"></script>

	<section class="rp-property-archive rentpress-core-container" id="rp_property_archive">

	    <nav class="rp-archive-fp-mobile-filter-header rp-advanced-archive-fp-mobile-filter-header">
	        <span class="rp-archive-fp-mobile-filter-title rp-button" id="rp-archive-fp-open-mobile-open-filters"><i class="rp-icon-equalizer"></i>Filter & Map</span>
	    </nav>

	    <section class="rp-archive-fp-container clearfix">

	        <header class="rp-archive-header-search advanced-search-header" id="rp-archive-fp-sidebar">

	            <div style="<?php if (strtolower($attributes['hide_filters']) == "true") { echo "display:none;"; } ?>" class="rp-row rp-prop-search">

            		<div class="rp-archive-fp-is-filter-module autocomplete rp-is-wider rp-prop-search-search-field">
                        <input id="rp-prop-search-field" type="search" placeholder="Search by Property Name, City, State, Zip, and more ">
                    </div>

            		<div title="beds" class="filter-dropdown">
						<div class="filter-dropdown-toggle-open">Bedrooms</div>
						<div class="filter-dropdown-content" style="display: none;">

					    	<section class="rp-archive-fp-is-filter-module rp-is-wider rp-prop-search-beds-field">
		                        <h4 class="rp-archive-fp-filters-title">
		                            Bedrooms
		                        </h4>
		                        <div class="rp-module-wrapper">
		                            <section class="rp-is-filter-wrapper" id="rp-archive-prop-bed-filter">
		                                <?php for ($i = $bed_range->min; $i <= $bed_range->max ; $i++) {
		                                    $text = ($i == 0) ? 'Studio' : $i ; 
		                                    echo '<input type="checkbox" name="bed-'.$i.'" value="'.$i.'" id="bed_'.$i.'" class="is-filter prop-bed-filter"><label for="bed_'.$i.'">'.$text.'</label>';
		                                } ?>
		                            </section>
		                        </div>
		                    </section>

		                    <div class="filter-dropdown-toggle-close">close</div>

						</div>
					</div>

					<div <?php if($globalPriceDisable == "true") { echo 'style="display:none;" ';}?> title="price" class="filter-dropdown">
						<div class="filter-dropdown-toggle-open">Price</div>
						<div class="filter-dropdown-content" style="display: none;">

					    	<section class="rp-archive-fp-is-filter-module rp-is-wider rp-prop-search-price-field" id="filter-module-section">
		                        <h4 class="rp-archive-fp-filters-title">Max Price / Month</h4>

		                        <div class="rp-module-wrapper">

		                            <input type="hidden" id="rp_properties_min_rent" name="rp_properties_min_rent" value="">
		                            <input type="hidden" id="rp_properties_max_rent" name="rp_properties_max_rent" value="">

		                            <div class="rp-range-wrapper">
		                                <div class="rp-range-slider" id="rp-archive-prop-price-range"></div>
		                                <!-- This section is constructed by an external library noUIslider called in the constructPriceSlider function of the archive-floorplans-basic.js file -->
		                            </div>
		                        </div>
		                    </section>

		                    <div class="filter-dropdown-toggle-close">close</div>

						</div>
					</div>
					<!-- <div title="price" class="filter-dropdown">
						<select <?php if($globalPriceDisable == "true") { echo 'style="display:none;" ';}?>>
							<option>Max Price / Month</option>
							<?php 
								$i = $min_selector_rent;
								while ($i <= $max_selector_rent) : 
									?>
										<option>$<?= $i ?></option>
									<?php
									$i += 100;
								endwhile;
							?>

						</select>
					</div> -->

					<div title="more" class="filter-dropdown">
						<div class="filter-dropdown-toggle-open">More</div>
						<div class="filter-dropdown-content" style="display: none;">

					    	<section class="rp-archive-fp-is-filter-module rp-prop-search-pets-field">

					    		<?php if (!empty($propertyTypes)){ ?>
					    		<div class="rp-archive-single-filter-wrapper">
			                        <h4 class="rp-archive-fp-filters-title">
			                            Type
			                        </h4>
			                        <div class="rp-module-wrapper">
			                            <section id="rp-archive-prop-type-filter" class="rp-is-filter-wrapper">
			                            <?php foreach ($propertyTypes as $propType) { 
			                                echo '<input type="checkbox" name="'.$propType.'" value="'.$propType.'" id="'.$propType.'" class="is-filter prop-type-filter"><label for="'.$propType.'">'.$propType.'</label>';
			                            } ?>
			                            </section>

			                        </div>
		                    	</div>
		                    	<?php } ?>
		                    	
					    		<div class="rp-archive-single-filter-wrapper">
			                        <h4 class="rp-archive-fp-filters-title">
			                            Pets
			                        </h4>
			                        <div class="rp-module-wrapper">
			                            <section id="rp-archive-prop-pet-filter" class="rp-is-filter-wrapper">
			                                <input type="checkbox" name="cat" value="Cat Friendly" id="cat" class="is-filter prop-pet-filter"><label for="cat">Cat</label>
			                                <input type="checkbox" name="dog" value="Dog Friendly" id="dog" class="is-filter prop-pet-filter"><label for="dog">Dog</label>
			                            </section>

			                        </div>
		                    	</div>

		                    	<div class="rp-archive-single-filter-wrapper">
			                        <h4 class="rp-archive-fp-filters-title">
			                        	Square Footage
			                        </h4>
			                        <section id="rp-archive-prop-sqft-filter" class="rp-is-filter-wrapper">
			                        	<input type="number" id="sqft-min" class="is-filter" name="sqft-min" min="100" max="3500" placeholder="Min">
			                        	<input type="number" id="sqft-max" class="is-filter" name="sqft-max" min="200" max="4000" placeholder="Max">
			                    	</section>
		                    	</div>

		                    </section>

		                    <div class="filter-dropdown-toggle-close">close</div>

						</div>
					</div>

                    <section class="rp-advanced-archive-fp-is-filter-module rp-prop-search-submit-field">
                        <button id="rp-prop-search-button" type="button" class="rp-button"><span class="rp-text-search">Search</span></button>
                    </section>

                    <section class="rp-advanced-archive-fp-is-filter-module rp-prop-reset-submit-field">
                        <button id="rp-prop-reset-button" type="button" class="rp-button" onclick="resetFilters()">Reset</button>
                    </section>

                    <?php if(wp_is_mobile()): ?>

		            	<div id="rp-search-map" style="height: 85vh; width: 100%;"></div>
		            	
		        	<?php endif; ?>

	            </div>

	        </header>

	        <div class="rp-archive-fp-main-section">

	            <div class="rp-advanced-archive-fp-data rp-is-open rp-row" id="rp-advanced-archive-fp-data" onscroll="rpLazyLoad()">

	            	<nav class="rp-archive-fp-nav rp-row">
			            <div class="rp-archive-fp-nav-section">
			                <span class="is-matching">Showing <strong id="properties-count-limit"></strong> <strong id="properties-count"></strong> Matching Properties</span>
			                <span class="is-sort" <?php if($globalPriceDisable == "true") { echo 'style="display:none;" ';}?> >Sort 
			                    <select name="" id="property_sort" onchange="sortProps()">
			                        <?php $options=[
										"Soonest Available" => 'avail:asc',
										"Specials" => 'specials:first',
										"Rent: Low to High" => 'price:asc',
										"Rent: High to Low" => 'price:desc',
										"Property: A to Z" => 'prop:a-z',
										"City: A to Z" => 'city:a-z'
									];
									foreach ($options as $text => $optValue) {
										if ($defaultSort == $optValue) {
											echo '<option value="'. esc_attr($optValue) .'" selected>'. $text .'</option>';
										} else {
											echo '<option value="'. esc_attr($optValue) .'">'. $text .'</option>';
										}
									} ?>
			                    </select>
			                </span>
			            </div>
			        </nav>

	                <section id="property_cards" class="rp-archive-fp-loop">

	                </section> <!-- #property_cards -->

	                <footer class="rp-load-more-row" id="rp-prop-load-more-row">
	                    <button class="rp-load-more-btn rp-button" id="rp-prop-load-more-btn">Show All</button>
	                </footer>

	            </div>

	            <?php if(!wp_is_mobile()): ?>

	            	<div id="rp-search-map" style="height: 85vh; width: 100%;"></div>

	        	<?php endif; ?>

	        </div>

	    </section>

	    <?php if(count($bad_data_properties) > 0): ?>
		    <div class="other-search-properties">
		    	<center>
		    		<br>
			    	<h3>Explore Other Options</h3>
			    	<ul>
			    		<?php foreach ($bad_data_properties as $property) : ?>
			    			<li><a href="<?= $property['url'] ?>"><?= get_the_title($property['post_ID']) ?></a></li>
			    		<?php endforeach; ?>
			    	</ul>
			    </center>
		    </div>
		<?php endif; ?>

	</section>

<?php

    wp_enqueue_script('template-search-properties-script', RENTPRESS_PLUGIN_ASSETS.'build/js/templates/archive-properties-advanced.min.js');

    wp_localize_script('template-search-properties-script', 'scriptVars', array(
        'properties'          	 => $properties,
        'rent_range'          	 => $rent_range,
        'taxonomies'          	 => $taxonomies,
        'property_load_limit' 	 => $property_load_limit,
        'rentPress_options'      => $rentPressOptions,
        'rentpress_plugin_assets'=> RENTPRESS_PLUGIN_ASSETS,
        'accentColor'            => $accentColor,
        'clusterGridSize'        => $clusterGridSize,
        'minClusterSize'		 => $minClusterSize,
    ));
?>