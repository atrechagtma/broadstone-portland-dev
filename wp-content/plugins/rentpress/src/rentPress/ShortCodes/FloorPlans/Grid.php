<?php

global $rentPress_Service;
$rentPressOptions        = new rentPress_Options();
$availability_type       = $rentPressOptions->getOption('override_unit_visibility');
$today                   = strtotime( "+1 days", time());
$lookahead               = rp_single_fp_grid_getFutureTime($rentPressOptions);

function rp_single_fp_grid_getFutureTime($rentPressOptions)
{
    $date = $rentPressOptions->getOption('use_avail_units_before_this_date');
    return strtotime( "+$date days", time());
}

function isExpired($date) {
    $currentDate   = new DateTime(); 
    $formattedDate = new DateTime($date); 

    if ($date !='' && $formattedDate < $currentDate) {
        $isExpired = true;
    } else {
        $isExpired = false;
    }
    return $isExpired;
}

function rp_single_fp_grid_isAvailable($unit, $option, $today, $lookahead){
    $isAvailable = false;

    switch ($option) {
        // Show all with date and/or availability status
        case 'unit_visibility_1':
            $isAvailable = (intval($unit->is_available) || $unit->is_available_on) ? true : false ;
            break;
        // Show all with availability status
        case 'unit_visibility_2':
            $isAvailable = (intval($unit->is_available)) ? true : false ;
            break;
        // Show units only available today
        case 'unit_visibility_3':
            $isAvailable = ($unit->availableDateTS && $unit->availableDateTS < $today) ? true : false ;
            break;
        // Show available today and soon
        case 'unit_visibility_4':
            $isAvailable = ($unit->availableDateTS && $unit->availableDateTS < $lookahead) ? true : false ;
            break;
        case 'unit_visibility_5':
            $isAvailable = true;
            break;
    }

    return $isAvailable;
}

