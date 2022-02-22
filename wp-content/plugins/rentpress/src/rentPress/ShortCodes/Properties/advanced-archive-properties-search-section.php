<?php 

	global $rentPress_Service;

	$rentPressOptions = new rentPress_Options(); 
	$globalPriceDisable = $rentPressOptions->getOption('rentPress_disable_pricing');
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

	                    <section class="rp-archive-fp-is-filter-module rp-is-wider rp-prop-search-beds-field">
	                        <h4 class="rp-archive-fp-filters-title">
	                            Bedrooms
	                        </h4>
	                        <div class="rp-module-wrapper">
	                            <section class="rp-is-filter-wrapper" id="rp-archive-prop-bed-filter">
	                                <?php for ($i = $bed_range->min; $i <= $bed_range->max ; $i++) {
	                                    $text = ($i == 0) ? 'Studio' : $i ; 
	                                    echo '<input type="checkbox" name="bed-'.$i.'" value="'.$i.'" id="bed_'.$i.'" class="is-filter"><label for="bed_'.$i.'">'.$text.'</label>';
	                                } ?>
	                            </section>
	                        </div>
	                    </section>

	                    
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

	                    <section class="rp-archive-fp-is-filter-module rp-prop-search-pets-field">
	                        <h4 class="rp-archive-fp-filters-title">
	                            Pets
	                        </h4>
	                        <div class="rp-module-wrapper">
	                            <section id="rp-archive-prop-pet-filter" class="rp-is-filter-wrapper">
	                                <input type="checkbox" name="cat" value="Cat Friendly" id="cat" class="is-filter"><label for="cat">Cat</label>
	                                <input type="checkbox" name="dog" value="Dog Friendly" id="dog" class="is-filter"><label for="dog">Dog</label>
	                            </section>

	                        </div>
	                    </section>

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
	                        <option value="price:asc" <?php //echo $hide_pricing; ?>>Rent: Low to High</option>
	                        <option value="price:desc" <?php //echo $hide_pricing; ?>>Rent: High to Low</option>
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

    wp_enqueue_script('template-search-properties-script', RENTPRESS_PLUGIN_ASSETS.'build/js/templates/archive-properties-advanced.min.js');

    wp_localize_script('template-search-properties-script', 'scriptVars', array(
        'properties'          => $properties,
        'rent_range'          => $rent_range,
        'taxonomies'          => $taxonomies,
        'property_load_limit' => $property_load_limit
    ));

?>
