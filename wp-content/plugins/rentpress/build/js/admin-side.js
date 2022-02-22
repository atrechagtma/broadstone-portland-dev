
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
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImpxdWVyeS5qc29udmlldy5qcyIsIm1ldGFib3hlcy5qcyIsInN5bmNpbmctcHJvcGVydGllcy5qcyIsInRvZ2dsZS1maWVsZHMuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUN0U0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUN0R0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQ3hJQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJhZG1pbi1zaWRlLmpzIiwic291cmNlc0NvbnRlbnQiOlsiXG4vKiFcbmpRdWVyeSBKU09OVmlldy5cbkxpY2Vuc2VkIHVuZGVyIHRoZSBNSVQgTGljZW5zZS5cbiAqL1xuKGZ1bmN0aW9uKGpRdWVyeSkge1xuICB2YXIgJCwgQ29sbGFwc2VyLCBKU09ORm9ybWF0dGVyLCBKU09OVmlldywgSlNPTl9WQUxVRV9UWVBFUztcbiAgSlNPTl9WQUxVRV9UWVBFUyA9IFsnb2JqZWN0JywgJ2FycmF5JywgJ251bWJlcicsICdzdHJpbmcnLCAnYm9vbGVhbicsICdudWxsJ107XG4gIEpTT05Gb3JtYXR0ZXIgPSAoZnVuY3Rpb24oKSB7XG4gICAgZnVuY3Rpb24gSlNPTkZvcm1hdHRlcihvcHRpb25zKSB7XG4gICAgICBpZiAob3B0aW9ucyA9PSBudWxsKSB7XG4gICAgICAgIG9wdGlvbnMgPSB7fTtcbiAgICAgIH1cbiAgICAgIHRoaXMub3B0aW9ucyA9IG9wdGlvbnM7XG4gICAgfVxuXG4gICAgSlNPTkZvcm1hdHRlci5wcm90b3R5cGUuaHRtbEVuY29kZSA9IGZ1bmN0aW9uKGh0bWwpIHtcbiAgICAgIGlmIChodG1sICE9PSBudWxsKSB7XG4gICAgICAgIHJldHVybiBodG1sLnRvU3RyaW5nKCkucmVwbGFjZSgvJi9nLCBcIiZhbXA7XCIpLnJlcGxhY2UoL1wiL2csIFwiJnF1b3Q7XCIpLnJlcGxhY2UoLzwvZywgXCImbHQ7XCIpLnJlcGxhY2UoLz4vZywgXCImZ3Q7XCIpO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuICcnO1xuICAgICAgfVxuICAgIH07XG5cbiAgICBKU09ORm9ybWF0dGVyLnByb3RvdHlwZS5qc1N0cmluZyA9IGZ1bmN0aW9uKHMpIHtcbiAgICAgIHMgPSBKU09OLnN0cmluZ2lmeShzKS5zbGljZSgxLCAtMSk7XG4gICAgICByZXR1cm4gdGhpcy5odG1sRW5jb2RlKHMpO1xuICAgIH07XG5cbiAgICBKU09ORm9ybWF0dGVyLnByb3RvdHlwZS5kZWNvcmF0ZVdpdGhTcGFuID0gZnVuY3Rpb24odmFsdWUsIGNsYXNzTmFtZSkge1xuICAgICAgcmV0dXJuIFwiPHNwYW4gY2xhc3M9XFxcIlwiICsgY2xhc3NOYW1lICsgXCJcXFwiPlwiICsgKHRoaXMuaHRtbEVuY29kZSh2YWx1ZSkpICsgXCI8L3NwYW4+XCI7XG4gICAgfTtcblxuICAgIEpTT05Gb3JtYXR0ZXIucHJvdG90eXBlLnZhbHVlVG9IVE1MID0gZnVuY3Rpb24odmFsdWUsIGxldmVsKSB7XG4gICAgICB2YXIgdmFsdWVUeXBlO1xuICAgICAgaWYgKGxldmVsID09IG51bGwpIHtcbiAgICAgICAgbGV2ZWwgPSAwO1xuICAgICAgfVxuICAgICAgdmFsdWVUeXBlID0gT2JqZWN0LnByb3RvdHlwZS50b1N0cmluZy5jYWxsKHZhbHVlKS5tYXRjaCgvXFxzKC4rKV0vKVsxXS50b0xvd2VyQ2FzZSgpO1xuICAgICAgaWYgKHRoaXMub3B0aW9ucy5zdHJpY3QgJiYgIWpRdWVyeS5pbkFycmF5KHZhbHVlVHlwZSwgSlNPTl9WQUxVRV9UWVBFUykpIHtcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKFwiXCIgKyB2YWx1ZVR5cGUgKyBcIiBpcyBub3QgYSB2YWxpZCBKU09OIHZhbHVlIHR5cGVcIik7XG4gICAgICB9XG4gICAgICByZXR1cm4gdGhpc1tcIlwiICsgdmFsdWVUeXBlICsgXCJUb0hUTUxcIl0uY2FsbCh0aGlzLCB2YWx1ZSwgbGV2ZWwpO1xuICAgIH07XG5cbiAgICBKU09ORm9ybWF0dGVyLnByb3RvdHlwZS5udWxsVG9IVE1MID0gZnVuY3Rpb24odmFsdWUpIHtcbiAgICAgIHJldHVybiB0aGlzLmRlY29yYXRlV2l0aFNwYW4oJ251bGwnLCAnbnVsbCcpO1xuICAgIH07XG5cbiAgICBKU09ORm9ybWF0dGVyLnByb3RvdHlwZS51bmRlZmluZWRUb0hUTUwgPSBmdW5jdGlvbigpIHtcbiAgICAgIHJldHVybiB0aGlzLmRlY29yYXRlV2l0aFNwYW4oJ3VuZGVmaW5lZCcsICd1bmRlZmluZWQnKTtcbiAgICB9O1xuXG4gICAgSlNPTkZvcm1hdHRlci5wcm90b3R5cGUubnVtYmVyVG9IVE1MID0gZnVuY3Rpb24odmFsdWUpIHtcbiAgICAgIHJldHVybiB0aGlzLmRlY29yYXRlV2l0aFNwYW4odmFsdWUsICdudW0nKTtcbiAgICB9O1xuXG4gICAgSlNPTkZvcm1hdHRlci5wcm90b3R5cGUuc3RyaW5nVG9IVE1MID0gZnVuY3Rpb24odmFsdWUpIHtcbiAgICAgIHZhciBtdWx0aWxpbmVDbGFzcywgbmV3TGluZVBhdHRlcm47XG4gICAgICBpZiAoL14oaHR0cHxodHRwc3xmaWxlKTpcXC9cXC9bXlxcc10rJC9pLnRlc3QodmFsdWUpKSB7XG4gICAgICAgIHJldHVybiBcIjxhIGhyZWY9XFxcIlwiICsgKHRoaXMuaHRtbEVuY29kZSh2YWx1ZSkpICsgXCJcXFwiPjxzcGFuIGNsYXNzPVxcXCJxXFxcIj5cXFwiPC9zcGFuPlwiICsgKHRoaXMuanNTdHJpbmcodmFsdWUpKSArIFwiPHNwYW4gY2xhc3M9XFxcInFcXFwiPlxcXCI8L3NwYW4+PC9hPlwiO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgbXVsdGlsaW5lQ2xhc3MgPSAnJztcbiAgICAgICAgdmFsdWUgPSB0aGlzLmpzU3RyaW5nKHZhbHVlKTtcbiAgICAgICAgaWYgKHRoaXMub3B0aW9ucy5ubDJicikge1xuICAgICAgICAgIG5ld0xpbmVQYXR0ZXJuID0gLyhbXj5cXFxcclxcXFxuXT8pKFxcXFxyXFxcXG58XFxcXG5cXFxccnxcXFxccnxcXFxcbikvZztcbiAgICAgICAgICBpZiAobmV3TGluZVBhdHRlcm4udGVzdCh2YWx1ZSkpIHtcbiAgICAgICAgICAgIG11bHRpbGluZUNsYXNzID0gJyBtdWx0aWxpbmUnO1xuICAgICAgICAgICAgdmFsdWUgPSAodmFsdWUgKyAnJykucmVwbGFjZShuZXdMaW5lUGF0dGVybiwgJyQxJyArICc8YnIgLz4nKTtcbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIFwiPHNwYW4gY2xhc3M9XFxcInN0cmluZ1wiICsgbXVsdGlsaW5lQ2xhc3MgKyBcIlxcXCI+XFxcIlwiICsgdmFsdWUgKyBcIlxcXCI8L3NwYW4+XCI7XG4gICAgICB9XG4gICAgfTtcblxuICAgIEpTT05Gb3JtYXR0ZXIucHJvdG90eXBlLmJvb2xlYW5Ub0hUTUwgPSBmdW5jdGlvbih2YWx1ZSkge1xuICAgICAgcmV0dXJuIHRoaXMuZGVjb3JhdGVXaXRoU3Bhbih2YWx1ZSwgJ2Jvb2wnKTtcbiAgICB9O1xuXG4gICAgSlNPTkZvcm1hdHRlci5wcm90b3R5cGUuYXJyYXlUb0hUTUwgPSBmdW5jdGlvbihhcnJheSwgbGV2ZWwpIHtcbiAgICAgIHZhciBjb2xsYXBzaWJsZSwgaGFzQ29udGVudHMsIGluZGV4LCBudW1Qcm9wcywgb3V0cHV0LCB2YWx1ZSwgX2ksIF9sZW47XG4gICAgICBpZiAobGV2ZWwgPT0gbnVsbCkge1xuICAgICAgICBsZXZlbCA9IDA7XG4gICAgICB9XG4gICAgICBoYXNDb250ZW50cyA9IGZhbHNlO1xuICAgICAgb3V0cHV0ID0gJyc7XG4gICAgICBudW1Qcm9wcyA9IGFycmF5Lmxlbmd0aDtcbiAgICAgIGZvciAoaW5kZXggPSBfaSA9IDAsIF9sZW4gPSBhcnJheS5sZW5ndGg7IF9pIDwgX2xlbjsgaW5kZXggPSArK19pKSB7XG4gICAgICAgIHZhbHVlID0gYXJyYXlbaW5kZXhdO1xuICAgICAgICBoYXNDb250ZW50cyA9IHRydWU7XG4gICAgICAgIG91dHB1dCArPSAnPGxpPicgKyB0aGlzLnZhbHVlVG9IVE1MKHZhbHVlLCBsZXZlbCArIDEpO1xuICAgICAgICBpZiAobnVtUHJvcHMgPiAxKSB7XG4gICAgICAgICAgb3V0cHV0ICs9ICcsJztcbiAgICAgICAgfVxuICAgICAgICBvdXRwdXQgKz0gJzwvbGk+JztcbiAgICAgICAgbnVtUHJvcHMtLTtcbiAgICAgIH1cbiAgICAgIGlmIChoYXNDb250ZW50cykge1xuICAgICAgICBjb2xsYXBzaWJsZSA9IGxldmVsID09PSAwID8gJycgOiAnIGNvbGxhcHNpYmxlJztcbiAgICAgICAgcmV0dXJuIFwiWzx1bCBjbGFzcz1cXFwiYXJyYXkgbGV2ZWxcIiArIGxldmVsICsgY29sbGFwc2libGUgKyBcIlxcXCI+XCIgKyBvdXRwdXQgKyBcIjwvdWw+XVwiO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuICdbIF0nO1xuICAgICAgfVxuICAgIH07XG5cbiAgICBKU09ORm9ybWF0dGVyLnByb3RvdHlwZS5vYmplY3RUb0hUTUwgPSBmdW5jdGlvbihvYmplY3QsIGxldmVsKSB7XG4gICAgICB2YXIgY29sbGFwc2libGUsIGhhc0NvbnRlbnRzLCBrZXksIG51bVByb3BzLCBvdXRwdXQsIHByb3AsIHZhbHVlO1xuICAgICAgaWYgKGxldmVsID09IG51bGwpIHtcbiAgICAgICAgbGV2ZWwgPSAwO1xuICAgICAgfVxuICAgICAgaGFzQ29udGVudHMgPSBmYWxzZTtcbiAgICAgIG91dHB1dCA9ICcnO1xuICAgICAgbnVtUHJvcHMgPSAwO1xuICAgICAgZm9yIChwcm9wIGluIG9iamVjdCkge1xuICAgICAgICBudW1Qcm9wcysrO1xuICAgICAgfVxuICAgICAgZm9yIChwcm9wIGluIG9iamVjdCkge1xuICAgICAgICB2YWx1ZSA9IG9iamVjdFtwcm9wXTtcbiAgICAgICAgaGFzQ29udGVudHMgPSB0cnVlO1xuICAgICAgICBrZXkgPSB0aGlzLm9wdGlvbnMuZXNjYXBlID8gdGhpcy5qc1N0cmluZyhwcm9wKSA6IHByb3A7XG4gICAgICAgIG91dHB1dCArPSBcIjxsaT48YSBjbGFzcz1cXFwicHJvcFxcXCIgaHJlZj1cXFwiamF2YXNjcmlwdDo7XFxcIj48c3BhbiBjbGFzcz1cXFwicVxcXCI+XFxcIjwvc3Bhbj5cIiArIGtleSArIFwiPHNwYW4gY2xhc3M9XFxcInFcXFwiPlxcXCI8L3NwYW4+PC9hPjogXCIgKyAodGhpcy52YWx1ZVRvSFRNTCh2YWx1ZSwgbGV2ZWwgKyAxKSk7XG4gICAgICAgIGlmIChudW1Qcm9wcyA+IDEpIHtcbiAgICAgICAgICBvdXRwdXQgKz0gJywnO1xuICAgICAgICB9XG4gICAgICAgIG91dHB1dCArPSAnPC9saT4nO1xuICAgICAgICBudW1Qcm9wcy0tO1xuICAgICAgfVxuICAgICAgaWYgKGhhc0NvbnRlbnRzKSB7XG4gICAgICAgIGNvbGxhcHNpYmxlID0gbGV2ZWwgPT09IDAgPyAnJyA6ICcgY29sbGFwc2libGUnO1xuICAgICAgICByZXR1cm4gXCJ7PHVsIGNsYXNzPVxcXCJvYmogbGV2ZWxcIiArIGxldmVsICsgY29sbGFwc2libGUgKyBcIlxcXCI+XCIgKyBvdXRwdXQgKyBcIjwvdWw+fVwiO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgcmV0dXJuICd7IH0nO1xuICAgICAgfVxuICAgIH07XG5cbiAgICBKU09ORm9ybWF0dGVyLnByb3RvdHlwZS5qc29uVG9IVE1MID0gZnVuY3Rpb24oanNvbikge1xuICAgICAgcmV0dXJuIFwiPGRpdiBjbGFzcz1cXFwianNvbnZpZXdcXFwiPlwiICsgKHRoaXMudmFsdWVUb0hUTUwoanNvbikpICsgXCI8L2Rpdj5cIjtcbiAgICB9O1xuXG4gICAgcmV0dXJuIEpTT05Gb3JtYXR0ZXI7XG5cbiAgfSkoKTtcbiAgKHR5cGVvZiBtb2R1bGUgIT09IFwidW5kZWZpbmVkXCIgJiYgbW9kdWxlICE9PSBudWxsKSAmJiAobW9kdWxlLmV4cG9ydHMgPSBKU09ORm9ybWF0dGVyKTtcbiAgQ29sbGFwc2VyID0gKGZ1bmN0aW9uKCkge1xuICAgIGZ1bmN0aW9uIENvbGxhcHNlcigpIHt9XG5cbiAgICBDb2xsYXBzZXIuYmluZEV2ZW50ID0gZnVuY3Rpb24oaXRlbSwgb3B0aW9ucykge1xuICAgICAgdmFyIGNvbGxhcHNlcjtcbiAgICAgIGl0ZW0uZmlyc3RDaGlsZC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIChmdW5jdGlvbihfdGhpcykge1xuICAgICAgICByZXR1cm4gZnVuY3Rpb24oZXZlbnQpIHtcbiAgICAgICAgICByZXR1cm4gX3RoaXMudG9nZ2xlKGV2ZW50LnRhcmdldC5wYXJlbnROb2RlLmZpcnN0Q2hpbGQsIG9wdGlvbnMpO1xuICAgICAgICB9O1xuICAgICAgfSkodGhpcykpO1xuICAgICAgY29sbGFwc2VyID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgICBjb2xsYXBzZXIuY2xhc3NOYW1lID0gJ2NvbGxhcHNlcic7XG4gICAgICBjb2xsYXBzZXIuaW5uZXJIVE1MID0gb3B0aW9ucy5jb2xsYXBzZWQgPyAnKycgOiAnLSc7XG4gICAgICBjb2xsYXBzZXIuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoZnVuY3Rpb24oX3RoaXMpIHtcbiAgICAgICAgcmV0dXJuIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgICAgICAgcmV0dXJuIF90aGlzLnRvZ2dsZShldmVudC50YXJnZXQsIG9wdGlvbnMpO1xuICAgICAgICB9O1xuICAgICAgfSkodGhpcykpO1xuICAgICAgaXRlbS5pbnNlcnRCZWZvcmUoY29sbGFwc2VyLCBpdGVtLmZpcnN0Q2hpbGQpO1xuICAgICAgaWYgKG9wdGlvbnMuY29sbGFwc2VkKSB7XG4gICAgICAgIHJldHVybiB0aGlzLmNvbGxhcHNlKGNvbGxhcHNlcik7XG4gICAgICB9XG4gICAgfTtcblxuICAgIENvbGxhcHNlci5leHBhbmQgPSBmdW5jdGlvbihjb2xsYXBzZXIpIHtcbiAgICAgIHZhciBlbGxpcHNpcywgdGFyZ2V0O1xuICAgICAgdGFyZ2V0ID0gdGhpcy5jb2xsYXBzZVRhcmdldChjb2xsYXBzZXIpO1xuICAgICAgaWYgKHRhcmdldC5zdHlsZS5kaXNwbGF5ID09PSAnJykge1xuICAgICAgICByZXR1cm47XG4gICAgICB9XG4gICAgICBlbGxpcHNpcyA9IHRhcmdldC5wYXJlbnROb2RlLmdldEVsZW1lbnRzQnlDbGFzc05hbWUoJ2VsbGlwc2lzJylbMF07XG4gICAgICB0YXJnZXQucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChlbGxpcHNpcyk7XG4gICAgICB0YXJnZXQuc3R5bGUuZGlzcGxheSA9ICcnO1xuICAgICAgcmV0dXJuIGNvbGxhcHNlci5pbm5lckhUTUwgPSAnLSc7XG4gICAgfTtcblxuICAgIENvbGxhcHNlci5jb2xsYXBzZSA9IGZ1bmN0aW9uKGNvbGxhcHNlcikge1xuICAgICAgdmFyIGVsbGlwc2lzLCB0YXJnZXQ7XG4gICAgICB0YXJnZXQgPSB0aGlzLmNvbGxhcHNlVGFyZ2V0KGNvbGxhcHNlcik7XG4gICAgICBpZiAodGFyZ2V0LnN0eWxlLmRpc3BsYXkgPT09ICdub25lJykge1xuICAgICAgICByZXR1cm47XG4gICAgICB9XG4gICAgICB0YXJnZXQuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgIGVsbGlwc2lzID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnc3BhbicpO1xuICAgICAgZWxsaXBzaXMuY2xhc3NOYW1lID0gJ2VsbGlwc2lzJztcbiAgICAgIGVsbGlwc2lzLmlubmVySFRNTCA9ICcgJmhlbGxpcDsgJztcbiAgICAgIHRhcmdldC5wYXJlbnROb2RlLmluc2VydEJlZm9yZShlbGxpcHNpcywgdGFyZ2V0KTtcbiAgICAgIHJldHVybiBjb2xsYXBzZXIuaW5uZXJIVE1MID0gJysnO1xuICAgIH07XG5cbiAgICBDb2xsYXBzZXIudG9nZ2xlID0gZnVuY3Rpb24oY29sbGFwc2VyLCBvcHRpb25zKSB7XG4gICAgICB2YXIgYWN0aW9uLCBjb2xsYXBzZXJzLCB0YXJnZXQsIF9pLCBfbGVuLCBfcmVzdWx0cztcbiAgICAgIGlmIChvcHRpb25zID09IG51bGwpIHtcbiAgICAgICAgb3B0aW9ucyA9IHt9O1xuICAgICAgfVxuICAgICAgdGFyZ2V0ID0gdGhpcy5jb2xsYXBzZVRhcmdldChjb2xsYXBzZXIpO1xuICAgICAgYWN0aW9uID0gdGFyZ2V0LnN0eWxlLmRpc3BsYXkgPT09ICdub25lJyA/ICdleHBhbmQnIDogJ2NvbGxhcHNlJztcbiAgICAgIGlmIChvcHRpb25zLnJlY3Vyc2l2ZV9jb2xsYXBzZXIpIHtcbiAgICAgICAgY29sbGFwc2VycyA9IGNvbGxhcHNlci5wYXJlbnROb2RlLmdldEVsZW1lbnRzQnlDbGFzc05hbWUoJ2NvbGxhcHNlcicpO1xuICAgICAgICBfcmVzdWx0cyA9IFtdO1xuICAgICAgICBmb3IgKF9pID0gMCwgX2xlbiA9IGNvbGxhcHNlcnMubGVuZ3RoOyBfaSA8IF9sZW47IF9pKyspIHtcbiAgICAgICAgICBjb2xsYXBzZXIgPSBjb2xsYXBzZXJzW19pXTtcbiAgICAgICAgICBfcmVzdWx0cy5wdXNoKHRoaXNbYWN0aW9uXShjb2xsYXBzZXIpKTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gX3Jlc3VsdHM7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICByZXR1cm4gdGhpc1thY3Rpb25dKGNvbGxhcHNlcik7XG4gICAgICB9XG4gICAgfTtcblxuICAgIENvbGxhcHNlci5jb2xsYXBzZVRhcmdldCA9IGZ1bmN0aW9uKGNvbGxhcHNlcikge1xuICAgICAgdmFyIHRhcmdldCwgdGFyZ2V0cztcbiAgICAgIHRhcmdldHMgPSBjb2xsYXBzZXIucGFyZW50Tm9kZS5nZXRFbGVtZW50c0J5Q2xhc3NOYW1lKCdjb2xsYXBzaWJsZScpO1xuICAgICAgaWYgKCF0YXJnZXRzLmxlbmd0aCkge1xuICAgICAgICByZXR1cm47XG4gICAgICB9XG4gICAgICByZXR1cm4gdGFyZ2V0ID0gdGFyZ2V0c1swXTtcbiAgICB9O1xuXG4gICAgcmV0dXJuIENvbGxhcHNlcjtcblxuICB9KSgpO1xuICAkID0galF1ZXJ5O1xuICBKU09OVmlldyA9IHtcbiAgICBjb2xsYXBzZTogZnVuY3Rpb24oZWwpIHtcbiAgICAgIGlmIChlbC5pbm5lckhUTUwgPT09ICctJykge1xuICAgICAgICByZXR1cm4gQ29sbGFwc2VyLmNvbGxhcHNlKGVsKTtcbiAgICAgIH1cbiAgICB9LFxuICAgIGV4cGFuZDogZnVuY3Rpb24oZWwpIHtcbiAgICAgIGlmIChlbC5pbm5lckhUTUwgPT09ICcrJykge1xuICAgICAgICByZXR1cm4gQ29sbGFwc2VyLmV4cGFuZChlbCk7XG4gICAgICB9XG4gICAgfSxcbiAgICB0b2dnbGU6IGZ1bmN0aW9uKGVsKSB7XG4gICAgICByZXR1cm4gQ29sbGFwc2VyLnRvZ2dsZShlbCk7XG4gICAgfVxuICB9O1xuICByZXR1cm4gJC5mbi5KU09OVmlldyA9IGZ1bmN0aW9uKCkge1xuICAgIHZhciBhcmdzLCBkZWZhdWx0T3B0aW9ucywgZm9ybWF0dGVyLCBqc29uLCBtZXRob2QsIG9wdGlvbnMsIG91dHB1dERvYztcbiAgICBhcmdzID0gYXJndW1lbnRzO1xuICAgIGlmIChKU09OVmlld1thcmdzWzBdXSAhPSBudWxsKSB7XG4gICAgICBtZXRob2QgPSBhcmdzWzBdO1xuICAgICAgcmV0dXJuIHRoaXMuZWFjaChmdW5jdGlvbigpIHtcbiAgICAgICAgdmFyICR0aGlzLCBsZXZlbDtcbiAgICAgICAgJHRoaXMgPSAkKHRoaXMpO1xuICAgICAgICBpZiAoYXJnc1sxXSAhPSBudWxsKSB7XG4gICAgICAgICAgbGV2ZWwgPSBhcmdzWzFdO1xuICAgICAgICAgIHJldHVybiAkdGhpcy5maW5kKFwiLmpzb252aWV3IC5jb2xsYXBzaWJsZS5sZXZlbFwiICsgbGV2ZWwpLnNpYmxpbmdzKCcuY29sbGFwc2VyJykuZWFjaChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIHJldHVybiBKU09OVmlld1ttZXRob2RdKHRoaXMpO1xuICAgICAgICAgIH0pO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIHJldHVybiAkdGhpcy5maW5kKCcuanNvbnZpZXcgPiB1bCA+IGxpIC5jb2xsYXBzaWJsZScpLnNpYmxpbmdzKCcuY29sbGFwc2VyJykuZWFjaChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIHJldHVybiBKU09OVmlld1ttZXRob2RdKHRoaXMpO1xuICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgICB9KTtcbiAgICB9IGVsc2Uge1xuICAgICAganNvbiA9IGFyZ3NbMF07XG4gICAgICBvcHRpb25zID0gYXJnc1sxXSB8fCB7fTtcbiAgICAgIGRlZmF1bHRPcHRpb25zID0ge1xuICAgICAgICBjb2xsYXBzZWQ6IGZhbHNlLFxuICAgICAgICBubDJicjogZmFsc2UsXG4gICAgICAgIHJlY3Vyc2l2ZV9jb2xsYXBzZXI6IGZhbHNlLFxuICAgICAgICBlc2NhcGU6IHRydWUsXG4gICAgICAgIHN0cmljdDogZmFsc2VcbiAgICAgIH07XG4gICAgICBvcHRpb25zID0gJC5leHRlbmQoZGVmYXVsdE9wdGlvbnMsIG9wdGlvbnMpO1xuICAgICAgZm9ybWF0dGVyID0gbmV3IEpTT05Gb3JtYXR0ZXIob3B0aW9ucyk7XG4gICAgICBpZiAoT2JqZWN0LnByb3RvdHlwZS50b1N0cmluZy5jYWxsKGpzb24pID09PSAnW29iamVjdCBTdHJpbmddJykge1xuICAgICAgICBqc29uID0gSlNPTi5wYXJzZShqc29uKTtcbiAgICAgIH1cbiAgICAgIG91dHB1dERvYyA9IGZvcm1hdHRlci5qc29uVG9IVE1MKGpzb24pO1xuICAgICAgcmV0dXJuIHRoaXMuZWFjaChmdW5jdGlvbigpIHtcbiAgICAgICAgdmFyICR0aGlzLCBpdGVtLCBpdGVtcywgX2ksIF9sZW4sIF9yZXN1bHRzO1xuICAgICAgICAkdGhpcyA9ICQodGhpcyk7XG4gICAgICAgICR0aGlzLmh0bWwob3V0cHV0RG9jKTtcbiAgICAgICAgaXRlbXMgPSAkdGhpc1swXS5nZXRFbGVtZW50c0J5Q2xhc3NOYW1lKCdjb2xsYXBzaWJsZScpO1xuICAgICAgICBfcmVzdWx0cyA9IFtdO1xuICAgICAgICBmb3IgKF9pID0gMCwgX2xlbiA9IGl0ZW1zLmxlbmd0aDsgX2kgPCBfbGVuOyBfaSsrKSB7XG4gICAgICAgICAgaXRlbSA9IGl0ZW1zW19pXTtcbiAgICAgICAgICBpZiAoaXRlbS5wYXJlbnROb2RlLm5vZGVOYW1lID09PSAnTEknKSB7XG4gICAgICAgICAgICBfcmVzdWx0cy5wdXNoKENvbGxhcHNlci5iaW5kRXZlbnQoaXRlbS5wYXJlbnROb2RlLCBvcHRpb25zKSk7XG4gICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIF9yZXN1bHRzLnB1c2godm9pZCAwKTtcbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIF9yZXN1bHRzO1xuICAgICAgfSk7XG4gICAgfVxuICB9O1xufSkoalF1ZXJ5KTsiLCIvLyBMZWFzZSBUZXJtc1xuXG52YXIgcmVudFByZXNzUHJpY2luZ1NlbGVjdGlvbnMgPSB7fTtcblxuKGZ1bmN0aW9uKCQpIHtcblx0ZnVuY3Rpb24gcmVudHByZXNzVW5pdExlYXNlVGVybU9wdGlvblNlbGVjdGlvblJlbW92YWwoJHRoaXMpIHtcblx0ICAgICR0aGlzLmNsb3Nlc3QoJ2RpdicpLnBhcmVudCgpLmZpbmQoJy5yZW50cHJlc3MtbGVhc2Utb3ZlcnJpZGUnKS5odG1sKCdPdmVycmlkZSBub3QgYWN0aXZlJyk7XG5cdCAgICAkKCcucmVudFByZXNzLWZsb29yLXBsYW4tdW5pdHMtY29udGFpbmVyIGlucHV0W3R5cGU9cmFkaW9dJykucHJvcCgnY2hlY2tlZCcsIGZhbHNlKTtcblx0ICAgIHJldHVybiB7XG5cdCAgICAgICAgYWN0aW9uIDogJ3VwZGF0ZV91bml0X2xlYXNlX3Rlcm1fcHJpY2Vfb3B0aW9uJyxcblx0ICAgICAgICBzZWxlY3Rpb25zIDogJHRoaXMuZGF0YSgnYWN0aW9uJyksXG5cdCAgICAgICAgcG9zdElEIDogJCgnI3Bvc3RfSUQnKS52YWwoKSxcblx0ICAgICAgICB1bml0Q29kZSA6ICR0aGlzLmRhdGEoJ3VuaXQtaWQnKVxuXHQgICAgfTtcblx0fVxuXG5cdGZ1bmN0aW9uIHJlbnRwcmVzc1VuaXRMZWFzZVRlcm1PcHRpb25TZWxlY3Rpb25zKCR0aGlzLCBwcmljaW5nU2VsZWN0aW9ucykge1xuXHQgICAgdmFyIHBhcmVudFVuaXQ7XG5cdCAgICB2YXIgbGVhc2VUZXJtTW9udGg7XG5cdCAgICAkdGhpcy5maW5kKCdpbnB1dFt0eXBlPXJhZGlvXScpLmVhY2goZnVuY3Rpb24oaXRlbSwgaW5kZXgpIHtcblx0ICAgICAgaWYgKCAkKHRoaXMpLmlzKCc6Y2hlY2tlZCcpICkge1xuXHQgICAgICAgIGxlYXNlVGVybU1vbnRoID0gJCh0aGlzKS52YWwoKTtcblx0ICAgICAgICBwYXJlbnRVbml0ID0gJHRoaXMuY2xvc2VzdCgnbGknKS5hdHRyKCdpZCcpO1xuXHQgICAgICAgIHByaWNpbmdTZWxlY3Rpb25zW3BhcmVudFVuaXRdID0gJCh0aGlzKS52YWwoKTtcblx0ICAgICAgICAkdGhpcy5jbG9zZXN0KCdkaXYnKS5wYXJlbnQoKS5maW5kKCcucmVudHByZXNzLWxlYXNlLW92ZXJyaWRlJykuaHRtbChsZWFzZVRlcm1Nb250aCArICcgbW9udGhzJyk7XG5cdCAgICAgIH1cblx0ICAgIH0pO1xuXHQgICAgcmV0dXJuIHtcblx0ICAgICAgICAvLyBDYWxscyB0byBjYWxsYmFjayBtZXRob2QgaW4gcmVudFByZXNzX1BsdWdpbi5waHAgLSB1cGRhdGVfdW5pdF9sZWFzZV90ZXJtX3ByaWNlX29wdGlvbl9jYWxsYmFjaygpXG5cdCAgICAgICAgYWN0aW9uIDogJ3VwZGF0ZV91bml0X2xlYXNlX3Rlcm1fcHJpY2Vfb3B0aW9uJyxcblx0ICAgICAgICBzZWxlY3Rpb25zOiBwcmljaW5nU2VsZWN0aW9ucyxcblx0ICAgICAgICBwb3N0SUQ6ICQoJyNwb3N0X0lEJykudmFsKCksXG5cdCAgICB9O1xuXHR9XG5cblx0JChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKSB7XG5cdCAgICAkKCcucmVudHByZXNzLWZwLXVuaXQtdmlldy1sZWFzZS10ZXJtLXByaWNpbmcnKS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0ICAgICAgICAkKHRoaXMpLnBhcmVudCgpLmZpbmQoJy5yZW50cHJlc3MtZnAtdW5pdC1sZWFzZS10ZXJtLXByaW5nLW9wdGlvbnMnKS5zaG93KCk7XG5cdCAgICB9KTtcblxuXHQgICAgJCgnLnJlbnRwcmVzcy1mcC11bml0LWNsb3NlLWxlYXNlLXRlcm0tcHJpY2luZycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHQgICAgICAgICQodGhpcykuY2xvc2VzdCgnLnJlbnRwcmVzcy1mcC11bml0LWxlYXNlLXRlcm0tcHJpbmctb3B0aW9ucycpLmhpZGUoKTtcblx0ICAgIH0pO1xuXG5cdCAgICAkKCcucmVudHByZXNzLWZwLXVuaXQtdGVybS1sZW5ndGgsIC5yZW50cHJlc3MtZnAtdW5pdC1yZW1vdmUtbGVhc2UtdGVybS1zZWxlY3Rpb24nKS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHQgICAgJCgnLnJlbnRQcmVzcy1mbG9vci1wbGFuLXVuaXRzLWNvbnRhaW5lciAubG9hZGluZy1naWYnKS5zaG93KCk7XG5cdFx0ICAgIFxuXHRcdCAgICBpZiAoICQodGhpcykuZGF0YSgnYWN0aW9uJykgPT0gJ3JlbW92ZS1sZWFzZS1zZWxlY3Rpb24nICkge1xuXHRcdCAgICAgICAgdmFyIGxlYXNlVGVybU9wdGlvblNlbGVjdGlvbnMgPSByZW50cHJlc3NVbml0TGVhc2VUZXJtT3B0aW9uU2VsZWN0aW9uUmVtb3ZhbCgkKHRoaXMpKTtcblx0XHQgICAgfSBlbHNlIHtcblx0XHQgICAgICAgIHZhciBsZWFzZVRlcm1PcHRpb25TZWxlY3Rpb25zID0gcmVudHByZXNzVW5pdExlYXNlVGVybU9wdGlvblNlbGVjdGlvbnMoJCh0aGlzKSwgcmVudFByZXNzUHJpY2luZ1NlbGVjdGlvbnMpO1xuXHRcdCAgICB9XG5cblx0XHQgICAgJC5hamF4KHtcblx0XHQgICAgICAgIC8vIFVSTCBleHBsYWluZWQgaW4gcmVudFByZXNzX1BsdWdpbi5waHAgLSBKdXN0IGNtZCArIHNoaWZ0ICsgZiBmb3IgJ3Byb3BlcnR5UmVmcmVzaCdcblx0XHQgICAgICAgIHVybCA6IHJlbnRQcmVzc09wdGlvbnMuYWpheF91cmwsIFxuXHRcdCAgICAgICAgdHlwZSA6ICdwb3N0Jyxcblx0XHQgICAgICAgIGRhdGEgOiBsZWFzZVRlcm1PcHRpb25TZWxlY3Rpb25zLFxuXHRcdCAgICAgICAgc3VjY2VzcyA6IGZ1bmN0aW9uKCByZXNwb25zZSApIHtcblx0XHQgICAgICAgICAgICB2YXIgcmVzcG9uc2VDb250YWluZXIgPSAkKCcucmVudFByZXNzLXVuaXQtdXBkYXRlLXJlc3BvbnNlLWNvbnRhaW5lcicpO1xuXHRcdCAgICAgICAgICAgIHJlc3BvbnNlQ29udGFpbmVyLnNob3coKTtcblx0XHQgICAgICAgICAgICByZXNwb25zZUNvbnRhaW5lci50ZXh0KHJlc3BvbnNlKTtcblx0XHQgICAgICAgICAgICByZXNwb25zZUNvbnRhaW5lci5kZWxheSgyMDAwKS5oaWRlKCk7XG5cdFx0ICAgICAgICAgICAgJCgnLnJlbnRQcmVzcy1mbG9vci1wbGFuLXVuaXRzLWNvbnRhaW5lciAubG9hZGluZy1naWYnKS5oaWRlKCk7XG5cdFx0ICAgICAgICB9XG5cdFx0ICAgIH0pO1xuXHRcdH0pO1xuXG5cdFx0JCgnaW5wdXRbbmFtZT1cImZwTWF0dGVycG9ydFwiXScpLm9uKCdpbnB1dCcsIGZ1bmN0aW9uKCkge1xuXG5cdFx0XHRpZiAoJCh0aGlzKS52YWwoKSA9PSAnJykge1xuXHRcdFx0XHQkKCdpbnB1dFtuYW1lPVwib3ZlcnJpZGVfbWV0YV9mcE1hdHRlcnBvcnRcIl0nKS5wcm9wKCdjaGVja2VkJywgZmFsc2UpO1xuXHRcdFx0fVxuXHRcdFx0ZWxzZSB7XG5cdFx0XHRcdCQoJ2lucHV0W25hbWU9XCJvdmVycmlkZV9tZXRhX2ZwTWF0dGVycG9ydFwiXScpLnByb3AoJ2NoZWNrZWQnLCB0cnVlKTtcblx0XHRcdH0gXG5cblx0XHR9KTtcblx0fSk7XG59KShqUXVlcnkpO1xuXG4vLyBQcm9wZXJ0eSBDb29yZHMgQ2FsdVxuXG5mdW5jdGlvbiByZW50cHJlc3NGZXRjaENvb3Jkc0Zyb21BZGRyZXNzKGFkZHJlc3MpIHtcblx0dmFyICBnZW9jb2RlciA9IG5ldyBnb29nbGUubWFwcy5HZW9jb2RlcigpO1xuXHRcblx0Z2VvY29kZXIuZ2VvY29kZSggeyAnYWRkcmVzcyc6IGFkZHJlc3N9LCBmdW5jdGlvbihyZXN1bHRzLCBzdGF0dXMpIHtcbiAgICAgICAgaWYgKHN0YXR1cyA9PSBnb29nbGUubWFwcy5HZW9jb2RlclN0YXR1cy5PSykge1xuICAgICAgICAgICAgalF1ZXJ5KCcjcHJvcF9jb29yZHMnKS52YWwocmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbik7XG5cbiAgICAgICAgICAgIGlmIChqUXVlcnkoJ2lucHV0W25hbWU9b3ZlcnJpZGVfc3luY2VkX3Byb3BlcnR5X2Nvb3Jkc19kYXRhXScpLnByb3AoJ2NoZWNrZWQnKSkge1xuXG4gICAgICAgICAgICBcdGpRdWVyeSgnaW5wdXRbbmFtZT1wcm9wTGF0aXR1ZGVdJykudmFsKHJlc3VsdHNbMF0uZ2VvbWV0cnkubG9jYXRpb24ubGF0KCkpO1xuICAgICAgICAgICAgXHRqUXVlcnkoJ2lucHV0W25hbWU9cHJvcExvbmdpdHVkZV0nKS52YWwocmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbi5sbmcoKSk7XG5cbiAgICAgICAgICAgIH1cblxuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgYWxlcnQoXCJHZW9jb2RlIHdhcyBub3Qgc3VjY2Vzc2Z1bCBmb3IgdGhlIGZvbGxvd2luZyByZWFzb246IFwiICsgc3RhdHVzKTtcbiAgICAgICAgfVxuICAgIH0pO1xuXG59IiwidmFyIHJwX2N1cnJlbnRseV9zeW5jX2lzX29uPTA7XG52YXIgcnBfdGhlX3Byb3BfY29kZXM9W107XG5cbnZhciBycF9sb2FkaW5nX2N1YmU9JzxkaXYgY2xhc3M9XCJycC1zay1mb2xkaW5nLWN1YmVcIj4nICtcbiAgICAnPGRpdiBjbGFzcz1cInJwLXNrLWN1YmUxIHJwLXNrLWN1YmVcIj48L2Rpdj4nICsgXG4gICAgJzxkaXYgY2xhc3M9XCJycC1zay1jdWJlMiBycC1zay1jdWJlXCI+PC9kaXY+JyArIFxuICAgICc8ZGl2IGNsYXNzPVwicnAtc2stY3ViZTQgcnAtc2stY3ViZVwiPjwvZGl2PicgKyBcbiAgICAnPGRpdiBjbGFzcz1cInJwLXNrLWN1YmUzIHJwLXNrLWN1YmVcIj48L2Rpdj4nICsgXG4nPC9kaXY+JztcblxuKGZ1bmN0aW9uKCQpIHtcbiAgICAkKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpIHtcbiAgICAgICAgJCgnI3JwLXN5bmMtcHJvcGVydGllcycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXG4gICAgICAgICAgICAkKCcucnAtc3luY2luZy1yZXNwb25kJykuc2hvdygpO1xuICAgICAgICAgICAgJCgnLnJwLXN5bmMtY3RhcycpLmhpZGUoKTtcblxuICAgICAgICAgICAgZnVuY3Rpb24gbmV4dFByb3AoKSB7XG4gICAgICAgICAgICAgICAgdmFyIHByb3BfY29kZT1ycF90aGVfcHJvcF9jb2Rlc1tycF9jdXJyZW50bHlfc3luY19pc19vbl07XG5cbiAgICAgICAgICAgICAgICBpZiAodHlwZW9mIHByb3BfY29kZSAhPSAndW5kZWZpbmVkJykge1xuICAgICAgICAgICAgICAgICAgICAkKCcucnAtc3luY2luZy1yZXNwb25kIC5zYXl0aGlzJykuaHRtbChcbiAgICAgICAgICAgICAgICAgICAgICAgICdSZWZyZXNoaW5nIGFjY291bnQgcHJvcGVydGllcy4gVGhpcyBtYXkgdGFrZSBhIHdoaWxlLi4uPGJyPicrXG4gICAgICAgICAgICAgICAgICAgICAgICAnU3luY2luZyBQcm9wZXJ0eSAnK1xuICAgICAgICAgICAgICAgICAgICAgICAgKChycF9jdXJyZW50bHlfc3luY19pc19vbisxKSsnIG9mICcrcnBfdGhlX3Byb3BfY29kZXMubGVuZ3RoKVxuICAgICAgICAgICAgICAgICAgICApO1xuXG4gICAgICAgICAgICAgICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICAgICAgICAgICAgICB1cmwgOiByZW50UHJlc3NPcHRpb25zLmFqYXhfdXJsLCBcbiAgICAgICAgICAgICAgICAgICAgICAgIHR5cGUgOiAncG9zdCcsXG4gICAgICAgICAgICAgICAgICAgICAgICBkYXRhIDoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGFjdGlvbiA6ICdyZXN5bmNfc2luZ2xlX3Byb3BlcnR5X2J5X3Byb3BfY29kZScsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcHJvcF9jb2RlIDogcHJvcF9jb2RlLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGN1cnJlbnRfcG9zdF90eXBlIDogXCJwcm9wZXJ0aWVzXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICAgICAgc3VjY2VzcyA6IGZ1bmN0aW9uKCByZXNwb25zZSApIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBycF9jdXJyZW50bHlfc3luY19pc19vbisrO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5leHRQcm9wKCk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICAgICAgZXJyb3I6IGZ1bmN0aW9uKHJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcnBfY3VycmVudGx5X3N5bmNfaXNfb24rKztcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBuZXh0UHJvcCgpOyAgXG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH0pOyBcblxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBlbHNlIHtcblxuICAgICAgICAgICAgICAgICAgICBycF9jdXJyZW50bHlfc3luY19pc19vbj0wO1xuICAgICAgICAgICAgICAgICAgICAkKCcucnAtc3luY2luZy1yZXNwb25kJykuZmFkZU91dCgpO1xuICAgICAgICAgICAgICAgICAgICAkKCcucnAtc3luYy1jdGFzJykuc2hvdygpO1xuXG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSAgICBcblxuICAgICAgICAgICAgJCgnLnJwLXN5bmNpbmctcmVzcG9uZCcpLmh0bWwoXG4gICAgICAgICAgICAgICAgJzxkaXYgc3R5bGU9XCJ0ZXh0LWFsaWduOmNlbnRlcjtiYWNrZ3JvdW5kOndoaXRlO3BhZGRpbmc6MjBweDtcIj48c3BhbiBjbGFzcz1cInNheXRoaXNcIj5GZXRjaGluZyBMaXN0IE9mIFByb3BlcnRpZXMhPC9zcGFuPidcbiAgICAgICAgICAgICAgICArcnBfbG9hZGluZ19jdWJlXG4gICAgICAgICAgICApO1xuXG4gICAgICAgICAgICAkLmFqYXgoe1xuICAgICAgICAgICAgICAgIHVybDogcmVudFByZXNzT3B0aW9ucy5hamF4X3VybCxcbiAgICAgICAgICAgICAgICB0eXBlb2Y6ICdwb3N0JyxcbiAgICAgICAgICAgICAgICBkYXRhVHlwZTogJ2pzb24nLFxuICAgICAgICAgICAgICAgIGRhdGEgOiB7XG4gICAgICAgICAgICAgICAgICAgIGFjdGlvbiA6ICdmZXRjaF9wcm9wZXJ0eV9jb2RlcycsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBzdWNjZXNzOiBmdW5jdGlvbiggcmVzcG9uc2UgKSB7XG4gICAgICAgICAgICAgICAgICAgIGlmICh0eXBlb2YgcmVzcG9uc2UuZXJyb3IgPT0gJ29iamVjdCcpIHtcblxuICAgICAgICAgICAgICAgICAgICAkKCcucnAtc3luY2luZy1yZXNwb25kJylcbiAgICAgICAgICAgICAgICAgICAgICAgIC5odG1sKFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICc8ZGl2IHN0eWxlPVwidGV4dC1hbGlnbjpjZW50ZXI7YmFja2dyb3VuZDp3aGl0ZTtwYWRkaW5nOjIwcHg7Y29sb3I6cmVkO2ZvbnQtd2VpZ2h0OmJvbGQ7XCI+JytcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXNwb25zZS5lcnJvci5tZXNzYWdlK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICc8L2Rpdj4nXG4gICAgICAgICAgICAgICAgICAgICAgICApXG4gICAgICAgICAgICAgICAgICAgICAgICAuZGVsYXkoMjAwMClcbiAgICAgICAgICAgICAgICAgICAgICAgIC5mYWRlT3V0KCk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgZWxzZSB7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgIHJwX3RoZV9wcm9wX2NvZGVzPXJlc3BvbnNlLlJlc3BvbnNlRGF0YTtcblxuICAgICAgICAgICAgICAgICAgICAgICAgbmV4dFByb3AoKTtcblxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBlcnJvcjogZnVuY3Rpb24gKHJlc3BvbnNlKSB7XG5cbiAgICAgICAgICAgICAgICAgICAgJCgnLnJwLXN5bmNpbmctcmVzcG9uZCcpLmh0bWwoXG4gICAgICAgICAgICAgICAgICAgICAgICAnPGRpdiBzdHlsZT1cInRleHQtYWxpZ246Y2VudGVyO2JhY2tncm91bmQ6d2hpdGU7cGFkZGluZzoyMHB4O2NvbG9yOiByZWQ7XCI+JytcbiAgICAgICAgICAgICAgICAgICAgICAgICc8Yj5BbiBFcnJvciBIYXMgSGFwcGVuZWQuLi4gVGhlIHN0YXR1cyBvZiB5b3VyIHJlcXVlc3Qgd2FzICcrIHJlc3BvbnNlLnN0YXR1cyArJy48L2I+JytcbiAgICAgICAgICAgICAgICAgICAgICAgICc8L2Rpdj4nXG4gICAgICAgICAgICAgICAgICAgICk7XG5cbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSk7XG5cbiAgICAgICAgJCgnI3JwLXN5bmMtcHJvcGVydHktZnJvbS1lZGl0aW5nLXBhZ2UnKS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblxuICAgICAgICAgICAgJCgnI3JwLXJlc3luYy1hY3Rpdml0eS1jb250YWluZXInKS5zaG93KCk7XG4gICAgICAgICAgICAgICAgICAgICAgICBcbiAgICAgICAgICAgICQoJyNycC1yZXN5bmMtYWN0aXZpdHktY29udGFpbmVyJykuaHRtbChcbiAgICAgICAgICAgICAgICAnPGRpdiBzdHlsZT1cInRleHQtYWxpZ246Y2VudGVyO2JhY2tncm91bmQ6d2hpdGU7cGFkZGluZzoyMHB4O1wiPlJlc3luY2luZyBwcm9wZXJ0eS4gVGhpcyBtYXkgdGFrZSBhIHdoaWxlLi4uJytcbiAgICAgICAgICAgICAgICBycF9sb2FkaW5nX2N1YmVcbiAgICAgICAgICAgICk7XG5cbiAgICAgICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICAgICAgdXJsIDogcmVudFByZXNzT3B0aW9ucy5hamF4X3VybCwgXG4gICAgICAgICAgICAgICAgdHlwZSA6ICdwb3N0JyxcbiAgICAgICAgICAgICAgICBkYXRhIDoge1xuICAgICAgICAgICAgICAgICAgICAvLyBDYWxscyB0byBjYWxsYmFjayBtZXRob2QgaW4gUGx1Z2luLnBocCAtIHJlZnJlc2hfYWNjb3VudF9wcm9wZXJ0aWVzX2NhbGxiYWNrKClcbiAgICAgICAgICAgICAgICAgICAgYWN0aW9uIDogJ3Jlc3luY19zaW5nbGVfcHJvcGVydHknLFxuICAgICAgICAgICAgICAgICAgICBwcm9wZXJ0eV9wb3N0X2lkIDogJCh0aGlzKS5kYXRhKCdwcm9wZXJ0eS1wb3N0LWlkJyksXG4gICAgICAgICAgICAgICAgICAgIGN1cnJlbnRfcG9zdF90eXBlIDogJCh0aGlzKS5kYXRhKCdwb3N0LXR5cGUnKVxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgc3VjY2VzcyA6IGZ1bmN0aW9uKCByZXNwb25zZSApIHtcbiAgICAgICAgICAgICAgICAgICAgJCgnI3JwLXJlc3luYy1hY3Rpdml0eS1jb250YWluZXInKVxuICAgICAgICAgICAgICAgICAgICAgICAgLmh0bWwocmVzcG9uc2UpXG4gICAgICAgICAgICAgICAgICAgICAgICAuZGVsYXkoMjAwMClcbiAgICAgICAgICAgICAgICAgICAgICAgIC5mYWRlT3V0KCk7XG5cbiAgICAgICAgICAgICAgICAgICAgbG9jYXRpb24ucmVsb2FkKCk7XG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBlcm9ycjogZnVuY3Rpb24gKHJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgICAgICQoJyNycC1yZXN5bmMtYWN0aXZpdHktY29udGFpbmVyJykuaHRtbChcbiAgICAgICAgICAgICAgICAgICAgICAgICc8ZGl2IHN0eWxlPVwidGV4dC1hbGlnbjpjZW50ZXI7YmFja2dyb3VuZDp3aGl0ZTtwYWRkaW5nOjIwcHg7Y29sb3I6IHJlZDtcIj4nK1xuICAgICAgICAgICAgICAgICAgICAgICAgJzxiPkFuIEVycm9yIEhhcyBIYXBwZW5lZC4uLiBUaGUgc3RhdHVzIG9mIHlvdXIgcmVxdWVzdCB3YXMgJysgcmVzcG9uc2Uuc3RhdHVzICsnLjwvYj4nK1xuICAgICAgICAgICAgICAgICAgICAgICAgJzwvZGl2PidcbiAgICAgICAgICAgICAgICAgICAgKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTsgXG4gICAgICAgIH0pOyAgICAgICAgXG4gICAgfSk7XG59KShqUXVlcnkpO1xuIiwiKGZ1bmN0aW9uKCQpIHtcblxuLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vL1xuLy8vIFRISVMgRlVOQ1RJT04gSElERVMgQU5EIFNIT1dTIElOUFVUIEZJRUxEUyBERVBFTkRJTkcgME4gQ0hFQ0tCT1ggT1BUSU9OUyAvLy8vLy8vL1xuLy8vIHRyaWdnZXIgPSBJRCBPRiBDSEVDS0JPWCBPUFRJT04gLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vL1xuLy8vIHRhcmdldENsYXNzID0gQ0xBU1MgT0YgQ09ORElUSU9OQUxTIEZJRUxEUyAodHIpIC8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vL1xuLy8vIHRhcmdldElucHV0Q2xhc3MgPSBDTEFTUyBPRiBJTlBVVFMgSU5TSURFIENPTkRJVElPTkFMIEZJRUxEUyAvLy8vLy8vLy8vLy8vLy8vLy8vL1xuLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vL1xuXG5cdGZ1bmN0aW9uIHRvZ2dsZUZpZWxkKHRyaWdnZXIsIHRhcmdldENsYXNzLCB0YXJnZXRJbnB1dENsYXNzKSB7XG5cdFx0dmFyIGNoZWNrYm94ID0gJCh0cmlnZ2VyKTtcblx0XHR2YXIgZmllbGRHcm91cCA9ICQodGFyZ2V0Q2xhc3MpO1xuXHRcdHZhciBmaWVsZEdyb3VwSW5wdXQgPSAkKHRhcmdldElucHV0Q2xhc3MpO1xuXG5cdFx0aWYgKGNoZWNrYm94LmlzKCc6Y2hlY2tlZCcpKSB7XG5cdFx0ICAgIGZpZWxkR3JvdXAuc2hvdygpO1xuXHRcdH0gZWxzZSB7XG5cdFx0ICAgIGZpZWxkR3JvdXAuaGlkZSgpO1xuXHRcdCAgICBmaWVsZEdyb3VwSW5wdXQudmFsKFwiXCIpO1xuXHRcdH1cblx0fVxuXG5cblx0Ly8gZnVuY3Rpb24gdG9nZ2xlU2VjdGlvbnMoKSB7XG5cdC8vIFx0dmFyIGNoZWNrYm94ID0gJChcIiNycF9zaW5nbGVfcHJvcGVydHlfb3B0aW9uXCIpO1xuXHQvLyBcdHZhciBzaW5nbGVQcm9wZXJ0eUZpZWxkcyA9ICQoXCIuZmllbGQtZ3JvdXAtN1wiKTtcblx0Ly8gXHR2YXIgYXJjaGl2ZVByb3BlcnR5RmllbGRzID0gJChcIi5maWVsZC1ncm91cC04XCIpO1xuXHQvLyBcdHZhciBzaW5nbGVGbG9vcnBsYW5GaWVsZHMgPSAkKFwiLmZpZWxkLWdyb3VwLTNcIik7XG5cdC8vIFx0dmFyIGFyY2hpdmVGbG9vcnBsYW5GaWVsZHMgPSAkKFwiLmZpZWxkLWdyb3VwLTRcIik7XG5cblx0Ly8gXHRpZiAoY2hlY2tib3guaXMoJzpjaGVja2VkJykpIHtcblx0Ly8gXHRcdHNpbmdsZUZsb29ycGxhbkZpZWxkcy5zaG93KCk7XG5cdC8vIFx0XHRhcmNoaXZlRmxvb3JwbGFuRmllbGRzLnNob3coKTtcblx0Ly8gXHQgICAgYXJjaGl2ZVByb3BlcnR5RmllbGRzLmhpZGUoKTtcblx0Ly8gXHR9IGVsc2Uge1xuXHQvLyBcdCAgICBhcmNoaXZlUHJvcGVydHlGaWVsZHMuc2hvdygpO1xuXHQvLyBcdCAgICBzaW5nbGVGbG9vcnBsYW5GaWVsZHMuaGlkZSgpO1xuXHQvLyBcdFx0YXJjaGl2ZUZsb29ycGxhbkZpZWxkcy5oaWRlKCk7XG5cdC8vIFx0fVxuXHQvLyB9XG5cbi8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy9cbi8vLyBUSElTIEZVTkNUSU9OIEhJREVTIEFORCBTSE9XUyBGSUVMRFMgVU5ERVIgVEhFIFVTRSBSRU5UUFJFU1MgVEVNUExBVEVTIE9QVElPTiAvLy9cbi8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy9cblxuXHQvLyBmdW5jdGlvbiB0b2dnbGVUZW1wbGF0ZUZpZWxkKCkge1xuXHQvLyBcdHZhciBjaGVja2JveCA9ICQoXCIjcmVudHByZXNzX3NpbmdsZV9mbG9vcnBsYW5fc2V0dGluZ1wiKTtcblx0Ly8gXHR2YXIgZmllbGRHcm91cCA9ICQoXCIuZmllbGQtZ3JvdXAtM1wiKTtcblx0Ly8gXHR2YXIgcmVxdWVzdEluZm9VcmwgPSAkKCBcIiNvdmVycmlkZV9yZXF1ZXN0X2xpbmtcIiApO1xuXG5cdC8vIFx0aWYgKGNoZWNrYm94LmlzKCc6Y2hlY2tlZCcpKSB7XG5cdC8vIFx0ICAgIGZpZWxkR3JvdXAuc2hvdygpO1xuXHQvLyBcdH0gZWxzZSB7XG5cdC8vIFx0ICAgIGZpZWxkR3JvdXAuaGlkZSgpO1xuXHQvLyBcdCAgICByZXF1ZXN0SW5mb1VybC5wcm9wKCBcImNoZWNrZWRcIiwgZmFsc2UgKS5jaGFuZ2UoKTtcblx0Ly8gXHR9XG5cdC8vIH1cblx0XG4vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vXG4vLy8gQ0FMTCBUT0dHTEUgRlVOQ1RJT05TIE9OIFJFQURZIC8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vXG4vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vXG5cblx0JChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24oKSB7XG5cblxuXHRcdHRvZ2dsZUZpZWxkKFwiI292ZXJyaWRlX2N0YV9saW5rXCIsIFwiLmZpZWxkLWdyb3VwLTFcIiwgXCIuZmllbGQtZ3JvdXAtMS1pbnB1dFwiKTtcblxuXHRcdHRvZ2dsZUZpZWxkKFwiI292ZXJyaWRlX3JlcXVlc3RfbGlua1wiLCBcIi5maWVsZC1ncm91cC0yXCIsIFwiLmZpZWxkLWdyb3VwLTItaW5wdXRcIik7XG5cblx0XHR0b2dnbGVGaWVsZChcIiNzaG93X3dhaXRsaXN0X2N0YXNcIiwgXCIuZmllbGQtZ3JvdXAtNlwiLCBcIi5maWVsZC1ncm91cC02LWlucHV0XCIpO1xuXG5cdFx0dG9nZ2xlRmllbGQoXCIjdGVybV9yZW50XCIsIFwiLmxlYXNlLXRlcm0tc2V0dGluZ1wiLCBudWxsKTtcblxuXHRcdC8vIHRvZ2dsZVNlY3Rpb25zKCk7XG5cblx0XHQvLyB0b2dnbGVGaWVsZChcIiNyZW50UHJlc3NfYXJjaGl2ZV9mbG9vcnBsYW5fc2V0dGluZ1wiLCBcIi5maWVsZC1ncm91cC00XCIsIG51bGwpO1xuXG5cdFx0Ly8gdG9nZ2xlVGVtcGxhdGVGaWVsZCgpO1xuXG4vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vXG4vLy8gQ0FMTCBUT0dHTEUgRlVOQ1RJT05TIE9OIENIQU5HRSBFVkVOVCBPRiBDSEVDS0JPWCBPUFRJT04gLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vXG4vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vLy8vXG5cblx0XHQkKCBcIiNvdmVycmlkZV9jdGFfbGlua1wiICkuY2hhbmdlKGZ1bmN0aW9uKCkge1xuXHRcdFx0dG9nZ2xlRmllbGQoXCIjb3ZlcnJpZGVfY3RhX2xpbmtcIiwgXCIuZmllbGQtZ3JvdXAtMVwiLCBcIi5maWVsZC1ncm91cC0xLWlucHV0XCIpO1xuXHRcdH0pO1xuXG5cdFx0JCggXCIjb3ZlcnJpZGVfcmVxdWVzdF9saW5rXCIgKS5jaGFuZ2UoZnVuY3Rpb24oKSB7XG5cdFx0XHR0b2dnbGVGaWVsZChcIiNvdmVycmlkZV9yZXF1ZXN0X2xpbmtcIiwgXCIuZmllbGQtZ3JvdXAtMlwiLCBcIi5maWVsZC1ncm91cC0yLWlucHV0XCIpO1xuXHRcdH0pO1x0XG5cblxuXHRcdCQoIFwiI3Nob3dfd2FpdGxpc3RfY3Rhc1wiICkuY2hhbmdlKGZ1bmN0aW9uKCkge1xuXHRcdFx0dG9nZ2xlRmllbGQoXCIjc2hvd193YWl0bGlzdF9jdGFzXCIsIFwiLmZpZWxkLWdyb3VwLTZcIiwgXCIuZmllbGQtZ3JvdXAtNi1pbnB1dFwiKTtcblx0XHR9KTtcblxuXHRcdCQoIFwiI3Rlcm1fcmVudFwiICkuY2hhbmdlKGZ1bmN0aW9uKCkge1xuXHRcdFx0dG9nZ2xlRmllbGQoXCIjdGVybV9yZW50XCIsIFwiLmxlYXNlLXRlcm0tc2V0dGluZ1wiLCBudWxsKTtcblx0XHR9KTtcblxuXHRcdC8vICQoIFwiI3JwX3NpbmdsZV9wcm9wZXJ0eV9vcHRpb25cIiApLmNoYW5nZShmdW5jdGlvbigpIHtcblx0XHQvLyBcdHRvZ2dsZVNlY3Rpb25zKCk7XG5cdFx0Ly8gfSk7XHRcdFxuXG5cdFx0Ly8gJCggXCIjcmVudFByZXNzX2FyY2hpdmVfZmxvb3JwbGFuX3NldHRpbmdcIiApLmNoYW5nZShmdW5jdGlvbigpIHtcblx0XHQvLyBcdHRvZ2dsZUZpZWxkKFwiI3JlbnRQcmVzc19hcmNoaXZlX2Zsb29ycGxhbl9zZXR0aW5nXCIsIFwiLmZpZWxkLWdyb3VwLTRcIiwgbnVsbCk7XG5cdFx0Ly8gfSk7XG5cblx0XHQvLyAkKCBcIiNyZW50cHJlc3Nfc2luZ2xlX2Zsb29ycGxhbl9zZXR0aW5nXCIgKS5jaGFuZ2UoZnVuY3Rpb24oKSB7XG5cdFx0Ly8gXHR0b2dnbGVUZW1wbGF0ZUZpZWxkKCk7XG5cdFx0Ly8gfSk7XG5cblx0fSk7XG5cbn0pKGpRdWVyeSk7Il19
