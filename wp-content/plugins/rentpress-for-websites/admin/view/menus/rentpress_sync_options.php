<?php
/**
 * Create Admin Menu
 */

function rentpress_sync_options_page()
{
    add_menu_page(
        'RentPress: Sync Settings',
        'RentPress',
        'manage_options',
        'rentpress',
        'rentpress_sync_options_page_html',
        RENTPRESS_PLUGIN_ADMIN_IMAGES_DIR . 'icon.png',
        RENTPRESS_MENU_POSITION
    );
}
add_action('admin_menu', 'rentpress_sync_options_page');

/**
 * Do settings field wrapper
 */
function add_settings_section_with_page($id, $title, $callback, $page, $args)
{
    global $wp_settings_sections;

    $wp_settings_sections[$page][$id] = array(
        'id' => $id,
        'title' => $title,
        'callback' => $callback,
        'args' => $args,
    );
}

function do_settings_fields_with_wrapper($page, $section)
{
    // this function is for adding setting section fields with additional html that helps with making the settings page tabs. this function is based on the wordpress do_settings_fields function and is only used in the do_settings_sections_with_wrapper function
    global $wp_settings_fields;

    if (!isset($wp_settings_fields[$page][$section])) {
        return;
    }

    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
        $class = '';

        if (!empty($field['args']['class'])) {
            $label = '';
            if (!empty($field['args']['label_for'])) {
                $label = $field['args']['label_for'];
            }
            $class = ' id="' . $label . '" class="' . esc_attr($field['args']['class']) . '"';
        }

        echo "<tr ". wp_kses_post($class) .">";

        if (!empty($field['args']['label_for'])) {
            echo '<th scope="row"><label for="' . esc_attr($field['args']['label_for']) . '">' . esc_html($field['title']) . '</label></th>';
        } else {
            echo '<th scope="row">' . esc_html($field['title']) . '</th>';
        }

        echo '<td>';
        call_user_func($field['callback'], $field['args']);
        echo '</td>';
        echo '</tr>';
    }
}

/**
 * Do settings with wrapper
 */
function do_settings_sections_with_wrapper($page)
{
    // this function is for adding setting sections with additional html that helps with making the settings page tabs. this function is based on the wordpress do_settings_sections function
    global $wp_settings_sections, $wp_settings_fields;

    if (!isset($wp_settings_sections[$page])) {
        return;
    }

    foreach ((array) $wp_settings_sections[$page] as $section) {
        $wrapperTitle = esc_attr(strtolower(str_replace(' ', '-', $section['title'])));
        $fieldPage = '';
        if (!empty($section['args']['setting_page']) && isset($section['args']['setting_page'])) {
            $fieldPage = 'data-page="' . esc_attr($section['args']['setting_page']) . '"';
        }
        echo "<div class='rentpress-settings-wrapper' id='rentpress-{$wrapperTitle}-settings-section' {$fieldPage} >";
        if ($section['title']) {
            echo "<h2>" . esc_html($section['title']) . "</h2>\n";
        }

        if ($section['callback']) {
            call_user_func($section['callback'], $section);
        }

        echo '<table class="form-table">';
        do_settings_fields_with_wrapper($page, $section['id']);
        echo '</table>';
        echo "</div>";

        if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) {
            continue;
        }
    }
}

/**
 * Create page view
 */
function rentpress_sync_options_page_html()
{

    // check user capabilities
    if (!current_user_can('manage_options')) {
        exit;
    }

    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if (isset($_GET['settings-updated'])) {
        // add settings saved message with the class of "updated"
        add_settings_error('rentpress_messages', 'rentpress_message', __('Settings Saved', 'rentpress'), 'updated');
    }

    // show error/update messages
    settings_errors('rentpress_messages');

    global $wpdb;
    // get the last sync date
    $table_name = $wpdb->prefix . 'rentpress_refresh';
    $lastSync = $wpdb->get_row("SELECT * FROM $table_name");
    $timezone_string = get_option('timezone_string');
    $timezone = '';
    if ($timezone_string && !is_null($timezone_string)) {
        $timezone = new DateTimeZone( $timezone_string);
    }
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    $timeFormat = isset($date_format) && isset($time_format) ? $date_format . ' ' . $time_format : 'm/d/Y H:i:s';
    $lastSyncStr = 'no sync data';
    if (isset($lastSync->last_refresh_time)) {
        if ($timezone) {
            $lastSyncStr = new DateTime();
            $lastSyncStr = $lastSyncStr->setTimeStamp($lastSync->last_refresh_time)->setTimeZone($timezone)->format($timeFormat);
        } else {
            $lastSyncStr = '';
        }
    }
    ?>
    <div id="rentpress-resync-loading-image" class="rentpress-pop-up-background" style="display: none;">
        <div></div>
        <img src="<?php echo RENTPRESS_PLUGIN_ADMIN_IMAGES_DIR . '30l-square.png'; ?>" style="position: absolute;" class="rentpress-animate">
    </div>
    <section class="rentpress-settings-main">
        <div class="rentpress-settings-header">
            <img class="rentpress-settings-img" src="<?php echo RENTPRESS_PLUGIN_ADMIN_IMAGES_DIR . '30l-square.png'; ?>">
            <h1 class="rentpress-settings-title">RentPress</h1>
        </div>
        <div class="rentpress-settings-controls">
            <aside class="rentpress-settings-menu-sticky">
                <div class="rentpress-tabs-menu">
                    <ul class="rentpress-tabs-menu-list" id="rentpress-tabs-menu-list">
                    </ul>
                    <div  class="resync-options-container">
                        <input onclick="submitForm()" type="submit" name="submit" id="submit" class="button button-primary rentpress-options-submit" value="Save Settings">
                    </div>
                    <?php if ($lastSyncStr): ?>
                        <h3>Last Sync: <?php echo $lastSyncStr; ?></h3>
                    <?php endif;?>
                </div>
            </aside>
            <div class="rentpress-admin-tabs-container">

                <div class="rentpress-admin-tab is-active-rentpress-admin-tab" id="rentpress_settings">
                  <h1><?php esc_html(get_admin_page_title());?></h1>
                    <h3 style="color: red"><div id="results_for_sync"></div></h3>

                    <form action="options.php" method="post" id="main-rentpress-settings">
                        <?php
// output security fields for the registered setting "rentpress_options"
    settings_fields('rentpress_settings');
    // output setting sections and their fields
    // (sections are registered for "rentpress", each field is registered to a specific section)
    do_settings_sections_with_wrapper('rentpress_settings');
    ?>
                        <div class="rentpress-settings-wrapper" id="rentpress-marketing-resync-section" data-page="Data Sync" style="display: none;">
                            <input id="rentpress-marketing-resync-button" class="rentpress-resync-button rentpress-settings-dark-btn" type="button" value="Sync Properties">
                            <!-- <input id="rentpress-pricing-resync-button"  class="rentpress-resync-button rentpress-settings-dark-btn" type="button" value="Resync Pricing"> -->
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
<?php
}

