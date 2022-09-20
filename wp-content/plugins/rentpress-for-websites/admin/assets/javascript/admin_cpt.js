var apply_link = {};
var gravity_form = {};

function openTab(event, tabId) {
  var i, tabSection, tabButtons;

  tabSection = document.getElementsByClassName("rentpress-tab-section");
  for (i = 0; i < tabSection.length; i++) {
    tabSection[i].style.display = "none";
  }

  tabButtons = document.getElementsByClassName("rentpress-tab-button");
  for (i = 0; i < tabButtons.length; i++) {
    tabButtons[i].className = tabButtons[i].className.replace(
      " rentpress-is-active",
      ""
    );
  }

  document.getElementById(tabId).style.display = "block";
  event.currentTarget.classList.add("rentpress-is-active");

  setTimeout(function () {
    var expandButton = document.getElementById("rentpress-expand-all");
    if (expandButton.classList.contains("rentpress-expanded")) {
      var accordions = document.getElementsByClassName("rentpress-accordion");
      for (i = 0; i < accordions.length; i++) {
        accordions[i].classList.add("rentpress-open");
        var openPanel = accordions[i].nextElementSibling;
        openPanel.style.maxHeight = openPanel.scrollHeight + "px";
      }
    }
  }, 50);
}

function openDefaultTab() {
  var tabButtons = document.getElementsByClassName("rentpress-tab-button");
  if (typeof tabButtons[0] != "undefined") {
    tabButtons[0].click();
  }
}

function toggleAccordion() {
  var accordion = document.getElementsByClassName("rentpress-accordion");
  for (i = 0; i < accordion.length; i++) {
    accordion[i].addEventListener("click", function () {
      this.classList.toggle("rentpress-open");
      var panel = this.nextElementSibling;
      if (panel.style.maxHeight) {
        panel.style.maxHeight = null;
      }
      openAccordionPanels();
    });
  }
}

function expandAll() {
  this.classList.toggle("rentpress-expanded");
  var accordions = document.getElementsByClassName("rentpress-accordion");
  for (i = 0; i < accordions.length; i++) {
    var panel = accordions[i].nextElementSibling;
    if (this.classList.contains("rentpress-expanded")) {
      this.innerHTML = "Close All";
      accordions[i].classList.add("rentpress-open");
      panel.style.maxHeight = panel.scrollHeight + "px";
    } else {
      this.innerHTML = "Expand All";
      accordions[i].classList.remove("rentpress-open");
      if (panel.style.maxHeight) {
        panel.style.maxHeight = null;
      }
    }
  }
}

function setUpExpandAll() {
  var expandButton = document.getElementById("rentpress-expand-all");
  if (typeof expandButton != "undefined" && expandButton != null) {
    expandButton.addEventListener("click", expandAll);
  }
}

function openAccordionPanels(event) {
  var openAccordions = document.getElementsByClassName("rentpress-open");
  for (i = 0; i < openAccordions.length; i++) {
    var openPanel = openAccordions[i].nextElementSibling;
    if (openPanel.style.maxHeight == 0) {
      openPanel.style.maxHeight = "none";
    }
  }
}

function toggleOverrides() {
  var overrideBoxes =
    this.parentElement.parentElement.parentElement.getElementsByClassName(
      "rentpress-override"
    );
  for (var i = 0; i < overrideBoxes.length; i++) {
    if (this.checked == true) {
      overrideBoxes[i].checked = true;
    } else {
      overrideBoxes[i].checked = false;
    }
  }
}

function setUpOverrideAll(event) {
  var selectAll = document.getElementsByClassName("rentpress-override-all");
  for (var i = 0; i < selectAll.length; i++) {
    selectAll[i].addEventListener("change", toggleOverrides);
  }
}

