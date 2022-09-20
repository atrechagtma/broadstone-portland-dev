<?php

/*
 *  Save the meta box’s post metadata.
 */

function remove_property_neighborhood($prop_code, $prop_data, $neighborhood_id, $prop_added)
{
  global $wpdb;
  $table_prefix = $wpdb->prefix;
  $neighborhood_post_data = get_post($neighborhood_id);
  // check if any properties have been added
  if ($prop_added) {
    // 
    if (!str_contains($prop_data->property_neighborhood_post_names, $neighborhood_post_data->post_title)) {
        $hood_list = $prop_data->property_neighborhood_post_names ? $prop_data->property_neighborhood_post_names . "," . $neighborhood_post_data->post_title : $neighborhood_post_data->post_title;
        $wpdb->update($table_prefix . 'rentpress_properties', array('property_neighborhood_post_names' => $hood_list), array('property_code' => $prop_code));
        $meta_update = update_post_meta(
            $prop_data->property_post_id,
            'rentpress_custom_field_property_neighborhood_post_names',
            $hood_list
        );
    }
    // if the properties string of ids doesnt contain the neighborhoods post id add it to the meta and rentpress tables
    if (!str_contains($prop_data->property_neighborhood_post_ids, $neighborhood_id)) {
      $prop_list = $prop_data->property_neighborhood_post_ids ? $prop_data->property_neighborhood_post_ids . "," . $neighborhood_id : $neighborhood_id;
      $wpdb->update($table_prefix . 'rentpress_properties', array('property_neighborhood_post_ids' => $prop_list), array('property_code' => $prop_code));
      $meta_update = update_post_meta(
          $prop_data->property_post_id,
          'rentpress_custom_field_property_neighborhood_post_ids',
          $prop_list
      );

    }
  } else {
    // if this properties primary neighborhood matches the provided id set the properties neighborhood id to nothing
    if ($prop_data->property_primary_neighborhood_post_id === strval($neighborhood_id)) {
      $wpdb->update($table_prefix . 'rentpress_properties', array('property_primary_neighborhood_post_id' => ''), array('property_code' => $prop_code));
      $meta_update = update_post_meta(
          $prop_data->property_post_id,
          'property_primary_neighborhood_post_id',
          null,
      );
    }
    // if the provided neighborhood id is in the properties neighborhood id string remove it and add the new string to the meta and database tables
    if (str_contains($prop_data->property_neighborhood_post_ids, strval($neighborhood_id))) {
      $prop_list = $prop_data->property_neighborhood_post_ids;
      $prop_list = explode(',', $prop_list);
      foreach ($prop_list as $key => $prop) {
          if ($prop === strval($neighborhood_id)) {
              unset($prop_list[$key]);
          }
      }
      $prop_list = implode(',', $prop_list);
      $wpdb->update($table_prefix . 'rentpress_properties', array('property_neighborhood_post_ids' => $prop_list), array('property_code' => $prop_code));
      $meta_update = update_post_meta(
          $prop_data->property_post_id,
          'rentpress_custom_field_property_neighborhood_post_ids',
          $prop_list,
      );
    }
    // 
    if (str_contains($prop_data->property_neighborhood_post_names, $neighborhood_post_data->post_title)) {
        $hood_list = $prop_data->property_neighborhood_post_names;
        $hood_list = explode(',', $hood_list);
        foreach ($hood_list as $key => $hood) {
            if ($hood === $neighborhood_post_data->post_title) {
                unset($hood_list[$key]);
            }
        }
        $hood_list = implode(',', $hood_list);
        $wpdb->update($table_prefix . 'rentpress_properties', array('property_neighborhood_post_names' => $hood_list), array('property_code' => $prop_code));
        $meta_update = update_post_meta(
            $prop_data->property_post_id,
            'rentpress_custom_field_property_neighborhood_post_ids',
            $hood_list,
        );
    }
  }
}

function rentpress_save_neighborhood_meta($post_id, $post)
{
    // Get the post type object
    $post_type = get_post_type_object($post->post_type);

    // Verify the nonce before proceeding and check if the current user has permission to edit the post
    if (isset($_POST['rentpress_custom_field_neighborhood_nonce']) &&
        wp_verify_nonce($_POST['rentpress_custom_field_neighborhood_nonce'], basename(__FILE__)) &&
        current_user_can($post_type->cap->edit_post, $post_id)) {
        
        $codes = '';
        // make string of codes
        foreach ($_POST as $key => $property_code) {
            if (strpos($key, 'rentpress_custom_field_neighborhood_property_code') !== false) {
                if (strlen($codes) > 0) {
                    $codes .= ",";
                }
                $codes .= $property_code;
            }
        }


        $post_meta = get_post_meta($post_id);
        $old_prop_codes = isset($post_meta['rentpress_custom_field_neighborhood_property_codes'][0]) ? $post_meta['rentpress_custom_field_neighborhood_property_codes'][0] : '';
        $has_codes = $codes || $old_prop_codes ? true : false;
        if ($post_meta && $has_codes) {
          $old_code_array = array_filter(explode(',', $old_prop_codes));
          $new_code_array = array_filter(explode(',', $codes));
          $props_removed = array_diff($old_code_array, $new_code_array);
          $props_added = array_diff($new_code_array, $old_code_array);
          // if new properties have been added update the properties neighborhoods
          if (count($props_added)) {
              require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
              foreach ($props_added as $prop_code) {
                $prop_data = rentpress_getAllPropertyDataWithCodeOrPostID($prop_code);
                remove_property_neighborhood($prop_code, $prop_data, $post_id, true);
              }
          }
          // if new properties have been removed update the properties neighborhoods
          if (count($props_removed)) {
            require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
            foreach ($props_removed as $prop_code) {
              $prop_data = rentpress_getAllPropertyDataWithCodeOrPostID($prop_code);
              remove_property_neighborhood($prop_code, $prop_data, $post_id, false);
            }
          }
        }

        // save property information always since it should always be in the $_POST
        update_post_meta(
            $post_id,
            'rentpress_custom_field_neighborhood_property_codes',
            sanitize_text_field($codes)
        );

        update_post_meta(
            $post_id,
            'rentpress_custom_field_neighborhood_romance_copy',
            sanitize_textarea_field($_POST['rentpress_custom_field_neighborhood_romance_copy'])
        );
    }
}
add_action('save_post', 'rentpress_save_neighborhood_meta', 10, 2);

