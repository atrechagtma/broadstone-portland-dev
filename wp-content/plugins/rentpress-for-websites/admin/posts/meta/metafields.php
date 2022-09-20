<?php
add_filter('safe_style_css', function ($styles) {
    $styles[] = 'display';
    return $styles;
});

$defaultAtts = array(
    'id' => array(),
    'class' => array(),
    'style' => array(
        'display',
    ),
);

$rentpress_allowed_HTML = array(
    'br' => array(),
    'b' => array(),
    'strong' => array(),
    'em' => array(),
    'table' => $defaultAtts,
    'thead' => $defaultAtts,
    'tr' => $defaultAtts,
    'td' => $defaultAtts,
    'th' => $defaultAtts,
    'h1' => $defaultAtts,
    'h2' => $defaultAtts,
    'h3' => $defaultAtts,
    'h4' => $defaultAtts,
    'h5' => $defaultAtts,
    'ul' => $defaultAtts,
    'ol' => $defaultAtts,
    'li' => $defaultAtts,
    'p' => $defaultAtts,
    'input' => array_merge($defaultAtts, array(
        'type' => array(),
        'name' => array(),
        'value' => array(),
        'placeholder' => array(),
        'data' => array(),
        'data-target' => array(),
        'data-limit' => array(),
        'data-start-value' => array(),
        'data-base-field' => array(),
        'checked' => array(),
        'disabled' => array(),
        'onchange' => array(),
    )),
    'textarea' => array_merge($defaultAtts, array(
        'rows' => array(),
        'name' => array(),
        'value' => array(),
        'data' => array(),
        'data-target' => array(),
        'data-limit' => array(),
        'data-start-value' => array(),
        'data-base-field' => array(),
        'disabled' => array(),
    )),
    'select' => array_merge($defaultAtts, array(
        'name' => array(),
        'disabled' => array(),
    )),
    'option' => array_merge($defaultAtts, array(
        'value' => array(),
        'selected' => array(),
    )),
    'label' => array_merge($defaultAtts, array(
        'name' => array(),
        'for' => array(),
    )),
    'span' => array_merge($defaultAtts, array(
        'onkeyup' => array(),
        'onclick' => array(),
        'data' => array(),
        'data-target' => array(),
        'data-limit' => array(),
        'data-start-value' => array(),
        'data-base-field' => array(),
    )),
    'div' => array_merge($defaultAtts, array(
        'onkeyup' => array(),
        'onchange' => array(),
        'data' => array(),
        'data-target' => array(),
        'data-limit' => array(),
        'data-start-value' => array(),
        'data-base-field' => array(),
    )),
    'i' => $defaultAtts,
    'em' => $defaultAtts,
    'img' => array_merge($defaultAtts, array(
        'src' => array(),
    )),
);

function rentpress_metaField($field, $rpm, $type, $placeholder = '')
{
    $value = (isset($rpm[$field][0])) ? esc_attr($rpm[$field][0]) : '';
    $override_field = $field . '_override';

    return "<input type='$type' name='$field' id='$field' class='rentpress-settings-text' value='$value' placeholder='$placeholder'>
    		<input style='display: none;' type='checkbox' name='$override_field' checked>";
}

function rentpress_metaShadowField($field, $rpm, $type, $placeholder = '', $baseField)
{
    $value = (isset($rpm->{$baseField})) ? esc_attr($rpm->{$baseField}) : '';

    return "<input type='$type' name='$field' id='$field' class='rentpress-settings-text rentpress-shadow-field' data-start-value='$value' data-base-field='$baseField' value='$value' placeholder='$placeholder'>";
}

function rentpress_metaFieldImage($field, $rpm, $type, $placeholder = '', $limit = '')
{
    $limit = $limit ? $limit : 'true';
    $value = (isset($rpm[$field][0])) ? $rpm[$field][0] : '';
    $images = '';
    if ($value) {
        $images = json_decode($value);
        $sanitizedValue = [];
        if ($images && !is_null($images) && $limit != 'true') {
            foreach ($images as $image) {
                $sanitizedSizes = [];
                foreach ($image->sizes as $key => $size) {
                    $sanitizedSizes[$key] = [
                        'url' => isset($size->url) ? esc_url_raw($size->url) : '',
                        'height' => isset($size->height) ? intval($size->height) : '',
                        'width' => isset($size->width) ? intval($size->width) : '',
                    ];
                }
                $sanitizedValue[] = [
                    'id' => isset($image->id) ? intval($image->id) : '',
                    'url' => isset($image->url) ? esc_url_raw($image->url) : '',
                    'sizes' => $sanitizedSizes,
                ];
            }
            $value = json_encode($sanitizedValue);
        } elseif ($limit == 'true') {
            $value = esc_url_raw($rpm[$field][0]);
        } else {
            $value = '';
        }
    }

    $imageHTML = '<div id="' . $field . '-upload-preview-container">';
    if (isset($images) && is_countable($images) ? count($images) >= 1 : '') {
        $imageHTML .= '<div class="rentpress-gallery-upload-previews-grid">';
        foreach ($images as $key => $image) {
            $imageUrl = $image->sizes->medium->url ? $image->sizes->medium->url : $image->url;
            $imageHTML .= "<div class='rentpress-gallery-single-image-wrapper'><span onclick='rentpressRemoveGalleryImage(" . $image->id . ", `" . $field . "`, this)'>X</span><img src='" . $imageUrl . "'></div>";
        }
        $imageHTML .= '</div>';
    } elseif (!is_array($images) && $value && $limit == 'true') {
        $imageHTML .= '<img src="' . $value . '" id="' . $field . '-image" class="rentpress-image-upload-preview">';
    }

    return "<div><input style='display: none;' type='$type' name='$field' id='{$field}-field' class='rentpress-settings-text' value='$value' placeholder='$placeholder'>
            <span id='{$field}-upload-btn' class='button rentpress-image-uploader-field' data-target='$field' data-limit='$limit'>Upload</span>
            <span id='rentpress-gallery-clear-upload-btn' class='button clear-gallery-images' onclick='rentpressClearPropertyGallery(this)'>Clear</span>" . $imageHTML . '</div></div>';
}