/**
 * Custom option and settings
 */
function rentpress_settings_init()
{
    // register a new setting for "rentpress_settings" page
    register_setting('rentpress_settings', 'rentpress_options');

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_resync_submit_section',
        '',
        'rentpress_sync_options_page_html',
        'rentpress_resync',
        [
            'setting_page' => 'Data Sync',
        ]
    );

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_api_credentials_section',
        'API Credentials',
        'rentpress_api_credentials_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Data Sync',
        ]
    );

    //register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_accent_color_section',
        'Accent Color',
        'rentpress_accent_color_section_field_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Appearance',
        ]
    );

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_post_templates_section',
        'RentPress Templates',
        'rentpress_post_templates_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Enable Templates',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_property_archive_section_input',
        'Property Search',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_property_archive_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_property_single_section_input',
        'Property Listing',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_property_single_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_floorplan_archive_section_input',
        'Floor Plan Grid',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_floorplan_archive_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_floorplan_single_section_input',
        'Floor Plan Single',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_floorplan_single_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_amenity_single_section_input',
        'Amenity Page',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_amenity_single_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_feature_single_section_input',
        'Feature Page',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_feature_single_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_pet_single_section_input',
        'Pet Page',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_pet_single_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_property_type_single_section_input',
        'Property Type Page',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_property_type_single_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_city_single_section_input',
        'City Page',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_city_single_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_city_archive_section_input',
        'Cities Archive',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_city_archive_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_post_templates_property_taxonomy_single_section_input',
        'Property Taxonomies',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_post_templates_section',
        [
            'label_for' => 'rentpress_post_templates_property_taxonomy_single_section',
            'class' => 'rentpress_row',
        ]
    );

    add_settings_field(
        'rentpress_accent_color_section_input', // what the field value will be saved as
        'Primary Accent Color', // Title
        'rentpress_accent_color_section_cb', // function to generate html
        'rentpress_settings', // settings page that it is added to
        'rentpress_accent_color_section', // what section the field will be added to
        [
            'label_for' => 'rentpress_accent_color_section_input',
            'class' => 'rentpress_row',
        ]// any data that will be used in the callback function
    );

    add_settings_field(
        'rentpress_secondary_accent_color_section_input',
        'Secondary Color',
        'rentpress_accent_color_section_cb',
        'rentpress_settings',
        'rentpress_accent_color_section',
        [
            'label_for' => 'rentpress_secondary_accent_color_section_input',
            'class' => 'rentpress_row',
        ]
    );

    // register a new field in the "rentpress_api_credentials_section" section, inside the "rentpress_settings" page
    add_settings_field(
        'rentpress_api_credentials_section_api_token',
        'License Key',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_api_credentials_section',
        [
            'label_for' => 'rentpress_api_credentials_section_api_token',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '',
            ],
        ]
    );

    add_settings_field(
        'rentpress_api_credentials_section_username',
        'Company Username',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_api_credentials_section',
        [
            'label_for' => 'rentpress_api_credentials_section_username',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '',
            ],
        ]
    );

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_unit_availability_section',
        'Unit Availability',
        'rentpress_unit_availability_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Feed Configuration',
        ]
    );

    add_settings_field(
        'rentpress_unit_availability_section_price_range_selector',
        'Price Ranges',
        'rentpress_createSettingsSelectorField_cb',
        'rentpress_settings',
        'rentpress_unit_availability_section',
        [
            'label_for' => 'rentpress_unit_availability_section_price_range_selector',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => 'Select an option that will determine how prices are saved.',
                'options' => [
                    'Use Floor Plan Price If No Units Are Available',
                    'Use Floor Plan Price Only',
                    'Use Available Unit Price Only',
                ],
            ],
        ]
    );

    add_settings_field(
        'rentpress_unit_availability_section_lookahead',
        'Lookahead',
        'rentpress_createSettingsSelectorField_cb',
        'rentpress_settings',
        'rentpress_unit_availability_section',
        [
            'label_for' => 'rentpress_unit_availability_section_lookahead',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => 'Select an option that will decide how far forward to look for available units.',
                'options' => [
                    '365 Days',
                    '90 Days',
                    '75 Days',
                    '60 Days',
                    '45 Days',
                    '30 Days',
                ],
            ],
        ]
    );

    add_settings_field(
        'rentpress_unit_availability_section_limit_unit_count',
        'Limit Number of Units saved to Floor Plans',
        'rentpress_createSettingsNumberField_cb',
        'rentpress_settings',
        'rentpress_unit_availability_section',
        [
            'label_for' => 'rentpress_unit_availability_section_limit_unit_count',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => 'Enter a value greater than 1. Leave field empty to import all units from feed.',
                'min' => 1,
            ],
        ]
    );

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_floorplan_display_settings_section',
        'Floor Plan Display Settings',
        'rentpress_floorplan_display_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Appearance',
        ]
    );

    add_settings_field(
        'rentpress_disable_floorplan_availability_counter',
        'Availability Counter',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_floorplan_display_settings_section',
        [
            'label_for' => 'rentpress_disable_floorplan_availability_counter',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => "Hide unit counter on floorplans",
            ],
        ]
    );

    add_settings_field(
        'rentpress_hide_floorplans_with_no_availability',
        'Visibilty by Available',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_floorplan_display_settings_section',
        [
            'label_for' => 'rentpress_hide_floorplans_with_no_availability',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => "Hide floor plans with no available units",
            ],
        ]
    );

    add_settings_field(
        'rentpress_floorplan_title_display_section',
        'Floor Plan Title Type',
        'rentpress_createSettingsSelectorField_cb',
        'rentpress_settings',
        'rentpress_floorplan_display_settings_section',
        [
            'label_for' => 'rentpress_floorplan_title_display_section',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '<br />Floor plans can display with a marketing name or a consistent Bed | Bath type label.',
                'options' => [
                    'Marketing Name',
                    'bed | bath',
                ],
            ],
        ]
    );

    add_settings_field(
        'rentpress_apply_link_section',
        'Apply Link Target',
        'rentpress_createSettingsSelectorField_cb',
        'rentpress_settings',
        'rentpress_floorplan_display_settings_section',
        [
            'label_for' => 'rentpress_apply_link_section',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '<br />Select an option that will decide how the apply link opens.',
                'options' => [
                    'New Window',
                    'Same Window',
                ],
            ],
        ]
    );

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_pricing_display_settings_section',
        'Pricing Display Settings',
        'rentpress_default_pricing_display_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Appearance',
        ]
    );

    add_settings_field(
        'rentpress-pricing-display-settings-section_price_display_selection',
        'Default Rent Price Display Type',
        'rentpress_createSettingsRadioField_cb',
        'rentpress_settings',
        'rentpress_pricing_display_settings_section',
        [
            'label_for' => 'rentpress_pricing_display_settings_section_price_display_selection',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'options' => [
                    'Base Rent',
                    'Market Rent',
                    'Effective Rent',
                    'Term Rent',
                    'Best Price',
                    'Minimum - Maximum',
                ],
            ],
            'default' => 'Best Price',
        ]
    );

    // add_settings_field(
    //     'rentpress-pricing-display-settings-section',
    //     'Default Lease Term',
    //     'rentpress_createSettingsNumberField_cb',
    //     'rentpress_settings',
    //     'rentpress-pricing-display-settings-section',
    //     [
    //         'label_for' => 'rentpress-pricing-display-settings-section',
    //         'class' => 'rentpress_row',
    //         'rentpress_custom_data' => [
    //             'display'  => 'none',
    //             'min' => 1,
    //             'max' => 24,
    //             'placeholder_text' => 'Term Rent in Months'
    //         ],
    //     ]
    // );

    // register a new field in the "rentpress_unit_rent_type_section" section, inside the "rentpress_settings" page

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_disable_pricing_section',
        'Disable Pricing',
        'rentpress_disable_pricing_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Appearance',
        ]
    );

    // register a new field in the "rentpress_disable_pricing_section" section, inside the "rentpress_settings" page
    add_settings_field(
        'rentpress_disable_pricing_section_disable_pricing',
        'Disable Pricing',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_disable_pricing_section',
        [
            'label_for' => 'rentpress_disable_pricing_section_disable_pricing',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => "Disable All Pricing",
            ],
        ]
    );

    add_settings_field(
        'rentpress_disable_pricing_section_disable_pricing_message',
        'Price Disabled Message',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_disable_pricing_section',
        [
            'label_for' => 'rentpress_disable_pricing_section_disable_pricing_message',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'Default' => 'Call For Pricing',
                'placeholder_text' => 'ex: Call for Pricing',
            ],
        ]
    );

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_disable_lease_term_pricing_section',
        'Disable Lease Term Pricing',
        'rentpress_disable_lease_term_pricing_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Appearance',
        ]
    );

    // register a new field in the "rentpress_disable_lease_term_pricing_section" section, inside the "rentpress_settings" page
    add_settings_field(
        'rentpress_disable_lease_term_pricing_section_disable_specific_id',
        'Disable Lease Term Pricing by ID',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_disable_lease_term_pricing_section',
        [
            'label_for' => 'rentpress_disable_lease_term_pricing_section_disable_specific_id',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => "<br />*You may enter comma-separated ID's for properties, floor plans, and units.",
            ],
        ]
    );

    add_settings_field(
        'rentpress_disable_lease_term_pricing_section_disable_lease_term_pricing',
        'Disable Lease Term Pricing',
        'rentpress_createSettingsCheckboxField_cb',
        'rentpress_settings',
        'rentpress_disable_lease_term_pricing_section',
        [
            'label_for' => 'rentpress_disable_lease_term_pricing_section_disable_lease_term_pricing',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => "Disable Lease Term Pricing",
            ],
        ]
    );

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_format_phone_numbers_section',
        'Format Phone Numbers',
        'rentpress_format_phone_numbers_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Appearance',
        ]
    );

    // register a new field in the "rentpress_format_phone_numbers_section" section, inside the "rentpress_settings" page
    add_settings_field(
        'rentpress_format_phone_numbers_section_formatted_number',
        'Phone Format',
        'rentpress_createSettingsSelectorField_cb',
        'rentpress_settings',
        'rentpress_format_phone_numbers_section',
        [
            'label_for' => 'rentpress_format_phone_numbers_section_formatted_number',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '',
                'options' => [
                    '(xxx) xxx-xxxx',
                    '(xxx) xxx.xxxx',
                    '(xxx) xxx xxxx',
                    'xxx xxx xxxx',
                    'xxx.xxx.xxxx',
                    'xxx-xxx-xxxx',
                    'xxxxxxxxxx',
                ],
            ],
        ]
    );

    // google maps api key
    add_settings_section_with_page(
        'rentpress_google_maps_api_section',
        'Google Maps API',
        'rentpress_google_maps_api_section_field_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Integrations',
        ]
    );

    add_settings_field(
        'rentpress_google_maps_api_section_api_key',
        'Google API Key',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_google_maps_api_section',
        [
            'label_for' => 'rentpress_google_maps_api_section_api_key',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'placeholder_text' => 'ex: AIzmSyBB2ND1X4K-LBkWS18uF2oKKinMINxFzWA',
            ],
        ]
    );

    // google analytics id
    add_settings_section_with_page(
        'rentpress_google_analytics_api_section',
        'Google Analytics',
        'rentpress_google_analytics_api_section_field_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Integrations',
        ]
    );

    add_settings_field(
        'rentpress_google_analytics_api_section_tracking_id',
        'Google Analytics ID',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_google_analytics_api_section',
        [
            'label_for' => 'rentpress_google_analytics_api_section_tracking_id',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'placeholder_text' => 'ex: UA-130303030-1',
            ],
        ]
    );

    // Map cluster image upload
    add_settings_section_with_page(
        'rentpress_cluster_image_section',
        'Placeholder Images',
        'rentpress_cluster_image_section_field_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Appearance',
        ]
    );

    add_settings_field(
        'rentpress_default_property_image_section',
        'Default Property Image',
        'rentpress_image_upload_section_cb',
        'rentpress_settings',
        'rentpress_cluster_image_section',
        [
            'label_for' => 'rentpress_default_property_image_section',
            'class' => 'rentpress_row',
            'default' => RENTPRESS_PLUGIN_ADMIN_IMAGES_DIR . 'default_property_image.jpg',
        ]
    );

    add_settings_field(
        'rentpress_default_floor_plan_image_section',
        'Default Floor Plan Image',
        'rentpress_image_upload_section_cb',
        'rentpress_settings',
        'rentpress_cluster_image_section',
        [
            'label_for' => 'rentpress_default_floor_plan_image_section',
            'class' => 'rentpress_row',
            'default' => RENTPRESS_PLUGIN_ADMIN_IMAGES_DIR . 'default_floor_plan_image.png',
        ]
    );

    add_settings_field(
        'rentpress_default_city_image_section',
        'Default City Image',
        'rentpress_image_upload_section_cb',
        'rentpress_settings',
        'rentpress_cluster_image_section',
        [
            'label_for' => 'rentpress_default_city_image_section',
            'class' => 'rentpress_row',
            'default' => RENTPRESS_PLUGIN_ADMIN_IMAGES_DIR . 'default_city_image.jpg',
        ]
    );

    add_settings_field(
        'rentpress_cluster_image_section_input',
        'Custom Map Pin Cluster Marker',
        'rentpress_image_upload_section_cb',
        'rentpress_settings',
        'rentpress_cluster_image_section',
        [
            'label_for' => 'rentpress_cluster_image_section_input',
            'class' => 'rentpress_row',
        ]
    );

    // register a new section in the "rentpress_settings" page
    add_settings_section_with_page(
        'rentpress_application_link_section',
        'Default URLs',
        'rentpress_application_link_section_cb',
        'rentpress_settings',
        [
            'setting_page' => 'Appearance',
        ]
    );

    // register a new field in the "rentpress_application_link_section" section, inside the "rentpress_settings" page
    add_settings_field(
        'rentpress_application_link_section_default_application_url',
        'Application URL',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_application_link_section',
        [
            'label_for' => 'rentpress_application_link_section_default_application_url',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '',
                'placeholder_text' => '',
                'text_field_type' => 'url',
            ],
        ]
    );

    // register a new field in the "rentpress_application_link_section" section, inside the "rentpress_settings" page
    add_settings_field(
        'rentpress_application_link_section_url_contact',
        'Contact Page URL',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_application_link_section',
        [
            'label_for' => 'rentpress_application_link_section_url_contact',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '',
                'placeholder_text' => get_site_url() . '/contact/',
                'default' => get_site_url() . '/contact/',
                'text_field_type' => 'url',
            ],
        ]
    );

    // register a new field in the "rentpress_application_link_section" section, inside the "rentpress_settings" page
    add_settings_field(
        'rentpress_application_link_section_url_tour',
        'Tour Page URL',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_application_link_section',
        [
            'label_for' => 'rentpress_application_link_section_url_tour',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '',
                'placeholder_text' => '',
                'text_field_type' => 'url',
            ],
        ]
    );

    add_settings_field(
        'rentpress_application_link_section_url_waitlist',
        'Waitlist Page URL',
        'rentpress_createSettingsTextField_cb',
        'rentpress_settings',
        'rentpress_application_link_section',
        [
            'label_for' => 'rentpress_application_link_section_url_waitlist',
            'class' => 'rentpress_row',
            'rentpress_custom_data' => [
                'label_text' => '',
                'placeholder_text' => '',
                'text_field_type' => 'url',
            ],
        ]
    );

}
add_action('admin_init', 'rentpress_settings_init');

