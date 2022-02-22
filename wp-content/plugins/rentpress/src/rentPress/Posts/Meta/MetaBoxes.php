<?php 

/**
* Create RentPress Meta Boxes for our custom post types
*/

    global $rentPress_Service;

class rentPress_Posts_Meta_MetaBoxes extends rentPress_Base_Meta
{

    public function create()
    {
        add_action( 'add_meta_boxes', [ $this, 'addMetaBoxesForCustomPostTypes' ], 0);
    }

    public function addMetaBoxesForCustomPostTypes()
    {
        global $_wp_post_type_features, $post;
        if ( isset($_wp_post_type_features['editor']) && $_wp_post_type_features['editor'] ) {
            unset($_wp_post_type_features['editor']);
            add_meta_box(
                'wp-content-editor-container',
                __('Description', RENTPRESS_LANG_KEY),
                'inner_custom_box',
                'editor', 'normal', 'low'
            );
        }
        /*************************************/
        /** Properties Post Type Meta Boxes **/
        /*************************************/
        
        /*-->general information */

        if (strpos(get_home_url(), 'venterra') ) {
            add_meta_box(
                'rentPress_prop_info',
                'Venterra - General Information',
                [$this, 'venterraPropertyMetaFields'],
                ['commercial', 'properties', 'apartments']
        );
        } else {
            add_meta_box(
                'rentPress_prop_info',
                'Property - General Information',
                [$this, 'propertyMetaFields'],
                ['commercial', 'properties', 'apartments'],
                'normal',
                'low'
        );
        }

        /*--> property rent ranges */
        $rentPressOptions = new rentPress_Options();
        $rentPressAPIToken = $rentPressOptions->getOption('rentPress_api_token');
        if ( $rentPressAPIToken == '') {
            add_meta_box(
                'rentPress_prop_ranges',
                __( 'Property - Ranges for Search', 'rentPress' ),
                [$this, 'propertyRanges'],
                ['commercial', 'properties', 'apartments'],
                'normal',
                'low'
            );
        }

        /*-->property specials */
        add_meta_box(
            'rentPress_prop_special',
            __( 'Property - Special', 'rentPress' ),
            [$this, 'propertySpecial'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'high'
        );

        /*-->Property Tracking Phone Number */
        add_meta_box(
            'rentPress_property_tracking_phone_meta_box',
            'Property - Tracking Phone Number',
            [$this, 'propertyTrackingPhone'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'high'
        );

        /*-->property apply link */
        add_meta_box(
            'rentPress_property_apply_link',
            __( 'Property - Apply Link', 'rentPress' ),
            [$this, 'propertyApplyLink'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'high'
        );

        /*-->property residents link */
        add_meta_box(
            'rentPress_property_residents_link',
            __( 'Property - Residents Link', 'rentPress' ),
            [$this, 'propertyResidentsLink'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'high'
        );

        /*-->property tagline */
        add_meta_box(
            'rentPress_property_tagline',
            __( 'Property - Tagline', 'rentPress' ),
            [$this, 'propertyTagLine'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'default'
        );

        /*--> property social media */
        add_meta_box(
            'rentPress_prop_social',
            __( 'Property - Social Media', 'rentPress' ),
            [$this, 'propertySocial'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'default'
        );

        /*-->property gallery and reviews shortcodes */
        add_meta_box(
            'rentPress_prop_shortcodes',
            __( 'Property - Gallery and Reviews', 'rentPress' ),
            [$this, 'propertyShortcodes'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'default'
        );

        /*-->property Pet Policy details */
        add_meta_box(
            'rentPress_prop_pet_policy',
            __( 'Property - Pet Policy Details', 'rentPress' ),
            [$this, 'propertyPetPolicy'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'default'
        ); 

        /*-->property tagline meta */
        add_meta_box(
            'rentPress_property_tagline',
            __( 'Property - Tagline', 'rentPress' ),
            [$this, 'propertyTagLine'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'default'
        );

        /*-->property additional search keywords */
        add_meta_box(
            'rentPress_search_terms_meta_box',
            'Additional Search Keywords',
            [$this, 'propertySearchKeywords'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'default'
        );

        //*********************//
        //* Sidebar metaboxes *//
        //*********************//

        /*-->feed override -- sidebar meta box */
        add_meta_box(
            'rentPress_override_feed',
            __( 'RentPress Resync', RENTPRESS_LANG_KEY ),
            [$this, 'propertyResync'],
            ['properties','floorplans','commercial','apartments'],
            'side',
            'high'
        );

        // Disable pricing
        // Commenting out because it is moved to the RentPress Resync metabox
        // add_meta_box(
        //     'rentPress_disable_pricing',
        //     __( 'Disable Pricing', RENTPRESS_LANG_KEY ),
        //     [$this, 'propertyDisablePricing'],
        //     ['properties', 'commercial','apartments'],
        //     'side',
        //     'high'
        // );

        /*-->property coordinates generator */
        $rentPressOptions = new rentPress_Options();
        if ($rentPressOptions->getOption('rentPress_google_api_token') != '') {
            add_meta_box(
                'rentPress_prop_coords_box',
                __( 'Coordinates', 'topline' ),
                [$this, 'rentpress_prop_coords_box'],
                ['commercial', 'properties', 'apartments'],
                'side'
            );
        }           

        /*-->office hours */
        if ($rentPressOptions->getOption('rentPress_api_token') != '') {
            add_meta_box(
                'rentPress_office_hours_meta_box',
                'Office Hours',
                [$this, 'propertyOfficeHours'],
                ['commercial', 'properties', 'apartments'],
                'side',
                'low'
        );
        }

        //**************************//
        //* Ventera-only metaboxes *//
        //**************************//

        if (strpos(get_home_url(), 'venterra') ) {

        /*-->venterra property awards */
        add_meta_box(
            'rentPress_awards_meta_box',
            'Awards',
            [$this, 'propertyAwards'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'high'
        );

        /*-->venterra property videos */
        add_meta_box(
            'rentPress_videos_meta_box',
            'Videos',
            [$this, 'propertyVideos'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'high'
        );

        /*-->Venterra property staff members */
        add_meta_box(
            'rentPress_staff_meta_box',
            'Staff Members',
            [$this, 'propertyStaff'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'low'
        );

        /*--> Venterra Property rankings/reviews  */
        add_meta_box(
            'rentPress_rankings_meta_box',
            'Rankings',
            [$this, 'propertyRankings'],
            ['commercial', 'properties', 'apartments'],
            'side',
            'low'
        );

        /*--> Venterra Property map image  */
        add_meta_box(
            'rentPress_property_map_image',
            'Property Map Image',
            [$this, 'propertyMapImage'],
            ['commercial', 'properties', 'apartments'],
            'side',
            'low'
        );

        /*-->Venterra ILS tracking codes */
        add_meta_box(
            'rentPress_tracking_codes_box',
            __( 'ILS Tracking Codes', 'topline' ),
            [$this, 'rentpress_ils_tracking_codes_box'],
            ['commercial', 'properties', 'apartments'],
            'side',
            'default'
        );

        /*-->Venterra property photos */
        add_meta_box(
            'rentPress_property_general_photos',
            'Property Photos',
            [$this, 'propertyGeneralPhotosContainer'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'high'
        );

        /*-->Venterra assets by bedroom -- wysiwig editor */
        if ( get_post_meta($post->ID, 'propAssetsByNumberOfRooms', true) ) {
            add_meta_box(
                'rentPress_bedroom_assets_meta_box',
                'Assets by Bedroom',
                [$this, 'propertyBedroomAssets'],
                ['commercial', 'properties', 'apartments'],
                'normal',
                'low'
            );
        }

        /*-->Venterra property amenities with images */
        if ( get_post_meta($post->ID, 'amenities', true) ) {
            add_meta_box(
                'rentPress_amenities_meta_box',
                'Apartment Amenities',
                [$this, 'propertyAmenities'],
                ['commercial', 'properties', 'apartments'],
                'normal',
                'low'
            );
        }

        // TODO: Remove in 2020 - Like that other comment
        // Venterra Community Amenities
        if ( get_post_meta($post->ID, 'propCommunityAmenities', true) ) {
        add_meta_box(
            'rentPress_community_amenities_meta_box',
            __( 'Community Amenities', 'rentPress' ),
            [$this, 'propertyCommunityAmenities'],
            ['commercial', 'properties', 'apartments'],
            'normal',
            'low'
        );
        }

        // Venterra Property Logo
        add_meta_box(
            'rentPress_property_logo',
            'Property Logo',
            [$this, 'propertyLogoField'],
            ['commercial', 'properties', 'apartments'],
            'side',
            'low'
        );

        }

        /*-->floor plan post type -- general info meta box */

        add_meta_box(
            'rentPress_fp_special',
            'Floor Plan Special',
            [$this, 'floorPlanSpecial'],
            'floorplans'
        );

        if (strpos(get_home_url(), 'venterra') ) {
            add_meta_box(
                'rentPress_fp_info',
                'Floor Plan Information',
                [$this, 'venterraFloorPlanMetaFields'],
                'floorplans'
            );
        } else { 
            add_meta_box(
                'rentPress_fp_info',
                'Floor Plan Information',
                [$this, 'floorPlanMetaFields'],
                'floorplans'
            );
        }

        add_meta_box(
            'rentPress_fp_unit_listing',
            'Available Units',
            [$this, 'floorPlanUnits'],
            'floorplans'
        );

        // add_meta_box(
        //     'rentPress_fp_matterport_link',
        //     'Matterport Link',
        //     [$this, 'fpMatterportLink'],
        //     'floorplans'
        // );

        // Commenting out until fixed
        // add_meta_box(
        //     'fp_gallery',
        //     'Floor Plan Gallery',
        //     [$this, 'floorPlanGalleryMetaField'],
        //     'floorplans'
        // );

    }

    
    public function propertyMetaFields($post)
    { 
        $this->propertyMetaBoxLayout(self::$propertyGeneralFields, $post);
    }

    public function venterraPropertyMetaFields($post)
    { 
        $this->propertyMetaBoxLayout(self::$venterraGeneralInfo, $post);
    }

    public function propertyLogoField($post)
    {
        $this->propertyLogoMetaBoxLayout(self::$propertyImageFields, $post);
    }

    public function propertyLogoMetaBoxLayout($imageFields, $post)      
    {
        foreach ($imageFields as $imageKey => $imageValue) {
            if ( $imageKey !== 'propLogo' ) continue; ?>
            <div class="rentPress-meta-container">
                <img src="<?php echo get_post_meta($post->ID, $imageKey, true); ?>"/>
            </div>
        <?php
        } // endforeach
    }

    public function propertyGeneralPhotosContainer($post)
    {
        $this->propertyGeneralPhotosLayout(self::$propertyImageFields, $post);
    }

    public function propertyGeneralPhotosLayout($imageFields, $post)
    {
        $photos = get_post_meta( $post->ID, 'propGeneralPhotos', true );
        if ( ! $photos || $photos === 'null' ) {
            ?>
            <h5><i>
                No photo gallery found in feed for 
                <?php echo sanitize_text_field(get_post_meta($post->ID, 'propName', true)); ?>
            </i></h5>
            <?php
            return;
        }
        wp_nonce_field( 'rentPress_save_meta_box_data', 'propGeneralPhotos' . '_nonce' );
        $photos= rentPress_Helpers_JsonHelper::decode($photos); ?>
        <div id="rentpress-property-general-photo-container" class="rentPress-meta-container">
        <?php foreach ( $photos as $photo ) : ?>
                <div class="photo-item">
                    <div class="photo-image" >
                        <img src="<?php echo $photo->Url != '' ? $photo->Url : 'https://placehold.it/210x120?text=Image'; ?>" style="width: 100%"/>
                    </div>
                    <div class="photo-info" >
                        <h3>[<?php echo $photo->ID . ']'.  $photo->Title; ?></h3>
                        <p style="">Rank: <?php echo $photo->Rank; ?></p>
                    </div>
                    <div style="clear:both;"></div>
                </div>
        <?php endforeach; ?>
        </div> <?php
    }

    public function propertyMapImage($post)
    {
        $mapImage = get_post_meta($post->ID, 'propMapImage', true);
        if ( isset($mapImage) && $mapImage !== '' ) : ?>
            <div class="rentPress-meta-container">
                <img src="<?php echo $mapImage; ?>"/>
            </div>
        <?php endif;
    }

    public function propertyBedroomAssets($post)
    {
        $assets = json_decode(get_post_meta($post->ID, 'propAssetsByNumberOfRooms', true));
        if ( ! isset($assets) || $assets === 'null' || count($assets) == 0 ) {
            ?> <h5><i>No bedroom assets found in the feed for this property</i></h5> <?php
        }
        if ( isset($assets) && count($assets) > 0 ) : foreach ( $assets as $asset ) : ?>
            <div class="rentPress-meta-container">
                <h1><?php echo $asset->numberOfBedrooms; ?> Bedroom</h1>
                <div class="rp-table-of-assets-wrapper">
                    <h3>Images</h3>
                    <table class="rp-table-of-assets">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Image</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $asset->bedroomImages as $bedroomImage ) : ?>
                                <tr>
                                    <td><?php echo $bedroomImage->Title; ?></td>
                                    <td class="rp-image-asset"><img src="<?php echo $bedroomImage->Url; ?>"/></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="rp-table-of-assets">
                    <h3>Videos</h3>
                    <table class="rp-table-of-assets">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Video Url</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $asset->bedroomVideos as $bedroomVideo ) : ?>
                                <tr>
                                    <td><?php echo $bedroomVideo->title; ?></td>
                                    <td><?php echo $bedroomVideo->url; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>  
        <?php endforeach;endif; 
    }

    public function propertyRankings($post)
    {
        $schoolRanking = get_post_meta($post->ID, 'propSchoolRanking', true);
        $averageCityRating = get_post_meta($post->ID, 'propCityAverageRating', true);
        $numberOfReviews = get_post_meta($post->ID, 'propNumberOfReviews', true);
        $percentageOfRecommends = get_post_meta($post->ID, 'propApartmentRatingPctRecommends', true);  ?>
        <div class="rentPress-meta-container">
            <?php if ( isset($schoolRanking) && $schoolRanking != '' ) : ?>
                <div id="school-ranking"><b>School Ranking:</b> <?php echo $schoolRanking; ?></div>
            <?php 
            endif; 
            if ( isset($averageCityRating) && $averageCityRating != '' ) : ?>
                <div id="ar-city-average"><b>City Average:</b> <?php echo $averageCityRating; ?></div>
                <div id="ar-number-of-reviews"><b># of Reviews:</b> <?php echo $numberOfReviews; ?></div>
                <div id="ar-percentage-of-recommends">
                    <b>Property Recommended:</b> <?php echo $percentageOfRecommends; ?>% of the time
                </div>
            <?php endif; ?>
        </div> <?php
    }

    public function propertyAwards( $post )
    {
        $awards = json_decode(get_post_meta($post->ID, 'propAwards', true));
        if ( isset($awards) && count($awards) > 0 ) : ?>
            <div class="rentPress-meta-container">
                <div class="rp-table-of-assets-wrapper">
                    <p>Property awards</p>
                    <table class="rp-table-of-assets">
                        <thead>
                            <tr>
                                <th>Award Description</th>
                                <th>Award Image</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $awards as $award ) : ?>
                                <tr>
                                    <td><?php echo $award->Description; ?></td>
                                    <?php if ( isset($award->Image->colour1ImageFilePath) ) :  ?>
                                        <td class="rp-image-asset"><img src="<?php echo $award->Image->colour1ImageFilePath; ?>"/></td>
                                    <?php else : ?>
                                        <td class="rp-image-asset"><i>Image not provided</i></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
        endif;
    }

    public function propertyVideos( $post )
    {
        $videos = json_decode(get_post_meta($post->ID, 'propVideos', true));
        if ( isset($videos) && count($videos) > 0 ) : ?>
            <div class="rentPress-meta-container">
                <div class="rp-table-of-assets-wrapper">
                    <p>Property videos</p>
                    <table class="rp-table-of-assets">
                        <thead>
                            <tr>
                                <th>Video ID</th>
                                <th>Rank</th>
                                <th>Video Title</th>
                                <th>Video Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $videos as $video ) : ?>
                                <tr>
                                    <td><?php echo $video->ID; ?></td>
                                    <td><?php echo $video->Rank; ?></td>
                                    <td><?php echo $video->Title; ?></td>
                                    <td><?php echo $video->Url; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
        endif;
    }

    public function propertyStaff( $post )
    {
        $staff = json_decode(get_post_meta($post->ID, 'propertyStaff', true));
        if ( ! isset($staff) || $staff === 'null' || count($staff) == 0 ) {
            ?> <h5><i>No staff listing found in the feed for this property</i></h5> <?php
        }
        if ( isset($staff) && count($staff) > 0 ) : ?>
            <div class="rentPress-meta-container">
                <div class="rp-table-of-assets-wrapper">
                    <p>Property staff</p>
                    <table class="rp-table-of-assets">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Title</th>
                                <th>Passion</th>
                                <th>Profile Image</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $staff as $member ) : ?>
                                <tr>
                                    <td><?php echo $member->name; ?></td>
                                    <td><?php echo $member->title; ?></td>
                                    <td><?php echo $member->passion; ?></td>
                                    <td><img width="100" src="<?php echo $member->imageUrl; ?>"/></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
        endif;
    }

    public function propertyRanges( $post )
    {
        $this->propertyMetaBoxLayout(self::$propertyRangeFields, $post);
    }

    public function propertyMetaBoxLayout($fields, $post)
    {
        /* Display input and nonce field */ ?>
       <div class="rentPress-meta-container">
         <table>
           <tbody>
             <?php
                $rentPressOptions = new rentPress_Options();
                $rentPressAPIToken = $rentPressOptions->getOption('rentPress_api_token');
                foreach ($fields as $key => $meta) : 
                $meta = get_post_meta( $post->ID, $key, true ); 
                if ( in_array($key, ['propUnits']) ) continue;
                    if ( ($rentPressAPIToken == '') && (preg_match('[Code|Name]', $fields[$key])) ):
                        $fieldName = ($fields[$key]).'*';
                        $fieldIsRequired = 'required';
                    else:
                        $fieldName = ($fields[$key]);
                        $fieldIsRequired = '';
                    endif; ?>
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b><?php echo $fieldName; ?></b>
                </td>
                 <td class="rentPressValue">
                    <input type="text" 
                        id="<?php echo $key; ?>" 
                        name="<?php echo $key; ?>" 
                        value="<?php echo esc_attr( $meta ); ?>"
                        placeholder="<?php echo $fieldName; ?>"
                        style="width:100%;" size="50" 
                        <?php echo $fieldIsRequired; ?>/></td>
                 <?php wp_nonce_field( 'rentPress_save_meta_box_data', $key.'_nonce' ); ?>
               </tr>
             <?php endforeach; ?>
           </tbody>
         </table>
       </div> <?php
    }    

    public function propertySocial( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_facebook' . '_nonce' );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_twitter' . '_nonce' );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_intstagram' . '_nonce' );
        $facebook = get_post_meta( $post->ID, 'prop_facebook', true ); 
        $twitter = get_post_meta( $post->ID, 'prop_twitter', true ); 
        $instagram = get_post_meta( $post->ID, 'prop_instagram', true ); ?>
       <div class="rentPress-meta-container">
         <table>
           <tbody>
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Facebook URL</b>
                </td>
                 <td class="rentPressValue">
                    <input type="url"  style="width:100%;" id="prop_facebook" name="prop_facebook" value="<?php echo $facebook; ?>" placeholder="https://facebook.com/bluelinelofts/" />
                 </td>
               </tr>               
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Twitter URL</b>
                </td>
                 <td class="rentPressValue">
                    <input type="url"  style="width:100%;" id="prop_twitter" name="prop_twitter" value="<?php echo $twitter; ?>" placeholder="https://twitter.com/bluelinelofts/"/>
                 </td>
               </tr>               
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Instagram URL</b>
                </td>
                 <td class="rentPressValue">
                    <input type="url"  style="width:100%;" id="prop_instagram" name="prop_instagram" value="<?php echo $instagram; ?>" placeholder="https://instagram.com/bluelinelofts/"/>
                 </td>
               </tr>
           </tbody>
         </table>
       </div> 
       <?php
    }

    public function propertySpecial( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_special_text' . '_nonce' );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_special_link' . '_nonce' );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_special_expiration' . '_nonce' );
        $specialText = get_post_meta( $post->ID, 'prop_special_text', true ); 
        $specialLink = get_post_meta( $post->ID, 'prop_special_link', true );
        $specialExpiration = get_post_meta( $post->ID, 'prop_special_expiration', true ); ?>
        <label for="prop_special">Add the current special for the property. You can also add a link destination and/or an expiration date. </label>
       <div class="rentPress-meta-container">
         <table>
           <tbody>
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Special Text</b>
                </td>
                 <td class="rentPressValue">
                    <input type="text"  style="width:100%;" id="prop_special_text" name="prop_special_text" placeholder="Enter the property special" value="<?php echo $specialText; ?>" />
                 </td>
               </tr>                           
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Special Link</b>
                </td>
                 <td class="rentPressValue">
                    <input type="url"  style="width:100%;" id="prop_special_link" name="prop_special_link" value="<?php echo $specialLink; ?>" placeholder="https://example.com"/>
                 </td>
               </tr>
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Special Expiration</b>
                </td>
                 <td class="rentPressValue">
                    <input type="date"  style="width:100%;" id="prop_special_expiration" name="prop_special_expiration" value="<?php echo $specialExpiration ?>"/>
                 </td>
               </tr>
           </tbody>
         </table>
       </div> 
       <?php
    }    

    public function propertyShortcodes( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_gallery' . '_nonce' );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_twitter' . '_nonce' );
        $gallery = get_post_meta( $post->ID, 'prop_gallery', true ); 
        $reviews = get_post_meta( $post->ID, 'prop_reviews', true ); ?>
        <label for="rentPress_prop_shortcodes">Add shortcodes for preferred image gallery and from RentPress: Reviews Add-on (slider is recommended). </label>
       <div class="rentPress-meta-container">
         <table>
           <tbody>
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Gallery Shortcode</b>
                </td>
                 <td class="rentPressValue">
                    <input type="text"  style="width:100%;" id="prop_gallery" name="prop_gallery" value="<?php echo esc_attr($gallery); ?>" placeholder="[gallery_id='1']"/>
                 </td>
               </tr>               
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Reviews Shortcode</b>
                </td>
                 <td class="rentPressValue">
                    <input type="text"  style="width:100%;" id="prop_reviews" name="prop_reviews" value="<?php echo esc_attr($reviews); ?>" placeholder="[rpp_display_reviews_as_slider your-id='123']" />
                 </td>
               </tr>               
           </tbody>
         </table>
       </div> 
       <?php
    }

    public function propertyTagLine( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_tagline' . '_nonce' );
        $tagline = get_post_meta( $post->ID, 'prop_tagline', true ); ?>
        <label for="prop_tagline">Use a few words to describe your property.</label>
        <p> <input type="text"  style="width:100%;" id="prop_tagline" name="prop_tagline" value="<?php echo $tagline; ?>" placeholder="A Landmark in Apartment Living"/> </p> 
        <?php
    }

    public function propertyPetPolicy( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_pet_policy' . '_nonce' );
        $petPolicy = get_post_meta( $post->ID, 'prop_pet_policy', true ); ?>
        <label for="prop_pet_policy">Add in the pet policy for your property. This will show if this property has a Pets taxonomy selected.</label>
        <p> <input type="text"  style="width:100%;" id="prop_pet_policy" name="prop_pet_policy" value="<?php echo $petPolicy; ?>" placeholder="Select apartments are cat-friendly. Contact us for details." /> </p> 
        <?php
    }    

    public function propertyApplyLink( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_apply' . '_nonce' );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_apply_unit' . '_nonce' );
        $applyLink = get_post_meta( $post->ID, 'prop_apply', true );
        $applyLinkUnit = get_post_meta( $post->ID, 'prop_apply_unit', true ); ?>
        <label for="prop_apply">Enter the URL for property online application. If not set, will default to /contact/ on site.</label>
        <p> <input type="url" placeholder="https://link-to-application.com/" style="width:100%;" id="prop_apply" name="prop_apply" value="<?php echo $applyLink; ?>" /> </p>
        <input type="hidden" name="prop_apply_unit" value="false">
        <input type="checkbox" id="prop_apply_unit" name="prop_apply_unit" <?php if ($applyLinkUnit == "true"){ ?> checked <?php }?> value="true"/>
        <label for="prop_apply_unit">Only use this link for main property Apply Now link</label>
        <?php
    }

    public function propertyResidentsLink( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'prop_residents_link' . '_nonce' );
        $residentsLink = get_post_meta( $post->ID, 'prop_residents_link', true ); ?>
        <label for="prop_residents_link">Enter the URL for the property online resident portal. If not set, link will not display.</label>
        <p> <input type="url" placeholder="https://link-to-resident-portal.com/" style="width:100%;" id="prop_residents_link" name="prop_residents_link" value="<?php echo $residentsLink; ?>" /> </p> 
        <?php
    }

    public function fpMatterportLink( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'fp_matterport_link' . '_nonce' );
        $matterportLink = get_post_meta( $post->ID, 'fp_matterport_link', true ); ?>
        <label for="fp_matterport_link">Enter the URL for Matterport 3d tour.</label>
        <p> <input type="text"  style="width:100%;" id="fp_matterport_link" name="fp_matterport_link" value="<?php echo $matterportLink; ?>" /> </p> 
        <?php
    }

    public function propertySearchKeywords( $post )
    {
        $searchterms = get_post_meta( $post->ID, 'property_searchterms', true );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'property_searchterms' . '_nonce' ); ?>
       <p>
         <i>Add in additional keywords for this property. Keep each keyword seperated by a comma. </i>
         <input class="widefat" type="text" name="property_searchterms" id="property_searchterms" value="<?php echo $searchterms; ?>" size="30" placeholder="Downtown, Stadium, Employer," />
       </p> <?php
    }

    public function propertyTrackingPhone( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'property_trackingphone' . '_nonce' );
        $propertyTrackingPhone = get_post_meta( $post->ID, 'property_trackingphone', true );   ?>
        <div class="rentPress-meta-container">
            <label for="propertyTrackingPhone"><i>Add in a tracking phone number. This will override the phone number from the feed. </i></label>
            <table>
                <tbody>
                    <tr>
                        <td class="rentPressTitle" style="padding-bottom: 5px;">
                            <b>Property Tracking Phone Number</b>
                        </td>
                        <td class="rentPressValue">
                                <input class="widefat" type="tel" pattern="(?:(\+?\d{1,3}) )?(?:([\(]?\d+[\)]?)[ -])?(\d{1,5}[\- ]?\d{1,5})" maxlength="16" name="property_trackingphone" id="property_trackingphone" value="<?php echo $propertyTrackingPhone; ?>" placeholder="(614) 555-3030" title="Please only enter numbers, parentheses, periods, or dashes. "/>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div> <?php
    }

    public function propertyAmenities( $post )
    {
        $content = get_post_meta( $post->ID, 'amenities', true );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'amenities' . '_nonce' );
        $amenities = rentPress_Helpers_JsonHelper::decode($content); ?>
            <div id="rentpress-admin-amenity-list">
                <?php if ( count($amenities) == 0 ) : ?>
                    <p>There are no amenities for this property</p>
                <?php else : ?>
                    <?php foreach ($amenities as $amenity) : 
                        $image = isset($amenity->Image) && $amenity->Image != '' ? 
                                $amenity->Image : 
                                'https://placehold.it/210x120?text=No+Amenity+Image'; ?>
                        <div class="amenity-item">
                            <div class="amenity-image" >
                                <img src="<?php echo $image; ?>" style="width: 100%"/>
                            </div>
                            <div class="amenity-info" >
                                <h3><?php echo $amenity->Title; ?></h3>
                                <p style="">
                                    <?php echo $amenity->Description != '' ? $amenity->Description : 'No description provided'; ?>
                                </p>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div style="clear:both;"></div> <?php
    }

    public function propertyCommunityAmenities( $post )
    {
        $content = get_post_meta( $post->ID, 'propCommunityAmenities', true );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'propCommunityAmenities' . '_nonce' );
        $amenities = rentPress_Helpers_JsonHelper::decode($content); ?>
            <div id="rentpress-admin-amenity-list">
                <?php if ( count($amenities) == 0 ) : ?>
                    <p>There are no amenities for this property</p>
                <?php else : ?>
                    <?php foreach ($amenities as $amenity) : 
                        $image = count($amenity->Images) > 0 ? $amenity->Images[0]->url : 'https://placehold.it/210x120?text=No+Amenity+Image'; ?>
                        <div class="amenity-item">
                            <div class="amenity-image" >
                                <img src="<?php echo $image; ?>" style="width: 100%"/>
                            </div>
                            <div class="amenity-info" >
                                <h3><?php echo $amenity->Title; ?></h3>
                                <p style="">
                                    <?php echo $amenity->Description != '' ? $amenity->Description : 'No description provided'; ?>
                                </p>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div style="clear:both;"></div> <?php
    }