function rentpress_metaShadowTextArea($field, $rpm, $rows = '6', $placeholder = '', $baseField)
{
    $value = (isset($rpm->{$baseField})) ? esc_attr($rpm->{$baseField}) : '';

    return "<textarea name='$field' id='$field' class='rentpress-settings-text rentpress-shadow-field' data-start-value='$value' data-base-field='$baseField' placeholder='$placeholder' rows='$rows'>$value</textarea>";
}

function rentpress_metaFieldSelector($field, $rpm, $options)
{
    $value = (isset($rpm[$field][0])) ? esc_attr($rpm[$field][0]) : '';
    $override_field = $field . '_override';

    $selector_str = "<select name='$field' id='$field' class='rentpress-settings-select'>";

    foreach ($options as $key => $option) {
        $labelValue = $option;
        $labelTitle = $option;
        if ( is_string($key) ) {
            $labelValue = $option;
            $labelTitle = $key;
        }
        if ($value == $option) {
            $selector_str .= "<option value='$labelValue' selected>$labelTitle</option>";
        } else {
            $selector_str .= "<option value='$labelValue'>$labelTitle</option>";
        }

    }

    return $selector_str .= "</select>";
}

function rentpress_metaFieldNeighborhoodSelector($field, $rpm, $neighborhoods)
{
    $value = (isset($rpm[$field][0])) ? esc_attr($rpm[$field][0]) : '';
    $prop = get_post_meta($_GET['post']);

    $selector_str = "<select class='rentpress-settings-select' name='$field' id='$field'><option value='' selected>No Primary Neighborhood </option>";
    foreach ($neighborhoods as $name => $id) {
        $meta = get_post_meta($id);
        $hood_props = isset($meta['rentpress_custom_field_neighborhood_property_codes'][0]) ? $meta['rentpress_custom_field_neighborhood_property_codes'][0] : '';

        if (strpos($hood_props, $prop['rentpress_custom_field_property_code'][0]) !== false) {
            if ($value == $id) {
                $selector_str .= "<option value='$id' selected>$name</option>";
            } else {
                $selector_str .= "<option value='$id'>$name</option>";
            }
        }
    }
    return $selector_str .= "</select>";
}

function rentpress_checkboxMetaField($field, $rpm, $label)
{
    $checked = (isset($rpm[$field][0])) ? 'checked' : '';

    return "<input type='checkbox' name='$field' id='$field' class='rentpress-checkbox' $checked>
            <label>$label</label>";
}

function rentpress_colorMetaField($field, $rpm)
{
    $value = !empty($rpm[$field]) ? $rpm[$field][0] : '';

    return "<input type='color' name='$field' id='$field' class='rentpress-color' value='$value'>";
}

function rentpress_overrideMetaField($field, $rpm, $overrides, $type = 'text', $placeholder = '')
{
    $override_field = $field . '_override';
    $disabled = isset($overrides[$override_field]) ? '' : 'disabled';
    $checked = isset($overrides[$override_field]) ? 'checked' : '';
    $value = (isset($rpm[$field][0])) ? esc_attr($rpm[$field][0]) : '';

    return "<input type='$type' name='$field' id='$field' class='rentpress-settings-text' value='$value' placeholder='$placeholder' $disabled>
            <input type='checkbox' name='$override_field' class='rentpress-override rentpress-checkbox' $checked>
            <label for='$override_field'>Override</label>";
}

function rentpress_overrideMetaTextArea($field, $rpm, $overrides, $rows = '6', $placeholder = '')
{
    $override_field = $field . '_override';
    $disabled = isset($overrides[$override_field]) ? '' : 'disabled';
    $checked = isset($overrides[$override_field]) ? 'checked' : '';
    $value = (isset($rpm[$field][0])) ? esc_textarea($rpm[$field][0]) : '';

    return "<textarea name='$field' id='$field' class='rentpress-settings-text' placeholder='$placeholder' rows='$rows' $disabled>$value</textarea>
            <input type='checkbox' name='$override_field' class='rentpress-override rentpress-checkbox' $checked>
            <label for='$override_field'>Override</label>";
}