/**
 * Custom option and settings:
 * Callback functions
 * These are used for formating and displaying the options
 */

function rentpress_resync_submit_section_cb($args)
{
    ?>
        <p>Connected to Top Line Connect. You can resync pricing and availability information whenever you like.</p>
            <input id="rentpress-marketing-resync-button" class="rentpress-resync-button rentpress-settings-dark-btn" type="button" value="Resync Properties">
            <input id="rentpress-pricing-resync-button" class="rentpress-resync-button rentpress-settings-dark-btn" type="button" value="Resync Pricing">
    <?php
// this form will fire off this hook when submitted
    add_action('admin_post_rentpress_resync_properties', 'rentpress_refresh_all_data_for_properties');
}

function rentpress_api_credentials_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Fill out these credentials to connect RentPress to your property data. Contact 30 Lines to get your license key for this website.')?>
        <br /><br />
        <?php esc_html_e('Once connected, your website will automatically resync every hour.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_unit_availability_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Use these options for pricing calculation. If no units are available, it will fall-back to default pricing ranges that come through the feed.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_unit_rent_type_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Choose which type of rent to show on your website.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_default_pricing_display_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Choose which type of rent to show on your website.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_floorplan_display_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Choose how floor plans display on your website.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_disable_pricing_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('You can opt to disable displaying pricing on the website.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_disable_lease_term_pricing_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Provide a comma-separated list of unit codes that you want to omit from using pricing matrices from property and floor plan rent calculations.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_post_templates_section_cb( $args ) {
    $options = get_option('rentpress_options');
    $fields = [
        isset($options['rentpress_post_templates_property_archive_section']) ? intval($options['rentpress_post_templates_property_archive_section']) : '',
        isset($options['rentpress_post_templates_floorplan_archive_section']) ? intval($options['rentpress_post_templates_floorplan_archive_section']) : '',
        isset($options['rentpress_post_templates_property_single_section']) ? intval($options['rentpress_post_templates_property_single_section']) : '',
        isset($options['rentpress_post_templates_floorplan_single_section']) ? intval($options['rentpress_post_templates_floorplan_single_section']) : '',
        isset($options['rentpress_post_templates_city_single_section']) ? intval($options['rentpress_post_templates_city_single_section']) : '',
        isset($options['rentpress_post_templates_city_archive_section']) ? intval($options['rentpress_post_templates_city_archive_section']) : '',
        isset($options['rentpress_post_templates_amenity_single_section']) ? intval($options['rentpress_post_templates_amenity_single_section']) : '',
        isset($options['rentpress_post_templates_property_taxonomy_single_section']) ? intval($options['rentpress_post_templates_property_taxonomy_single_section']) : '',
        isset($options['rentpress_post_templates_amenity_single_section']) ? intval($options['rentpress_post_templates_amenity_single_section']) : '',
        isset($options['rentpress_post_templates_property_type_single_section']) ? intval($options['rentpress_post_templates_property_type_single_section']) : '',
        isset($options['rentpress_post_templates_feature_single_section']) ? intval($options['rentpress_post_templates_feature_single_section']) : '',
        isset($options['rentpress_post_templates_pet_single_section']) ? intval($options['rentpress_post_templates_pet_single_section']) : '',
    ];
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Choose post types you want to use RentPress templates on, then click Save Changes.', 'rentpress_settings');?></p>
        <?php if (in_array(1, $fields)): ?>
            <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Click Add Theme Files to add your chosen templates to your active theme.', 'rentpress_settings');?></p>
            <input type="button" value="Add Theme Files" id="rentpress-theme-template-create" class="rentpress-settings-dark-btn">
            <br />
        <?php endif;?>
    <?php
}

function rentpress_format_phone_numbers_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Select a default format for phone numbers.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_application_link_section_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Use these fields to set default link targets for CTA\'s. Empty fields will not be used.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_accent_color_section_field_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Use these colors to customize templates to your brand. Most buttons and links use the Primary Accent Color.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_google_maps_api_section_field_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title">
            Enter your Google API key to use in RentPress templates. You can find the API key from your <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/">Google Map Developer console</a>. <br /><br />

            This API key will be used to retrieve reviews when using the Reviews Add-On, and can be used to display maps on this site. More information can be found at: <a target="blank" rel="noopener noreferrer" href="https://via.30lines.com/X7TllTnK">Understanding RentPress + Google Maps integration</a>.
        </p>
    <?php
}

