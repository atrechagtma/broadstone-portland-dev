var accentColor            = options.accentColor;
var singlePropTitle   	   = document.getElementById("rp-prop-title");
var singlePropDirections   = document.getElementById("single-prop-directions");
var singlePropPhone        = document.getElementById("single-prop-phone");
var singlePropEmail        = document.getElementById("single-prop-email");
var singlePropWebsite      = document.getElementById("single-prop-website");
var singlePropSchedule     = document.getElementById("single-prop-schedule");
var singlePropContact      = document.getElementById("single-prop-contact");
var singlePropApply        = document.getElementById("single-prop-apply");
var singlePropShare        = document.getElementById("single-prop-share");
var rpFacebook             = document.getElementById("rp-facebook");
var rpTwitter              = document.getElementById("rp-twitter");
var rpInstagram            = document.getElementById("rp-instagram");
var singlePropApplyFilters = document.getElementById("apply_filters_button");
var neighborhoodLearnMore  = document.getElementById("neighborhood-learn-more");
var propSearchBtn          = document.getElementById("rp-prop-search-button");
var fpRequestInfo          = document.getElementById("rp-fp-request-info");
var fpApplyNow             = document.getElementById("rp-fp-apply-now");
var fpRequestInfo2         = document.getElementById("rp-fp-request-info-2");
var fpApplyNow2            = document.getElementById("rp-fp-apply-now-2");
var fpScheduleTour         = document.getElementById("rp-fp-schedule-tour");
var fpScheduleTour2        = document.getElementById("rp-fp-schedule-tour-2");
var fpWaitlist             = document.getElementById("rp-fp-waitlist");
var rpEmail                = document.getElementById("rp-email");
var floorplanCards         = document.getElementsByClassName("open-fp-modal");
var floorplanRequest       = document.getElementsByClassName("fp-request");
var floorplanTour          = document.getElementsByClassName("fp-tour");
var floorplanApplyNow      = document.getElementsByClassName("fp-apply-now");
if(singlePropTitle !== null) {
	singlePropTitle = singlePropTitle.innerText;
}

function RGBToHex(rgb) {
	let sep = rgb.indexOf(",") > -1 ? "," : " ";
	rgb = rgb.substr(4).split(")")[0].split(sep);
	let r = (+rgb[0]).toString(16), g = (+rgb[1]).toString(16), b = (+rgb[2]).toString(16);

	if (r.length == 1)
	r = "0" + r;
	if (g.length == 1)
	g = "0" + g;
	if (b.length == 1)
	b = "0" + b;

	return "#" + r + g + b;
}

function getFloorplanInfoModal(floorplan) {
	fpName = "Floorplan: " + floorplan.parentElement.parentElement.parentElement.getElementsByClassName("unit-modal-fp-title")[0].innerText;
	fpUnits = floorplan.parentElement.parentElement.getElementsByClassName("unit-selector");
	selectedUnit = "Selected Unit: N/A"
	for(i = 0; i < fpUnits.length; i++) {
		labelBackgroundColor = RGBToHex(window.getComputedStyle(fpUnits[i]).backgroundColor);
		if (labelBackgroundColor == accentColor) {
			selectedUnit = "Selected Unit: " + fpUnits[i].htmlFor;
		} 
	}
	fpUnitInfo = fpName + " - " + selectedUnit;
	return fpUnitInfo;
}

function getFloorplanInfo(floorplan) {
	fpName = "Floorplan: " + document.getElementById("rp-fp-name").innerText; 
	fpUnits = document.getElementsByClassName("unit-selector");
	selectedUnit = "Selected Unit: N/A"
	for(i = 0; i < fpUnits.length; i++) {
		labelBackgroundColor = RGBToHex(window.getComputedStyle(fpUnits[i]).backgroundColor);
		if (labelBackgroundColor == accentColor) {
			selectedUnit = "Selected Unit: " + fpUnits[i].htmlFor;
		} 
	}
	fpUnitInfo = fpName + " - " + selectedUnit;
	return fpUnitInfo;
}

// 1
function singlePropertyDirections() {
	ga('send', 'event', 'Get Directions', 'Click', singlePropTitle);
}

// 2
function singlePropertyPhone() {
	ga('send', 'event', 'Phone Call', 'Click', singlePropTitle);
}

// 3
function singlePropertyEmail() {
	ga('send', 'event', 'Send Email', 'Click', singlePropTitle);
}

