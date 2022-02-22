var rent_range = scriptVars.rent_range;
var taxonomies = scriptVars.taxonomies;
var properties = scriptVars.properties;
var property_load_limit = scriptVars.property_load_limit;
var selected_price_min = 0;
var selected_price_max = 0;
var searchTerm = '';
var showAllProps = false;
var filterCount = 0;
var showAllButton = document.getElementById('rp-prop-load-more-btn');
var selectedFilters = {
  'size' : [],
  'pet' : [],
  'type' : []
};
params = new URLSearchParams(location.search);
var maxRent = params.getAll('max_price');
var beds = params.getAll('beds');
var pets = params.getAll('pets');
var types = params.getAll('types');
var term = params.getAll('search');

document.addEventListener('DOMContentLoaded', function(){
  // make sure all required values are gathered

  // get any parameters from url for bookmarking and sharing

  // construct price slider
  if (rent_range.max[0] != 1 && rent_range.max[0] != rent_range.min[0]) {
    window.price_range_slider = rp_archive_prop_constructPriceSlider();
  }

  // sort and apply filters
  sortProps();

}, false);

function rp_archive_prop_constructPriceSlider() {
  //gather the existing values
    var price_range_slider = document.getElementById('rp-archive-prop-price-range');
    var input_prop_max_rent = document.getElementById('rp_properties_max_rent').value;

    //if the input has selected value, the set them as such
    var start_min = rent_range.min[0];
    if (maxRent.length !== 0 && maxRent[0] !== "" && filterCount == 0) {
      var start_max = maxRent;
    }
    else if (input_prop_max_rent) {
        var start_max = input_prop_max_rent;
    }
    else {
        var start_max = rent_range.max[0];
    }

    //call the uislider library
    noUiSlider.create(price_range_slider, {
        start: start_max,
        connect: [true, false],
        tooltips: true,
        step: 10,
        range: {
            'min': Math.ceil(Number(rent_range.min[0]) / 10) * 10,
            'max': Math.ceil(Number(rent_range.max[0]) / 10) * 10
        },
        format: {
            to: function (value) {
                return Math.floor(Number(value) / 10) * 10;
            },
            from: function (value) {
                return value.replace(',-', '');
            }
        }
    });
    
    //update the ui slider with the set values
    price_range_slider.noUiSlider.on('update', function( values, handle ) {
        input_prop_max_rent.value=values;
    });

    //return the slider object so it doesn't have to be remade to update it
    return price_range_slider;
}

currentFilters = document.getElementsByClassName('is-filter');

function setFilters() {
  selectedFilters = {
    'size' : [],
    'pet' : [],
    'type' : []
  };

  if (beds.length !== 0 && beds[0] !== "" && filterCount == 0) {
    selectedFilters.size = beds;
    bedInputs = document.getElementsByClassName('prop-bed-filter');
    for (i = 0; bedInputs.length > i; i++) {
      for (b = 0; beds.length > b; b++) {
        if (bedInputs[i].value == beds[b]) {
          bedInputs[i].checked = true;
        }
      }
    }
  }  

  if (pets.length !== 0 && pets[0] !== "" && filterCount == 0) {
    selectedFilters.pet = pets;
    petInputs = document.getElementsByClassName('prop-pet-filter');
    for (i = 0; petInputs.length > i; i++) {
      for (p = 0; pets.length > p; p++) {
        if (petInputs[i].value == pets[p]) {
          petInputs[i].checked = true;       
        }
      }
    }
  }   

  if (types.length !== 0 && types[0] !== "" && filterCount == 0) {
    selectedFilters.type = types;
    typeInputs = document.getElementsByClassName('prop-type-filter');
    for (i = 0; typeInputs.length > i; i++) {
      for (p = 0; types.length > p; p++) {
        if (typeInputs[i].value == types[p]) {
          typeInputs[i].checked = true;
        }
      }
    }
  }  
  
  if (filterCount !== 0) {
    currentFilters = document.getElementsByClassName('is-filter');
    for (var i = 0; i < currentFilters.length; i++) {
      if(currentFilters[i].checked){
        switch(currentFilters[i].parentNode.id) {
          case 'rp-archive-prop-bed-filter':
            selectedFilters.size.push(currentFilters[i].value);
            break;
          case 'rp-archive-prop-pet-filter':
            selectedFilters.pet.push(currentFilters[i].value);
            break;          
            case 'rp-archive-prop-type-filter':
            selectedFilters.type.push(currentFilters[i].value);
            break;
        }
      }
    }
  }
  
  if (term.length !== 0 && term[0] !== "" && filterCount == 0) {
    searchTerm = term[0];
    document.getElementById("rp-prop-search-field").value = searchTerm;
  } else {
    searchTerm = document.getElementById("rp-prop-search-field").value;
  }
  
  z = document.getElementById("rp-archive-prop-price-range").innerText;
  selected_price_max = z;

  paramStr = "?";
  paramStr += "search="+searchTerm;
  paramStr += "&max_price="+selected_price_max;
  for (i = 0; selectedFilters.size.length > i; i++) {
    paramStr += "&beds="+selectedFilters.size[i];
  }
  for (i = 0; selectedFilters.pet.length > i; i++) {
    paramStr += "&pets="+selectedFilters.pet[i];
  }  
  for (i = 0; selectedFilters.type.length > i; i++) {
    paramStr += "&types="+selectedFilters.type[i];
  }
  
  newUrl = encodeURI(location.origin+location.pathname+paramStr);
  window.history.replaceState({},document.title,newUrl);

  filterCount++;

  displayPropertiesSection();
  changeImagesSize(false);
}

