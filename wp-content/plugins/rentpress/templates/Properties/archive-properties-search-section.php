<?php 
	global $rentPress_Service;
	$rentPressOptions = new rentPress_Options(); 
	$globalPriceDisable = $rentPressOptions->getOption('rentPress_disable_pricing');
	$defaultSort = $rentPressOptions->getOption('rentPress_archive_property_default_sort');
	include RENTPRESS_PLUGIN_DIR . 'misc/template-schema/property-search-schema.php';
?>

	<section class="rp-property-archive rentpress-core-container" id="rp_property_archive">

	    <nav class="rp-archive-fp-mobile-filter-header">
	        <span class="rp-archive-fp-mobile-filter-title rp-button" id="rp-archive-fp-open-mobile-open-filters"><i class="rp-icon-equalizer"></i>Filter & Sort</span>
	    </nav>

	    <section class="rp-archive-fp-container clearfix">

	        <header class="rp-archive-header-search" id="rp-archive-fp-sidebar">

	            <div style="<?php if (strtolower($attributes['hide_filters']) == "true") { echo "display:none;"; } ?>" class="rp-row rp-prop-search">

	                    <div class="rp-archive-fp-is-filter-module autocomplete rp-is-wider rp-prop-search-search-field">
	                        <h4 class="rp-archive-fp-filters-title">
	                            Where
	                        </h4>
	                        <input id="rp-prop-search-field" type="search" placeholder="Search by Property Name, City, State, or Zip ">
	                    </div>

	                    <?php if (!($bed_range->min == $bed_range->max)) : ?>
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
	                	<?php endif; ?>

	                	<?php if (!($rent_range->min == $rent_range->max)) : ?>
	                    <section <?php if($globalPriceDisable == "true") { echo 'style="display:none;" ';}?> class="rp-archive-fp-is-filter-module rp-is-wider rp-prop-search-price-field" id="filter-module-section">
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
	                	<?php endif;?>

	                	<?php if (!empty(get_terms('prop_pet_restrictions'))) :
	                	$petsArray = array_keys($taxonomies); ?>
	                    <section class="rp-archive-fp-is-filter-module rp-prop-search-pets-field">
	                        <h4 class="rp-archive-fp-filters-title">
	                            Pets
	                        </h4>
	                        <div class="rp-module-wrapper">
	                            <section id="rp-archive-prop-pet-filter" class="rp-is-filter-wrapper">
	                                <?php if (in_array('Cat Friendly', $petsArray)) : ?>
	                                <input type="checkbox" name="cat" value="Cat Friendly" id="cat" class="is-filter prop-pet-filter"><label for="cat">Cat</label>
	                                <?php endif; 
	                                if (in_array('Dog Friendly', $petsArray)) : ?>
	                                <input type="checkbox" name="dog" value="Dog Friendly" id="dog" class="is-filter prop-pet-filter"><label for="dog">Dog</label>
	                            	<?php endif; ?>
	                            </section>
	                        </div>
	                    </section>
	                	<?php endif; ?>

	                    <?php if (false){ ?>
	                    <section class="rp-archive-fp-is-filter-module rp-prop-search-type-field">
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
	                    </section>
	                	<?php } ?>

	                    <section class="rp-archive-fp-is-filter-module rp-prop-search-submit-field">
	                        <h4 class="rp-archive-fp-filters-title -rp-visually-hidden">Submit</h4>
	                        <button id="rp-prop-search-button" type="button" class="rp-button"><span class="rp-text-search">Search</span></button>
	                    </section>

	            </div>

	        </header>

	        <nav class="rp-archive-fp-nav rp-row">

	            <div class="rp-archive-fp-nav-section">
	                <span class="is-matching">Showing <strong id="properties-count-limit"></strong> <strong id="properties-count"></strong> Matching Properties</span>
	                <span class="is-sort">Sort 
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

	        <div class="rp-archive-fp-main-section">

	            <div class="rp-archive-fp-data rp-is-open rp-row" id="rp-archive-fp-data">

	                <section id="property_cards" class="rp-archive-fp-loop">

	                </section> <!-- #property_cards -->

	                <footer class="rp-load-more-row" id="rp-prop-load-more-row">
	                    <button class="rp-load-more-btn rp-button" id="rp-prop-load-more-btn">Show All</button>
	                </footer>

	            </div>

	        </div>

	    </section>

	</section>

<?php

    wp_enqueue_script('template-search-properties-script', RENTPRESS_PLUGIN_ASSETS.'build/js/templates/archive-properties-basic.min.js');

    wp_localize_script('template-search-properties-script', 'scriptVars', array(
        'properties'          => $properties,
        'rent_range'          => $rent_range,
        'taxonomies'          => $taxonomies,
        'property_load_limit' => $property_load_limit
    ));

?>
