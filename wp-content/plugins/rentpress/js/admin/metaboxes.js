// Lease Terms

var rentPressPricingSelections = {};

(function($) {
	$(document).ready(function() {
	    $('.rentpress-fp-unit-view-lease-term-pricing').on('click', function() {
	        $(this).parent().find('.rentpress-fp-unit-lease-term-pring-options').show();
	    });

	    $('.rentpress-fp-unit-close-lease-term-pricing').on('click', function() {
	        $(this).closest('.rentpress-fp-unit-lease-term-pring-options').hide();
	    });

		$('input[name="fpMatterport"]').on('input', function() {

			if ($(this).val() == '') {
				$('input[name="override_meta_fpMatterport"]').prop('checked', false);
			}
			else {
				$('input[name="override_meta_fpMatterport"]').prop('checked', true);
			} 

		});
	});
})(jQuery);

// Property Coords Calu

function rentpressFetchCoordsFromAddress(address) {
	var  geocoder = new google.maps.Geocoder();
	
	geocoder.geocode( { 'address': address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            jQuery('#prop_coords').val(results[0].geometry.location);

            if (jQuery('input[name=override_synced_property_coords_data]').prop('checked')) {

            	jQuery('input[name=propLatitude]').val(results[0].geometry.location.lat());
            	jQuery('input[name=propLongitude]').val(results[0].geometry.location.lng());

            }

        } else {
            alert("Geocode was not successful for the following reason: " + status);
        }
    });

}