function resetFilters() {
  selectedFilters = {
    'size' : [],
    'pet' : [],
    'type' : []
  };
  currentFilters = document.getElementsByClassName('is-filter');
  for (var i = 0; i < currentFilters.length; i++) {
    currentFilters[i].checked = false;
  }
  displayPropertiesSection();
}

function displayPropertiesSection() {
  propertiesCount = 0;
  propstr = '';
  
  for (var i = 0; i < properties.length; i++) {
    if (!isPropertyFilteredOut(properties[i])) {
      propertiesCount++;
      if (propertiesCount <= property_load_limit || showAllProps) {
        createPropertyCard(properties[i]);
      }
    }
  }

  if (propertiesCount == 0) {
    rp_archive_fp_makeNoFloorplansMessage();
  }

  document.getElementById('property_cards').innerHTML = propstr;
  document.getElementById('properties-count').innerHTML = propertiesCount;
  if (propertiesCount > property_load_limit && !showAllProps) {
    document.getElementById('properties-count-limit').innerHTML = '1-' + property_load_limit + ' of';
  } else {
    document.getElementById('properties-count-limit').innerHTML = '';
  }

  if (propertiesCount > property_load_limit) {
    showAllButton.style.display = 'block';
  } else {
    showAllButton.style.display = 'none';
  }

}

function rp_archive_fp_makeNoFloorplansMessage() {
    propstr += "<div class='rp-no-results-wrapper'>";
    propstr += "<p class='rp-primary-accent'>No Apartments Found</p>";
    propstr += "<p>Please try another search or <a class='rp-primary-accent' href='"+location.origin+"/contact'>Contact Us</a>.</p>";
    propstr += "<\/div>";
}

function sortProps() {
  var specials = []; 
  var noSpecials = [];
  var props = properties;
  switch (document.getElementById('property_sort').value) {
    case 'avail:asc':
      properties.sort((a, b) => (parseInt(a.propUnitsAvailable[0]) < parseInt(b.propUnitsAvailable[0])) ? 1 : -1);
      break;
    case 'price:asc':
      properties.sort((a, b) => (parseInt(a.wpPropMinRent[0]) > parseInt(b.wpPropMinRent[0])) ? 1 : -1);
      break;
    case 'price:desc':
      properties.sort((a, b) => (parseInt(a.wpPropMaxRent[0]) < parseInt(b.wpPropMaxRent[0])) ? 1 : -1);
      break;    
    case 'prop:a-z':
      properties.sort((a, b) => (a.propName[0] > b.propName[0]) ? 1 : -1);
      break;    
    case 'city:a-z':
      properties.sort((a, b) => (a.propCity[0] > b.propCity[0]) ? 1 : -1);
      break;
    case 'specials:first':
      for (var i = 0; i < props.length; i++) {
        prop = props[i];
        if (prop.prop_special_text[0] && prop.prop_special_text[0] !== "") {
          specials.push(prop);
        } else {
          noSpecials.push(prop);
        }
      }
      noSpecials.sort((a, b) => (parseInt(a.wpPropMinRent[0]) > parseInt(b.wpPropMinRent[0])) ? 1 : -1);
      properties = specials.concat(noSpecials);
      break;
    }
  setFilters();
}

