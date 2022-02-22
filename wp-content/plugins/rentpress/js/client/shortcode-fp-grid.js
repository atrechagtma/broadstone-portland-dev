(function($) {
	$(document).ready(function() {
		// Init floor plan short code grid mixitup if it is present on the page
		if ( $('#rp-floor-plan-grid-bedroom-filters').length ) {
			mixitup('#rp-floor-plan-grid', {
			    controls: {
			        toggleLogic: 'and'
			    }
			});
		}
	});
})(jQuery);