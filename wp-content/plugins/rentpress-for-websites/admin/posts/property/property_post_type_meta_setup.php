<?php
/*
 *  Save the meta box’s post metadata.
 */
function rentpress_save_property_meta($post_id, $post)
{
    // Get the post type object
    $post_type = get_post_type_object($post->post_type);

    // Verify the nonce before proceeding and check if the current user has permission to edit the post
    if (isset($_POST['rentpress_custom_field_property_nonce']) &&
        wp_verify_nonce($_POST['rentpress_custom_field_property_nonce'], basename(__FILE__)) &&
        current_user_can($post_type->cap->edit_post, $post_id)) {
        // firstly mark this property as no longer a feed property, this will be undone immediately during next resync if it is in the feed still
        update_post_meta($post_id, 'rentpress_custom_field_property_is_feed', false);
        // get all the post meta
        $rpm = get_post_meta($post_id);
        $rentpress_add_on_field_args = get_option('rentpress_add_on_field_args');
        $rentpress_add_on_field_args = $rentpress_add_on_field_args ? json_decode($rentpress_add_on_field_args) : false;

        // delete the override post meta if it doesn't exist in the form submit
        foreach ($rpm as $meta_override_key => $meta_override_value) {
            if (strpos($meta_override_key, 'rentpress_custom_field_property') !== false &&
                strpos($meta_override_key, 'override') !== false) {

                // if the override doesn't exist in the request delete the unused meta
                if (!isset($_POST[$meta_override_key])) {
                    delete_post_meta($post_id, $meta_override_key);
                }
            }
        }

        // set up the meta arrays to save them based on type
        $post_emails = rentpress_createPropertyEmailMetaList($rentpress_add_on_field_args);
        $post_text_fields = rentpress_createPropertyTextFieldMetaList($rentpress_add_on_field_args);
        $post_text_area = rentpress_createPropertyTextAreaMetaList($rentpress_add_on_field_args);
        $post_urls = rentpress_createPropertyURLMetaList($rentpress_add_on_field_args);

        // if there is an override set in the request, set the value
        foreach ($_POST as $override_key => $override_value) {
            if (strpos($override_key, '_override') !== false &&
                strpos($override_key, 'rentpress_custom_field_property') !== false &&
                $override_value == 'on') {

                // remove override from the name of the key
                $meta_key = str_replace('_override', '', $override_key);
                $new_meta_value = '';

                /* Get the posted data and sanitize it for use as an HTML class. */
                if (in_array($meta_key, $post_emails)) {
                    $new_meta_value = (isset($_POST[$meta_key]) ? sanitize_email($_POST[$meta_key]) : '');
                } elseif (in_array($meta_key, $post_text_fields)) {
                    $new_meta_value = (isset($_POST[$meta_key]) ? sanitize_text_field($_POST[$meta_key]) : '');
                } elseif (in_array($meta_key, $post_text_area)) {
                    $new_meta_value = (isset($_POST[$meta_key]) ? wp_filter_post_kses($_POST[$meta_key]) : '');
                } elseif (in_array($meta_key, $post_urls)) {
                    $new_meta_value = (isset($_POST[$meta_key]) ? esc_url_raw($_POST[$meta_key]) : '');
                }

                update_post_meta($post_id, $meta_key, $new_meta_value);
                update_post_meta($post_id, $override_key, $override_value);
            }
        }

        // check to see if the pricing has been overridden
        if (isset($_POST['rentpress_custom_field_property_rent_type_selection'])) {
            update_post_meta($post_id, 'rentpress_custom_field_property_rent_type_selection', sanitize_text_field($_POST['rentpress_custom_field_property_rent_type_selection']));
            if ($_POST['rentpress_custom_field_property_rent_type_selection'] == 'Global Setting') {
                delete_post_meta($post_id, 'rentpress_custom_field_property_rent_type_selection_override');
            } else {
                update_post_meta($post_id, 'rentpress_custom_field_property_rent_type_selection_override', 'on');
            }
        }

        // the the hours are overridden, then save them correctly
        if (isset($_POST['rentpress_custom_field_property_office_hours_checkbox']) && $_POST['rentpress_custom_field_property_office_hours_checkbox'] === 'on') {
            update_post_meta($post_id, 'rentpress_custom_field_property_office_hours_checkbox', 'on');
            $office_hour_setting = [
                'monday_open',
                'monday_close',
                'tuesday_open',
                'tuesday_close',
                'wednesday_open',
                'wednesday_close',
                'thursday_open',
                'thursday_close',
                'friday_open',
                'friday_close',
                'saturday_open',
                'saturday_close',
                'sunday_open',
                'sunday_close',
            ];
            foreach ($office_hour_setting as $time_of_setting) {
                $time_of_setting = 'rentpress_custom_field_property_' . $time_of_setting;
                if (!empty($_POST[$time_of_setting])) {
                    $time = date_create_from_format('H:i', $_POST[$time_of_setting])->format('g:i a');
                    update_post_meta($post_id, $time_of_setting, $time);
                } else {
                    update_post_meta($post_id, $time_of_setting, '');
                }
            }
        } else {
            delete_post_meta($post_id, 'rentpress_custom_field_property_office_hours_checkbox');
        }

        if (isset($_POST['rentpress_custom_field_property_accent_color'])) {
          update_post_meta($post_id, 'rentpress_custom_field_property_accent_color', sanitize_text_field($_POST['rentpress_custom_field_property_accent_color']));
        }

        if (isset($_POST['rentpress_custom_field_property_accent_color_use_property_branding'])) {
            update_post_meta($post_id, 'rentpress_custom_field_property_accent_color_use_property_branding', sanitize_text_field($_POST['rentpress_custom_field_property_accent_color_use_property_branding']));
        } else {
            delete_post_meta($post_id, 'rentpress_custom_field_property_accent_color_use_property_branding');
        }

        if (isset($_POST['rentpress_custom_field_property_gallery_images'])) {
            update_post_meta($post_id, 'rentpress_custom_field_property_gallery_images', sanitize_text_field($_POST['rentpress_custom_field_property_gallery_images']));
        }

        if (isset($_POST['rentpress_custom_field_property_accent_color'])) {
          update_post_meta($post_id, 'rentpress_custom_field_property_accent_color', sanitize_text_field($_POST['rentpress_custom_field_property_accent_color']));
        }

        if (isset($_POST['rentpress_custom_field_property_disable_pricing'])) {
            update_post_meta($post_id, 'rentpress_custom_field_property_disable_pricing', 'on');
        } else {
            delete_post_meta($post_id, 'rentpress_custom_field_property_disable_pricing');
        }

        if (isset($_POST['rentpress_custom_field_property_link_options'])) {
            update_post_meta($post_id, 'rentpress_custom_field_property_link_options', sanitize_text_field($_POST['rentpress_custom_field_property_link_options']));
        } else {
            update_post_meta($post_id, 'rentpress_custom_field_property_link_options', 'Default apply link');
        }

        if (isset($_POST['rentpress_custom_field_property_contact_type'])) {
            update_post_meta($post_id, 'rentpress_custom_field_property_contact_type', sanitize_text_field($_POST['rentpress_custom_field_property_contact_type']));
        } else {
            update_post_meta($post_id, 'rentpress_custom_field_property_contact_type', 'Default apply link');
        }

        // neighborhoods
        if (isset($_POST['rentpress_custom_field_property_neighborhood_post_id'])) {
            update_post_meta($post_id, 'rentpress_custom_field_property_neighborhood_post_id', sanitize_text_field($_POST['rentpress_custom_field_property_neighborhood_post_id']));

            $neighborhoods = null;
            if (empty($GLOBALS['rentpress_neighborhood_data'])) {
                require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'neighborhood/neighborhood_post_type_data.php';
                $neighborhoods = rentpress_getAllNeighborhoods();
                $GLOBALS['rentpress_neighborhood_data'] = $neighborhoods;
            } else {
                $neighborhoods = $GLOBALS['rentpress_neighborhood_data'];
            }

            $neighborhood_name = array_search(intval($_POST['rentpress_custom_field_property_neighborhood_post_id']), $neighborhoods);
            update_post_meta($post_id, 'rentpress_custom_field_property_neighborhood_post_name', sanitize_text_field($neighborhood_name));
        } else {
            update_post_meta($post_id, 'rentpress_custom_field_property_neighborhood_post_id', '');
            update_post_meta($post_id, 'rentpress_custom_field_property_neighborhood_post_name', '');
        }

        require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/single_property_refresh.php';
        rentpress_syncFeedAndWPPropertyMeta($post_id);
    }
}
add_action('save_post', 'rentpress_save_property_meta', 10, 2);

