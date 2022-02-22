
/*!
jQuery JSONView.
Licensed under the MIT License.
 */
(function(jQuery) {
  var $, Collapser, JSONFormatter, JSONView, JSON_VALUE_TYPES;
  JSON_VALUE_TYPES = ['object', 'array', 'number', 'string', 'boolean', 'null'];
  JSONFormatter = (function() {
    function JSONFormatter(options) {
      if (options == null) {
        options = {};
      }
      this.options = options;
    }

    JSONFormatter.prototype.htmlEncode = function(html) {
      if (html !== null) {
        return html.toString().replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
      } else {
        return '';
      }
    };

    JSONFormatter.prototype.jsString = function(s) {
      s = JSON.stringify(s).slice(1, -1);
      return this.htmlEncode(s);
    };

    JSONFormatter.prototype.decorateWithSpan = function(value, className) {
      return "<span class=\"" + className + "\">" + (this.htmlEncode(value)) + "</span>";
    };

    JSONFormatter.prototype.valueToHTML = function(value, level) {
      var valueType;
      if (level == null) {
        level = 0;
      }
      valueType = Object.prototype.toString.call(value).match(/\s(.+)]/)[1].toLowerCase();
      if (this.options.strict && !jQuery.inArray(valueType, JSON_VALUE_TYPES)) {
        throw new Error("" + valueType + " is not a valid JSON value type");
      }
      return this["" + valueType + "ToHTML"].call(this, value, level);
    };

    JSONFormatter.prototype.nullToHTML = function(value) {
      return this.decorateWithSpan('null', 'null');
    };

    JSONFormatter.prototype.undefinedToHTML = function() {
      return this.decorateWithSpan('undefined', 'undefined');
    };

    JSONFormatter.prototype.numberToHTML = function(value) {
      return this.decorateWithSpan(value, 'num');
    };

    JSONFormatter.prototype.stringToHTML = function(value) {
      var multilineClass, newLinePattern;
      if (/^(http|https|file):\/\/[^\s]+$/i.test(value)) {
        return "<a href=\"" + (this.htmlEncode(value)) + "\"><span class=\"q\">\"</span>" + (this.jsString(value)) + "<span class=\"q\">\"</span></a>";
      } else {
        multilineClass = '';
        value = this.jsString(value);
        if (this.options.nl2br) {
          newLinePattern = /([^>\\r\\n]?)(\\r\\n|\\n\\r|\\r|\\n)/g;
          if (newLinePattern.test(value)) {
            multilineClass = ' multiline';
            value = (value + '').replace(newLinePattern, '$1' + '<br />');
          }
        }
        return "<span class=\"string" + multilineClass + "\">\"" + value + "\"</span>";
      }
    };

    JSONFormatter.prototype.booleanToHTML = function(value) {
      return this.decorateWithSpan(value, 'bool');
    };

    JSONFormatter.prototype.arrayToHTML = function(array, level) {
      var collapsible, hasContents, index, numProps, output, value, _i, _len;
      if (level == null) {
        level = 0;
      }
      hasContents = false;
      output = '';
      numProps = array.length;
      for (index = _i = 0, _len = array.length; _i < _len; index = ++_i) {
        value = array[index];
        hasContents = true;
        output += '<li>' + this.valueToHTML(value, level + 1);
        if (numProps > 1) {
          output += ',';
        }
        output += '</li>';
        numProps--;
      }
      if (hasContents) {
        collapsible = level === 0 ? '' : ' collapsible';
        return "[<ul class=\"array level" + level + collapsible + "\">" + output + "</ul>]";
      } else {
        return '[ ]';
      }
    };

    JSONFormatter.prototype.objectToHTML = function(object, level) {
      var collapsible, hasContents, key, numProps, output, prop, value;
      if (level == null) {
        level = 0;
      }
      hasContents = false;
      output = '';
      numProps = 0;
      for (prop in object) {
        numProps++;
      }
      for (prop in object) {
        value = object[prop];
        hasContents = true;
        key = this.options.escape ? this.jsString(prop) : prop;
        output += "<li><a class=\"prop\" href=\"javascript:;\"><span class=\"q\">\"</span>" + key + "<span class=\"q\">\"</span></a>: " + (this.valueToHTML(value, level + 1));
        if (numProps > 1) {
          output += ',';
        }
        output += '</li>';
        numProps--;
      }
      if (hasContents) {
        collapsible = level === 0 ? '' : ' collapsible';
        return "{<ul class=\"obj level" + level + collapsible + "\">" + output + "</ul>}";
      } else {
        return '{ }';
      }
    };

    JSONFormatter.prototype.jsonToHTML = function(json) {
      return "<div class=\"jsonview\">" + (this.valueToHTML(json)) + "</div>";
    };

    return JSONFormatter;

  })();
  (typeof module !== "undefined" && module !== null) && (module.exports = JSONFormatter);
  Collapser = (function() {
    function Collapser() {}

    Collapser.bindEvent = function(item, options) {
      var collapser;
      item.firstChild.addEventListener('click', (function(_this) {
        return function(event) {
          return _this.toggle(event.target.parentNode.firstChild, options);
        };
      })(this));
      collapser = document.createElement('div');
      collapser.className = 'collapser';
      collapser.innerHTML = options.collapsed ? '+' : '-';
      collapser.addEventListener('click', (function(_this) {
        return function(event) {
          return _this.toggle(event.target, options);
        };
      })(this));
      item.insertBefore(collapser, item.firstChild);
      if (options.collapsed) {
        return this.collapse(collapser);
      }
    };

    Collapser.expand = function(collapser) {
      var ellipsis, target;
      target = this.collapseTarget(collapser);
      if (target.style.display === '') {
        return;
      }
      ellipsis = target.parentNode.getElementsByClassName('ellipsis')[0];
      target.parentNode.removeChild(ellipsis);
      target.style.display = '';
      return collapser.innerHTML = '-';
    };

    Collapser.collapse = function(collapser) {
      var ellipsis, target;
      target = this.collapseTarget(collapser);
      if (target.style.display === 'none') {
        return;
      }
      target.style.display = 'none';
      ellipsis = document.createElement('span');
      ellipsis.className = 'ellipsis';
      ellipsis.innerHTML = ' &hellip; ';
      target.parentNode.insertBefore(ellipsis, target);
      return collapser.innerHTML = '+';
    };

    Collapser.toggle = function(collapser, options) {
      var action, collapsers, target, _i, _len, _results;
      if (options == null) {
        options = {};
      }
      target = this.collapseTarget(collapser);
      action = target.style.display === 'none' ? 'expand' : 'collapse';
      if (options.recursive_collapser) {
        collapsers = collapser.parentNode.getElementsByClassName('collapser');
        _results = [];
        for (_i = 0, _len = collapsers.length; _i < _len; _i++) {
          collapser = collapsers[_i];
          _results.push(this[action](collapser));
        }
        return _results;
      } else {
        return this[action](collapser);
      }
    };

    Collapser.collapseTarget = function(collapser) {
      var target, targets;
      targets = collapser.parentNode.getElementsByClassName('collapsible');
      if (!targets.length) {
        return;
      }
      return target = targets[0];
    };

    return Collapser;

  })();
  $ = jQuery;
  JSONView = {
    collapse: function(el) {
      if (el.innerHTML === '-') {
        return Collapser.collapse(el);
      }
    },
    expand: function(el) {
      if (el.innerHTML === '+') {
        return Collapser.expand(el);
      }
    },
    toggle: function(el) {
      return Collapser.toggle(el);
    }
  };
  return $.fn.JSONView = function() {
    var args, defaultOptions, formatter, json, method, options, outputDoc;
    args = arguments;
    if (JSONView[args[0]] != null) {
      method = args[0];
      return this.each(function() {
        var $this, level;
        $this = $(this);
        if (args[1] != null) {
          level = args[1];
          return $this.find(".jsonview .collapsible.level" + level).siblings('.collapser').each(function() {
            return JSONView[method](this);
          });
        } else {
          return $this.find('.jsonview > ul > li .collapsible').siblings('.collapser').each(function() {
            return JSONView[method](this);
          });
        }
      });
    } else {
      json = args[0];
      options = args[1] || {};
      defaultOptions = {
        collapsed: false,
        nl2br: false,
        recursive_collapser: false,
        escape: true,
        strict: false
      };
      options = $.extend(defaultOptions, options);
      formatter = new JSONFormatter(options);
      if (Object.prototype.toString.call(json) === '[object String]') {
        json = JSON.parse(json);
      }
      outputDoc = formatter.jsonToHTML(json);
      return this.each(function() {
        var $this, item, items, _i, _len, _results;
        $this = $(this);
        $this.html(outputDoc);
        items = $this[0].getElementsByClassName('collapsible');
        _results = [];
        for (_i = 0, _len = items.length; _i < _len; _i++) {
          item = items[_i];
          if (item.parentNode.nodeName === 'LI') {
            _results.push(Collapser.bindEvent(item.parentNode, options));
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      });
    }
  };
})(jQuery);
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
var rp_currently_sync_is_on=0;
var rp_the_prop_codes=[];

var rp_loading_cube='<div class="rp-sk-folding-cube">' +
    '<div class="rp-sk-cube1 rp-sk-cube"></div>' + 
    '<div class="rp-sk-cube2 rp-sk-cube"></div>' + 
    '<div class="rp-sk-cube4 rp-sk-cube"></div>' + 
    '<div class="rp-sk-cube3 rp-sk-cube"></div>' + 
'</div>';

(function($) {
    $(document).ready(function() {
        $('#rp-sync-properties').on('click', function() {

            $('.rp-syncing-respond').show();
            $('.rp-sync-ctas').hide();

            function nextProp() {
                var prop_code=rp_the_prop_codes[rp_currently_sync_is_on];

                if (typeof prop_code != 'undefined') {
                    $('.rp-syncing-respond .saythis').html(
                        'Refreshing account properties. This may take a while...<br>'+
                        'Syncing Property '+
                        ((rp_currently_sync_is_on+1)+' of '+rp_the_prop_codes.length)
                    );

                    $.ajax({
                        url : rentPressOptions.ajax_url, 
                        type : 'post',
                        data : {
                            action : 'resync_single_property_by_prop_code',
                            prop_code : prop_code,
                            current_post_type : "properties",
                        },
                        success : function( response ) {
                            rp_currently_sync_is_on++;
                            nextProp();
                        },
                        error: function(response) {
                            rp_currently_sync_is_on++;
                            nextProp();  
                        }
                    }); 

                }
                else {

                    rp_currently_sync_is_on=0;
                    $('.rp-syncing-respond').fadeOut();
                    $('.rp-sync-ctas').show();

                }
            }    

            $('.rp-syncing-respond').html(
                '<div style="text-align:center;background:white;padding:20px;"><span class="saythis">Fetching List Of Properties!</span>'
                +rp_loading_cube
            );

            $.ajax({
                url: rentPressOptions.ajax_url,
                typeof: 'post',
                dataType: 'json',
                data : {
                    action : 'fetch_property_codes',
                },
                success: function( response ) {
                    if (typeof response.error == 'object') {

                    $('.rp-syncing-respond')
                        .html(
                            '<div style="text-align:center;background:white;padding:20px;color:red;font-weight:bold;">'+
                            response.error.message+
                            '</div>'
                        )
                        .delay(2000)
                        .fadeOut();
                    }
                    else {

                        rp_the_prop_codes=response.ResponseData;

                        nextProp();

                    }
                },
                error: function (response) {

                    $('.rp-syncing-respond').html(
                        '<div style="text-align:center;background:white;padding:20px;color: red;">'+
                        '<b>An Error Has Happened... The status of your request was '+ response.status +'.</b>'+
                        '</div>'
                    );

                }
            });
        });

        $('#rp-sync-property-from-editing-page').on('click', function() {

            $('#rp-resync-activity-container').show();
                        
            $('#rp-resync-activity-container').html(
                '<div style="text-align:center;background:white;padding:20px;">Resyncing property. This may take a while...'+
                rp_loading_cube
            );

            $.ajax({
                url : rentPressOptions.ajax_url, 
                type : 'post',
                data : {
                    // Calls to callback method in Plugin.php - refresh_account_properties_callback()
                    action : 'resync_single_property',
                    property_post_id : $(this).data('property-post-id'),
                    current_post_type : $(this).data('post-type')
                },
                success : function( response ) {
                    $('#rp-resync-activity-container')
                        .html(response)
                        .delay(2000)
                        .fadeOut();

                    location.reload();
                },
                erorr: function (response) {
                    $('#rp-resync-activity-container').html(
                        '<div style="text-align:center;background:white;padding:20px;color: red;">'+
                        '<b>An Error Has Happened... The status of your request was '+ response.status +'.</b>'+
                        '</div>'
                    );
                }
            }); 
        });        
    });
})(jQuery);

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