class rentPress_ShortCodes_FloorPlans_Grid extends rentPress_ShortCodes_Base 
{
    public function handleShortcode($atts, $content = '') 
    {

        ob_start();

        global $wpdb;

        $attributes = shortcode_atts( array(
            'property_code' => false,
            'gravity_form_id' => false,
            'link' => 'popup' //popup: makes modal | availability: goes to availability url | post: goes to the floorplan page
        ), $atts );

        $isShortcodeUsingPopup = $attributes['link'] == 'popup';
        $isShortcode = true;

        //start section
        echo '<section class="rentpress-core-container">';

        // get the floorplan section
        require(RENTPRESS_PLUGIN_DIR."/templates/FloorPlans/archive-floorplans-basic.php");

        if ($isShortcodeUsingPopup) {

            $modalBackgroundStr = '<div id="popup-background" onclick="closeFPModals()"></div>';
            echo $modalBackgroundStr;

            // create modals for each floorplans
            $fpIndex = 0;

            foreach ($all_floorplans as $fp) {
                $unitGridStr = '';
                $leaseTermStr = '';
                $buttonStr = '';
                $available_units_count = 0;
                $fpIndex++;

                // make the tour button
                $showTourButton     = $rentPressOptions->getOption('tour_cta_button');
                $tourURLoverride    = $rentPressOptions->getOption('single_floorplan_schedule_a_tour_url');

                if ($showTourButton == 'true'){

                    if ($tourURLoverride =='') {
                        $schedule_a_tour_url=get_site_url().$tourURLoverride.'?fpName='. $fp->fpName .'&fpBeds='.$fp->fpBeds .'&property_code='. $fp->parent_property_code. '&propertyName=' .get_the_title();
                    } else {
                        $schedule_a_tour_url=$rentPressOptions->getOption('single_floorplan_schedule_a_tour_url') .'?fpName='. $fp->fpName .'&fpBeds='.$fp->fpBeds .'&property_code='. $fp->parent_property_code. '&propertyName=' .get_the_title();
                    }
                $buttonStr.='<a href=" '.$schedule_a_tour_url.'"class="rp-button-alt fp-tour">Schedule Tour</a>';

                }

                // if the shortcode is calling a gravity form use that modal instead of linking out
                if ($attributes['gravity_form_id'] != false) {
                    $buttonStr .= '<a onclick="openFPFormModal()" class="rp-button fp-request-info fp-request">Request Info</a>';
                } else {
                    $requestMoreInfoUrl = get_site_url().'/contact';

                    // if the contact link is overridden, set as the user inputted value
                    if ($rentPressOptions->getOption('override_request_link') == 'true') {
                        $requestMoreInfoUrl = $rentPressOptions->getOption('single_floorplan_request_more_info_url');
                    }

                    // add information to the url for tracking purposes
                    $fpName = str_replace(' ', '%20', $fp->fpName);
                    $requestMoreInfoUrl .= '?fpName='.$fpName.'&fpBeds='.$fp->fpBeds.'&property_code='. $fp->parent_property_code. '&propertyName=' .get_the_title();
                    if ($showTourButton == 'true') {
                        $infoButtonClass = '" class="rp-button-alt fp-request"';
                    } else {
                        $infoButtonClass = '" class="rp-button fp-request-info fp-request"';
                    } 
                    $buttonStr .= '<a href=" '.$requestMoreInfoUrl.'"'.$infoButtonClass.'>Request Info</a>';
                }

                if (isset($distinctTermRents)) {
                    unset($distinctTermRents);
                }
                $distinctTermRents=[];

                if (isset($termValues)) {
                    unset($termValues);
                }
                $termValues=[];


                // if there are units make a units section, otherwise show no availability
                 if (!empty($fp->units)) {
                    $unitGridStr .= '<h4>Choose An Apartment</h4><section class="rp-unit-cards shortcode-units">';
                    $currentDate = date(current_time( 'Y-m-d', $gmt = 0 ));
                    $disablePricing = get_post_meta( get_the_ID(), 'propDisablePricing', true );
                    // add unit card for each unit
                    foreach ($fp->units as $unit) {

                        $unitAvailableDate = date("Y-m-d", strtotime($unit->is_available_on));

                        if ( ( $unit->is_available_on != '1970-01-01' or $availability_type == 'unit_visibility_5' ) && rp_single_fp_grid_isAvailable($unit, $availability_type, $today, $lookahead)) {
                            if ( $currentDate >= $unitAvailableDate ) {
                                $availStr = '<strong>Available Now</strong>';
                            } else {
                                $availStr = 'Available On <br>'.date("m/d/y", strtotime($unit->is_available_on));
                            }
                            $data = json_decode($unit->tpl_data);
                            array_push($distinctTermRents, $data->Rent->TermRent);
                            array_push($termValues, $distinctTermRents[0]);
                            $rentTerms = esc_attr(json_encode((array)$data->Rent->TermRent));

                            if ($rentPressOptions->getOption('rentPress_disable_pricing') !== 'true' && $disablePricing !== 'true') {
                                $unitPricing = '$<span data-is-price data-defualt-rent="'.$unit->rent.'" class="rp-card-rent" data-rent-terms="'.$rentTerms.'"></span>/mo';
                            } else {
                                $unitPricing = $rentPressOptions->getOption('rentPress_disable_pricing_message');
                            }

                            $unitGridStr .= '                    <div class="rp-unit-card" data-unit-code="'.$data->Identification->UnitCode.'">';
                            $unitGridStr .= '                      <input class="rp-radio-unit-number" id="unit-'.$data->Information->Name.'" type="radio" name="unit" value="'.$data->Information->AvailabilityURL.'">';
                            $unitGridStr .= '                        <label class="unit-selector" role="button" for="unit-'.$data->Information->Name.'">';
                            $unitGridStr .= '                          <h5 class="rp-card-title">'.$data->Information->Name.'</h5>';
                            $unitGridStr .= '                          <p>';
                            $unitGridStr .=                           $unitPricing;
                            $unitGridStr .= '                          </p>';
                            $unitGridStr .= '                          <p class="rp-card-sqft">'.$unit->sqft.' sq. ft.</p>';
                            $unitGridStr .= '                          <p class="rp-card-avail">'.$availStr.'</p>';
                            $unitGridStr .= '                        </label>';
                            $unitGridStr .= '                    </div>';
                            $available_units_count ++;
                        } 
                    }
                    $unitGridStr .= '            </section>';
                    $termValues = array_column($termValues, 'Term');
                    $globalApplyOverride  = $rentPressOptions->getOption('rentPress_override_apply_url');
                    $propertyApplyLink = get_post_meta(get_the_ID(), 'prop_apply', true );
                    $unitApplyLink = get_post_meta( get_the_ID(), 'prop_apply_unit', true );

                    // make the apply button
                    if ($globalApplyOverride !=='') { //&& $unitApplyLink !=='true') { //global link is not set
                        $buttonStr .= '<a href="'.$globalApplyOverride.'"target="_blank" class="rp-button-alt more-info-button">Apply Now</a>';
                    } elseif ($propertyApplyLink !=='' && $unitApplyLink !== 'true') { //propertyApplyLink exists and option to use only for top section is not true
                        $buttonStr .= '<a href="'.$propertyApplyLink.'"target="_blank" class="rp-button-alt more-info-button">Apply Now</a>';
                    } else { // default state - use the floorplan apply link
                        $buttonStr .= '<a href="'.$fp->fpAvailURL.'" class="rp-button-alt more-info-button fp-apply-now">Apply Now</a>';
                    }
                } else {
                    $unitGridStr .= '<div class="rp-single-no-units-wrapper">
                <h4 class="rp-single-no-units-headline">No Apartments Available</h4>
                <p class="rp-single-no-units-text">This is one of our most popular layouts. No apartments are currently available.</p>
                <br>
                </div>';
                }

                if ($rentPressOptions->getOption('override_request_link') !== 'true' || ($rentPressOptions->getOption('single_floorplan_request_more_info_url') == '')) {
                $requestMoreInfoUrl=get_site_url().'/contact';
                }
                else {
                    $requestMoreInfoUrl=$rentPressOptions->getOption('single_floorplan_request_more_info_url');
                }
                $fpName = str_replace(' ', '%20', $fp->fpName);
                $requestMoreInfoUrl.='?fpName='.$fpName.'&property_code='. $fp->parent_property_code. '&propertyName=' .get_the_title();

                 if (isset($requestMoreInfoUrl)) {
                   $requestInfoStr = '<a href="'.$requestMoreInfoUrl.'" class="rp-button-alt fp-request">Request Info</a>';
                }


                if (!($fp->matterportLink =='')) { ?>

                    <script type="text/javascript">
                        function create3dTourModel<?php echo $fpIndex ?>() {
                        var modalWidth = window.innerWidth*.8;
                        var modalHeight = window.innerHeight*.8;
                        var marginLeft = window.innerWidth*.1;
                        var marginTop = window.innerHeight*.1;
                        var tourModel = '';
                        tourModel += '<div class="background-3d-tour-modal"></div>';
                        tourModel += '<div class="unit-3d-tour-modal"> ';
                        tourModel += '  <div class="close-gallery-modal" onclick="close3dTourUnitModel()"><a></a></div> ';
                        tourModel += '  <div class="body-3d-tour-modal" onclick="close3dTourUnitModel()"> ';
                        tourModel += '      <iframe style="margin-left:'+marginLeft+'px;margin-top:'+marginTop+'px;" width="'+modalWidth+'" height="'+modalHeight+'" class="unit-3d-tour-iframe" src="<?php echo $fp->matterportLink ?>"></iframe> ';
                        tourModel += '  </div> ';
                        tourModel += '</div> ';
                        document.getElementById('unit-3d-tour-modal-wrapper').innerHTML = tourModel;
                        document.getElementById('unit-3d-tour-modal-wrapper').style.display = 'block';
                        }

                        function close3dTourUnitModel() {
                            document.getElementById('unit-3d-tour-modal-wrapper').style.display = 'none';
                        }
                    </script>

                    <style type="text/css">
                        .close-gallery-modal a {
                            float: right;
                            color: #fff;
                            background-color: #838486;
                            width: 24px;
                            text-align: center;
                            border-radius: 50%;
                            height: 24px;
                            cursor: pointer;
                            margin: 24px;           
                        }

                        .close-gallery-modal a:hover {
                            color: #fff;
                        }

                        .close-gallery-modal a:before {
                            font-family: "icomoon";
                            content: "\e938";
                        } 

                        #unit-3d-tour-modal-wrapper {
                            display: none;
                            z-index: 1000000;
                            background-color: #0000008c;
                            width: 100%;
                            height: 100vh;
                            position: fixed;
                            top: 0;
                            left: 0;
                        }
                    </style>

                    <div id="unit-3d-tour-modal-wrapper"></div> 

                <?php 
                $matterportStr = '<a class="rp-3d-tour-link" onclick="create3dTourModel'.$fpIndex.'()"><div class="rp-3d-link-icon"><i class="far fa-play-circle"></i></div>Explore Floor Plan</a>';                

                } else {
                    $matterportStr = "";
                    $tourModel = "";
                }

                if ($fp->bedCount == 0) {
                    $bedCount = "studio";
                } else {
                    $bedCount = $fp->bedCount." bed";
                }

                if (!empty($fp->units) && !empty($termValues) && $rentPressOptions->getOption('disbale_all_units_lt_pricing') !== 'true' && $rentPressOptions->getOption('rentPress_disable_pricing') !== 'true' && $disablePricing !== 'true') {
                $leaseTermStr .= '        <div class="rp-form-legend">';
                $leaseTermStr .= '           <h4>Choose a lease term</h4>';
                $leaseTermStr .= '           <select name="rentTerm" id="">';
                $leaseTermStr .= '                        <option value="">Choose...</option>';
                                         foreach ($termValues as $termRent) :
                $leaseTermStr .= '         <option value="'.$termRent.'">'.$termRent.' Months</option>';
                                         endforeach;
                $leaseTermStr .= '           </select>';
                $leaseTermStr .= '        </div>';
                }

                $specialIsExpired = isExpired($fp->fp_special_expiration);
                
                if($fp->fp_special_text && $specialIsExpired !== true) { 
                    if ($fp->fp_special_link) {
                    $specialSection = '<div class="rp-archive-special"><a href="'.$fp->fp_special_link.'" target="_blank"><span style="font-size: 1.25rem;">&#x2605</span> Special - '.$fp->fp_special_text.'</a></div>';
                    } else {
                        $specialSection = '<div class="rp-archive-special"><span style="font-size: 1.25rem;">&#x2605</span> Special - '.$fp->fp_special_text .'</div>';
                    }
                } else {
                    $specialSection = "";
                }

                // create the modal for this floorplan
                $htmlstr = '';
                $htmlstr .= '<div id="'.$fp->ID.'" class="unit-modal unit-modal-is-active" style="display: none;">';
                $htmlstr .= $specialSection;
                $htmlstr .= '    <span style="cursor: pointer;" onclick="closeFPModals()" class="close-button" aria-label="Close modal">';
                $htmlstr .= '        <span aria-hidden="true">&times;</span>';
                $htmlstr .= '    </span>';
                $htmlstr .= '    <div class="unit-modal-data">';
                $htmlstr .= '        <div class="unit-modal-img-container">';
                $htmlstr .= '            <img class="rp-lazy" src="" data-src="'.$fp->fpImg['image'].'">';
                $htmlstr .=              $matterportStr;
                $htmlstr .= '        </div>';
                $htmlstr .= '        <div class="unit-modal-grid-container">';
                $htmlstr .= '        <h2 class="unit-modal-fp-title">'.$fp->post_title.'</h2>';
                $htmlstr .= '        <div>'.$fp->displaySqft.'| '.$fp->displayRent.'</div>';
                $htmlstr .= '<form class="rp-single-fp-all-the-unit-things">';
                $htmlstr .= '        <div class="rp-form-legend">';
                $htmlstr .= $leaseTermStr;
                $htmlstr .= $unitGridStr;
                $htmlstr .= '        </div>';
                $htmlstr .= '            <footer class="form-buttons">';
                $htmlstr .= $buttonStr;
                $htmlstr .= '            </footer>';
                $htmlstr .= '       </div>';
                $htmlstr .= '        <div class="clearfix"></div>';
                $htmlstr .= '    </div>';
                $htmlstr .= '</div>';
                $htmlstr .= '</form>';
            
                echo $htmlstr;

            }

            // gravity form modal
            if ($attributes['gravity_form_id'] != false) {
                // create clickable background
                $formModal = '<div id="form-popup-background" onclick="closeFPFormModal()" style="display: none; z-index: 100002; background-color: #0000008c; width: 100%; height: 100vh; position: fixed; top: 0; left: 0;"></div>';
                // create the form modal
                $formModal .= '<div id="gravityFormModal" class="fp-modal-form" style="position: fixed; top: 41px; border: 1px solid black; width:100%; height: auto; padding: 15px; background-color: #fff; z-index: 100003; display: none; overflow-y: auto;">';
                // create exit modal X
                $formModal .= '<span style="cursor: pointer;" onclick="closeFPFormModal()" class="close-button" aria-label="Close modal" type="button"></span>';
                echo $formModal;
                // add reguested gravity form to modal
                echo do_shortcode("[gravityform id=".$attributes['gravity_form_id']." title=false description=false ajax=false]");
                // close modal div
                echo '</div>';
            }

        } //end floor plan modal

        //end section
        echo "</section>";

        return ob_get_clean();
        
    }
}