function rentpress_google_analytics_api_section_field_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title">When connected, shopper clicks and actions on RentPress templates will be automatically reported into your Google Analytics account. To find your tracking ID, go to your Google Analytics account and click on Admin in the sidebar. <br /><br />

More information can be found at: <a target="blank" rel="noopener noreferrer" href="https://via.30lines.com/WIoDyQiF">Understanding RentPress + Google Analytics integration</a>.</p>
    <?php
}

function rentpress_cluster_image_section_field_cb($args)
{
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>" class="rentpress-settings-sub-title"><?php esc_html_e('Upload placeholder images for use in RentPress shortcodes and templates.', 'rentpress_settings');?></p>
    <?php
}

function rentpress_createSettingsTextField_cb($args)
{
    // get the value of the setting we've registered with register_setting()
    $options = get_option('rentpress_options');
    $defaultValue = isset($args['rentpress_custom_data']['default']) ? esc_attr($args['rentpress_custom_data']['default']) : '';
    $needsValue = $defaultValue ? $options[$args['label_for']] : true;

    // output the field
    ?>
        <input
            type="<?php echo isset($args['rentpress_custom_data']['text_field_type']) ? $args['rentpress_custom_data']['text_field_type'] : 'text' ?>"
            id="<?php echo esc_attr($args['label_for']); ?>"
            class="<?php echo esc_attr($args['class']); ?> rentpress-text-field rentpress-settings-input"
            data-custom=""
            placeholder="<?php echo isset($args['rentpress_custom_data']['placeholder_text']) ? esc_attr($args['rentpress_custom_data']['placeholder_text']) : '' ?>"
            name="rentpress_options[<?php echo esc_attr($args['label_for']); ?>]"
            value="<?php echo isset($options[$args['label_for']]) && $needsValue ? esc_attr($options[$args['label_for']]) : $defaultValue ?>">
        <label for="<?php echo esc_attr($args['label_for']); ?>" class="rentpress-text-field-label rentpress-settings-label">
            <?php echo isset($args['rentpress_custom_data']['label_text']) ? esc_html($args['rentpress_custom_data']['label_text']) : '' ?></label>
    <?php
}

