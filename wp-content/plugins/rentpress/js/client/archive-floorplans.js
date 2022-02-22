(function($) {
	$(document).ready(function() {

		var price_range_slider = document.getElementById('rp-archive-fp-price-range');

		if (price_range_slider) {
			var input_fp_min_rent=document.querySelector('#rp-archive-fp-sidebar input[name*=floorplans_min_rent]');
			var input_fp_max_rent=document.querySelector('#rp-archive-fp-sidebar input[name*=floorplans_max_rent]');
			
			if (input_fp_min_rent.value != "") {
				var start_min=input_fp_min_rent.value;
			}
			else {
				var start_min=rentPressRanges.rent_range.min;
			}

			if (input_fp_max_rent.value != "") {
				var start_max=input_fp_max_rent.value;
			}
			else {
				var start_max=rentPressRanges.rent_range.max;
			}

			noUiSlider.create(price_range_slider, {
				start: [start_min, start_max],
				connect: true,
				tooltips: true,
				range: {
					'min': Number(rentPressRanges.rent_range.min),
					'max': Number(rentPressRanges.rent_range.max)
				}
			});
			
			price_range_slider.noUiSlider.on('update', function( values, handle ) {
				input_fp_min_rent.value=values[0];
				input_fp_max_rent.value=values[1];
			});
		}

		// Sorting 

		var selector_of_sort=document.querySelector('.rp-archive-fp-nav-section select[name="floorplans_sortedby"]');

		if (selector_of_sort) {

			selector_of_sort.addEventListener('change', function() {

				document.getElementById('rp-archive-fp-filters').submit();

			});
		}

		//

		$('#rp-archive-fp-open-mobile-open-filters').on( 'click', function(){
			$('#rp-archive-fp-sidebar').toggleClass('rp-is-mobile-open');
		});

		$('#rpMobileFilterToggle').on( 'click', function(){
			$(this).parent('.rp-mobile-filter-header').toggleClass('is-active');
			$('#rp-archive-fp-filters').css('top', $mobileHeaderHeight ).toggleClass('mobile-open');
			$('#filterModal').toggleClass('rp-is-open')
		});

		$('#rp-archive-fp-filters input[type=checkbox]').each( function(){
	      var $this = $(this);
	      $this.on('click', function(){
	        $this.closest('p').toggleClass('rp-is-checked');
	      });
	    });

		// removing for 2.0 ?
	    // $('.rp-archive-fp-is-filter-module').each( function(){
	    //   var $this = $(this).find('.rp-archive-fp-filters-title').eq(0);
	      
	    //   $this.prepend('<span class="rp-widget-toggle"></span>');
	      
	    //   $this.find('.rp-widget-toggle').on( 'click', function(){
	    //     $(this).closest('.rp-archive-fp-is-filter-module').toggleClass('rp-is-closed');
	    //   });
	    // });

	    // $("#rp-archive-fp-toggle-filters").on('click', function(){
	    //   var $this = $(this);
	    //   $this.toggleClass('rp-is-open rp-is-closed');
	    //   $('#rp-archive-fp-sidebar').toggleClass('rp-is-open');
	    //   $('#rp-archive-fp-data').toggleClass('rp-is-open');
	    //   if($this.hasClass('rp-is-open')){
	    //         $this.text('Hide');         
	    //     } else {
	    //         $this.text('Show');
	    //     }
	    // });

	});

})(jQuery);