    public function rentpress_ils_tracking_codes_box( $post )
    {
        $content = get_post_meta( $post->ID, 'propTrackingCodes', true );
        $trackingCodes = json_decode($content);
        wp_nonce_field( 'rentPress_save_meta_box_data', 'propTrackingCodes' . '_nonce' );
        // Tracking codes
        if ( $trackingCodes && count($trackingCodes) > 0 )  : ?>
            <table>
                <thead style="background: #efefef;">
                    <tr>
                        <th width="100">ID</th>
                        <th width="100">Source</th>
                        <th width="100">Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $trackingCodes as $tracker ) : ?>
                        <tr>
                            <td><?php echo esc_html($tracker->trackingId); ?></td>
                            <td><?php echo esc_html($tracker->marketingSourceCd); ?></td>
                            <td><?php echo esc_html($tracker->email); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="font-size: 0.6rem;font-style:italic;">*Phone numbers not displayed</p>
        <?php endif;
    }

    public function propertyOfficeHours( $post )
    {
        $content = get_post_meta( $post->ID, 'propOfficeHours', true );
        $officeHours = json_decode($content);
        wp_nonce_field( 'rentPress_save_meta_box_data', 'propOfficeHours' . '_nonce' );
        // Display office hours
        if ($officeHours == null) {
            echo 'No office hours found';
        } elseif (get_post_meta($post->ID)['propSource'][0] == 'vaultware') { //Vaultware
            foreach (array_slice( $officeHours, 0, 7) as $hours ) :
                $dayName = "<b>".(date( 'l', strtotime(($hours->Day)))).":</b> ";
                if (is_numeric(strtotime($hours->OpenTime))) :
                    $openTime = (date( 'g:i A', strtotime( $hours->OpenTime ) ));
                else: 
                    $openTime = ( $hours->OpenTime );
                endif;
                if (is_numeric(strtotime($hours->CloseTime))) :
                    $closeTime = " - " .(date( 'g:i A', strtotime( $hours->CloseTime ) ));
                else: 
                    $closeTime = '';
                endif; ?>
                <li><?php echo $dayName.$openTime.$closeTime; ?></li><?php
            endforeach;
        } else { //not vaultware
            if ( $officeHours && count($officeHours) > 0 )  : ?>
                <table>
                    <thead style="background: #efefef;">
                        <tr>
                            <th width="100">Day</th>
                            <th width="100">Open At</th>
                            <th width="100">Closed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $officeHours as $hours ) : 
                            if ( isset($hours->Iday) ) : // RentCafe ?>
                                <?php if ($hours->Iday > 7) : ?>
                                    <tr>
                                        <td><?php echo ($hours->Iday > 8)?"Saturday - Sunday":"Monday - Friday"; ?></td>
                                        <td><?php echo esc_html(date( 'g:i A', strtotime( $hours->StartTime ) )); ?></td>
                                        <td><?php echo esc_html(date( 'g:i A', strtotime( $hours->EndTime ) )); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo esc_html($this->dayByNumber($hours->Iday)); ?></td>
                                        <td><?php echo esc_html(date( 'g:i A', strtotime( $hours->StartTime ) )); ?></td>
                                        <td><?php echo esc_html(date( 'g:i A', strtotime( $hours->EndTime ) )); ?></td>
                                    </tr>
                                <?php endif;

                            if ( isset($hours->Day) ) : //ResMan ?>
                                <tr>
                                    <td><?php echo esc_html($hours->Day); ?></td>
                                    <td><?php echo esc_html(date( 'g:i A', strtotime( $hours->OpenTime ) )); ?></td>
                                    <td><?php echo esc_html(date( 'g:i A', strtotime( $hours->CloseTime ) )); ?></td>
                                </tr><?php
                            endif; ?>

                            <?php else : // Encasa ?>
                                <tr>
                                    <td><?php echo esc_html($hours->day); ?></td>
                                    <td><?php echo esc_html(date( 'g:i A', strtotime( $hours->openTime.':00' ) )); ?></td>
                                    <td><?php echo esc_html(date( 'g:i A', strtotime( $hours->closeTime.':00' ) )); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif;           
    };

        // option to hide hours
        if ($officeHours != null) :
            wp_nonce_field( 'rentPress_save_meta_box_data', 'hide_office_hours' . '_nonce' );
            $hideOfficeHours = get_post_meta( $post->ID, 'hide_office_hours', true ); ?>
            <div style="padding-left: 5px; padding-top: 10px; padding-bottom: 5px;"><hr />
                <input type="hidden" name="hide_office_hours" value="false">
                <input type="checkbox" id="hide_office_hours" name="hide_office_hours" <?php if ($hideOfficeHours == "true"){ ?> checked <?php }?> value="true"/>
                <label for="hide_office_hours">Hide office hours</label>
            </div><?php 
        endif;
    }

    public function dayByNumber($dayNum = 1)
    {
        $arrWeek = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
        return $arrWeek[$dayNum] ?: $arrWeek[0];
    }

    public function propertyResync($post)
    {
        if ( $post->post_type == 'properties' ) {
            $postName = get_post_meta($post->ID, 'propName', true);
        } elseif ( $post->post_type == 'floorplans' ) {
            $postName = get_post_meta($post->ID, 'fpName', true);
        }

        /* Display input and nonce field */ ?>
        <div id="rp-resync-activity-container"> </div>
       
        <div style="padding: 5px;">
            <a id="rp-sync-property-from-editing-page" 
                href="javascript:void(0)" 
                class="button button-primary button-large"
                data-property-post-id="<?php echo $post->ID; ?>"
                data-post-type="<?php echo $post->post_type; ?>">

                <?php echo __('Resync '.$postName, RENTPRESS_LANG_KEY); ?>
            </a>
        </div>
        
        <div style="padding-left: 5px; padding-top: 5px; padding-bottom: 5px;">
        <label>
            <input type="checkbox" name="propDisablePricing" value="true" <?php checked('true', get_post_meta($post->ID, 'propDisablePricing', true)); ?>>
                Disable Pricing 
        </label>
        </div>

       <?php
    }

    // public function propertyDisablePricing($post) {

    /* ?>

    //         <label>
    //             <input type="checkbox" name="propDisablePricing" value="true" <?php checked('true', get_post_meta($post->ID, 'propDisablePricing', true)); ?>>
    //             Disable Pricing 
    //         </label>

    //     <?php
        
    // }

    /**
     * Creates meta fields for floorplans post type
     */

    public function floorPlanSpecial( $post ) {
        wp_nonce_field( 'rentPress_save_meta_box_data', 'fp_special_text' . '_nonce' );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'fp_special_link' . '_nonce' );
        wp_nonce_field( 'rentPress_save_meta_box_data', 'fp_special_expiration' . '_nonce' );
        $fpSpecialText = get_post_meta( $post->ID, 'fp_special_text', true ); 
        $fpSpecialLink = get_post_meta( $post->ID, 'fp_special_link', true );
        $fpSpecialExpiration = get_post_meta( $post->ID, 'fp_special_expiration', true ); 
        ?>
        
        <label for="fp_special">Add the current special for the floor plan. You can also add a link destination and/or an expiration date. </label>
       <div class="rentPress-meta-container">
         <table>
           <tbody>
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Special Text</b>
                </td>
                 <td class="rentPressValue">
                    <input type="text"  style="width:100%;" id="fp_special_text" name="fp_special_text" value="<?php echo $fpSpecialText; ?>" placeholder="Enter the floor plan special" />
                 </td>
               </tr>                           
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Special Link</b>
                </td>
                 <td class="rentPressValue">
                    <input type="url"  style="width:100%;" id="fp_special_link" name="fp_special_link" value="<?php echo $fpSpecialLink; ?>" placeholder="https://example.com"/>
                 </td>
               </tr>
               <tr>
                 <td class="rentPressTitle" style="padding-bottom: 5px;">
                    <b>Special Expiration</b>
                </td>
                 <td class="rentPressValue">
                    <input type="date"  style="width:100%;" id="fp_special_expiration" name="fp_special_expiration" value="<?php echo $fpSpecialExpiration ?>"/>
                 </td>
               </tr>
           </tbody>
         </table>
       </div> 
       <?php
    }

    public function floorPlanMetaFields( $post ) {
        $this->defaultMetaBoxLayout(self::$floorPlanMeta, $post);
    }

    public function venterraFloorPlanMetaFields( $post ) {
        $this->defaultMetaBoxLayout(self::$venterraFloorPlanMeta, $post);
    }

    public function floorPlanUnits( $post ) {
        $units = new rentPress_Units_Query([
            'post_id' => $post->ID,
        ]);
        $this->floorPlanUnitsMetaBoxLayout($units->run_query(), $post);
    }

    public function floorPlanUnitsMetaBoxLayout($units, $post)
    { ?>
        <div class="rentPress-floor-plan-units-container">
            <div class="rentPress-unit-update-response-container"> </div>
            <div class="loading-gif" style="display:none;">
                <img src="<?php echo get_admin_url(); ?>/images/spinner.gif"/> Saving changes...
            </div>
            <?php if ( count($units) > 0 ) : foreach ( $units as $unit ) : 
                $name = $unit->Information->Name ? $unit->Information->Name : $unit->Identification->UnitCode;
                $code = $unit->Identification->UnitCode;
                $baseRent = sanitize_text_field($unit->Rent->Amount); 
                $marketRent = sanitize_text_field($unit->Rent->MarketRent);
                $effectiveRent = sanitize_text_field($unit->Rent->EffectiveRent);
                $minimum = sanitize_text_field($unit->Rent->MinRent); 
                $maximum = sanitize_text_field($unit->Rent->MaxRent);
                $termRent = false;
                $termIsArray = is_array($unit->Rent->TermRent);
                $leaseTermOption = get_transient('rentpress_unit_lease_term_price_'.$code);
                if ( ($termIsArray) && (isset($unit->Rent->TermRent)) && (count($unit->Rent->TermRent) > 0) ) :
                    $termRent = isset($unit->Rent->TermRent->data) ? $unit->Rent->TermRent->data : $unit->Rent->TermRent; 
                endif; ?>
                <div class="rentpress-fp-unit" style="position: relative;">
                    <h3><?php echo sanitize_text_field($unit->Information->Name); ?></h3>
                    <!-- <hr> -->
                    <div class="rentpress-fp-unit-details">
                        <b>Unit: </b><?php echo $name; ?><br />
                        <b>Available: </b><?php echo sanitize_text_field($unit->Information->AvailableOn); ?> <br/>
                        <b>SF: </b><?php echo sanitize_text_field($unit->SquareFeet->Min); ?> <br/>
                        <b>Beds: </b><?php echo sanitize_text_field($unit->Rooms->Bedrooms); ?> <br/>
                        <b>Baths: </b><?php echo sanitize_text_field($unit->Rooms->Bathrooms); ?> <br/>
                        <?php if ( $unit->Information->isAvailable ) : ?>
                            <span style="color:green;"><i>Available for rent</i></span>
                        <?php else : ?>
                            <span style="color:red;"><i>Not available for rent</i></span>
                        <?php endif; ?>
                       
                        <p><b>Pricing options</b></p>

                        <div class="rentpress-fp-unit-pricing-options">
                            <b>Base:</b> <?php echo $baseRent ? '$'.number_format($baseRent) : 'Not available'; ?> <br/>
                            <b>Market:</b> <?php echo $marketRent ? '$'.number_format($marketRent) : 'Not available'; ?> <br/>
                            <b>Effective:</b> <?php echo $effectiveRent ? '$'.number_format($effectiveRent) : 'Not available'; ?> <br/>
                            <b>Minimum:</b> <?php echo $minimum ? '$'.number_format($minimum) : 'Not available'; ?> <br/>
                            <b>Maximum:</b> <?php echo $maximum ? '$'.number_format($maximum) : 'Not available'; ?> <br/> 
                        </div>
                    </div>
                </div>  
            <?php endforeach; else : ?>
                <div class="rp-admin-no-fp-units-message" style="padding:2rem; background: #efefef;">
                    <span class="fa fa-warning"></span>&nbsp;There are no units available for this floor plan
                </div>
            <?php endif; ?>
        </div>
       

        <?php
    }

    public function floorPlanGalleryMetaField( $post )
    {
        $this->floorPlanGalleryMetaBoxLayout( $post );
    }

    public function floorPlanGalleryMetaBoxLayout($post)
    {
        $media = get_attached_media( 'image', $post->ID ); ?>
        <div class="rentPress-attached-fp-images">
            <?php foreach ( $media as $floorPlanImage ) : ?>
                <div id="rentpress-admin-tl-attached-image">
                    <div style="background:url(<?php echo $floorPlanImage->guid; ?>);background-size:cover;background-position:center;height:200px;width:200px;"></div>
                </div>  
            <?php endforeach; ?>
        </div>
        <?php
    }

    public function defaultMetaBoxLayout($fields, $post)
    {
        /* Display input and nonce field */ ?>
        <div class="rentPress-meta-container">
            <table>
                <tbody><?php
                $rentPressOptions = new rentPress_Options();
                $rentPressAPIToken = $rentPressOptions->getOption('rentPress_api_token');
                $fpRequiredField = '[ID|Name|Bedroom|Bathroom|Minimum|Maximum|Availability|Code]';
                foreach ($fields as $key => $meta) : 
                    $meta = get_post_meta( $post->ID, $key, true ); 
                    if ( $key == 'fpGalleryImages' ) continue; 
                        $overrideName = 'override_meta_'.sanitize_text_field($key); 
                        $overrideValue = get_post_meta($post->ID, $overrideName, true);
                        if ( ($rentPressAPIToken == '') && (preg_match($fpRequiredField, $fields[$key])) ):
                             $fieldName = ($fields[$key]).'*';
                             $fieldIsRequired = 'required';
                         else:
                             $fieldName = ($fields[$key]);
                             $fieldIsRequired = '';
                         endif;?>
                <tr>
                <td class="rentPressTitle" style="padding-bottom: 5px;"><b><?php echo __(sanitize_text_field($fieldName), RENTPRESS_LANG_KEY); ?></b></td>
                <td class="rentPressValue">
                    <input type="text" 
                        id="<?php echo $key; ?>" 
                        name="<?php echo $key; ?>" 
                        value="<?php echo esc_attr( $meta ); ?>"
                        placeholder="<?php echo __($fieldName, RENTPRESS_LANG_KEY); ?>" 
                        style="width:80%;" size="44"
                        <?php echo $fieldIsRequired; ?> />
                    <label for="<?php echo sanitize_text_field($overrideName); ?>">
                        <input type="checkbox" 
                            id="<?php echo sanitize_text_field($overrideName); ?>"
                            name="<?php echo sanitize_text_field($overrideName); ?>" 
                            style="display:inline-block;"
                            <?php if ( $overrideValue ) : ?> 
                                checked
                            <?php endif; ?>> 
                        <span style="font-size:.8rem;font-style:italic;">Override</span>
                    </label>
                </td><?php 
                wp_nonce_field( 'rentPress_save_meta_box_data', $key.'_nonce' );
                wp_nonce_field( 'rentPress_save_meta_box_data', 'override_meta_'.$key.'_nonce' ); ?>
                </tr><?php 
                endforeach; ?>
           </tbody>
         </table>
       </div> <?php
    }

    /**
    * Uses google maps API to generate map coordinates for current property
    *
    * @return mixed // map coordinates auto-fill property meta
    */
    public function rentpress_prop_coords_box($post)
    {
        wp_nonce_field( 'rentpress_save_meta_box_data', 'prop_coords'.'_nonce' );
        wp_nonce_field( 'rentpress_save_meta_box_data', 'override_synced_property_coords_data'.'_nonce' );
        
        $coords = get_post_meta( $post->ID, 'prop_coords', true );

        $fullAddress = get_post_meta($post->ID, 'propAddress', true) .' '.
            get_post_meta($post->ID, 'propCity', true).', '.
            get_post_meta($post->ID, 'propState', true).' '.
            get_post_meta($post->ID, 'propZip', true);
        ?>

        <p style="font-size: 10px;font-style: italic;color: #a3a3a3;">
            Click the button to generate the lat/long information based on the property's address, city, and state. You must also have a Google API key entered.
        </p>

        <p>
            <input class="widefat"
                type="text" 
                name="prop_coords" 
                id="prop_coords" 
                value="<?php echo ($coords) ? $coords : '';?>" 
                size="30" 
                style="margin-bottom: 1rem; padding: 5px 2px;" />

            <?php echo get_post_meta($post->ID, 'override_synced_property_coord_data', true); ?>

            <a  href="javascript:void(0)"
                style="width:100%;" 
                class="calc-latlng button button-primary button-large" 
                onclick="rentpressFetchCoordsFromAddress('<?php echo esc_attr($fullAddress); ?>');return false;">
                    Calculate lat/long coordinates
            </a>
            <p></p>
            <label>
                <input type="checkbox" name="override_synced_property_coords_data" value="1" <?php checked(get_post_meta($post->ID, 'override_synced_property_coords_data', true), '1'); ?>>
                Override lat/long from feed
            </label>
            
        </p>
        <?php
    }

}

// include RENTPRESS_PLUGIN_DIR . '/src/rentPress/Posts/Meta/property-logo-upload.php';