function rentpress_createSettingsNumberField_cb($args)
{
    // get the value of the setting we've registered with register_setting()
    $options = get_option('rentpress_options');
    // output the field
    ?>
        <input
            type="number"
            id="<?php echo esc_attr($args['label_for']); ?>"
            class="<?php echo esc_attr($args['class']); ?> rentpress-text-field rentpress-settings-input"
            data-custom=""
            max="<?php echo esc_attr($args['rentpress_custom_data']['max']) ?? '' ?>"
            min="<?php echo esc_attr($args['rentpress_custom_data']['min']) ?? '' ?>"
            placeholder="<?php echo isset($args['rentpress_custom_data']['placeholder_text']) ? esc_attr($args['rentpress_custom_data']['placeholder_text']) : '' ?>"
            name="rentpress_options[<?php echo esc_attr($args['label_for']); ?>]"
            value="<?php echo isset($options[$args['label_for']]) ? esc_attr($options[$args['label_for']]) : '' ?>">
        <label for="<?php echo esc_attr($args['label_for']); ?>" class="rentpress-text-field-label rentpress-settings-label">
            <?php echo isset($args['rentpress_custom_data']['label_text']) ? esc_html($args['rentpress_custom_data']['label_text']) : '' ?></label>
    <?php
}

function rentpress_createSettingsCheckboxField_cb($args)
{
    // get the value of the setting we've registered with register_setting()
    $options = get_option('rentpress_options');
    // output the field
    ?>
        <input
            type="checkbox"
            id="<?php echo esc_attr($args['label_for']); ?>"
            class="<?php echo esc_attr($args['class']); ?> rentpress-checkbox-field rentpress-settings-input"
            data-custom=""
            name="rentpress_options[<?php echo esc_attr($args['label_for']); ?>]"
            value="1"
            <?php echo isset($options[$args['label_for']]) ? (($options[$args['label_for']] == '1') ? ' checked' : '') : '' ?>>
        <label for="<?php echo esc_attr($args['label_for']); ?>" class="rentpress-checkbox-field-label rentpress-settings-label">
            <?php echo isset($args['rentpress_custom_data']['label_text']) ? esc_html($args['rentpress_custom_data']['label_text']) : '' ?></label>

    <?php
}