function rentpress_metaTextArea($field, $rpm, $rows = '6', $placeholder = '')
{
    $value = (isset($rpm[$field][0])) ? wp_kses_post($rpm[$field][0]) : '';
    $override_field = $field . '_override';

    return "<textarea name='$field' id='$field' class='rentpress-settings-text' placeholder='$placeholder' rows='$rows'>$value</textarea>
    		<input style='display: none;' type='checkbox' name='$override_field' checked>";
}

function rentpress_rangeMetaFields($minfield, $maxfield, $rpm, $overrides, $type = 'number', $first_label = 'Min:', $second_label = 'Max:')
{
    $override_field_min = $minfield . '_override';
    $override_field_max = $maxfield . '_override';
    $disabled = isset($overrides[$override_field_min]) ? '' : 'disabled';
    $checked = isset($overrides[$override_field_min]) ? 'checked' : '';
    $value1 = (isset($rpm[$minfield][0])) ? esc_attr($rpm[$minfield][0]) : '';
    $value2 = (isset($rpm[$maxfield][0])) ? esc_attr($rpm[$maxfield][0]) : '';

    return "<div class='rentpress-range-label'>$first_label</div>
             <input type='$type' name='$minfield' id='$minfield' class='rentpress-settings-text' value='$value1' $disabled>
             <div class='rentpress-range-label'>$second_label</div>
             <input type='$type' name='$maxfield' id='$maxfield' class='rentpress-settings-text' value='$value2' $disabled>
             <input type='checkbox' name='$override_field_min' class='rentpress-override rentpress-checkbox rentpress-override-range' $checked>
             <input style='display: none;' type='checkbox' name='$override_field_max' class='rentpress-override' $checked>
             <label>Override</label>";
}

function rentpress_timeMetaFields($minfield, $maxfield, $rpm, $overridden = false)
{
    $disabled = $overridden ? '' : 'disabled';
    $value1 = (isset($rpm[$minfield][0])) ? $rpm[$minfield][0] : '';
    $value2 = (isset($rpm[$maxfield][0])) ? $rpm[$maxfield][0] : '';
    if (!empty($value1)) {
        $value1 = date_create_from_format('g:i a', $value1);
        if ($value1) {
            $value1 = $value1->format('H:i');
        } else {
            $value1 = '';
        }
    }
    if (!empty($value2)) {
        $value2 = date_create_from_format('g:i a', $value2);
        if ($value2) {
            $value2 = $value2->format('H:i');
        } else {
            $value2 = '';
        }
    }

    return "<div class='rentpress-range-label'>Open: </div>
            <input type='time' name='$minfield' id='$minfield' class='rentpress-settings-text rentpress-office-hours' value='$value1' $disabled>
            <div class='rentpress-range-label'>Close: </div>
            <input type='time' name='$maxfield' id='$maxfield' class='rentpress-settings-text rentpress-office-hours' value='$value2' $disabled>";
}

function rentpress_floorplan_createPropertyCodeSelector($field, $rpm)
{
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/property_post_type_data.php';
    $property_codes = rentpress_getAllPropertyCodes();
    $meta_value = (isset($rpm[$field][0])) ? esc_attr($rpm[$field][0]) : '';
    $options = '';

    foreach ($property_codes as $property_code => $property_name) {
        $options .= "<option value='$property_code'";
        $options .= selected($meta_value, $property_code, false);
        $options .= ">$property_code - $property_name</option>";
    }

    return "
		<select id='$field' class='rentpress-settings-select' name='$field'>
            $options
        </select>
	";
}

function rentpress_neighborhood_createPropertyCodeSelector($field, $selected_codes)
{
    require_once RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php';
    $properties = rentpress_getAllProperties();
    $checkboxes = "";

    $checkboxes .= "<div class='rentpress-prop-selector'>";
    $checkboxes .= "<div class='rentpress-admin-search-bar-container'><span><span class='fas fa-search' aria-hidden='true'></span></span><input type='text' id='rentpress-property-selector-search' class='rentpress-admin-search-bar' onkeyup='filterPropertySelector()' placeholder='Filter by property name...'></div><div class='rentpress-prop-selector-container'>";

    if (count($properties) > 0) {
        usort($properties, function ($a, $b) {
            return strcmp(strtolower($a->property_name), strtolower($b->property_name));
        });
        foreach ($properties as $property) {
            $property_code = esc_attr($property->property_code);
            $property_name = esc_attr($property->property_name);
            $checkboxes .= "<div class='rentpress-prop-selector-row'>";
            $checkboxes .= "<input type='checkbox' name='rentpress_custom_field_neighborhood_property_code_$property_code' value='$property_code' class='rentpress-checkbox'";
            $checkboxes .= in_array($property_code, $selected_codes) ? ' checked>' : '>';
            $checkboxes .= "<span class='rentpress-prop-selector-title'>$property_name</span>";
            $checkboxes .= "</div>";
        }
        $checkboxes .= "</div></div>";
    } else {
        $checkboxes = 'No property codes';
    }

    return $checkboxes;
}