function overrideFields() {
  var overrideCheckboxes =
    document.getElementsByClassName("rentpress-override");
  for (var i = 0; i < overrideCheckboxes.length; i++) {
    if (overrideCheckboxes[i].classList.contains("rentpress-override-all")) {
      continue;
    }
    var overrideCheckboxName = overrideCheckboxes[i].getAttribute("name");
    var inputName = overrideCheckboxName.substring(
      0,
      overrideCheckboxName.length - 9
    );
    var input = document.getElementById(inputName);
    if (overrideCheckboxes[i].checked == true) {
      input.disabled = false;
    } else {
      input.disabled = true;
    }
  }
}

function overrideRangeFields() {
  var overrideCheckboxName = this.getAttribute("name");
  var maxInputOverrideName = overrideCheckboxName.replace("min", "max");
  var maxOverride = document.getElementsByName(maxInputOverrideName)[0];
  maxOverride.click();
}

function setUpOverrides(event) {
  var overrideCheckboxes =
    document.getElementsByClassName("rentpress-override");
  var overrideRangeCheckboxes = document.getElementsByClassName(
    "rentpress-override-range"
  );
  for (var i = 0; i < overrideCheckboxes.length; i++) {
    overrideCheckboxes[i].addEventListener("change", overrideFields);
  }
  for (var i = 0; i < overrideRangeCheckboxes.length; i++) {
    overrideRangeCheckboxes[i].addEventListener("change", overrideRangeFields);
  }
}

function openEditUnitModal(unit) {
  var modalPadding =
    window.innerHeight * 0.1 + "px " + window.innerWidth * 0.1 + "px";
  document.getElementById("rentpress-edit-unit-form-container").style.display =
    "block";
  document.getElementById("rentpress-edit-unit-form-container").style.padding =
    modalPadding;
  document.body.style.overflow = "hidden";

  var unitElements = document.getElementsByClassName(
    "rentpress-edit-unit-form-value"
  );
  for (let index = 0; index < unitElements.length; index++) {
    let editUnitValueKey = unitElements[index].name.replace("_edit", "");
    if (editUnitValueKey == "unit_ready_date") {
      let d = new Date(unit[editUnitValueKey]);
      unitElements[index].value = d.toISOString().split("T")[0];
    } else {
      unitElements[index].value = unit[editUnitValueKey];
    }
  }
}

function closeEditUnitModal() {
  document.getElementById("rentpress-edit-unit-form-container").style.display =
    "none";
  document.body.style.overflow = "auto";
  hideDeleteConfirmation();
}

function clearRequestResults() {
  setTimeout(function () {
    var requestResults = document.getElementsByClassName(
      "rentpress-request-results"
    );
    for (var i = 0; i < requestResults.length; i++) {
      requestResults[i].innerHTML = "";
    }
  }, 3000);
}

function showDeleteConfirmation() {
  document.getElementById(
    "rentpress-delete-confirmation-container"
  ).style.display = "block";
  document.getElementById(
    "rentpress-edit-unit-button-container"
  ).style.display = "none";
}

function hideDeleteConfirmation() {
  document.getElementById(
    "rentpress-edit-unit-button-container"
  ).style.display = "block";
  document.getElementById(
    "rentpress-delete-confirmation-container"
  ).style.display = "none";
}

function filterPropertySelector() {
  var input = document.getElementById("rentpress-property-selector-search");
  var filter = input.value.toUpperCase();
  var properties = document.getElementsByClassName(
    "rentpress-prop-selector-title"
  );

  for (i = 0; i < properties.length; i++) {
    var propertyTitle = properties[i].innerText;
    var selectorRow = properties[i].parentElement;
    if (propertyTitle.toUpperCase().indexOf(filter) > -1) {
      selectorRow.style.display = "inherit";
    } else {
      selectorRow.style.display = "none";
    }
  }
}

window.addEventListener("load", function () {
  openDefaultTab();
  toggleAccordion();
  setUpOverrideAll();
  setUpOverrides();
  setUpExpandAll();
});

window.addEventListener("resize", function () {
  openAccordionPanels();
});