function rentpress_createSettingsSelectorField_cb($args)
{
    $options = get_option('rentpress_options');
    ?>
        <select
            id="<?php echo esc_attr($args['label_for']); ?>"
            class="<?php echo esc_attr($args['class']); ?> rentpress-selector-field rentpress-settings-input"
            data-custom=""
            name="rentpress_options[<?php echo esc_attr($args['label_for']); ?>]" >
            <?php if (isset($args['rentpress_custom_data']['options'])):
        foreach ($args['rentpress_custom_data']['options'] as $opt): ?>
								                    <option value="<?php echo esc_attr($opt) ?>" class="rentpress-selector-field-option"
								                        <?php echo isset($options[$args['label_for']]) ? (selected($options[$args['label_for']], $opt, false)) : (''); ?>>
								                        <?php esc_html_e($opt, 'rentpress_settings');?>
								                    </option>
								                    <?php
endforeach;
    endif;?>
        </select>
        <label for="<?php echo esc_attr($args['label_for']); ?>" class="rentpress-selector-field-label rentpress-settings-label">
            <?php echo isset($args['rentpress_custom_data']['label_text']) ? esc_html($args['rentpress_custom_data']['label_text']) : '' ?></label>
    <?php
}

function rentpress_createSettingsRadioField_cb($args)
{
    $options = get_option('rentpress_options');
    if (isset($args['rentpress_custom_data']['options'])):
        foreach ($args['rentpress_custom_data']['options'] as $index => $opt): ?>
            <div class="rentpress-radio-field-wrapper">
                <input
                    type="radio"
                    id="<?php echo esc_attr($args['label_for']) . esc_attr($index); ?>"
                    class="rentpress-radio-field rentpress-settings-input"
                    name="rentpress_options[<?php echo esc_attr($args['label_for']); ?>]"
                    data-custom=""
                    value="<?php echo esc_attr($opt) ?>"
                    <?php
    // if the value has not been set, then set the default value if it exists
        if (!isset($options[$args['label_for']])) {
            echo isset($args['default']) && $args['default'] == $opt ? ' checked' : '';
        } else {
            echo $options[$args['label_for']] == $opt ? ' checked' : '';
        }
        ?>>
								                <label for="<?php echo esc_attr($args['label_for']) . esc_attr($index); ?>" class="rentpress-radio-field-label rentpress-settings-label"><?php echo esc_html($opt); ?></label><br>
								            </div>
								            <?php
endforeach;
    endif;
}