/*
 *  Create meta box to hold all of the viewable meta data fields
 */
function rentpress_add_custom_property_data_box()
{
    $box_title = 'RentPress - Property Editor';

    add_meta_box(
        'rentpress_custom_property_data_box', // Unique ID
        $box_title, // Box title
        'rentpress_custom_property_data_box_html', // Content callback, must be of type callable
        'rentpress_property' // Post type
    );
}
add_action('add_meta_boxes', 'rentpress_add_custom_property_data_box');

/*
 *  Create HTML that will show in the meta box
 */
function rentpress_custom_property_data_box_html($post)
{
    wp_nonce_field(basename(__FILE__), 'rentpress_custom_field_property_nonce');
    require_once RENTPRESS_PLUGIN_ADMIN_DIR . 'posts/meta/metafields.php';
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'floorplan/floorplan_post_type_data.php';

    $rentpress_add_on_field_args = get_option('rentpress_add_on_field_args');
    $rentpress_add_on_field_args = $rentpress_add_on_field_args ? json_decode($rentpress_add_on_field_args) : false;
    $rpm = get_post_meta($post->ID); // Get RentPress Property Meta
    $overrides = array();
    $floorplan_posts = array();
    $rentpress_options = get_option('rentpress_options');
    $selected_price_type = (isset($rentpress_options['rentpress_pricing_display_settings_section_price_display_selection'])) ? $rentpress_options['rentpress_pricing_display_settings_section_price_display_selection'] : 'Best Price';
    if (isset($rpm['rentpress_custom_field_property_code']) ? $rpm['rentpress_custom_field_property_code'] : '') {
        require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
        $property_data = rentpress_getAllPropertyDataWithCodeOrPostID($rpm['rentpress_custom_field_property_code'][0]);
        $floorplan_posts = rentpress_getAllFloorplanPostsForPropertyCode($rpm['rentpress_custom_field_property_code'][0]);
    }

    foreach ($rpm as $key => $value) {
        if (strpos($key, 'override') !== false &&
            strpos($key, 'rentpress_custom_field_property') !== false &&
            $value[0] == 'on') {
            $overrides[$key] = $value[0];
        }
    }

    // neighborhoods
    $neighborhoods = null;
    if (empty($GLOBALS['rentpress_neighborhood_data'])) {
        require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'neighborhood/neighborhood_post_type_data.php';
        $neighborhoods = rentpress_getAllNeighborhoods();
        $GLOBALS['rentpress_neighborhood_data'] = $neighborhoods;
    } else {
        $neighborhoods = $GLOBALS['rentpress_neighborhood_data'];
    }

    // Pricing Selector
    $pricing_options = ['Global Setting', 'Base Rent', 'Market Rent', 'Term Rent', 'Effective Rent', 'Best Price', 'Minimum - Maximum'];
    $pricing_selector_value = (isset($rpm['rentpress_custom_field_property_rent_type_selection'][0])) ? $rpm['rentpress_custom_field_property_rent_type_selection'][0] : 'Global Setting';

    $pricing_selector_str = "<select name='rentpress_custom_field_property_rent_type_selection' id='rentpress_custom_field_property_rent_type_selection' class='rentpress-settings-select'>";
    foreach ($pricing_options as $option) {
        if ($pricing_selector_value == $option) {
            $pricing_selector_str .= "<option value='$option' selected>$option</option>";
        } else {
            $pricing_selector_str .= "<option value='$option'>$option</option>";
        }
    }
    $pricing_selector_str .= "</select>";

    ?>

<div class="rentpress-cpt-editor-container">
  <div class="rentpress-tabs">
    <div class="rentpress-tab-button" onclick="openTab(event, 'prop-marketing')"><span class="fas fa-bullhorn"
        aria-hidden="true"></span>
      Marketing</div>
    <div class="rentpress-tab-button" onclick="openTab(event, 'prop-info')"><span class="fas fa-info-circle"
        aria-hidden="true"></span> Info
    </div>
    <?php
        if (isset($rentpress_add_on_field_args->rentpress_property_meta) ? count((array)$rentpress_add_on_field_args->rentpress_property_meta ) : '') {
            foreach ($rentpress_add_on_field_args->rentpress_property_meta as $property_meta) {
                if (isset($property_meta->tab_callback) && isset($property_meta->new_tab) ? $property_meta->new_tab : '') {
                    call_user_func($property_meta->tab_callback);
                }
            }
        }
    ?>
    <div class="rentpress-tab-button" onclick="openTab(event, 'prop-floorplans')"><span
        class="wp-menu-image dashicons-before dashicons-layout" aria-hidden="true"></span> Floor
      Plans</div>
    <div id="rentpress-expand-all">Expand All</div>
  </div>

  <div id="prop-marketing" class="rentpress-tab-section">
    <div class="rentpress-accordion"><span class="fas fa-tags" aria-hidden="true"></span> Special</div>
    <div class="rentpress-panel">
      <p class="rentpress-panel-heading">If your property is running a special discount, enter that information here
        (keep less than 120 characters). You can also optionally add a link and an expiration date to automatically
        remove the special from display.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Special Text</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_special_text', $rpm, 'text'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Special Link</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_special_link', $rpm, 'url', ''), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Special Expiration</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_special_expiration', $rpm, 'date'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span aria-hidden="true" class="fas fa-edit"></span> Contact Form</div>
    <div class="rentpress-panel">
      <p>Set a destination for the "Request Info" CTA for this property and its floor plans. <br /><br />
        The default <strong>Global Setting</strong> will use the value set in the "Contact Page URL" option from
        RentPress settings.<br />
        The <strong>Specific URL</strong> option will allow you to set a URL for a contact for specific to this
        property.<br />
        Choosing <strong>Gravity Forms Shortcode</strong> will allow you to enter the shortcode for a Gravity Form you
        have configured. The form will appear in a modal on page.<br /><br />
      </p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Contact Destination</label>
        <?php echo wp_kses(rentpress_metaFieldSelector('rentpress_custom_field_property_contact_type', $rpm, ['Global Setting' => 1, 'Specific URL' => 2, 'Gravity Forms Shortcode' => 3]), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group" id="rentpress_custom_field_property_specific_contact_link_group">
        <label class="rentpress-settings-title">Contact Form URL</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_specific_contact_link', $rpm, 'url', ''), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group" id="rentpress_custom_field_property_specific_gravity_form_group">
        <label class="rentpress-settings-title">Gravity Form Shortcode</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_specific_gravity_form', $rpm, 'text', ''), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-share-alt" aria-hidden="true"></span> Social Profiles</div>
    <div class="rentpress-panel">
      <p>Add links to social profiles to invite shoppers to connect with your property or management company on social
        networks.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Facebook Profile URL</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_facebook_url', $rpm, 'url'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Twitter Profile URL</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_twitter_url', $rpm, 'url', ), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Instagram Profile URL</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_instagram_url', $rpm, 'url', ), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="far fa-images" aria-hidden="true"></span> Photo Gallery</div>
    <div class="rentpress-panel">
      <p>Add a WordPress shortcode from a photo or video gallery plugin. When active, the "Gallery" section will show on
        this property's page.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Gallery Shortcode</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_property_gallery_shortcode', $rpm, 'text'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Gallery Images</label>
        <?php echo wp_kses(rentpress_metaFieldImage('rentpress_custom_field_property_gallery_images', $rpm, 'text', 'Choose Images To Display In Property Gallery', 'false'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <!-- ADD BACK IN WHEN FEATURE IS MET ABOUT WITH THE MARKETING TEAM -->
    <!-- <div class="rentpress-accordion"><span class="far fa-comments" aria-hidden="true"></span> Reviews Shortcode</div>
            <div class="rentpress-panel">
                <p>Add a shortcode from RentPress: Reviews Add-on to display resident reviews. Slider view is recommended. More information is available at the <a href="https://rentpress.com/support" rel="noopener noreferrer" target="_blank">Support Site >></a></p>

                <div class="rentpress-settings-group">
                    <label class="rentpress-settings-title">Reviews Shortcode</label> -->
    <?php //echo rentpress_metaField('rentpress_custom_field_property_reviews_shortcode', $rpm, 'text'); ?>
    <!-- </div>
            </div> -->

    <div class="rentpress-accordion"><span class="fas fa-paw" aria-hidden="true"></span> Pet Policy Details</div>
    <div class="rentpress-panel">
      <p>Add details about the property's pet policy. Limit to around 100-120 words. This will show if the property has
        a Pets taxonomy selected. Supports HTML tags.</p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Pet Policy Details</label>
        <?php echo wp_kses(rentpress_metaTextArea('rentpress_custom_field_property_pet_policy', $rpm, '10'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="wp-menu-image dashicons-before dashicons-location-alt"
        aria-hidden="true"></span> Neighborhood Details</div>
    <div class="rentpress-panel">
      <p>Properties can exist within multiple neighborhoods. <br />
        <br />
        First create your neighborhoods and asssign properties into them. You can then choose to assign this property's
        primary neighborhood. If no neighborhood is set as primary, the property's city will be used.
      </p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Primary Neighborhood </label>
        <?php echo wp_kses(rentpress_metaFieldNeighborhoodSelector('rentpress_custom_field_property_neighborhood_post_id', $rpm, $neighborhoods), $rentpress_allowed_HTML) ?>
      </div>
    </div>

    <!-- ADD THIS SECTION BACK IN WHEN DESIGN IS FINALIZED -->
    <!-- <div class="rentpress-accordion"><span class="fas fa-certificate" aria-hidden="true"></span> Logo and Tagline</div>
            <div class="rentpress-panel">
                <p>Add a logo to represent the property and a tagline or slogan if desired.</p>

                <div class="rentpress-settings-group">
                    <label class="rentpress-settings-title">Property Logo</label> -->
    <?php //echo rentpress_metaField('rentpress_custom_field_property_logo', $rpm, 'url', 'https://photourl.com/'); ?>
    <!-- </div>

                <div class="rentpress-settings-group">
                    <label class="rentpress-settings-title">Property Tagline</label> -->
    <?php //echo rentpress_metaField('rentpress_custom_field_property_tagline', $rpm, 'text', 'this is your tagline'); ?>
    <!-- </div>
            </div> -->

    <div class="rentpress-accordion"><span class="fas fa-certificate" aria-hidden="true"></span> Branding</div>
    <div class="rentpress-panel">
      <p>Add branding to represent the property.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Property Accent Color</label>
        <?php echo rentpress_colorMetaField('rentpress_custom_field_property_accent_color', $rpm); ?>
        <div style="width:20px;"></div>
        <?php echo rentpress_checkboxMetaField('rentpress_custom_field_property_accent_color_use_property_branding', $rpm, 'Use Property Accent Where Available'); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-search" aria-hidden="true"></span> Additional Search Keywords
    </div>
    <div class="rentpress-panel">
      <p>Add additional search keywords for this property. Keep each keyword separated by a comma. These keywords will
        be used in searches, but not shown to shoppers.</p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Search Keywords</label>
        <?php echo wp_kses(rentpress_metaTextArea('rentpress_custom_field_property_search_keywords', $rpm, '3', 'Downtown, Westside, Near Bus Line'), $rentpress_allowed_HTML); ?>
      </div>
    </div>
  </div>

  <div id="prop-info" class="rentpress-tab-section">
    <div class="rentpress-accordion"><span class="fas fa-phone" aria-hidden="true"></span> Phone Number</div>
    <div class="rentpress-panel">
      <p>The phone number of the property. If you have a tracking number, enter it here.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Phone Number</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_phone', $rpm, $overrides, 'phone', '(555) 555-1234'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-align-left" aria-hidden="true"></span> Property Description
    </div>
    <div class="rentpress-panel">
      <p>Header text for Property Description: Enter a description about the property. Limit to around 100-120 words.
        Supports HTML tags.
      </p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Property Description</label>
        <?php echo wp_kses(rentpress_overrideMetaTextArea('rentpress_custom_field_property_description', $rpm, $overrides, '7'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-file-signature" aria-hidden="true"></span> Application Link
    </div>
    <div class="rentpress-panel">
      <p>Enter the URL for property's online application and select how you would like the link to affect the
        floorplans
        and units. If box is left empty, the "Default Application Link" from the settings page is used.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Apply Link</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_apply_link', $rpm, $overrides, 'url', ''), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Link Options</label>
        <?php echo wp_kses(rentpress_metaFieldSelector('rentpress_custom_field_property_link_options', $rpm, ['Default apply link', 'Override every apply link']), $rentpress_allowed_HTML); ?>
      </div>

    </div>

    <div class="rentpress-accordion"><span class="fas fa-user-circle" aria-hidden="true"></span> Residents Link</div>
    <div class="rentpress-panel">
      <p>Enter the URL for the property's online resident portal. If not set, link will not display.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Residents Link</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_residents_link', $rpm, $overrides, 'url'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-envelope" aria-hidden="true"></span> Email Address</div>
    <div class="rentpress-panel">
      <p>The email address for the property. If you have a tracking email address, enter it here.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Email Address</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_email', $rpm, $overrides, 'email'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-link" aria-hidden="true"></span> Website</div>
    <div class="rentpress-panel">
      <p>The address for a standalone property website.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Website Address</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_website', $rpm, $overrides, 'url'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-map" aria-hidden="true"></span> Location</div>
    <div class="rentpress-panel">
      <p>The address information for your property.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Street Address</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_address', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">City</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_city', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">State Abbreviation</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_state', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Zip Code</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_zip', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Longitude</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_longitude', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Latitude</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_latitude', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-clock" aria-hidden="true"></span> Office Hours</div>
    <div class="rentpress-panel">
      <div class="rentpress-override-row rentpress-p-top">
        <span>
          <?php echo wp_kses(rentpress_checkboxMetaField('rentpress_custom_field_property_office_hours_checkbox', $rpm, "Override Office Hours"), $rentpress_allowed_HTML); ?>
          <?php $office_hours_override_checked = isset($rpm['rentpress_custom_field_property_office_hours_checkbox'][0]);?>
        </span>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Monday</label>
        <?php echo wp_kses(rentpress_timeMetaFields('rentpress_custom_field_property_monday_open', 'rentpress_custom_field_property_monday_close', $rpm, $office_hours_override_checked), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Tuesday</label>
        <?php echo wp_kses(rentpress_timeMetaFields('rentpress_custom_field_property_tuesday_open', 'rentpress_custom_field_property_tuesday_close', $rpm, $office_hours_override_checked), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Wednesday</label>
        <?php echo wp_kses(rentpress_timeMetaFields('rentpress_custom_field_property_wednesday_open', 'rentpress_custom_field_property_wednesday_close', $rpm, $office_hours_override_checked), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Thursday</label>
        <?php echo wp_kses(rentpress_timeMetaFields('rentpress_custom_field_property_thursday_open', 'rentpress_custom_field_property_thursday_close', $rpm, $office_hours_override_checked), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Friday</label>
        <?php echo wp_kses(rentpress_timeMetaFields('rentpress_custom_field_property_friday_open', 'rentpress_custom_field_property_friday_close', $rpm, $office_hours_override_checked), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Saturday</label>
        <?php echo wp_kses(rentpress_timeMetaFields('rentpress_custom_field_property_saturday_open', 'rentpress_custom_field_property_saturday_close', $rpm, $office_hours_override_checked), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Sunday</label>
        <?php echo wp_kses(rentpress_timeMetaFields('rentpress_custom_field_property_sunday_open', 'rentpress_custom_field_property_sunday_close', $rpm, $office_hours_override_checked), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-cogs" aria-hidden="true"></span> Configuration</div>
    <div class="rentpress-panel">
      <p class="rentpress-override-bottom">Configuration for the property data.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Property Code</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_code', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Import Source</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_import_source', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-dollar-sign" aria-hidden="true"></span> Pricing</div>
    <div class="rentpress-panel">
      <p>Values are calculated from the property’s floor plans. You can select which price you would like to display
        or
        optionally override each value.</p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Minimum Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_rent_min', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Maximum Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_rent_max', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Base Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_rent_base', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Market Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_rent_market', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Term Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_rent_term', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Effective Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_rent_effective', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Best Price</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_property_rent_best', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Price to Display</label>
        <?php echo wp_kses($pricing_selector_str, $rentpress_allowed_HTML); ?>
        <?php echo !isset($rpm['rentpress_custom_field_property_rent_type_selection_override'][0]) ? "<div style='margin-left:16px;margin-top:4px;'>Global Setting: $selected_price_type</div>" : '' ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Disable Pricing</label>
        <?php echo wp_kses(rentpress_checkboxMetaField('rentpress_custom_field_property_disable_pricing', $rpm, 'Disable pricing at this property'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <?php 
        if (isset($rentpress_add_on_field_args->rentpress_property_meta) ? count((array)$rentpress_add_on_field_args->rentpress_property_meta ) : '') {
            foreach ($rentpress_add_on_field_args->rentpress_property_meta as $property_meta) {
                if (isset($property_meta->callback) && isset($property_meta->new_tab) ? !$property_meta->new_tab : '') {
                    call_user_func($property_meta->callback, ['rpm' => $rpm, 'rentpress_allowed_HTML' => $rentpress_allowed_HTML]);
                }
            }
        }
    ?>
  </div>

  <?php 
    if (isset($rentpress_add_on_field_args->rentpress_property_meta) ? count((array)$rentpress_add_on_field_args->rentpress_property_meta ) : '') {
        foreach ($rentpress_add_on_field_args->rentpress_property_meta as $property_meta) {
            if (isset($property_meta->callback) && isset($property_meta->new_tab) ? $property_meta->new_tab : '') {
                call_user_func($property_meta->callback, ['rpm' => $rpm, 'rentpress_allowed_HTML' => $rentpress_allowed_HTML]);
            }
        }
    }
  ?>

  <div id="prop-floorplans" class="rentpress-tab-section">
    <div class="rentpress-accordion"><span class="fas fa-regular fa-circle" aria-hidden="true"></span> Floor Plan
      Stats
    </div>
    <div class="rentpress-panel">
      <h3>Floor Plan Starting Price</h3>
      <ul>
        <?php
$matrix = json_decode($property_data->property_availability_matrix);
    foreach ($matrix as $data): if ((isset($data->name) ? $data->name : '') && (isset($data->price) ? $data->price : '')): ?>
        <li><strong><?php echo $data->name; ?></strong> : $<?php echo $data->price; ?></li>
        <?php endif;endforeach;?>
      </ul>
    </div>
    <div class="rentpress-accordion"><span class="fas fa-list" aria-hidden="true"></span> Associated Floor Plans</div>
    <div class="rentpress-panel">
      <p class="rentpress-panel-heading">Floor plans that have this property listed as their parent will appear
        here for quick access.</p>
      <?php foreach ($floorplan_posts as $fp_key => $fp): ?>
      <p><a href='<?php echo esc_url($fp->guid); ?>'><?php echo esc_html($fp->post_title); ?></a></p>
      <?php endforeach;?>
    </div>
  </div>
</div>

<?php
}

function rentpress_createPropertyEmailMetaList($rentpress_add_on_field_args)
{
    $EmailMetaList = [
        'rentpress_custom_field_property_email',
    ];
    if (isset($rentpress_add_on_field_args->rentpress_property_meta) ? count((array)$rentpress_add_on_field_args->rentpress_property_meta ) : '') {
        foreach ($rentpress_add_on_field_args->rentpress_property_meta as $property_meta) {
            if (isset($property_meta->meta_type) ? $property_meta->meta_type === 'email' : '') {
                $EmailMetaList[] = $property_meta->meta_key;
            }
        }
    }
    return $EmailMetaList;
}

function rentpress_createPropertyTextFieldMetaList($rentpress_add_on_field_args)
{
    $TextFieldMetaList = [
        'rentpress_custom_field_property_special_text',
        'rentpress_custom_field_property_special_expiration',
        'rentpress_custom_field_property_gallery_shortcode',
        'rentpress_custom_field_property_accent_color',
        'rentpress_custom_field_property_neighborhood_post_id',
        'rentpress_custom_field_property_neighborhood_post_ids',
        'rentpress_custom_field_property_search_keywords',
        'rentpress_custom_field_property_phone',
        'rentpress_custom_field_property_address',
        'rentpress_custom_field_property_city',
        'rentpress_custom_field_property_state',
        'rentpress_custom_field_property_zip',
        'rentpress_custom_field_property_longitude',
        'rentpress_custom_field_property_latitude',
        'rentpress_custom_field_property_code',
        'rentpress_custom_field_property_import_source',
        'rentpress_custom_field_property_rent_min',
        'rentpress_custom_field_property_rent_max',
        'rentpress_custom_field_property_rent_base',
        'rentpress_custom_field_property_rent_market',
        'rentpress_custom_field_property_rent_term',
        'rentpress_custom_field_property_rent_effective',
        'rentpress_custom_field_property_rent_best',
        'rentpress_custom_field_property_specific_gravity_form',
    ];
    if (isset($rentpress_add_on_field_args->rentpress_property_meta) ? count((array)$rentpress_add_on_field_args->rentpress_property_meta ) : '') {
        foreach ($rentpress_add_on_field_args->rentpress_property_meta as $property_meta) {
            if (isset($property_meta->meta_type) ? $property_meta->meta_type === 'text' : '') {
                $TextFieldMetaList[] = $property_meta->meta_key;
            }
        }
    }
    return $TextFieldMetaList;
}

function rentpress_createPropertyTextAreaMetaList($rentpress_add_on_field_args)
{
    $TextAreaMetaList = [
        'rentpress_custom_field_property_pet_policy',
        'rentpress_custom_field_property_description',
    ];
    if (isset($rentpress_add_on_field_args->rentpress_property_meta) ? count((array)$rentpress_add_on_field_args->rentpress_property_meta ) : '') {
        foreach ($rentpress_add_on_field_args->rentpress_property_meta as $property_meta) {
            if (isset($property_meta->meta_type) ? $property_meta->meta_type === 'textarea' : '') {
                $TextAreaMetaList[] = $property_meta->meta_key;
            }
        }
    }
    return $TextAreaMetaList;
}

function rentpress_createPropertyURLMetaList($rentpress_add_on_field_args)
{
    $URLMetaList = [
        'rentpress_custom_field_property_special_link',
        'rentpress_custom_field_property_facebook_url',
        'rentpress_custom_field_property_twitter_url',
        'rentpress_custom_field_property_instagram_url',
        'rentpress_custom_field_property_apply_link',
        'rentpress_custom_field_property_residents_link',
        'rentpress_custom_field_property_website',
        'rentpress_custom_field_property_specific_contact_link',
    ];
    if (isset($rentpress_add_on_field_args->rentpress_property_meta) ? count((array)$rentpress_add_on_field_args->rentpress_property_meta ) : '') {
        foreach ($rentpress_add_on_field_args->rentpress_property_meta as $property_meta) {
            if (isset($property_meta->meta_type) ? $property_meta->meta_type === 'url' : '') {
                $URLMetaList[] = $property_meta->meta_key;
            }
        }
    }
    return $URLMetaList;
}