function isPropertyFilteredOut(prop) {
  // Set default values
  isFilteredOut = false;
  matchedFiltersCount = 0;
  requiredFiltersCount = 0;

  if (selectedFilters.size.length > 0 || 
    selectedFilters.pet.length > 0 ||
    selectedFilters.type.length > 0 ||
    selected_price_min != rent_range.min[0] ||
    selected_price_max != rent_range.max[0] ||
    searchTerm != '') {

    
    

    if (selected_price_min != rent_range.min[0] || selected_price_max != rent_range.max[0]) {
      requiredFiltersCount++;
      if (fallsWithinPriceRange(prop)) {
        matchedFiltersCount++;
      }
    }

    if (selectedFilters.size.length > 0 && requiredFiltersCount == matchedFiltersCount) {
      requiredFiltersCount++;
      for (var i = 0; i < selectedFilters.size.length; i++) {
        if (fallsWithinBedRange(selectedFilters.size[i], prop)) {
          matchedFiltersCount++;
          break;
        }
      }
    }

    if (selectedFilters.pet.length > 0 && requiredFiltersCount == matchedFiltersCount) {
      // since this section requires all filters to match, increase the required filters by number selected
      requiredFiltersCount = selectedFilters.pet.length + requiredFiltersCount;
      for (var i = 0; i < selectedFilters.pet.length; i++) {
        if (meetsPetFilterCriteria(selectedFilters.pet[i], prop)) {
          matchedFiltersCount++;
        }
      }
    }

    if (searchTerm != '' && requiredFiltersCount == matchedFiltersCount){
      requiredFiltersCount++;
      if (propContainsSearchTerm(prop)) {
        matchedFiltersCount++;
      }
    }

    if (selectedFilters.type.length > 0 && requiredFiltersCount == matchedFiltersCount) {
      requiredFiltersCount = selectedFilters.type.length + requiredFiltersCount;
      for (var i = 0; i < selectedFilters.type.length; i++) {
        if (meetsTypeFilterCriteria(selectedFilters.type[i], prop)) {
          if (isInRange == true) {
            matchedFiltersCount++;
            return;
          }
        }
      }
    }

    // if the property does not match the number of required filters, then it is filtered out
    if (requiredFiltersCount != matchedFiltersCount) {
      isFilteredOut = true;
    }

  }

  return isFilteredOut;
}

function propContainsSearchTerm(prop) {
  containsTerm = false;

  if (prop.propType != false){
    for (var i = 0; prop.propType.length > i; i++) {
      if (typeof prop.propType[i] != 'undefined' && prop.propType[i].toUpperCase().indexOf(searchTerm.toUpperCase()) > -1) {
        containsTerm = true;
        return containsTerm;
      }
    }
  }

  if ( typeof prop.propName[0] != 'undefined' && prop.propName[0].toUpperCase().indexOf(searchTerm.toUpperCase()) > -1 
    || typeof prop.propAddress[0] != 'undefined' && prop.propAddress[0].toUpperCase().indexOf(searchTerm.toUpperCase()) > -1  
    || typeof prop.propCity[0] != 'undefined' && prop.propCity[0].toUpperCase().indexOf(searchTerm.toUpperCase()) > -1 
    || typeof prop.propState[0] != 'undefined' && prop.propState[0].toUpperCase().indexOf(searchTerm.toUpperCase()) > -1 
    || typeof prop.propStateSearch != 'undefined' && prop.propStateSearch.toUpperCase().indexOf(searchTerm.toUpperCase()) > -1 
    || typeof prop.propZip[0] != 'undefined' && prop.propZip[0].toUpperCase().indexOf(searchTerm.toUpperCase()) > -1 
    || typeof prop.property_searchterms != 'undefined' && prop.property_searchterms[0].toUpperCase().indexOf(searchTerm.toUpperCase()) > -1 ) {
    containsTerm = true;
  }

  return containsTerm;
}

function fallsWithinPriceRange(prop) {
    isInRange = false;
    
    if (Number(prop.wpPropMinRent[0]) <= Number(selected_price_max) && Number(prop.wpPropMaxRent[0]) >= Number(selected_price_min) ) {
      isInRange = true;
    }

    return isInRange;
}

function fallsWithinBedRange(bedString, prop) {
  propBeds = prop.propBedsList[0];

  return propBeds.includes(bedString);
}

function meetsPetFilterCriteria(filterString, prop) {
  isInRange = false;

  switch(filterString) {
    case 'Dog Friendly':
      if (typeof taxonomies['Dog Friendly'] != 'undefined' && taxonomies['Dog Friendly'].associated_properties.indexOf(prop['post_ID']) > -1) {
        isInRange = true;
      }
      break;
    case 'Cat Friendly':
      if (typeof taxonomies['Cat Friendly'] != 'undefined' && taxonomies['Cat Friendly'].associated_properties.indexOf(prop['post_ID']) > -1) {
        isInRange = true;
      }
      break;
  }

  return isInRange;
}