// 4
function singlePropertyWebsite() {
	ga('send', 'event', 'Visit Property Website', 'Click', singlePropTitle);
}

//
function singlePropertySchedule() {
	ga('send', 'event', 'Schedule a Tour', 'Click', singlePropTitle);
}

// 5
function singlePropertyContact() {
	ga('send', 'event', 'Contact Leasing', 'Click', singlePropTitle);
}

// 6
function singlePropertyApply() {
	ga('send', 'event', 'Apply', 'Click', singlePropTitle);
}

// 7
function singlePropertyShare() {
	ga('send', 'event', 'Share', 'Click', singlePropTitle);
}

// 8
function facebook() {
	ga('send', 'event', 'Social - Facebook', 'Click', singlePropTitle);
}

// 9
function twitter() {
	ga('send', 'event', 'Social - Twitter', 'Click', singlePropTitle);
}

// 10
function instagram() {
	ga('send', 'event', 'Social - Instagram', 'Click', singlePropTitle);
}

//11
function singlePropertyFilterEvents() {
	bedList = document.getElementsByName("selected_floorplans_beds");
	maxPrice = "Max Price/Month: $" + document.getElementById("rp-archive-fp-price-range").innerText;
	bedChoices = [];
	bedChoice = "Bedrooms:";
	for (i = 0; i < bedList.length; i++) {
		if(bedList[i].checked == true) {
			bedChoices.push(" " + bedList[i].value + " bedrooms");
		}
	}
	if (bedChoices.length == 0) {
		bedChoices = " N/A";
	}
	bedChoice += bedChoices;
	searchValues = bedChoice + " - " + maxPrice;
	ga('send', 'event', 'Floorplan Search', 'Filter', searchValues);
} 

//12
function viewFloorplan() {
	fpName = this.nextElementSibling.getElementsByClassName("rp-fp-title")[0].innerText;
	ga('send', 'event', 'View Floorplan', 'Click', fpName);
}

//13
function floorplanRequestInfoModal() {
	event.preventDefault();
	var href = this.href;
	getFloorplanInfoModal(this);
	ga('send', 'event', 'Request Info', 'Click', fpUnitInfo);
	setTimeout(function(){ window.location = href; }, 500);
}

//14
function floorplanTourModal() {
	event.preventDefault();
	var href = this.href;
	getFloorplanInfoModal(this);
	ga('send', 'event', 'Schedule a Tour', 'Click', fpUnitInfo);
	setTimeout(function(){ window.location = href; }, 500);
}

//15
function floorplanApplyModal() {
	event.preventDefault();
	var href = this.href;
	getFloorplanInfoModal(this);
	ga('send', 'event', 'Apply Now', 'Click', fpUnitInfo);
	setTimeout(function(){ window.location = href; }, 500);
}

//16
function neighborhoodLearnMoreEvent() {
	city = this.parentElement.getElementsByTagName("h3")[0].innerText;
	ga('send', 'event', 'Learn More', 'Click', city);
}

//17
function propSearchEvent() {
	searchText = "Search Text: " + document.getElementById("rp-prop-search-field").value;
	bedList = document.getElementsByClassName("prop-bed-filter");
	petList = document.getElementsByClassName("prop-pet-filter");
	bedChoices = [];
	petChoices = [];
	bedChoice = "Bedrooms:";
	petChoice = "Pets:";
	maxPrice = "Max Price/Month: $" + document.getElementById("rp-archive-prop-price-range").innerText;
	if (document.getElementById("rp-prop-search-field").value == "") {
		searchText = "Search Text: N/A"; 
	}
	for (i = 0; i < bedList.length; i++) {
		if(bedList[i].checked == true) {
			bedChoices.push(" " + bedList[i].value + " bedrooms");
		}
	}
	if (bedChoices.length == 0) {
		bedChoices = " N/A";
	}
	bedChoice += bedChoices;

	for (i = 0; i < petList.length; i++) {
		if(petList[i].checked == true) {
			petChoices.push(" " + petList[i].value);
		}
	}
	if (petChoices.length == 0) {
		petChoices = " N/A";
	}
	petChoice += petChoices;

	searchValues = searchText + " - " + bedChoice + " - " + maxPrice + " - " + petChoice;

	ga('send', 'event', 'Property Search', 'Filter', searchValues);
}

//18
function floorplanRequestInfo() {
	getFloorplanInfo(this);
	ga('send', 'event', 'Request Info', 'Click', fpUnitInfo);
}