jQuery(document).ready(function ($) {
  $("#rentpress_custom_field_property_office_hours_checkbox").change(
    function () {
      if ($(this).is(":checked")) {
        $(".rentpress-office-hours").each(function () {
          $(this).prop("disabled", false);
        });
      } else {
        $(".rentpress-office-hours").each(function () {
          $(this).prop("disabled", true);
        });
      }
    }
  );

  function refreshAddedUnits() {
    var unitParentFloorplanCode = $(
      "input[name='unit_parent_floorplan_code']"
    ).val();
    var data = {
      action: "rentpress_refresh_added_units_action",
      unit_parent_floorplan_code: unitParentFloorplanCode,
    };
    jQuery.post(ajaxurl, data, function (response) {
      $("#rentpress-unit-display").html(response);
    });
    return false;
  }
  refreshAddedUnits();

  function addUnit() {
    var data = {
      action: "rentpress_create_unit_action",
    };
    $(".rentpress-new-unit-form-value").each((_, unit_data_element) => {
      data[unit_data_element.name] = unit_data_element.value;
    });

    // Unit Input Validations
    // clear error message
    $("#results_for_new_unit").html("");
    // if unit code is not unique or is empty, display error text and don't submit
    if (
      $("#rentpress-unit-code").hasClass("rentpress-unit-error") ||
      $("#rentpress-unit-code").val() == ""
    ) {
      $("#results_for_new_unit").html(
        "<span class='rentpress-error'>The Unit Code must be unique</span>"
      );
      return;
    }
    // if unit name is empty, display error text and don't submit
    if ($("#rentpress-unit-name").val() == "") {
      $("#results_for_new_unit").html(
        "<span class='rentpress-error'>The Unit Name must exist</span>"
      );
      return;
    }
    // if unit date is empty, display error text and don't submit
    if ($("#rentpress-unit-ready-date").val() == "") {
      $("#results_for_new_unit").html(
        "<span class='rentpress-error'>There must be an available date</span>"
      );
      return;
    }

    jQuery.post(ajaxurl, data, function (response) {
      if (response == 0) {
        $("#results_for_new_unit").html(
          "<span class='rentpress-error'>There was an error trying to add new unit</span>"
        );
      } else {
        $("#results_for_new_unit").html(
          "<span class='rentpress-success'>New unit successfully added</span>"
        );
        $("input[name='unit_code']").val("");
        refreshAddedUnits();
      }
      clearRequestResults();
    });
  }

  function editUnit() {
    var data = {
      action: "rentpress_edit_unit_action",
    };
    $(".rentpress-edit-unit-form-value").each((_, unit_data_element) => {
      data[unit_data_element.name] = unit_data_element.value;
    });

    // Unit Input Validations
    // clear error message
    $("#results_for_edit_unit").html("");
    // if unit code is empty, display error text and don't submit
    if ($("#rentpress-edit-unit-code").val() == "") {
      $("#results_for_edit_unit").html(
        "<span class='rentpress-error'>The Unit Code must exist</span>"
      );
      return;
    }
    // if unit name is empty, display error text and don't submit
    if ($("#rentpress-edit-unit-name").val() == "") {
      $("#results_for_edit_unit").html(
        "<span class='rentpress-error'>The Unit Name must exist</span>"
      );
      return;
    }
    // if unit date is empty, display error text and don't submit
    if ($("#rentpress-edit-unit-ready-date").val() == "") {
      $("#results_for_edit_unit").html(
        "<span class='rentpress-error'>There must be an available date</span>"
      );
      return;
    }

    jQuery.post(ajaxurl, data, function (response) {
      if (response == 0) {
        $("#results_for_edit_unit").html(
          "<span class='rentpress-error'>There was an error trying to edit a unit</span>"
        );
      } else {
        $("#results_for_edit_unit").html(
          "<span class='rentpress-success'>Unit successfully edited</span>"
        );
        $("input[name='unit_code']").val("");
        closeEditUnitModal();
        refreshAddedUnits();
      }
      clearRequestResults();
    });
  }

  function deleteUnit() {
    var unitCode = $("#rentpress-edit-unit-code").val();
    var data = {
      action: "rentpress_delete_unit_action",
      unit_code: unitCode,
    };

    jQuery.post(ajaxurl, data, function (response) {
      if (response == 0) {
        $("#results_for_edit_unit").html(
          "<span class='rentpress-error'>There was an error trying to delete unit</span>"
        );
        clearRequestResults();
      } else {
        refreshAddedUnits();
        closeEditUnitModal();
      }
    });
    return false;
  }

  $("#rentpress-new-unit-button").click(function (e) {
    e.preventDefault();
    addUnit();
  });

  $("#rentpress-save-unit-button").click(function (e) {
    e.preventDefault();
    editUnit();
  });

  $("#rentpress-permanent-delete-unit-button").click(function (e) {
    e.preventDefault();
    deleteUnit();
  });
});