function rentpress_accent_color_section_cb($args)
{
    $options = get_option('rentpress_options');
    // output the field
    ?>
        <input
            type="color"
            id="<?php echo esc_attr($args['label_for']); ?>"
            class="<?php echo esc_attr($args['label_for']); ?> rentpress-color-field rentpress-settings-input"
            data-custom=""
            name="rentpress_options[<?php echo esc_attr($args['label_for']); ?>]"
            value="<?php echo esc_attr($options[$args['label_for']]); ?>"
            <?php echo isset($options[$args['label_for']]) ? (($options[$args['label_for']] == '1') ? ' checked' : '') : '' ?>>
        <label for="<?php echo esc_attr($args['label_for']); ?>" class="rentpress-checkbox-field-label rentpress-settings-label">
            <?php echo isset($args['rentpress_custom_data']['title']) ? esc_html($args['label_for']) : '' ?></label>

    <?php
}

function rentpress_cluster_image_section_cb($args)
{
    $options = get_option('rentpress_options');
    // output the field
    ?>
        <input id="rentpress-cluster-image-upload" value="<?php echo esc_attr($options[$args['label_for']]) ?? ''; ?>" type="text" name="rentpress_options[<?php echo esc_attr($args['label_for']); ?>]">
        <button class="button wpse-228085-upload">Upload</button>
        <label for="<?php echo esc_attr($args['label_for']); ?>" class="rentpress-checkbox-field-label rentpress-settings-label">
            <?php echo isset($args['rentpress_custom_data']['title']) ? esc_html($args['label_for']) : '' ?></label>
            <?php if (isset($options[$args['label_for']]) && $options[$args['label_for']] != ''): ?>
                <div>
                    <img id="rentpress-custom-cluster-pin-image" class="rentpress-custom-cluster-pin-image" src="<?php echo esc_attr($options[$args['label_for']]) ?? ''; ?>">
                </div>
            <?php endif;?>
    <?php
}

