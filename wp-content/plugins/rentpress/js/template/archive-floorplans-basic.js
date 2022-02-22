//set up globals for each function, so the data can be refiltered and reused after editing
rp_archive_fp_resetValues();
default_sort = floorplans_data['selected_sort'];
var google_tag_events = 0;

document.addEventListener('DOMContentLoaded', function(){ 
    //set up the element events on the page on the page
    document.getElementById("floorplan_sort").onchange = function() {rp_archive_fp_makeAllCards(floorplans)};
    document.getElementById("reset_button").onclick = function() {rp_archive_fp_resetFilters()};
    document.getElementById("apply_filters_button").onclick = function() {rp_archive_fp_applyFilters()};

    //construct the page and capture the slider element globally
    if (floorplans_data.rent_range.max != 1 && floorplans_data.rent_range.max != floorplans_data.rent_range.min) {
        window.price_range_slider = rp_archive_fp_constructPriceSlider();
    }
    rp_archive_fp_constructPage();

    //make the hide toggle html element work
    document.getElementById("rp-archive-fp-toggle-filters").onclick = function() {
        this.classList.toggle('rp-is-open');
        this.classList.toggle('rp-is-closed');
        document.getElementById("rp-archive-fp-sidebar").classList.toggle('rp-is-open');
        document.getElementById("rp-archive-fp-data").classList.toggle('rp-is-open');
        if(this.classList.contains('rp-is-open')){
            this.innerText = ' Hide Filters';
        } else {
            this.innerText = ' Show Filters';
        }
    };

    document.getElementById("rp-archive-fp-open-mobile-open-filters").onclick = function() {
        document.getElementById("rp-archive-fp-sidebar").classList.toggle('rp-is-mobile-open');
    };

    fpModals = document.getElementsByClassName("unit-modal");

}, false);

function rp_archive_fp_constructPage() {

    //set the parameter object with the new url
    params = new URLSearchParams(location.search);
    filters = [...params.entries()];

    //filter out the floorplans, then construct the new floorplan cards
    floorplans = all_floorplans_data['all_floorplans'];
    all_floorplans_bed_types = rp_archive_fp_getUniqueBedTypes(all_floorplans_data['all_floorplans']);
    all_floorplans_feature_types = rp_archive_fp_getUniqueFeatureTypes(all_floorplans_data['all_floorplans']);
    floorplans = rp_archive_fp_filterFloorplans(floorplans);
    rp_archive_fp_makeAllCards(floorplans);
    rp_archive_fp_constructBedroomsFilter(all_floorplans_bed_types);
    rp_archive_fp_constructFeaturesFilter(all_floorplans_feature_types);

    //check the slider values
    price = params.get('selected_price_range');
    
    if (price) {
        price_range_slider.noUiSlider.updateOptions({
            start: price[0]
        });
    }
        
}

/**
* this function runs if the reset button is clicked
* has no params or returns
* just sets the url to default and resets the shown floorplans to all floorplans
*/
function rp_archive_fp_resetFilters() {
    //rewrite url
    history.replaceState(
        {},
        document.title,
        location.origin+location.pathname
    );

    //reset the values
    rp_archive_fp_resetValues();

    //reset the parameter object
    params = new URLSearchParams(location.search);
    filters = [...params.entries()];

    //make the price slider have base values
    price_range_slider.noUiSlider.updateOptions({
        start: floorplans_data.rent_range.max
    });

    //reset the selector
    document.getElementById('floorplans_available_filter').value = "";

    //remake all of the cards
    rp_archive_fp_makeAllCards(floorplans);
    //list all the bedroom types
    rp_archive_fp_constructBedroomsFilter(all_floorplans_bed_types);    
    //list all the feature types
    rp_archive_fp_constructFeaturesFilter(all_floorplans_feature_types);
}

