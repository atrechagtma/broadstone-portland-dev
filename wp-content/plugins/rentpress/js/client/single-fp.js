function rentPress_singleFpLeaseTermPricing($) {

  $('.rp-single-fp-all-the-unit-things').each(function (index,element) {

    var units_from_table=element.querySelectorAll('.rp-unit-card[data-unit-code]');

    var form_rent_term=element.querySelector('select[name=rentTerm]');

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

(function($) {
  $(document).ready(function() {
     if ($('.single-floorplans').length) {
      
      rentPress_singleFpLeaseTermPricing($);

      $('#rp-single-fp-all-the-unit-things select[name="rentTerm"]').on('change', function() {
        
        rentPress_singleFpLeaseTermPricing($);

      });

      $('.rp-radio-unit-number').on('change', function() {
        $('#rpUnitCards .rp-unit-card').removeClass('rp-active');
        
        $(this).parent().parent().addClass('rp-active');

        var unitNumber = $(this).attr('id');
        var moreInfoButton = $('#rp-single-fp-form-buttons .more-info-button');
        var requestLink = moreInfoButton.attr('href');
        var unitAppLink = $(this).data('unit-avail-link');

        console.log(unitAppLink);

        if ( requestLink.match('&unitID=') ) {
          var n = requestLink.indexOf('&unitID=');
          requestLink = requestLink.substring(0, n);
        }

        var newLink = requestLink + '&unitID=' + unitNumber;
        moreInfoButton.attr('href', newLink);
        
        $('#rp-fp-apply-now').attr('href', unitAppLink);
        $('#rp-fp-waitlist').attr('href', unitAppLink);
      });

      $('#rp-single-fp-open-image-popup').magnificPopup({
          type: 'image',
          closeOnContentClick: true,
          closeBtnInside: false,
          mainClass: 'mfp-with-zoom mfp-img-mobile rp-single-fp-image-popup',
          image: {
              verticalFit: true,
          }
      });
    }
  });
})(jQuery);
