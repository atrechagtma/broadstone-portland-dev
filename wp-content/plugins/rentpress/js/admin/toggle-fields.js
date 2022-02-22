(function($) {

/////////////////////////////////////////////////////////////////////////////////////
/// THIS FUNCTION HIDES AND SHOWS INPUT FIELDS DEPENDING 0N CHECKBOX OPTIONS ////////
/// trigger = ID OF CHECKBOX OPTION /////////////////////////////////////////////////
/// targetClass = CLASS OF CONDITIONALS FIELDS (tr) /////////////////////////////////
/// targetInputClass = CLASS OF INPUTS INSIDE CONDITIONAL FIELDS ////////////////////
/////////////////////////////////////////////////////////////////////////////////////

	function toggleField(trigger, targetClass, targetInputClass) {
		var checkbox = $(trigger);
		var fieldGroup = $(targetClass);
		var fieldGroupInput = $(targetInputClass);

		if (checkbox.is(':checked')) {
		    fieldGroup.show();
		} else {
		    fieldGroup.hide();
		    fieldGroupInput.val("");
		}
	}


	// function toggleSections() {
	// 	var checkbox = $("#rp_single_property_option");
	// 	var singlePropertyFields = $(".field-group-7");
	// 	var archivePropertyFields = $(".field-group-8");
	// 	var singleFloorplanFields = $(".field-group-3");
	// 	var archiveFloorplanFields = $(".field-group-4");

	// 	if (checkbox.is(':checked')) {
	// 		singleFloorplanFields.show();
	// 		archiveFloorplanFields.show();
	// 	    archivePropertyFields.hide();
	// 	} else {
	// 	    archivePropertyFields.show();
	// 	    singleFloorplanFields.hide();
	// 		archiveFloorplanFields.hide();
	// 	}
	// }

/////////////////////////////////////////////////////////////////////////////////////
/// THIS FUNCTION HIDES AND SHOWS FIELDS UNDER THE USE RENTPRESS TEMPLATES OPTION ///
/////////////////////////////////////////////////////////////////////////////////////

	// function toggleTemplateField() {
	// 	var checkbox = $("#rentpress_single_floorplan_setting");
	// 	var fieldGroup = $(".field-group-3");
	// 	var requestInfoUrl = $( "#override_request_link" );

	// 	if (checkbox.is(':checked')) {
	// 	    fieldGroup.show();
	// 	} else {
	// 	    fieldGroup.hide();
	// 	    requestInfoUrl.prop( "checked", false ).change();
	// 	}
	// }
	
/////////////////////////////////////////////////////////////////////////////////////
/// CALL TOGGLE FUNCTIONS ON READY //////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////

	$(document).ready(function() {


		toggleField("#override_cta_link", ".field-group-1", ".field-group-1-input");

		toggleField("#override_request_link", ".field-group-2", ".field-group-2-input");

		toggleField("#show_waitlist_ctas", ".field-group-6", ".field-group-6-input");

		toggleField("#term_rent", ".lease-term-setting", null);

		// toggleSections();

		// toggleField("#rentPress_archive_floorplan_setting", ".field-group-4", null);

		// toggleTemplateField();

/////////////////////////////////////////////////////////////////////////////////////
/// CALL TOGGLE FUNCTIONS ON CHANGE EVENT OF CHECKBOX OPTION ////////////////////////
/////////////////////////////////////////////////////////////////////////////////////

		$( "#override_cta_link" ).change(function() {
			toggleField("#override_cta_link", ".field-group-1", ".field-group-1-input");
		});

		$( "#override_request_link" ).change(function() {
			toggleField("#override_request_link", ".field-group-2", ".field-group-2-input");
		});	


		$( "#show_waitlist_ctas" ).change(function() {
			toggleField("#show_waitlist_ctas", ".field-group-6", ".field-group-6-input");
		});

		$( "#term_rent" ).change(function() {
			toggleField("#term_rent", ".lease-term-setting", null);
		});

		// $( "#rp_single_property_option" ).change(function() {
		// 	toggleSections();
		// });		

		// $( "#rentPress_archive_floorplan_setting" ).change(function() {
		// 	toggleField("#rentPress_archive_floorplan_setting", ".field-group-4", null);
		// });

		// $( "#rentpress_single_floorplan_setting" ).change(function() {
		// 	toggleTemplateField();
		// });

	});

})(jQuery);