/**
* this function runs if the apply button is clicked
* has no params or returns
* just sets the url to whatever was selected and filters the floorplans
**/
function rp_archive_fp_applyFilters() {

    //reset the values
    rp_archive_fp_resetValues();

    //get the selected values from the html elements
    paramStr = "";
    w = document.getElementsByName("selected_floorplans_beds");
    x = document.getElementsByName("selected_floorplans_features");
    y = document.getElementById("floorplans_available_filter");
    z = document.getElementById("rp-archive-fp-price-range").innerText;

    //for each floorplan selected, add it to the parameter stream
    for (var i = w.length - 1; i >= 0; i--) {
        if (w[i].checked == true) {
            paramStr += 'selected_floorplans_beds='+w[i].value+'&';
        }
    }    

    //for each floorplan selected, add it to the parameter stream
    for (var i = x.length - 1; i >= 0; i--) {
        if (x[i].checked == true) {
            paramStr += 'selected_floorplans_features='+x[i].value+'&';
        }
    }

    //if a date selection has been made, add that to the parameter string
    if (y.options[y.selectedIndex].value != '') {
        paramStr += 'floorplans_available_by='+y.options[y.selectedIndex].value+'&';
    }
    
    //check the slider values, if there has been a change, add the change to the parameter string
    if(z != floorplans_data.rent_range.max) {
        paramStr += 'selected_price_range='+z;
        price_range_slider.noUiSlider.updateOptions({
            start: z
        });
    }
    
    //create the url string
    full_address = location.origin+location.pathname;
    if (paramStr.length > 1) {
        full_address = location.origin+location.pathname+'?'+paramStr;
    }

    //reconstruct the url with the new string
    history.replaceState(
        {},
        document.title,
        full_address
    );

    //reset the parameter object with the new url
    params = new URLSearchParams(location.search);
    filters = [...params.entries()];
    console.log(filters);

    //filter out the floorplans, then construct the new floorplan cards
    floorplans = rp_archive_fp_filterFloorplans(floorplans);
    rp_archive_fp_makeAllCards(floorplans);
    rp_archive_fp_constructBedroomsFilter(all_floorplans_bed_types);
    rp_archive_fp_constructFeaturesFilter(all_floorplans_feature_types);
}

/**
* @param floorplan array
* @return floorplan array
* takes the given floorplan array and returns a sorted floorplans array based on sort selector
*/
function rp_archive_fp_sortFloorplans(floorplans) {
    // default sort is a rentpress option
    // this block will only ever run once on page load, then value is false and will never run again
    if (default_sort) {
        document.getElementById("floorplan_sort").value = default_sort;
        default_sort = false;
    }

    //see what value is in the selector
    selected_sort = document.getElementById("floorplan_sort").value;

    //based on the selected sort, apply that sort to the array
    switch(selected_sort) {
        case "avail:asc":
            // sorts by rent ascending, then by availability
            floorplans.sort(function(a, b) {
                return a.fpMinRent - b.fpMinRent;
            });
            floorplans.sort(function(a, b) {
                return b.fpAvailUnitCount - a.fpAvailUnitCount;
            });
            break;
        case "rent:asc":
            floorplans.sort(function(a, b) {
                return a.fpMinRent - b.fpMinRent;
            });
            break;
        case "rent:desc":
            floorplans.sort(function(a, b) {
                return b.fpMinRent - a.fpMinRent;
            });
            break;
        case "sqft:asc":
            floorplans.sort(function(a, b) {
                return a.sqft - b.sqft;
            });
            break;
        case "sqft:desc":
            floorplans.sort(function(a, b) {
                return b.sqft - a.sqft;
            });
            break;
        case "beds:asc":
            // sorts by rent ascending, then by number of bedrooms
            floorplans.sort(function(a, b) {
                return a.fpMinRent - b.fpMinRent;
            });
            floorplans.sort(function(a, b) {
                return a.bedCount - b.bedCount;
            });
            break;
        default:
    }
    return floorplans;
}