function rentpress_image_upload_section_cb($args)
{
    $options = get_option('rentpress_options');
    $defaultValue = isset($args['default']) ? $args['default'] : '';
    $needsValue = $defaultValue ? $options[$args['label_for']] : true;
    // output the field
    ?>
        <input id="<?php echo esc_attr($args['label_for']); ?>-field" value="<?php echo isset($options[$args['label_for']]) && $needsValue ? esc_attr($options[$args['label_for']]) : esc_attr($defaultValue) ?>" type="text" name="rentpress_options[<?php echo esc_attr($args['label_for']); ?>]">
        <span class="button rentpress-image-uploader-field <?php echo esc_attr($args['label_for']); ?>" data-target="<?php echo esc_attr($args['label_for']); ?>" data-limit='true'>Upload</span>
        <label for="<?php echo esc_attr($args['label_for']); ?>" class="rentpress-checkbox-field-label rentpress-settings-label">
            <?php echo isset($args['rentpress_custom_data']['title']) ? esc_html($args['label_for']) : '' ?></label>
            <?php if (isset($options[$args['label_for']]) && $options[$args['label_for']] != ''): ?>
                <div id="<?php echo esc_attr($args['label_for']); ?>-upload-preview-container" class="<?php echo esc_attr($args['label_for']); ?>-image-wrapper">
                    <img id="<?php echo esc_attr($args['label_for']); ?>-image" class="<?php echo esc_attr($args['label_for']); ?>-image rentpress-image-upload-preview" src="<?php echo esc_attr($options[$args['label_for']]) ?? ''; ?>">
                </div>
            <?php else: ?>
                <div style="display: none;" id="<?php echo esc_attr($args['label_for']); ?>-image-wrapper" class="<?php echo esc_attr($args['label_for']); ?>-image-wrapper">
                    <img id="<?php echo esc_attr($args['label_for']); ?>-image" class="<?php echo esc_attr($args['label_for']); ?>-image rentpress-image-upload-preview">
                </div>
            <?php endif;
}
/**
 * Resync Hooks
 */

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_media();
});

add_action('wp_ajax_rentpress_getAllMarketingDataForProperties', 'rentpress_getAllMarketingDataForProperties');
add_action('wp_ajax_rentpress_getAllPricingDataForProperties', 'rentpress_getAllPricingDataForProperties');
add_action('wp_ajax_rentpress_createThemeTemplateFile', 'rentpress_createThemeTemplateFile');

function rentpress_getAllMarketingDataForProperties()
{
    require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/marketing_refresh.php';
    rentpress_syncFeedAndWPProperties();
    wp_die(); // this is required to terminate immediately and return a proper response
}

function rentpress_getAllPricingDataForProperties()
{
    require_once RENTPRESS_PLUGIN_DATA_SYNC . 'property/pricing_refresh.php';
    rentpress_syncFeedAndWPProperties();
    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_media();
});

function rentpress_createThemeTemplateFile()
{
    $options = get_option('rentpress_options');
    $templates = [];
    $usePropTaxTemplate = isset($options['rentpress_post_templates_property_taxonomy_single_section']) ? intval($options['rentpress_post_templates_property_taxonomy_single_section']) : '';
    if ($usePropTaxTemplate) {
        $templates[] = 'taxonomy.php';
    }
    if (isset($options['rentpress_post_templates_property_archive_section']) ? intval($options['rentpress_post_templates_property_archive_section']) : '') {
        $templates[] = 'archive-rentpress_property.php';
    }
    if (isset($options['rentpress_post_templates_floorplan_archive_section']) ? intval($options['rentpress_post_templates_floorplan_archive_section']) : '') {
        $templates[] = 'archive-rentpress_floorplan.php';
    }
    if (isset($options['rentpress_post_templates_property_single_section']) ? intval($options['rentpress_post_templates_property_single_section']) : '') {
        $templates[] = 'single-rentpress_property.php';
    }
    if (isset($options['rentpress_post_templates_floorplan_single_section']) ? intval($options['rentpress_post_templates_floorplan_single_section']) : '') {
        $templates[] = 'single-rentpress_floorplan.php';
    }
    if (isset($options['rentpress_post_templates_city_single_section']) ? intval($options['rentpress_post_templates_city_single_section']) : '' && !$usePropTaxTemplate) {
        $templates[] = 'taxonomy-city.php';
    }
    if (isset($options['rentpress_post_templates_city_archive_section']) ? intval($options['rentpress_post_templates_city_archive_section']) : '') {
        $templates[] = 'template-city-archive.php';
    }
    if (isset($options['rentpress_post_templates_amenity_single_section']) ? intval($options['rentpress_post_templates_amenity_single_section']) : '' && !$usePropTaxTemplate) {
        $templates[] = 'taxonomy-amenity.php';
    }
    if (isset($options['rentpress_post_templates_pet_single_section']) ? intval($options['rentpress_post_templates_pet_single_section']) : '' && !$usePropTaxTemplate) {
        $templates[] = 'taxonomy-pet.php';
    }
    if (isset($options['rentpress_post_templates_feature_single_section']) ? intval($options['rentpress_post_templates_feature_single_section']) : '' && !$usePropTaxTemplate) {
        $templates[] = 'taxonomy-feature.php';
    }
    if (isset($options['rentpress_post_templates_property_type_single_section']) ? intval($options['rentpress_post_templates_property_type_single_section']) : '' && !$usePropTaxTemplate) {
        $templates[] = 'taxonomy-property_type.php';
    }

    foreach ($templates as $key => $template) {
        if ($template) {
            $theme = get_template_directory() . '/' . $template;
            $plugin = RENTPRESS_PLUGIN_DIR . 'public/templates/' . $template;
            
            if ($template == 'taxonomy.php' && file_exists($theme) && !file_exists(get_template_directory() . '/original_taxonomy.php')) {
                $rename = rename($theme, get_template_directory() . '/original_taxonomy.php');
            }
            if(!copy($plugin, $theme)) {
                $errors= error_get_last();
                echo ('error: ' . $errors['message'] . ' ' . $template);
            } else {
                echo ('Success: ' . $template . ' has been created ');
            }
        }
    }
}
?>