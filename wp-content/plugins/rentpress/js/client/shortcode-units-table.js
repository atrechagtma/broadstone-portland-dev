function rpShortcodeUnitTableRender() {

	var fp_unit_sections = document.querySelectorAll('.unit-modal-grid-container');

  	if (fp_unit_sections.length > 0) {
		fp_unit_sections.forEach(function(section) {
	  		var units_from_table = section.querySelectorAll('.rp-unit-card[data-unit-code]');
	    	var form_rent_term = section.querySelector('select[name=rentTerm]');

	    	units_from_table.forEach(function(ele_unit) {
	      		var ele_price=ele_unit.querySelector("*[data-is-price]");

	      		if (ele_price) {
	        		var ele_price_attr_rent_terms=ele_price.getAttribute("data-rent-terms");

	        		if (form_rent_term && form_rent_term.value) {
	          			if (ele_price_attr_rent_terms && ele_price_attr_rent_terms != '') {
	            			var TermData=JSON.parse(ele_price_attr_rent_terms);

	            			if (TermData.length >= 1) {
	              				TermData.forEach(function(termRent) {
	                				if (form_rent_term.value == termRent.Term) {
	                  					ele_price.innerHTML=Number(termRent.Rent);
	                				}
	              				});
	            			}
	            			else {
	              				ele_price.innerHTML=Number(ele_price.getAttribute('data-defualt-rent'));
	            			}
	          			}
	          			else {
	            			ele_price.innerHTML=Number(ele_price.getAttribute('data-defualt-rent'));
	          			}
	        		}
	        		else {
	          			ele_price.innerHTML=Number(ele_price.getAttribute('data-defualt-rent'));
	        		}
	    		}
	    	});	
		});
	} 
}

(function($) {
  $(document).ready(function() {
  	if ($('.unit-modal').length) {
  	
      rpShortcodeUnitTableRender();

      $('select[name="rentTerm"]').on('change', function() {
        rpShortcodeUnitTableRender();

      });
  	}
  });
})(jQuery);