function floorplanRequestInfo2() {
	getFloorplanInfo(fpRequestInfo);
	ga('send', 'event', 'Request Info', 'Click', fpUnitInfo);
}

//19
function floorplanApply() {
	getFloorplanInfo(this);
	ga('send', 'event', 'Apply Now', 'Click', fpUnitInfo);
}

function floorplanApply2() {
	getFloorplanInfo(fpApplyNow);
	ga('send', 'event', 'Apply Now', 'Click', fpUnitInfo);
}

//20
function floorplanScheduleTour() {
	getFloorplanInfo(this);
	ga('send', 'event', 'Schedule a Tour', 'Click', fpUnitInfo);
}

function floorplanScheduleTour2() {
	getFloorplanInfo(fpScheduleTour);
	ga('send', 'event', 'Schedule a Tour', 'Click', fpUnitInfo);
}

//21
function floorplanWaitlist() {
	getFloorplanInfo(this);
	ga('send', 'event', 'Join Waitlist', 'Click', fpUnitInfo);
}

//22
function socialShare() {
	ga('send', 'event', 'Social - Share', 'Click');
}

// set up dynamic floorplan event listeners
function dynamicFpEventListeners() {
	if(floorplanCards !== null) {
		for (i = 0; i < floorplanCards.length; i++) {
			floorplanCards[i].addEventListener("click", viewFloorplan);
		}
	}

	if(floorplanRequest !== null) {
		for (i = 0; i < floorplanRequest.length; i++) {
			floorplanRequest[i].addEventListener("click", floorplanRequestInfoModal);
		}
	}	

	if(floorplanTour !== null) {
		for (i = 0; i < floorplanTour.length; i++) {
			floorplanTour[i].addEventListener("click", floorplanTourModal);
		}
	}

	if(floorplanApplyNow !== null) {
		for (i = 0; i < floorplanApplyNow.length; i++) {
			floorplanApplyNow[i].addEventListener("click", floorplanApplyModal);
		}
	}
}
dynamicFpEventListeners();

// set up event listeners
if(singlePropDirections !== null) {
	singlePropDirections.addEventListener("click", singlePropertyDirections);
}

if(singlePropPhone !== null) {
	singlePropPhone.addEventListener("click",singlePropertyPhone);
}

if(singlePropEmail !== null) {
	singlePropEmail.addEventListener("click", singlePropertyEmail);
}

if(singlePropWebsite !== null) {
	singlePropWebsite.addEventListener("click", singlePropertyWebsite);
}

if(singlePropSchedule !== null) {
	singlePropSchedule.addEventListener("click", singlePropertySchedule);
}

if(singlePropContact !== null) {
	singlePropContact.addEventListener("click", singlePropertyContact);
}

if(singlePropApply !== null) {
	singlePropApply.addEventListener("click", singlePropertyApply);
}

if(singlePropShare !== null) {
	singlePropShare.addEventListener("click", singlePropertyShare);
}

if(rpFacebook !== null) {
	rpFacebook.addEventListener("click", facebook);
}

if(rpTwitter !== null) {
	rpTwitter.addEventListener("click", twitter);
}

if(rpInstagram !== null) {
	rpInstagram.addEventListener("click", instagram);
}

if(singlePropApplyFilters !== null) {
	singlePropApplyFilters.addEventListener("click", singlePropertyFilterEvents);
}

if(neighborhoodLearnMore !== null) {
	neighborhoodLearnMore.addEventListener("click", neighborhoodLearnMoreEvent);
}

if(propSearchBtn !== null) {
	propSearchBtn.addEventListener("click", propSearchEvent);
}

if(fpRequestInfo !== null) {
	fpRequestInfo.addEventListener("click", floorplanRequestInfo);
}

if(fpApplyNow !== null) {
	fpApplyNow.addEventListener("click", floorplanApply);
}

if(fpRequestInfo2 !== null) {
	fpRequestInfo2.addEventListener("click", floorplanRequestInfo2);
}

if(fpApplyNow2 !== null) {
	fpApplyNow2.addEventListener("click", floorplanApply2);
}

if(fpScheduleTour !== null) {
	fpScheduleTour.addEventListener("click", floorplanScheduleTour);
}

if(fpScheduleTour2 !== null) {
	fpScheduleTour2.addEventListener("click", floorplanScheduleTour2);
}

if(fpWaitlist !== null) {
	fpWaitlist.addEventListener("click", floorplanWaitlist);
}

if(rpEmail !== null) {
	rpEmail.addEventListener("click", socialShare);
}




