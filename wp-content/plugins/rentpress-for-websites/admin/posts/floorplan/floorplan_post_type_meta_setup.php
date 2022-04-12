<?php

/*
 *  Save the meta box’s post metadata.
 */
function rentpress_save_floorplan_meta($post_id, $post)
{

    // Get the post type object
    $post_type = get_post_type_object($post->post_type);

    // Verify the nonce before proceeding and check if the current user has permission to edit the post
    if (isset($_POST['rentpress_custom_field_floorplan_nonce']) &&
        wp_verify_nonce($_POST['rentpress_custom_field_floorplan_nonce'], basename(__FILE__)) &&
        current_user_can($post_type->cap->edit_post, $post_id)) {
        // get all the post meta
        $rpm = get_post_meta($post_id);

        // save parent property information always since it is always in the $_POST
        update_post_meta(
            $post_id,
            'rentpress_custom_field_floorplan_parent_property_code',
            sanitize_text_field($_POST['rentpress_custom_field_floorplan_parent_property_code'])
        );

        // delete the override post meta if it doesnt exist in the form submit
        foreach ($rpm as $meta_override_key => $meta_override_value) {
            if (strpos($meta_override_key, 'rentpress_custom_field_floorplan') !== false &&
                strpos($meta_override_key, 'override') !== false) {

                // if the override doesnt exist in the request delete the unused meta
                if (!isset($_POST[$meta_override_key])) {
                    delete_post_meta($post_id, $meta_override_key);
                }
            }
        }

        // set up the meta arrays to save them based on type
        $post_text_fields = rentpress_createFloorplanTextFieldMetaList();
        $post_text_area = rentpress_createFloorplanTextAreaMetaList();
        $post_urls = rentpress_createFloorplanURLMetaList();

        // if there is an override set in the request, set the value
        foreach ($_POST as $key => $value) {
            if (strpos($key, '_override') !== false &&
                strpos($key, 'rentpress_custom_field_floorplan') !== false &&
                $value == 'on') {

                // remove override from the name of the key
                $meta_key = str_replace('_override', '', $key);
                $new_meta_value = '';

                /* Get the posted data and sanitize it for use as an HTML class. */
                if (in_array($meta_key, $post_text_fields)) {
                    $new_meta_value = (isset($_POST[$meta_key]) ? sanitize_text_field($_POST[$meta_key]) : '');
                } elseif (in_array($meta_key, $post_text_area)) {
                    $new_meta_value = (isset($_POST[$meta_key]) ? sanitize_textarea_field($_POST[$meta_key]) : '');
                } elseif (in_array($meta_key, $post_urls)) {
                    $new_meta_value = (isset($_POST[$meta_key]) ? esc_url_raw($_POST[$meta_key]) : '');
                }

                update_post_meta($post_id, $meta_key, $new_meta_value);
                update_post_meta($post_id, $key, $value);

            }
        }

        if (isset($_POST['rentpress_custom_field_floorplan_gallery_images'])) {
            update_post_meta($post_id, 'rentpress_custom_field_floorplan_gallery_images', sanitize_text_field($_POST['rentpress_custom_field_floorplan_gallery_images']));
        }

        // When floorplan is updated, run the single property resync to maintain data integrity
        if (isset($_POST['rentpress_custom_field_floorplan_parent_property_code'])) {
            $args = array(
                'post_type' => 'rentpress_property',
                'meta_query' => array(
                    array(
                        'key' => 'rentpress_custom_field_property_code',
                        'value' => sanitize_text_field($_POST['rentpress_custom_field_floorplan_parent_property_code']),
                    ),
                ),
            );
            $property_query = new WP_Query($args);
            require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/single_property_refresh.php';
            rentpress_syncFeedAndWPPropertyMeta($property_query->posts[0]->ID);
        }
    }
}
add_action('save_post', 'rentpress_save_floorplan_meta', 10, 2);

/*
 *  Create meta box to hold all of the viewable meta data fields
 */