/**
* has no params or returns
* sets the floorplans that can be selected, or have been selected
*/
function rp_archive_fp_constructBedroomsFilter() {
    //set up the needed variables
    var bed_count_types = all_floorplans_bed_types;
    bedroomChoices = params.getAll('selected_floorplans_beds');

    //sort in ascending order
    bed_count_types.sort(function(a, b){return b - a});

    bedStr = "";

    //for each bed type
    for (var i = bed_count_types.length - 1; i >= 0; i--) {
        // that has been selected in the url, mark the element as checked
        checked = ''
        if (bedroomChoices.includes(bed_count_types[i])) {
            checked = ' checked';
        }

        paragraph = "<p>";
        if (checked == 'checked') {
            paragraph = "<p class='rp-is-checked'>";
        }

        //construct the bed type elemements
        if (bed_count_types[i] == 0) {
            bedStr += paragraph+"<label><input type='checkbox' class='rp-input rp-bed-filter' name='selected_floorplans_beds' value='"+bed_count_types[i]+"'"+checked+">Studio</label></p>"
        }
        else {
            bedStr += paragraph+"<label><input type='checkbox' class='rp-input rp-bed-filter' name='selected_floorplans_beds' value='"+bed_count_types[i]+"'"+checked+"> "+bed_count_types[i]+" Bedroom</label></p>"
        }
    }

    bedStr += "<div class='clearfix'></div>";

    //apply the constructed elements to the dom
    document.getElementById('rp-archive-fp-bed-filter').innerHTML = bedStr;


    bedroom_checkboxes = document.getElementsByClassName('rp-input rp-bed-filter');

    for (var i = 0; i < bedroom_checkboxes.length; i++) {
        bedroom_checkboxes[i].onclick = function() {
            this.parentNode.parentNode.classList.toggle('rp-is-checked');
        }

    }

    
}

/**
* has no params or returns
* sets the floorplans that can be selected, or have been selected
*/
function rp_archive_fp_constructFeaturesFilter() {
    //set up the needed variables
    var feature_types = all_floorplans_feature_types;
    featureChoices = params.getAll('selected_floorplans_features');

    if(feature_types.length == 0) {
        document.getElementById("rp-archive-fp-feature-filter-section").style.display = "none";
    }

    //sort in ascending order
    feature_types.sort(function(a, b){return b - a});

    featureStr = "";

    //for each feature type
    for (var i = feature_types.length - 1; i >= 0; i--) {
        // that has been selected in the url, mark the element as checked
        checked = ''
        if (featureChoices.includes(feature_types[i])) {
            checked = 'checked';
        }

        paragraph = "<p>";
        if (checked == 'checked') {
            paragraph = "<p class='rp-is-checked'>";
        }


        featureStr += paragraph+"<label><input type='checkbox' class='rp-input rp-feature-filter' name='selected_floorplans_features' value='"+feature_types[i]+"' "+checked+"> "+feature_types[i]+"</label></p>"

    }

    featureStr += "<div class='clearfix'></div>";

    //apply the constructed elements to the dom
    document.getElementById('rp-archive-fp-feature-filter').innerHTML = featureStr;


    feature_checkboxes = document.getElementsByClassName('rp-input rp-feature-filter');

    for (var i = 0; i < feature_checkboxes.length; i++) {
        feature_checkboxes[i].onclick = function() {
            this.parentNode.parentNode.classList.toggle('rp-is-checked');
        }

    }

}