function meetsTypeFilterCriteria(filterString, prop) {
  isInRange = false;
  if(prop['propType'].length > 0 && prop['propType'].includes(filterString)) {
    isInRange = true;
  }
  return isInRange;
}

function createPropertyCard(prop) {

  if (prop.wpPropMinBeds[0] == prop.wpPropMaxBeds[0]) {
    if (prop.wpPropMinBeds[0] == '0') {
      bedstr = 'Studio';
    } else {
      bedstr = prop.wpPropMinBeds[0]+' Bed';
    }
  } else if (prop.wpPropMinBeds[0] == '0') {
    bedstr = 'Studio - '+prop.wpPropMaxBeds[0]+' Bed';
  } else {
    bedstr = prop.wpPropMinBeds[0]+' Bed - '+prop.wpPropMaxBeds[0]+' Bed';
  }

  if (typeof taxonomies['Dog Friendly'] != 'undefined' && taxonomies['Dog Friendly'].associated_properties.indexOf(prop['post_ID']) > -1) {
    dogstr = '<div class="rp-dog-icon"><span class="rp-visually-hidden">Dog is OK</span></div>';
  } else {
    dogstr = '';
  }

  if (typeof taxonomies['Cat Friendly'] != 'undefined' && taxonomies['Cat Friendly'].associated_properties.indexOf(prop['post_ID']) > -1) {
    catstr = '<div class="rp-cat-icon"><span class="rp-visually-hidden">Cat is OK</span></div>';
  } else {
    catstr = '';
  }
  specialText = prop['prop_special_text'];
  specialExpiration = prop['prop_special_expiration'];
  if (specialText && specialText != '' && prop['specialIsExpired'] != true) {
    propSpecial = '<div class="rp-prop-is-special"><h6><span>&#x2605</span> Special</h6><aside class="rp-prop-is-special-msg">'+specialText+'</aside></div>';
  } else {
    propSpecial = '';
  }

  pricestr = '$'+prop.wpPropMinRent[0]+' - $'+prop.wpPropMaxRent[0];
  if (prop.wpPropMinRent[0] == "" || prop.wpPropMinRent[0] == "0") {
    pricestr = "Call For Pricing";
  } else if (prop.wpPropMaxRent[0] == prop.wpPropMinRent[0]) {
    pricestr = "$"+prop.wpPropMinRent[0];
  }

  propstr += '<div class="is-rp-prop">';              
  propstr += '<a href="'+prop['url']+'" >';
  propstr += '<figure class="rp-prop-figure">';
  propstr += '<img class="rp-prop-image rp-lazy" ';
  if( prop.imageSizes) {
    for (var i = prop.imageSizes.length - 1; i >= 0; i--) {
      image = prop.imageSizes[i];
      propstr += 'data-' + image.size + '="' + image.url + ',' + image.width + ',' + image.height + '" ';
    }
  } else {
    propstr += 'src="'+prop["image"]+'"';
  }
  propstr += '>';
  propstr +=  propSpecial;
  propstr += '</figure>';                  
  propstr += '<footer class="rp-prop-details">';                
  propstr += '<div class="rp-prop-top">';
  propstr += '<h4 class="rp-primary-accent rp-prop-name">';
  propstr +=  prop.propName[0];
  propstr += '</h4>';
  propstr += '<p class="rp-prop-location">'+prop.propCity[0]+', '+prop.propState[0]+'</p>';
  propstr += '</div>';
  propstr += '<div class="rp-prop-bottom">';
  propstr += '<div class="rp-prop-bed-count"><span>'+bedstr+'</span></div>';
  propstr += prop['displayPrice'];
  propstr += '<div class="rp-pets-welcome">';
  propstr += catstr;
  propstr += dogstr;
  propstr += '</div></div></footer></a></div>';                
}

showAllButton.addEventListener("click", function () {
  if (showAllButton.innerHTML == 'Show All') {
    showAllProps = true;
    showAllButton.innerHTML = 'Show Fewer';
    setFilters();
  } else if (showAllButton.innerHTML == 'Show Fewer') {
    showAllProps = false;
    showAllButton.innerHTML = 'Show All';
    setFilters();
  }
})

window.onkeyup = function(event) {
  isEnterButton(event);
}

function isEnterButton(event) {
  if(event.keyCode == 13) {
    setFilters();
    if (typeof propSearchEvent == 'function') { 
      propSearchEvent();
    }
  }
}

document.getElementById('rp-prop-search-button').addEventListener("click", function() {
  setFilters();
});

document.getElementById("rp-archive-fp-open-mobile-open-filters").onclick = function() {
    document.getElementById("rp-archive-fp-sidebar").classList.toggle('rp-is-mobile-open');
};