function rentpress_add_custom_floorplan_data_box()
{
    $box_title = 'RentPress - Floor Plan Editor';

    add_meta_box(
        'rentpress_custom_floorplan_data_box', // Unique ID
        $box_title, // Box title
        'rentpress_custom_floorplan_data_box_html', // section callback, must be of type callable
        'rentpress_floorplan' // Post type
    );
}

add_action('add_meta_boxes', 'rentpress_add_custom_floorplan_data_box');

/*
 *  Create HTML that will show in the meta box
 */
function rentpress_custom_floorplan_data_box_html($post)
{
    wp_nonce_field(basename(__FILE__), 'rentpress_custom_field_floorplan_nonce');
    $rpm = get_post_meta($post->ID); // Get RentPress Floor Plan Meta
    $overrides = array();

    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $floorplan = rentpress_getAllFloorplanDataWithCodeOrPostID($post->ID);
    $unit_codes = rentpress_getAllUnitCodes();

    foreach ($rpm as $key => $value) {
        if (strpos($key, 'override') !== false &&
            strpos($key, 'rentpress_custom_field_floorplan') !== false &&
            $value[0] == 'on') {
            $overrides[$key] = $value[0];
        }
    }

    require_once RENTPRESS_PLUGIN_ADMIN_DIR . 'posts/meta/metafields.php';
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/property_post_type_data.php';
    if (isset($rpm['rentpress_custom_field_floorplan_parent_property_code'])) {
        $parent_property_meta = rentpress_getAllPropertyMetaByCode($rpm['rentpress_custom_field_floorplan_parent_property_code'][0]);
        $parent_property_rent_type_selection = $parent_property_meta['rentpress_custom_field_property_rent_type_selection'][0];
    }
    ?>

<div class="rentpress-cpt-editor-container">
  <div class="rentpress-tabs">
    <div class="rentpress-tab-button" onclick="openTab(event, 'fp-marketing')"><span class="fas fa-bullhorn"
        aria-hidden="true"></span>
      Marketing
    </div>
    <div class="rentpress-tab-button" onclick="openTab(event, 'fp-info')"><span class="fas fa-info-circle"
        aria-hidden="true"></span> Info
    </div>
    <div class="rentpress-tab-button" onclick="openTab(event, 'fp-units')"><span class="fas fa-cube"
        aria-hidden="true"></span> Units</div>
    <div id="rentpress-expand-all">Expand All</div>
  </div>

  <div id="fp-marketing" class="rentpress-tab-section">
    <div class="rentpress-accordion"><span class="fas fa-tags" aria-hidden="true"></span> Special</div>
    <div class="rentpress-panel">
      <p class="rentpress-panel-heading">Add the current special for this floor plan. You can also add a link
        destination and/or an expiration date.</p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Special Text</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_floorplan_special_text', $rpm, 'text'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Special Link</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_floorplan_special_link', $rpm, 'url', ''), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Special Expiration</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_floorplan_special_expiration', $rpm, 'date'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="far fa-play-circle" aria-hidden="true"></span> Virtual Tour</div>
    <div class="rentpress-panel">
      <p>Enter the URL to this floor plan's virtual tour. Supports Matterport, YouTube, and other embedded videos.</p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Floor Plan Tour</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_matterport_video', $rpm, $overrides, 'url'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="far fa-images" aria-hidden="true"></span> Images</div>
    <!--  <div class="rentpress-panel">
                <p>Enter the URL to this floor plan's image file</p>
                <div class="rentpress-settings-group">
                    <label class="rentpress-settings-title">Floor Plan Image URL</label>
                    <?php //echo rentpress_metaField('rentpress_custom_field_floorplan_image', $rpm, 'url', 'https://imageurlexample.com'); ?>
                </div>
            </div> -->

    <div class="rentpress-panel">
      <p>Add images for the floor plan</p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Add Gallery Images</label>
        <?php echo wp_kses(rentpress_metaFieldImage('rentpress_custom_field_floorplan_gallery_images', $rpm, 'text', 'Choose Images To Display the Floor Plan', 'false'), $rentpress_allowed_HTML); ?>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-align-left" aria-hidden="true"></span> Narrative Description
    </div>
    <div class="rentpress-panel">
      <p>Add a description about this floor plan. Limit to one short paragraph or about 100-120 words.</p>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Floor Plan Description</label>
        <?php echo wp_kses(rentpress_metaTextArea('rentpress_custom_field_floorplan_description', $rpm, '6'), $rentpress_allowed_HTML); ?>
      </div>
    </div>
  </div>

  <div id="fp-info" class="rentpress-tab-section">
    <div class="rentpress-accordion"><span class="fas fa-info-circle" aria-hidden="true"></span> Floor Plan Info</div>
    <div class="rentpress-panel">
      <div class="rentpress-override-row clearfix">
        <span>
          <input type="checkbox" class="rentpress-override-all rentpress-override">
          <label>Override All</label>
        </span>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Bedrooms</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_bedroom_count', $rpm, $overrides, 'number'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Bathrooms</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_bathroom_count', $rpm, $overrides, 'number'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Square Foot Range</label>
        <?php echo wp_kses(rentpress_rangeMetaFields('rentpress_custom_field_floorplan_min_sqft', 'rentpress_custom_field_floorplan_max_sqft', $rpm, $overrides), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Availability URL</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_availability_url', $rpm, $overrides, 'url'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Floor Plan Code</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_code', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Unit Type Mapping</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_unit_type_mapping', $rpm, $overrides, 'text'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Parent Property Code</label>
        <?php echo wp_kses(rentpress_floorplan_createPropertyCodeSelector('rentpress_custom_field_floorplan_parent_property_code', $rpm), $rentpress_allowed_HTML); ?>
      </div>
    </div>
    <div class="rentpress-accordion"><span class="fas fa-dollar-sign" aria-hidden="true"></span> Pricing</div>
    <div class="rentpress-panel">
      <p>Values are calculated based on the "Price Ranges" RentPress setting. You can select which you would like to
        display or optionally override each value.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Minimum Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_rent_min', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Maximum Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_rent_max', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Base Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_rent_base', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Market Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_rent_market', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Term Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_rent_term', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Effective Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_rent_effective', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>
      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Best Rent</label>
        <?php echo wp_kses(rentpress_overrideMetaField('rentpress_custom_field_floorplan_rent_best', $rpm, $overrides, 'number', '0'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Property Rent Type</label>
        <?php echo "<input type='text' value='$parent_property_rent_type_selection' readonly>" ?>
      </div>
    </div>
  </div>

  <div id="fp-units" class="rentpress-tab-section">
    <div class="rentpress-accordion"><span class="fas fa-chart-bar" aria-hidden="true"></span> Unit Stats</div>
    <div class="rentpress-panel">
      <p class="rentpress-override-bottom">Note that the unit stats shown here are calculated from the units for this
        floor plan.</p>
      <div>
        <div class="rentpress-settings-group">
          <div class="rentpress-settings-title-alt"></div>
          <div>
            <?php $isFloorplanAvail = isset($floorplan->floorplan_available) ? $floorplan->floorplan_available : '0';?>
            <?php echo $isFloorplanAvail ? 'Floorplan is available' : 'Floorplan is not available' ?>
          </div>
        </div>

        <div class="rentpress-settings-group">
          <div class="rentpress-settings-title-alt">Total Units</div>
          <div>
            <?php echo isset($floorplan->floorplan_units_total) ? esc_html($floorplan->floorplan_units_total) : '0'; ?>
          </div>

        </div>

        <div class="rentpress-settings-group">
          <div class="rentpress-settings-title-alt">Available Based on settings</div>
          <div>
            <?php echo isset($floorplan->floorplan_units_available) ? esc_html($floorplan->floorplan_units_available) : '0'; ?>
          </div>

        </div>

        <div class="rentpress-settings-group">
          <div class="rentpress-settings-title-alt">Available in 30 days </div>
          <div>
            <?php echo isset($floorplan->floorplan_units_available_30) ? esc_html($floorplan->floorplan_units_available_30) : '0'; ?>
          </div>

        </div>

        <div class="rentpress-settings-group">
          <div class="rentpress-settings-title-alt">Available in 60 days</div>
          <div>
            <?php echo isset($floorplan->floorplan_units_available_60) ? esc_html($floorplan->floorplan_units_available_60) : '0'; ?>
          </div>

        </div>

        <div class="rentpress-settings-group">
          <div class="rentpress-settings-title-alt">Unavailable Units</div>
          <div>
            <?php echo isset($floorplan->floorplan_units_unavailable) ? esc_html($floorplan->floorplan_units_unavailable) : '0'; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="rentpress-accordion"><span class="fas fa-plus-square" aria-hidden="true"></span> Add New Unit</div>
    <div class="rentpress-panel">
      <h3 id="results_for_new_unit" class="rentpress-request-results"></h3>
      <p class="rentpress-panel-heading">To add a unit to this floor plan, fill out the form with the required
        information. Most values are pre-populated based on this floor plan's information.</p>
      <div id="rentpress-new-unit-form">
        <input class="rentpress-new-unit-form-value" type="hidden" name="unit_parent_property_code"
          value="<?php echo esc_attr($floorplan->floorplan_parent_property_code); ?>">
        <input class="rentpress-new-unit-form-value" type="hidden" name="unit_parent_floorplan_code"
          value="<?php echo esc_attr($floorplan->floorplan_code); ?>">
        <input class="rentpress-new-unit-form-value" type="hidden" name="unit_bedrooms"
          value="<?php echo esc_attr($floorplan->floorplan_bedrooms) ?>">
        <input class="rentpress-new-unit-form-value" type="hidden" name="unit_bathrooms"
          value="<?php echo esc_attr($floorplan->floorplan_bathrooms) ?>">

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_code">Unit Code</label>
          <input id="rentpress-unit-code" class="rentpress-settings-text rentpress-new-unit-form-value" type="text"
            name="unit_code">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_name">Unit Name</label>
          <input id="rentpress-unit-name" class="rentpress-settings-text rentpress-new-unit-form-value" type="text"
            name="unit_name">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_sqft">SQ FT</label>
          <input class="rentpress-settings-text rentpress-new-unit-form-value" type="number" name="unit_sqft"
            value="<?php echo esc_attr(!empty($floorplan->floorplan_sqft_max) ? $floorplan->floorplan_sqft_max : $floorplan->floorplan_sqft_min); ?>">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_rent_min">Min Rent</label>
          <input class="rentpress-settings-text rentpress-new-unit-form-value" type="number" name="unit_rent_min"
            value="<?php echo esc_attr($floorplan->floorplan_rent_min); ?>">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_rent_max">Max Rent</label>
          <input class="rentpress-settings-text rentpress-new-unit-form-value" type="number" name="unit_rent_max"
            value="<?php echo esc_attr($floorplan->floorplan_rent_max); ?>">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_rent_base">Base Rent</label>
          <input class="rentpress-settings-text rentpress-new-unit-form-value" type="number" name="unit_rent_base"
            value="<?php echo esc_attr($floorplan->floorplan_rent_base); ?>">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_rent_market">Market Rent</label>
          <input class="rentpress-settings-text rentpress-new-unit-form-value" type="number" name="unit_rent_market"
            value="<?php echo esc_attr($floorplan->floorplan_rent_market); ?>">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_rent_term_best">Best Term Rent</label>
          <input class="rentpress-settings-text rentpress-new-unit-form-value" type="number" name="unit_rent_term_best"
            value="<?php echo esc_attr($floorplan->floorplan_rent_term) ?>">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_rent_effective">Effective Rent</label>
          <input class="rentpress-settings-text rentpress-new-unit-form-value" type="number" name="unit_rent_effective"
            value="<?php echo esc_attr($floorplan->floorplan_rent_effective); ?>">
        </div>
        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_rent_best">Best Rent</label>
          <input class="rentpress-settings-text rentpress-new-unit-form-value" type="number" name="unit_rent_best"
            value="<?php echo esc_attr($floorplan->floorplan_rent_best); ?>">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_ready_date">Soonest Available Date </label>
          <input id="rentpress-unit-ready-date" class="rentpress-settings-text rentpress-new-unit-form-value"
            type="date" name="unit_ready_date">
        </div>

        <div class="rentpress-settings-group">
          <label class="rentpress-settings-title" for="unit_availability_url">Unit Application URL </label>
          <input id="unit_availability_url" class="rentpress-settings-text rentpress-new-unit-form-value" type="url"
            name="unit_availability_url">
        </div>

        <div class="rentpress-button-container">
          <div id="rentpress-new-unit-button" class="rentpress-action-button rentpress-action-button-active"><span
              class="far fa-plus-square" aria-hidden="true"></span> Add Unit</div>
        </div>
      </div>

    </div>

    <div class="rentpress-accordion"><span class="fas fa-cube" aria-hidden="true"></span> Manage Units</div>
    <div class="rentpress-panel">
      <!-- Units are populated by refreshAddedUnits() js function -->
      <div id="rentpress-unit-display"></div>
    </div>
  </div>
</div>

<div id="rentpress-edit-unit-form-container" class="rentpress-admin-modal" onclick="closeEditUnitModal()">
  <a></a>
  <div onclick="event.stopPropagation()" class="rentpress-modal-form-container">

    <div id="rentpress-edit-unit-form" class="rentpress-modal-form">
      <h3 id="results_for_edit_unit" class="rentpress-request-results"></h3>

      <h1 class="rentpress-edit-unit-title ">Unit <input id="rentpress-edit-unit-code"
          class="rentpress-edit-unit-form-value" name="unit_edit_code" disabled></h1>

      <input class="rentpress-edit-unit-form-value" type="hidden" name="unit_edit_parent_property_code"
        value="<?php echo esc_attr($floorplan->floorplan_parent_property_code); ?>">
      <input class="rentpress-edit-unit-form-value" type="hidden" name="unit_edit_parent_floorplan_code"
        value="<?php echo esc_attr($floorplan->floorplan_code); ?>">
      <input class="rentpress-edit-unit-form-value" type="hidden" name="unit_edit_bedrooms"
        value="<?php echo esc_attr($floorplan->floorplan_bedrooms); ?>">
      <input class="rentpress-edit-unit-form-value" type="hidden" name="unit_edit_bathrooms"
        value="<?php echo esc_attr($floorplan->floorplan_bathrooms); ?>">

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title" for="unit_edit_name">Name</label>
        <input id="rentpress-edit-unit-name" class="rentpress-settings-text rentpress-edit-unit-form-value"
          name="unit_edit_name">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title" for="unit_edit_sqft">SQ FT</label>
        <input class="rentpress-settings-text rentpress-edit-unit-form-value" type="number" name="unit_edit_sqft">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Min Rent</label>
        <input type="number" name="unit_edit_rent_min" class="rentpress-settings-text rentpress-edit-unit-form-value">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Max Rent</label>
        <input type="number" name="unit_edit_rent_max" class="rentpress-settings-text rentpress-edit-unit-form-value">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Base Rent</label>
        <input type="number" name="unit_edit_rent_base" class="rentpress-settings-text rentpress-edit-unit-form-value">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Market Rent</label>
        <input type="number" name="unit_edit_rent_market"
          class="rentpress-settings-text rentpress-edit-unit-form-value">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Best Term Rent</label>
        <input type="number" name="unit_edit_rent_term_best"
          class="rentpress-settings-text rentpress-edit-unit-form-value">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Effective Rent</label>
        <input type="number" name="unit_edit_rent_effective"
          class="rentpress-settings-text rentpress-edit-unit-form-value">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Best Rent</label>
        <input type="number" name="unit_edit_rent_best" class="rentpress-settings-text rentpress-edit-unit-form-value">
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title" for="unit_edit_ready_date">Soonest Available Date </label>
        <input id="rentpress-edit-unit-ready-date" class="rentpress-settings-text rentpress-edit-unit-form-value"
          type="date" name="unit_edit_ready_date">
      </div>


      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title" for="unit_edit_availability_url">Unit Application URL </label>
        <input class="rentpress-settings-text rentpress-edit-unit-form-value" type="url"
          name="unit_edit_availability_url">
      </div>

      <div id="rentpress-edit-unit-button-container" class="rentpress-button-container">
        <div id="rentpress-save-unit-button" class="rentpress-action-button rentpress-action-button-active"><span
            class="far fa-share-square" aria-hidden="true"></span> Update Unit</div>

        <div id="rentpress-delete-unit-button" class="rentpress-action-button rentpress-delete-button"
          onclick="showDeleteConfirmation()"><span class="far fa-trash-alt" aria-hidden="true"></span> Delete Unit</div>
      </div>

      <div id="rentpress-delete-confirmation-container" class="rentpress-button-container">
        <p class="rentpress-delete-confirm-message">Are you sure you want to permanently delete unit?</p>

        <div id="rentpress-cancel-unit-button" class="rentpress-action-button rentpress-cancel-button"
          onclick="hideDeleteConfirmation()"><span class="fas fa-exclamation-triangle" aria-hidden="true"></span> Cancel
        </div>

        <div id="rentpress-permanent-delete-unit-button" class="rentpress-action-button rentpress-delete-button"><span
            class="far fa-trash-alt" aria-hidden="true"></span> Delete Unit</div>
      </div>

    </div>
  </div>
</div>
<style>
.rentpress-unit-error {
  background-color: lightcoral !important;
}
</style>
<script>
jQuery(document).ready(function($) {
  $('#rentpress-unit-code').on('change', doesUnitCodeExist)

  var unit_codes = <?php echo json_encode($unit_codes) ?>;

  function doesUnitCodeExist() {
    code = $(this).val();
    if ($.inArray(code, unit_codes) == -1) {
      $(this).removeClass("rentpress-unit-error");
    } else {
      $(this).addClass("rentpress-unit-error");
    }
  }
});
</script>
<?php }

function rentpress_createFloorplanTextFieldMetaList()
{
    return [
        'rentpress_custom_field_floorplan_special_text',
        'rentpress_custom_field_floorplan_special_expiration',
        'rentpress_custom_field_floorplan_bedroom_count',
        'rentpress_custom_field_floorplan_bathroom_count',
        'rentpress_custom_field_floorplan_min_sqft',
        'rentpress_custom_field_floorplan_max_sqft',
        'rentpress_custom_field_floorplan_code',
        'rentpress_custom_field_floorplan_unit_type_mapping',
        'rentpress_custom_field_floorplan_rent_min',
        'rentpress_custom_field_floorplan_rent_max',
        'rentpress_custom_field_floorplan_rent_base',
        'rentpress_custom_field_floorplan_rent_market',
        'rentpress_custom_field_floorplan_rent_term',
        'rentpress_custom_field_floorplan_rent_effective',
        'rentpress_custom_field_floorplan_rent_best',
    ];
}

function rentpress_createFloorplanTextAreaMetaList()
{
    return [
        'rentpress_custom_field_floorplan_description',
    ];
}

function rentpress_createFloorplanURLMetaList()
{
    return [
        'rentpress_custom_field_floorplan_special_link',
        'rentpress_custom_field_floorplan_matterport_video',
        'rentpress_custom_field_floorplan_availability_url',
    ];
}