/**
* has no params
* @return the uislider object
* constructs the ui slider dynamically
*/
function rp_archive_fp_constructPriceSlider() {
 //gather the existing values
    var price_range_slider = document.getElementById('rp-archive-fp-price-range');
    var input_fp_max_rent = document.getElementById('floorplans_max_rent').value;

    //if pricing is disabled on the site, then don't show this element on the dom
    if (floorplans_data["disable_pricing"] == "true") {
        
        document.getElementById('filter-module-section').style.display = "none";
    }
    
    if (input_fp_max_rent) {
        var start_max = input_fp_max_rent;
    }
    else {
        var start_max = floorplans_data.rent_range.max;
    }

    //call the uislider library
    noUiSlider.create(price_range_slider, {
        start: start_max,
        connect: [true, false],
        tooltips: true,
        step: 10,
        range: {
            'min': Math.ceil(Number(floorplans_data.rent_range.min) / 10) * 10,
            'max': Math.ceil(Number(floorplans_data.rent_range.max) / 10) * 10
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
        input_fp_max_rent.value=values;
    });

    //return the slider object so it doesn't have to be remade to update it
    return price_range_slider;
}

/**
* @param floorplan array
* @return bed type array
* returns each bed type found in the floorplan array
*/
function rp_archive_fp_getUniqueBedTypes(fp) {
    var bed_types = [];

    for (var i = fp.length - 1; i >= 0; i--) {
        bed_type = fp[i]['bedCount'];

        if (bed_type === undefined || bed_type.length == 0) {
            continue;
        } else {
            if(bed_types.indexOf(bed_type) == -1) {
                bed_types.push(bed_type);
            }
        }
    }
    return bed_types;
}

/**
* @param floorplan array
* @return feature type array
* returns each feature type found in the floorplan array
*/
function rp_archive_fp_getUniqueFeatureTypes(fp) {
    var feature_types = [];

    for (var i = fp.length - 1; i >= 0; i--) {
        feature_type = fp[i]['featureName'];

        if (feature_type === undefined || feature_type.length == 0) {
            continue;
        } else {
            for (var x = 0; x < feature_type.length; x++) {
                if (feature_types.includes(feature_type[x])) {
                    continue;
                } else {
                    feature_types.push(feature_type[x]);
                }
            }
        }
    }
    var feature_types = feature_types.filter(function (el) {
      return el != null;
    });
    
    return feature_types;
}

/**
* @param floorplan array
* @return floorplan array
* returns filtered out floorplan array
*/
function rp_archive_fp_filterFloorplans(floorplans) {
    //get each of the selected filters from the url 
    price = params.get('selected_price_range');
    dateChangeString = params.get('floorplans_available_by');
    bedroomChoices = params.getAll('selected_floorplans_beds');
    featureChoices = params.getAll('selected_floorplans_features');

    //if one or more bedroom type is selected, then filter out floorplans not selected
    if (bedroomChoices.length > 0) {
        floorplans = rp_archive_fp_filterOutBedrooms(floorplans, bedroomChoices);
    }    

    //if one or more feature type is selected, then filter out floorplans not selected
    if (featureChoices.length > 0) {
        floorplans = rp_archive_fp_filterOutFeatures(floorplans, featureChoices);
    }

    //if a price range is selected, then filter out floorplans that are not in it
    if (price) {
        floorplans = rp_archive_fp_filterOutPrice(floorplans, price);
    }

    //if a date range is selected, then filter out floorplans that are later than it
    if (dateChangeString) {
        floorplans = rp_archive_fp_filterOutDate(floorplans, dateChangeString);
    }

    return floorplans;
}

function rp_archive_fp_filterOutDate(floorplans, dateChangeString) {
    selectedDate = new Date(); 
    if (dateChangeString == 'Next Two Weeks') {
        selectedDate.setDate(selectedDate.getDate()+14);
    }
    if (dateChangeString == 'Next Month') {
        selectedDate.setMonth(selectedDate.getMonth()+2)
        selectedDate.setDate(0);
    }
    if (dateChangeString == 'Next Two Months') {
        selectedDate.setMonth(selectedDate.getMonth()+3)
        selectedDate.setDate(0);
    }

    unit_day = new Date();

    for (var key = floorplans.length - 1; key >= 0; key--) {

        totalUnits = floorplans[key]['units'].length;
        for (var i = totalUnits - 1; i >= 0; i--) {
            
            unit_day.setTime(floorplans[key]['units'][i]['newDate']);

            if (unit_day.getTime() > selectedDate.getTime()) {
                floorplans[key]['units'].splice([i],1);
            } else if (floorplans[key]['units'][i]['is_available_on'] == "1970-01-01") {
                floorplans[key]['units'].splice([i],1);
            }
        }

        if (floorplans[key]['units'].length == 0) {
            floorplans.splice([key],1);
        } else if (floorplans_data['show_all_units'] == 'true') {
            floorplans[key]['fpAvailUnitCountDisplay'] = (totalUnits > 1 ? totalUnits+" Apartments" : totalUnits+" Apartment" );
        } else if (floorplans_data['show_waitlist'] && floorplans[key]['units'].length == 0) {
            floorplans[key]['fpAvailUnitCountDisplay'] = "Join Waitlist";
        } else {
            floorplans[key]['fpAvailUnitCountDisplay'] = floorplans[key]['units'].length+" Available";
        }
    }

    return floorplans;
}

function rp_archive_fp_filterOutPrice(floorplans, price) {
    max = price;
    for (var key = floorplans.length - 1; key >= 0; key--) {
        if (floorplans[key]['fpMinRent'] > max) {
            floorplans.splice([key],1);
        }
    }

    return floorplans;
}

function rp_archive_fp_filterOutBedrooms(floorplans, bedroomChoices) {

    var floorplansWithSelectedbedrooms = [];

    for (var i = 0; i < bedroomChoices.length; i++) {
        var searchTerm = bedroomChoices[i];
        for (var key = 0; key < floorplans.length; key++) {
            if (floorplans[key]['bedCount'].includes(searchTerm)) {
                floorplansWithSelectedbedrooms.push(floorplans[key]);
            }
        }
    }

    var floorplans = floorplansWithSelectedbedrooms;
    return floorplans;
}

function rp_archive_fp_filterOutFeatures(floorplans, featureChoices) {

    var floorplansWithSelectedFeatures = [];

    for (var i = 0; i < featureChoices.length; i++) {
        var searchTerm = featureChoices[i];
        for (var key = 0; key < floorplans.length; key++) {
            if (floorplans[key]['featureName'].includes(searchTerm) && !floorplansWithSelectedFeatures.includes(floorplans[key])) {
                floorplansWithSelectedFeatures.push(floorplans[key]);
            }
        }
    }

    var floorplans = floorplansWithSelectedFeatures;
    return floorplans;
}

/**
* @param floorplan array
* this function iterates through the array and constructs a card for each one
*/
function rp_archive_fp_makeAllCards(floorplans){
    strVar = "";

    if (floorplans.length > 0) {
        document.getElementById('rp-archive-fp-data').classList.remove('rp-no-results');
        floorplans = rp_archive_fp_sortFloorplans(floorplans);
        floorplans.forEach(rp_archive_fp_makeFpCard);
    } else {
        document.getElementById('rp-archive-fp-data').classList.add('rp-no-results');
        rp_archive_fp_makeNoFloorplansMessage();
    }


    //apply the html string to the dom
    document.getElementById("floorplans_count").innerHTML = floorplans.length;
    document.getElementById("floorplan_cards").innerHTML = strVar;

    modelEventOpener = document.getElementsByClassName("open-fp-modal");

    for (var i = 0; i < modelEventOpener.length; i++) {
        modelEventOpener[i].addEventListener("click", function(event) {
            document.body.style.overflow = 'hidden';
            if (typeof event.target.parentNode != 'undefined' && event.target.parentNode.dataset.floorplanId != null) {
                document.getElementById(event.target.parentNode.dataset.floorplanId).style.display = 'block';
                //document.getElementById(event.target.parentNode.dataset.floorplanId).classList.add("unit-modal-is-active");
                document.getElementById('popup-background').style.display = 'block';
            }
        });


    }
    
    if (typeof dynamicFpEventListeners == 'function') { 
      dynamicFpEventListeners();
    }
    
}

/**
* @param floorplan
* creates the html elements for the floorplan given
*/

function rp_archive_fp_makeFpCard(floorplan) {
    var svg360 = "<div class='rp-archive-fp-card-icon'><i class='far fa-play-circle'></i></div>"
    if(floorplans_data['isShortcodeUsingPopup']){
        linkto = "<a><figure class='open-fp-modal' data-floorplan-id='"+floorplan['ID']+"'>";
    } else {
        linkto = "<a href="+floorplan['post_url']+"><figure>";
    }
    if (floorplan['matterportLink']) {
        var matterportIcon = svg360;
    } else {
        var matterportIcon = "";
    }

    if (floorplan['has_special'] == "true") {
        var SpecialIcon = "<div class='rp-fp-has-special'><h6><span>&#x2605</span> Special</h6></div>"
    } else {
        var SpecialIcon = "";
    }

    strVar += "<div class='is-rp-fp'>";
    strVar += linkto;
    strVar += "         <img class='rp-lazy' src='' data-src="+floorplan['fpImg']['image']+" alt='"+floorplan['fpImg']['alt']+"' >";
    strVar +=           SpecialIcon;
    strVar += "     <\/figure>";
    strVar += "     <footer class='rp-fp-details'>";
    strVar += "         <div class='rp-fp-title'><h4>"+floorplan['post_title']+"<\/h4>";
    strVar +=           matterportIcon;
    strVar += "         </div><p>"+floorplan['displaySqft']+"<\/p>";
    strVar += "         <p class='rp-starting-at'>";
    strVar += "             <span>"+floorplan['displayRent']+"<\/span>";
    strVar += "             <span class='rp-num-avail rp-primary-accent'>"+floorplan['fpAvailUnitCountDisplay']+"<\/span>";
    strVar += "         <\/p>";
    strVar += "     <\/footer>";
    strVar += "  <\/a>";
    strVar += "<\/div>";

}

function closeFPModals() {
    for (var i = 0; i < fpModals.length; i++) {
        fpModals[i].style.display = 'none';
        //fpModals[i].classList.remove("unit-modal-is-active");
    }
    document.getElementById('popup-background').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openFPFormModal() {
    document.getElementById('gravityFormModal').style.display = 'block';
    document.getElementById('form-popup-background').style.display = 'block';
}

function closeFPFormModal(argument) {
    document.getElementById('gravityFormModal').style.display = 'none';
    document.getElementById('form-popup-background').style.display = 'none';
}

function openApplyPage() {

    radioButtons = document.getElementsByClassName("rp-radio-unit-number");
    for (var i = 0; i < radioButtons.length; i++) {
        if (radioButtons[i].checked) {
            window.open(radioButtons[i].value, "_blank");
        }
    }
}

function rp_archive_fp_makeNoFloorplansMessage() {
    strVar += "<div class='rp-no-results-wrapper'>";
    strVar += "<p class='rp-primary-accent'>No Apartments Found</p>";
    strVar += "<p>Please try another search or <a class='rp-primary-accent' href='"+location.origin+"/contact'>Contact Us</a>.</p>";
    strVar += "<\/div>";
}

function rp_archive_fp_resetValues() {
    all_floorplans_data = JSON.parse(all_floorplans_data_encoded);
    floorplans_data = all_floorplans_data['data'];
    floorplans = all_floorplans_data['all_floorplans'];
    all_floorplans_bed_types = rp_archive_fp_getUniqueBedTypes(all_floorplans_data['all_floorplans']);
    all_floorplans_feature_types = rp_archive_fp_getUniqueFeatureTypes(all_floorplans_data['all_floorplans']);
}