/*
 *
 * Property Post Meta JS
 *
 */

/*
 *
 * Sync Options JS
 *
 */

jQuery(document).ready(function ($) {
  var propertyCodes = [];
  var syncIndex = 0;
  var syncLimit = 10;
  var manualSyncIndex = 0;
  var manualPropertyCount = 0;
  var isManualSyncing = false;

  function rentpress_startFullPropertyDataResync() {
    $("#rentpress-resync-loading-image").css("display", "flex");
    $("#results-for-sync-topline-call").html(
      "<span style='color: #3399FF;'>Your property feed data request is starting.</span>"
    );
    $("#results-for-marketing-sync").html("");
    $("#results-for-template-changes").html("");
    syncIndex = 0;
    propertyCodes = [];

    var data = {
      action: "rentpress_getAllRemoteData",
    };

    jQuery
      .post(ajaxurl, data, function (response) {
        $("#results-for-sync-topline-call").html(response);
      })
      .then(function (response) {
        if (response["error"]) {
          $("#results-for-sync-topline-call").html(response["message"]);
          $("#rentpress-resync-loading-image").css("display", "none");
          console.log(response["errorMessage"]);
          return response;
        }

        if (response["synced"]) {
          $("#results-for-sync-topline-call").html(
            "<span style='color: #3399FF;'>Success! Your property feed data has been synced</span>"
          );
        } else {
          $("#results-for-sync-topline-call").html(
            "<span style='color: #3399FF;'>Your property feed data is up to date</span>"
          );
        }
        propertyCodes = response["propertyCodes"];
        rentpress_runMarketingRefreshOnPropertyCodes().then(function () {
          // $("#rentpress-resync-loading-image").css("display", "none");
        });
      })
      .fail(function (response) {
        console.log(response);
        if (response.status === 500) {
          $("#results-for-sync-topline-call").html(
            "It appears there is an internal server error with your site. Check your WordPress debug.log or console. If problem persists, contact 30 Lines for assistance"
          );
        }
        $("#rentpress-resync-loading-image").css("display", "none");
      });
  }

  function rentpress_runMarketingRefreshOnPropertyCodes() {
    if (propertyCodes.length === 0) {
      $("#results-for-marketing-sync").html("No Properties to Sync");
      return;
    }

    if (syncIndex >= propertyCodes.length) {
      $("#rentpress-resync-loading-image").css("display", "none");
      $("#results-for-marketing-sync").html(
        "<span style='color: #3399FF;'>Syncing property post meta: Finished!</span>"
      );
      rentpress_runManualPropertySync();
      return;
    }

    let currentPropertyNumber = syncIndex + 1;
    let currentCodes = propertyCodes.slice(syncIndex, syncIndex + syncLimit);
    $("#results-for-marketing-sync").html(
      "<span style='color: #3399FF;'>Syncing property post meta: " +
        currentPropertyNumber +
        " of " +
        propertyCodes.length +
        "</span>"
    );
    var data = {
      action: "rentpress_getAllMarketingDataForProperties",
      propertyCodes: currentCodes,
    };

    return jQuery
      .post(ajaxurl, data, function () {})
      .then(function () {
        syncIndex += syncLimit;
        rentpress_runMarketingRefreshOnPropertyCodes();
      });
  }

  function rentpress_runManualPropertySync() {
    if (!isManualSyncing) {
      $("#results-for-manual-sync").html(
        "<span style='color: #3399FF;'>Begin manual property data refresh</span>"
      );
      isManualSyncing = true;
    } else {
      let currentPropertyNumber = manualSyncIndex + 1;
      $("#results-for-manual-sync").html(
        "<span style='color: #3399FF;'>Saving manual properties: " +
          currentPropertyNumber +
          " of " +
          manualPropertyCount +
          "</span>"
      );
    }

    var data = {
      action: "rentpress_saveManualPropertyDataToDB",
      propertyCodes: propertyCodes,
      limit: syncLimit,
      offset: manualSyncIndex,
      manualPropertyCount: manualPropertyCount,
    };

    jQuery
      .post(ajaxurl, data, function () {})
      .then(function (response) {
        if (response["error"]) {
          console.log(response["errorMessage"]);
          $("#results-for-manual-sync").html(
            "<span>" + response["message"] + "</span>"
          );
        } else if (response["finished_sync"]) {
          $("#results-for-manual-sync").html(
            "<span style='color: #3399FF;'>" + response["message"] + "</span>"
          );
          isManualSyncing = false;
        } else {
          manualPropertyCount = response["manual_property_count"];
          manualSyncIndex += syncLimit;
          if (manualSyncIndex > manualPropertyCount) {
            $("#results-for-manual-sync").html(
              "<span style='color: #3399FF;'>Finished Manual Data Refresh</span>"
            );
            isManualSyncing = false;
          } else {
            rentpress_runManualPropertySync();
          }
        }
      });
  }

  $("#rentpress-marketing-resync-button").click(function () {
    rentpress_startFullPropertyDataResync();
  });

  // $("#rentpress-pricing-resync-button").click(function () {
  //   $("#rentpress-resync-loading-image").css("display", "block");
  //   $("#results_for_sync").html("");

  //   var data = {
  //     action: "rentpress_getAllPricingDataForProperties",
  //   };

  //   // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
  //   jQuery
  //     .post(ajaxurl, data, function (response) {
  //       $("#results_for_sync").html(response);
  //     })
  //     .fail(function (response) {
  //       console.log(response);
  //       if (response.status === 500) {
  //         $("#results_for_sync").html(
  //           "It appears there is an issue communicating with the server, please try again later. If problem persists contact 30 Lines for assistance"
  //         );
  //       }
  //     })
  //     .always(function () {
  //       $("#rentpress-resync-loading-image").css("display", "none");
  //     });
  // });

  $("#rentpress-theme-template-create").click(function () {
    $("#results-for-sync-topline-call").html("");
    $("#results-for-marketing-sync").html("");
    $("#results-for-template-changes").html("");
    var data = {
      action: "rentpress_createThemeTemplateFile",
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    if (
      confirm(
        "This action will add files to your currently active theme for your chosen posts and pages. Any customizations will be overwritten."
      )
    ) {
      jQuery.post(ajaxurl, data, function (response) {
        console.log(response);
        if (response.includes("Success")) {
          $("#results-for-template-changes").html(
            "<span style='color: #3399FF;'>Success! RentPress Templates have been added to your active theme.</span>"
          );
        } else {
          $("#results-for-template-changes").html(
            "Error! RentPress Templates could not be added to your theme. If this error persists, contact 30 lines for assistance."
          );
        }
      });
    }
  });

  // check the url to see if it should set the tab
  var url = new URL(window.location.href);
  var lastTab = getParams(document.referrer)["settingstab"];
  var urlGet = url.searchParams.get("settingstab")
    ? url.searchParams.get("settingstab")
    : "Feed Configuration";
  var urlTab = lastTab ? lastTab : urlGet;

  btn = document.getElementById("rentpress-marketing-resync-section");
  apiFields = document.querySelectorAll(
    "#rentpress-api-credentials-settings-section input"
  );
  submit_btn = document.querySelector(".resync-options-container #submit");

  if (apiFields.length) {
    for (var i = apiFields.length - 1; i >= 0; i--) {
      field = apiFields[i];
      args2 = { apiFields: apiFields, btn: btn };
      field.addEventListener("change", resyncFieldListener.bind(args2));
    }
    if (apiFields[0].value && apiFields[1].value) {
      btn.style.display = "block";
    } else {
      btn.style.display = "none";
    }
  }

  if (submit_btn) {
    allFields = [
      ...document.querySelectorAll(".rentpress-settings-wrapper input"),
      ...document.querySelectorAll(".rentpress-settings-wrapper select"),
    ];
    for (var i = allFields.length - 1; i >= 0; i--) {
      field = allFields[i];
      field.addEventListener("change", adminFieldsListener.bind(allFields));

      if (field.type == "checkbox" || field.type == "radio") {
        field.dataset.startval = field.checked;
        continue;
      } else {
        field.dataset.startval = field.value;
      }
    }
    if (submit_btn.classList.value.includes("rentpress-options-submit")) {
      submit_btn.style.display = "none";
    }
  }
  changeSettingsTab(urlTab);
});

function makeOptionsPageConfig() {
  // this function is used to collect the diffrent fields and tabs to categorize them for use in the tab switcher
  optionsPageDisplayConfig = {};
  tabsListEl = document.getElementById("rentpress-tabs-menu-list");
  tabListHtml = [];
  // find all settings sections
  if (tabsListEl) {
    sections = document.getElementsByClassName("rentpress-settings-wrapper");
    for (var i = sections.length - 1; i >= 0; i--) {
      section = sections[i];
      page = "";
      fieldIds = [];
      // make sure they have a tab to be added to
      if (typeof section.dataset.page != "undefined") {
        page = section.dataset.page;
        // add the page to the sidebars menu
        tabListHtml.push(
          '<li id="rentpress-' +
            page.replace(/\s/g, "-").toLowerCase() +
            '-option" class="rentpress-tabs-menu-item" onclick="changeSettingsTab(`' +
            page +
            '`)"><h2><span>' +
            page +
            "</a></h2></li>"
        );
        // make the tabs field array if it doesn't exist
        if (typeof optionsPageDisplayConfig[page] == "undefined") {
          optionsPageDisplayConfig[page] = [];
        }
        // add field to the tab array
        optionsPageDisplayConfig[page].push(section.id);
      }
    }
    // create the list items in the menus sidebar
    tabsListEl.innerHTML = [...new Set(tabListHtml)].join("");
    // send the list of tabs and fields
    return optionsPageDisplayConfig;
  }
  return "";
}

function changeSettingsTab(tab) {
  // change active tab in the url
  var refresh =
    window.location.protocol +
    "//" +
    window.location.host +
    window.location.pathname +
    "?page=rentpress&settingstab=" +
    tab;

  optionsPageDisplayConfig = makeOptionsPageConfig();

  selectedSections = optionsPageDisplayConfig[tab];
  // find all settings sections
  sections = document.getElementsByClassName("rentpress-settings-wrapper");
  for (var i = sections.length - 1; i >= 0; i--) {
    section = sections[i];
    if (section != null) {
      // hide all of the fields to be set to block later
      section.style.display = "none";
    }
  }

  if (typeof selectedSections != "undefined" ? selectedSections.length : "") {
    for (var j = selectedSections.length - 1; j >= 0; j--) {
      selectedSection = document.getElementById(selectedSections[j]);
      if (selectedSection != null) {
        // set the fields in this tab to block
        selectedSection.style.display = "block";
      }
    }
  }

  // remove active class from all buttons
  tabBtn = document.getElementsByClassName("rentpress-tabs-menu-item");
  for (var i = tabBtn.length - 1; i >= 0; i--) {
    btn = tabBtn[i];
    btn.classList.remove("rentpress-active-settings-tab-item");
  }
  // add active class to the tab that was clicked
  activeTab = document.getElementById(
    "rentpress-" + tab.replace(/\s/g, "-").toLowerCase() + "-option"
  );
  if (activeTab) {
    activeTab.classList.add("rentpress-active-settings-tab-item");
    window.history.pushState({ path: refresh }, "", refresh);
  }
}

function getParams(url) {
  // get the params from a url string
  var params = {};
  var parser = document.createElement("a");
  parser.href = url;
  var query = parser.search.substring(1);
  var vars = query.split("&");
  for (var i = 0; i < vars.length; i++) {
    var pair = vars[i].split("=");
    params[pair[0]] = decodeURIComponent(pair[1]);
  }
  return params;
}

function adminFieldsListener(event) {
  submit = document.querySelector(".resync-options-container #submit");
  change = false;
  for (var i = allFields.length - 1; i >= 0; i--) {
    el = allFields[i];
    if (el.type == "checkbox" || el.type == "radio") {
      if (el.checked != (el.dataset.startval === "true")) {
        change = true;
      }
    } else {
      if (el.value != el.dataset.startval) {
        change = true;
      }
    }
  }
  if (change) {
    submit.style.display = "block";
  } else {
    submit.style.display = "none";
  }
}

window.onload = function () {
  checkboxes = getTemplateCheckBoxes();
  for (var i = checkboxes.length - 1; i >= 0; i--) {
    checkbox = checkboxes[i];
    checkbox.addEventListener("click", checkTemplateValues);
  }
  checkTemplateValues();
};

function checkTemplateValues() {
  checkboxes = getTemplateCheckBoxes();
  btn = document.getElementById("rentpress-theme-template-create");
  checked = false;
  for (var i = checkboxes.length - 1; i >= 0; i--) {
    if (checkboxes[i].checked) {
      checked = true;
    }
  }
  if (checked) {
    btn.style.display = "block";
  } else {
    btn.style.display = "none";
  }
}

function getTemplateCheckBoxes() {
  return [
    document.querySelector(
      "input#rentpress_post_templates_property_archive_section"
    ),
    document.querySelector(
      "input#rentpress_post_templates_property_single_section"
    ),
    document.querySelector(
      "input#rentpress_post_templates_floorplan_archive_section"
    ),
    document.querySelector(
      "input#rentpress_post_templates_floorplan_single_section"
    ),
    document.querySelector(
      "input#rentpress_post_templates_amenity_single_section"
    ),
    document.querySelector(
      "input#rentpress_post_templates_city_single_section"
    ),
    document.querySelector(
      "input#rentpress_post_templates_city_archive_section"
    ),
    document.querySelector(
      "input#rentpress_post_templates_property_taxonomy_single_section"
    ),
    document.querySelector(
      "input#rentpress_post_templates_property_type_single_section"
    ),
    document.querySelector("input#rentpress_post_templates_pet_single_section"),
    document.querySelector(
      "input#rentpress_post_templates_feature_single_section"
    ),
  ];
}

jQuery(document).ready(function ($) {
  var custom_uploader,
    click_elem = jQuery(".rentpress-image-uploader-field");

  click_elem.click(function (e) {
    target = document.getElementById(e.currentTarget.dataset.target + "-field");
    image = document.getElementById(e.currentTarget.dataset.target + "-image");
    limit = e.currentTarget.dataset.limit ?? true;
    e.preventDefault(e.currentTarget.dataset);
    //If the uploader object has already been created, reopen the dialog
    if (custom_uploader) {
      custom_uploader.open();
      return;
    }
    //Extend the wp.media object
    custom_uploader = wp.media.frames.file_frame = wp.media({
      title: "Choose Image",
      button: {
        text: "Choose Image",
      },
      multiple: limit == "false",
    });
    //When a file is selected, grab the URL and set it as the text field's value
    if (limit == "false") {
      custom_uploader.on("select", function () {
        var selection = custom_uploader.state().get("selection");
        attachment = custom_uploader.state().get("selection").first().toJSON();
        target.value = attachment.url;

        images = [];
        selection.map(function (attachment) {
          attachment = attachment.toJSON();
          images.push(attachment);
        });
        imagesString = JSON.stringify(images);
        imageHTML = "";
        if (images.length) {
          imageHTML += '<div class="rentpress-gallery-upload-previews-grid">';
          for (var i = images.length - 1; i >= 0; i--) {
            image = images[i];
            imageUrl =
              typeof image.sizes.medium.url != undefined
                ? image.sizes.medium.url
                : image.url;
            imageHTML +=
              "<div class='rentpress-gallery-single-image-wrapper'><span onclick='rentpressRemoveGalleryImage(" +
              image.id +
              ", `" +
              e.currentTarget.dataset.target +
              "`, this)'>X</span><img src='" +
              imageUrl +
              "'></div>";
          }
          imageHTML += "</div>";
        }
        document.getElementById(
          e.currentTarget.dataset.target + "-upload-preview-container"
        ).innerHTML = imageHTML;
        target.value = imagesString;
      });
    } else {
      custom_uploader.on("select", function () {
        var selection = custom_uploader.state().get("selection");
        attachment = custom_uploader.state().get("selection").first().toJSON();
        preview = document.getElementById(
          e.currentTarget.dataset.target + "-upload-preview-container"
        );
        image = attachment.url;

        imageHTML = "";
        if (image) {
          imageUrl = attachment.sizes.medium.url
            ? attachment.sizes.medium.url
            : attachment.url;
          imageHTML +=
            '<img id="' +
            e.currentTarget.dataset.target +
            '-image" class="rentpress-image-upload-preview" src="' +
            imageUrl +
            '">';
        }

        target.value = image;
        preview.innerHTML = imageHTML;
        adminFieldsListener();
      });
    }

    //Open the uploader dialog
    custom_uploader.open();
  });
});

function rentpressClearPropertyGallery(event) {
  parent = event.parentElement;
  image_container = document.getElementById(
    parent.getElementsByClassName("rentpress-image-uploader-field")[0].dataset
      .target + "-upload-preview-container"
  );
  parent.querySelector("input").value = "";
  image_container.innerHTML = "";
}

function rentpressRemoveGalleryImage(id, target, event) {
  fieldValue = document.getElementById(target + "-field").value;
  fieldValueObj = JSON.parse(fieldValue);
  thisImage = fieldValueObj.filter((element) => element.id != id);
  imageStr = JSON.stringify(thisImage);
  document.getElementById(target + "-field").value = imageStr;
  event.parentElement.style.display = "none";
}

//window.addEventListener("load", fieldChange, false);

function submitForm() {
  document.getElementById("main-rentpress-settings").submit();
}

function resyncFieldListener(event) {
  if (this.apiFields[0].value && this.apiFields[1].value) {
    this.btn.style.display = "block";
  } else {
    this.btn.style.display = "none";
  }
}

function ApplySettingsEventListener(value = "") {
  let fieldValue = this.value;
  if (value && typeof value === "string") {
    fieldValue = value;
  }
  switch (fieldValue) {
    case "1":
      apply_link.style.display = "none";
      gravity_form.style.display = "none";
      break;
    case "2":
      apply_link.style.display = "block";
      gravity_form.style.display = "none";
      break;

    case "3":
      apply_link.style.display = "none";
      gravity_form.style.display = "block";
      break;
  }
}

window.onload = function () {
  const fields = document.getElementsByClassName("rentpress-shadow-field");
  const applyType = document.getElementById(
    "rentpress_custom_field_property_contact_type"
  );
  apply_link = document.getElementById(
    "rentpress_custom_field_property_specific_contact_link_group"
  );
  gravity_form = document.getElementById(
    "rentpress_custom_field_property_specific_gravity_form_group"
  );

  for (var i = fields.length - 1; i >= 0; i--) {
    field = fields[i];
    field.addEventListener("change", function () {
      if (this.getAttribute("data-start-value") != this.value) {
        document.getElementById(this.getAttribute("data-base-field")).value =
          this.value;
      }
    });
  }

  if (applyType) {
    applyType.addEventListener("change", ApplySettingsEventListener);
    ApplySettingsEventListener(applyType.value);
  }
};
