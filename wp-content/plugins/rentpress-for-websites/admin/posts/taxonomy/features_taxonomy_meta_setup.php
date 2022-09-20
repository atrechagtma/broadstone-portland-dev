<?php
/*
 *  Save the meta box’s term metadata.
 */

function rentpress_edit_feature_meta($term) {
  require_once(RENTPRESS_PLUGIN_ADMIN_DIR . 'posts/meta/metafields.php');
  $rpm = get_term_meta( $term->term_id );
  $rpd = get_term( $term->term_id, 'feature' );

  // put the term ID into a variable
  $t_id = $term->term_id;

  // retrieve the existing value(s) for this meta field. This returns an array
  $term_meta = get_option( "taxonomy_$t_id" ); ?>

<div class="rentpress-cpt-editor-container">

  <div class="rentpress-tabs">
    <div class="rentpress-tab-button" onclick="openTab(event, 'feature-marketing')"><span class="fas fa-bullhorn"
        aria-hidden="true"></span> Marketing</div>
    <div class="rentpress-tab-button" onclick="openTab(event, 'feature-info')"><span class="fas fa-info-circle"
        aria-hidden="true"></span> Info</div>
    <div id="rentpress-expand-all">Expand All</div>
  </div>

  <!-- tab start -->
  <div id="feature-marketing" class="rentpress-tab-section">

    <!-- tab accordion start -->
    <div class="rentpress-accordion"><span class="fas fa-images" aria-hidden="true"></span> Featured Image</div>
    <div class="rentpress-panel">
      <p class="rentpress-panel-heading">Add an image to represent this feature. For best results, use an image with
        landscape orientation and at least 1000px on the shortest side.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Featured Image</label>
        <?php echo wp_kses(rentpress_metaFieldImage('rentpress_custom_field_feature_image', $rpm, 'text', '', 'true'), $rentpress_allowed_HTML); ?>
      </div>
    </div>
    <!-- tab accordion end -->

    <!-- accordion start -->
    <div class="rentpress-accordion"><span class="fas fa-quote-left" aria-hidden="true"></span> Short Description</div>
    <div class="rentpress-panel">
      <p class="rentpress-panel-heading">Add a short summary description about this feature. This description will also
        be used on property pages. For best results, keep less than 120 characters.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Short Description</label>
        <?php echo wp_kses(rentpress_metaShadowTextArea('rentpress_custom_field_feature_short_description', $rpd, 6, '', 'description'), $rentpress_allowed_HTML); ?>
      </div>
    </div>
    <!-- tab accordion end -->

    <!-- accordion start -->
    <div class="rentpress-accordion"><span class="fas fa-align-left" aria-hidden="true"></span> Extended Content</div>
    <div class="rentpress-panel">
      <p class="rentpress-panel-heading">Add in more content or information about the feature.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Extended Content</label>
        <?php echo wp_kses(rentpress_metaTextArea('rentpress_custom_field_feature_extended_content', $rpm, '25'), $rentpress_allowed_HTML); ?>
      </div>
    </div>
    <!-- tab accordion end -->

    <!-- accordion start -->
    <div class="rentpress-accordion"><span class="fas fa-puzzle-piece" aria-hidden="true"></span> Shortcode</div>
    <div class="rentpress-panel">
      <p class="rentpress-panel-heading">Insert a shortcode from a WordPress plugin or theme. This works great with a
        photo gallery, map, or contact form.</p>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Shortcode</label>
        <?php echo wp_kses(rentpress_metaField('rentpress_custom_field_feature_shortcode', $rpm, 'text'), $rentpress_allowed_HTML); ?>
      </div>
    </div>
    <!-- tab accordion end -->

  </div>
  <!-- tab end -->

  <!-- tab start -->
  <div id="feature-info" class="rentpress-tab-section">

    <!-- tab accordion start -->
    <div class="rentpress-accordion"><span class="fas fa-cogs" aria-hidden="true"></span> Term Info</div>
    <div class="rentpress-panel">
      <p class="rentpress-panel-heading">These fields represent the name and slug for this term. For terms maintained by
        a property data feed, you shouldn't need to edit these.

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Name</label>
        <?php echo wp_kses(rentpress_metaShadowField('rentpress_custom_field_feature_name', $rpd, 'text', '', 'name'), $rentpress_allowed_HTML); ?>
      </div>

      <div class="rentpress-settings-group">
        <label class="rentpress-settings-title">Slug</label>
        <?php echo wp_kses(rentpress_metaShadowField('rentpress_custom_field_feature_slug', $rpd, 'text', '', 'slug'), $rentpress_allowed_HTML); ?>
      </div>
    </div>
    <!-- tab accordion end -->

    <!-- accordion start -->
    <!--  <div class="rentpress-accordion"><span class="fas fa-tags" aria-hidden="true"></span> Properties</div>
            <div class="rentpress-panel">
                <p class="rentpress-panel-heading">If your property is running a special discount, enter that information here (keep less than 120 characters). You can also optionally add a link and an expiration date to automatically remove the special from display.</p>

                <div class="rentpress-settings-group">
                    Properties
                    <?php // echo rentpress_metaField('rentpress_custom_field_feature_special_text', $rpm, 'text'); ?>
                </div>
            </div> -->
    <!-- tab accordion end -->

  </div>
  <!-- tab end -->

</div>

<?php
}
add_action( 'feature_edit_form_fields', 'rentpress_edit_feature_meta', 10, 2 );

function rentpress_save_feature_meta( $term_id ) {
    foreach ($_POST as $key => $value) {
        if ( strpos($key, 'rentpress_custom_field') !== false ) {
            if (!get_term_meta($term_id, $key)) {
                add_term_meta($term_id, $key, '', true);
            }
            if ($key === "rentpress_custom_field_feature_extended_content" && isset($_POST[$key])) {
              update_term_meta( $term_id, $key, wp_kses_post($_POST[$key]) );
              continue ;
            }
            if (isset($_POST[$key])) {
                update_term_meta($term_id, $key, sanitize_text_field($_POST[$key]));
            }
        }
    }
}
add_action( 'edited_feature', 'rentpress_save_feature_meta', 10, 2 );
add_action( 'create_feature', 'rentpress_save_feature_meta', 10, 2 );