/*
 *  Create meta box to hold all of the viewable meta data fields
 */
function rentpress_add_custom_neighborhood_data_box()
{
    $box_title = 'RentPress - Neighborhood Editor';

    add_meta_box(
        'rentpress_custom_neighborhood_data_box', // Unique ID
        $box_title, // Box title
        'rentpress_custom_neighborhood_data_box_html', // Content callback, must be of type callable
        'rentpress_hood' // Post type
    );
}
add_action('add_meta_boxes', 'rentpress_add_custom_neighborhood_data_box');

function before_delete_neighborhood( $post_id ) 
{ 
    // if this post isnt a rentpress_hood move on
    if ( "rentpress_hood" !== get_post_type($post_id) ) {
      return;
    }
    $post_meta = get_post_meta($post_id);
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $properties = isset($post_meta['rentpress_custom_field_neighborhood_property_codes'][0]) ? $post_meta['rentpress_custom_field_neighborhood_property_codes'][0] : '';
    if (str_contains($properties, ',')) {
        $properties = explode(',',$properties);
    } else {
        $properties = array($properties);
    }
    // remove this neighborhood from its properties meta
    foreach ($properties as $prop_id) {
        $prop_data = rentpress_getAllPropertyDataWithCodeOrPostID($prop_id);
        remove_property_neighborhood($prop_data->property_code, $prop_data, $post_id, false);
    }
}
add_action( 'wp_trash_post', 'before_delete_neighborhood', 99, 2 );
add_action( 'publish_to_draft', 'before_delete_neighborhood', 99, 2 );

/*
 *  Create HTML that will show in the meta box
 */
function rentpress_custom_neighborhood_data_box_html($post)
{
    wp_nonce_field(basename(__FILE__), 'rentpress_custom_field_neighborhood_nonce');
    $rpm = get_post_meta($post->ID); // Get RentPress Neighborhood Meta
    $selected_codes = array();
    if (isset($rpm['rentpress_custom_field_neighborhood_property_codes'])) {
        $selected_codes = explode(',', $rpm['rentpress_custom_field_neighborhood_property_codes'][0]);
    }

    require_once RENTPRESS_PLUGIN_ADMIN_DIR . 'posts/meta/metafields.php';
    ?>
<div class="rentpress-cpt-editor-container">
  <div class="rentpress-tabs">
    <div class="rentpress-tab-button" onclick="openTab(event, 'neighborhood-properties')">
      <span class="fas fa-list" aria-hidden="true"></span>
      Properties
    </div>
    <div class="rentpress-tab-button" onclick="openTab(event, 'neighborhood-info')">
      <span class="fas fa-info-circle" aria-hidden="true"></span>
      Info
    </div>
    <!-- <div class="rentpress-tab-button" onclick="openTab(event, 'neighborhood-location')"><i class="fas fa-map-marker-alt"></i> Location</div> -->
    <div id="rentpress-expand-all">Expand All</div>
  </div>

  <div id="neighborhood-properties" class="rentpress-tab-section">
    <div class="rentpress-accordion"><span class="fas fa-list" aria-hidden="true"></span> Properties</div>
    <div class="rentpress-panel">
      <p class="rentpress-prop-selector-heading">Choose properties to include in this neighborhood.</p>

      <div class="rentpress-settings-group">
        <?php echo wp_kses(rentpress_neighborhood_createPropertyCodeSelector('rentpress_custom_field_neighborhood_property_code', $selected_codes), $rentpress_allowed_HTML); ?>
      </div>
    </div>
  </div>

  <div id="neighborhood-info" class="rentpress-tab-section">
    <div class="rentpress-accordion"><span class="fas fa-info-circle" aria-hidden="true"></span> Info</div>
    <div class="rentpress-panel">
      <p>Add romance copy about this neighborhood. This copy will be used on the neighborhood and property page
        templates.</p>

      <div class="rentpress-settings-group">
        <?php echo wp_kses(rentpress_metaTextArea('rentpress_custom_field_neighborhood_romance_copy', $rpm, '5', 'Enter Romance Copy'), $rentpress_allowed_HTML); ?>
      </div>
    </div>
  </div>

  <!-- <div id="neighborhood-location" class="rentpress-tab-section">
            <div class="rentpress-accordion"><span class="fas fa-map-marker-alt" aria-hidden="true"></span> Location</div>
            <div class="rentpress-panel">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
        </div> -->

</div>

<?php }