<?php

function rentpress_registerAllPostTypes()
{
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/register_property_post_type.php';
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'floorplan/register_floorplan_post_type.php';
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'neighborhood/register_neighborhood_post_type.php';
}

// Fire our meta box setup function only on the post editor screens
add_action('load-post.php', 'rentpress_allPostMetaSetup');
add_action('load-post-new.php', 'rentpress_allPostMetaSetup');

function rentpress_allPostMetaSetup()
{
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'property/property_post_type_meta_setup.php';
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'floorplan/floorplan_post_type_meta_setup.php';
    require_once RENTPRESS_PLUGIN_ADMIN_POSTS . 'neighborhood/neighborhood_post_type_meta_setup.php';
}

// This function removes comments from rentpress's post types
function rentpress_disable_comments_for_all_custom_post_types($open, $post_id)
{
    $post_type = get_post_type($post_id);

    if ($post_type == 'rentpress_property' ||
        $post_type == 'rentpress_floorplan' ||
        $post_type == 'rentpress_hood') {
        return false;
    }
    // leave comments enabled for any other post types
    return true;
}
add_filter('comments_open', 'rentpress_disable_comments_for_all_custom_post_types', 10, 2);

add_action('pre_get_posts', 'rentpress_posts_orderby');
function rentpress_posts_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ('rentpress_custom_field_floorplan_parent_property_code' === $query->get('orderby')) {
        $query->set('orderby', 'meta_value');
        $query->set('meta_key', 'rentpress_custom_field_floorplan_parent_property_code');
    }
}

function rentpress_custom_column_callback($callback, $query, $args)
{
    // function for calling other functions and is used in the rentpress_add_custom_column_filters function
    $callback($query, $args);
}

function array_move($a, $oldpos, $newpos)
{
    if ($oldpos == $newpos) {return;}
    array_splice($a, max($newpos, 0), 0, array_splice($a, max($oldpos, 0), 1));
}

function rentpress_add_custom_column($column_title, $id, $post_type, $cb, $order = -1, $has_column_sort = '')
{
    // this function makes custom columns and is used in the rentpress_add_custom_column_filters function

    // Column Header
    add_filter('manage_' . $post_type . '_posts_columns', function ($columns) use ($column_title, $order, $id) {
        $columns[sanitize_title($id)] = $column_title;
        return $columns;
    });

    // Column Content
    add_action('manage_' . $post_type . '_posts_custom_column', function ($column, $post_id) use ($column_title, $cb, $id) {

        if (sanitize_title($id) === $column) {
            $cb($post_id);
        }

    }, 10, 2);
}

function rentpress_add_custom_column_helper($args)
{
    $query_post_type = (isset($_GET['post_type'])) ? sanitize_text_field($_GET['post_type']) : 'post';
    if ($args['has_column'] && $query_post_type == $args['post_type']) {
        // check the data type
        $the_column_header = '';
        if (isset($args['column_header']) ? $args['column_header'] : '') {
            $the_column_header = $args['column_header'];
        } elseif (isset($args['placeholder']) ? $args['placeholder'] : '') {
            $the_column_header = $args['placeholder'];
        } else {
            $the_column_header = $args['field_name'];
        }
        rentpress_add_custom_column($the_column_header, $args['field_name'], $args['post_type'], function ($post_id) use ($args) {
            $pagenow = $args['pagenow'];
            $post_type = $args['post_type'];
            $field_name = $args['field_name'];
            $field_type = $args['field_type'];
            $field_sort = $args['field_sort'];
            $data_type = $args['data_type'];
            $meta_key = $args['meta_key'];
            $taxonomy = $args['taxonomy'];
            $has_column = $args['has_column'];
            $has_sort = $args['has_sort'];
            $column_prefix = $args['column_prefix'];
            $column_suffix = $args['column_suffix'];
            $field_prefix = $args['field_prefix'];
            $field_suffix = $args['field_suffix'];
            $placeholder = $args['placeholder'];
            $data_type = $args['data_type'];
            $column_header = $args['column_header'];
            $allow_false = $args['allow_false'];
            $column_sort = $args['column_sort'];
            $column_order = $args['column_order'];
            $column_capitalization = $args['column_capitalization'];
            $column_display = $args['column_display'];
            // everything after this uses the post id to make the column display and echo it out

            // make calls
            require_once (RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php');
            switch ($data_type) {
                case 'bedbath':
                case 'meta':
                    // case start
                    if (!isset($thisMeta)) {
                        $thisMeta = get_post_meta($post_id);
                    }
                    // case end
                    break;
                case 'availability':
                    // case start
                    if (!isset($floorplan)) {
                        $floorplan = rentpress_getFloorplanDataWithCodeOrPostID($post_id);
                    }
                    // case end
                    break;
                case 'prop_codes_nh':
                case 'prop_codes_fp':
                    // case start
                    if (!isset($thisMeta)) {
                        $thisMeta = get_post_meta($post_id);
                    }
                    // case end
                    break;

                case 'post':
                    // case start
                    $data = get_post($post_id);
                    // case end
                    break;

                case 'term':
                    // case start
                    $terms = wp_get_post_terms($post_id, $taxonomy);
                    // case end
                    break;

                case 'neighborhood':
                case 'property_data':
                    // case start
                    $property = rentpress_getAllPropertyDataWithCodeOrPostID($post_id);
                    // case end
                    break;
            }

            switch ($data_type) {
                case 'meta':
                    // case start
                    $column_value = isset($thisMeta[$meta_key][0]) ? $thisMeta[$meta_key][0] : '';
                    $column_value_display = $column_value;
                    $column_style = '';
                    if ($column_display) {
                        foreach ($column_display as $key => $display) {
                            $compare = isset($display['compare_type']) ? $display['compare_type'] : '';
                            $compare = $compare == "=" ? '==' : $compare;
                            $compare_value = isset($display['compare_value']) ? $display['compare_value'] : '';
                            $replace = isset($display['replace']) ? $display['replace'] : '';
                            $style = isset($display['style']) ? $display['style'] : '';
                            if (isset($compare) && isset($compare_value)) {
                                switch ($compare) {
                                    case '==':
                                        $eval = $column_value == $compare_value;
                                        break;

                                    case '>':
                                        $eval = $column_value > $compare_value;
                                        break;

                                    case '<':
                                        $eval = $column_value < $compare_value;
                                        break;

                                    case '>=':
                                        $eval = $column_value >= $compare_value;
                                        break;

                                    case '<=':
                                        $eval = $column_value <= $compare_value;
                                        break;

                                    default:
                                        $eval = '';
                                        break;
                                }
                            }

                            if ($eval) {
                                if ($replace) {
                                    if (strpos($replace, '%val%') !== false) {
                                        $replace = str_replace("%val%", $column_value, $replace);
                                    }
                                    $column_value_display = $replace;
                                }
                                if ($style) {
                                    $column_style .= $style;
                                }
                            }
                        }
                        $column_value = $column_value_display;
                    }
                    if ($column_value && (!empty($thisMeta[$meta_key][0] || $allow_false))) {
                        if ($column_capitalization) {
                            switch ($column_capitalization) {
                                case 'upper':
                                    $column_value = strtoupper($column_value);
                                    break;

                                case 'lower':
                                    $column_value = strtolower($column_value);
                                    break;

                                case 'first':
                                    $column_value = ucfirst($column_value);
                                    break;

                                case 'words':
                                    $column_value = ucwords($column_value);
                                    break;
                            }
                        }
                        if ($column_style) {
                            echo "<span style='" . esc_attr($column_style) . "'>" . esc_html($column_prefix . $column_value . $column_suffix) . "</span>";
                            break;
                        }
                        echo esc_html($column_prefix . $column_value . $column_suffix);
                    }
                    // case end
                    break;

                case 'property_data':
                    // case start
                    $column_value = isset($property[$meta_key]) ? $property[$meta_key] : '';
                    if ($column_capitalization) {
                        switch ($column_capitalization) {
                            case 'upper':
                                $column_value = strtoupper($column_value);
                                break;

                            case 'lower':
                                $column_value = strtolower($column_value);
                                break;

                            case 'first':
                                $column_value = ucfirst($column_value);
                                break;

                            case 'words':
                                $column_value = ucwords($column_value);
                                break;
                        }
                    }
                    echo esc_html($column_prefix . isset($property[$meta_key]) ? $property[$meta_key] : '' . $column_suffix);
                    // case end
                    break;

                case 'neighborhood':
                    // case start
                    $hoodID = isset($property->{$meta_key}) ? $property->{$meta_key} : '';
                    if ($hoodID) {
                        $hood = get_post($hoodID);
                        $column_value = $hood->post_title;
                        if ($column_capitalization) {
                            switch ($column_capitalization) {
                                case 'upper':
                                    $column_value = strtoupper($column_value);
                                    break;

                                case 'lower':
                                    $column_value = strtolower($column_value);
                                    break;

                                case 'first':
                                    $column_value = ucfirst($column_value);
                                    break;

                                case 'words':
                                    $column_value = ucwords($column_value);
                                    break;
                            }
                        }
                        $columnHTML = $column_prefix . $column_value . $column_suffix;
                        $link = get_edit_post_link($hoodID);
                        if ($link) {
                            $columnHTML = '<a href="' . esc_url($link) . '">' . wp_kses_post($columnHTML) . '</a>';
                        }
                        echo wp_kses_post($columnHTML);
                    }
                    // case end
                    break;

                case 'bedbath':
                    // case start
                    $bed = isset($thisMeta['rentpress_custom_field_floorplan_bedroom_count'][0]) ? $thisMeta['rentpress_custom_field_floorplan_bedroom_count'][0] : '';
                    $bath = isset($thisMeta['rentpress_custom_field_floorplan_bathroom_count'][0]) ? $thisMeta['rentpress_custom_field_floorplan_bathroom_count'][0] : '';
                    if (intval($bed) == 0) {
                        $bed = 'Studio';
                    } else {
                        $bed = $bed ? $bed . ' Bed' : '';
                    }
                    $hasBoth = $bed && $bath ? ' | ' : '';
                    $bath = $bath ? $bath . ' Bath' : '';
                    $column_value = $bed . $hasBoth . $bath;
                    if ($column_capitalization) {
                        switch ($column_capitalization) {
                            case 'upper':
                                $column_value = strtoupper($column_value);
                                break;

                            case 'lower':
                                $column_value = strtolower($column_value);
                                break;

                            case 'first':
                                $column_value = ucfirst($column_value);
                                break;

                            case 'words':
                                $column_value = ucwords($column_value);
                                break;
                        }
                    }
                    echo esc_html($column_prefix . $column_value . $column_suffix);
                    // case end
                    break;

                case 'prop_codes_nh':
                case 'prop_codes_fp':
                    // case start
                    $codesHTML = '';
                    $codes = explode(',', $thisMeta[$meta_key][0]);
                    $properties = [];
                    foreach ($codes as $key => $code) {
                        if ($code) {
                            $property = rentpress_getAllPropertyDataWithCodeOrPostID($code);
                            $link = get_edit_post_link(isset($property->property_post_id) ? $property->property_post_id : '');
                            $column_value = isset($property->property_name) ? $property->property_name : '';
                            if ($column_capitalization) {
                                switch ($column_capitalization) {
                                    case 'upper':
                                        $column_value = strtoupper($column_value);
                                        break;

                                    case 'lower':
                                        $column_value = strtolower($column_value);
                                        break;

                                    case 'first':
                                        $column_value = ucfirst($column_value);
                                        break;

                                    case 'words':
                                        $column_value = ucwords($column_value);
                                        break;
                                }
                            }
                            if ($link && $column_value) {
                                $properties[$column_value] = "<a href='" . $link . "'>" . $column_value . "</a>";
                            }
                        }
                    } //

                    // sort values before display
                    ksort($properties);
                    $thisPropCount = 0;
                    foreach ($properties as $key => $prop) {
                        $thisPropCount++;
                        $comma = ', ';
                        if ($thisPropCount == count(array_keys($properties))) {
                            $comma = ' ';
                        }
                        echo wp_kses_post($prop . $comma);
                    }
                    // case end
                    break;

                case 'post':
                    // case start
                    if (isset($termsStr) && !empty($termsStr) && $termsStr) {
                        $column_value = $data->{$meta_key};
                        if ($column_capitalization) {
                            switch ($column_capitalization) {
                                case 'upper':
                                    $column_value = strtoupper($column_value);
                                    break;

                                case 'lower':
                                    $column_value = strtolower($column_value);
                                    break;

                                case 'first':
                                    $column_value = ucfirst($column_value);
                                    break;

                                case 'words':
                                    $column_value = ucwords($column_value);
                                    break;
                            }
                        }
                        echo esc_html($column_prefix . $data->{$meta_key} . $column_suffix);
                    }
                    // case end
                    break;

                case 'term':
                    // case start
                    $termsStr = '';
                    if ($column_sort && isset($terms[1])) {
                        usort($terms, function ($a, $b) {
                            return $a->name <=> $b->name;
                        });
                    }
                    if ($terms && isset($terms[0])) {
                        foreach ($terms as $term) {
                            $term_value = $term->name;
                            if ($column_capitalization) {
                                switch ($column_capitalization) {
                                    case 'upper':
                                        $term_value = strtoupper($term_value);
                                        break;

                                    case 'lower':
                                        $term_value = strtolower($term_value);
                                        break;

                                    case 'first':
                                        $term_value = ucfirst($term_value);
                                        break;

                                    case 'words':
                                        $term_value = ucwords($term_value);
                                        break;
                                }
                            }
                            $termsStr .= $term_value . ' ';
                        }
                    }
                    if (isset($termsStr) && !empty($termsStr) && $termsStr) {
                        echo esc_html($column_prefix . $termsStr . $column_suffix);
                    }
                    // case end
                    break;

                case 'availability':
                    // case start
                    $units = isset($floorplan->{$meta_key}) ? $floorplan->{$meta_key} : '';
                    if ($units && !is_null($units)) {
                        echo $units == 1 ? "1 Unit" : esc_html($units) . " Units";
                    } else {
                        echo "No Available Units";
                    }
                    // case end
                    break;
            }
        });
    }
}

function rentpress_add_custom_column_restrict_manage_posts($args)
{
    add_action('restrict_manage_posts', function () use ($args) {
        $query_post_type = (isset($_GET['post_type'])) ? sanitize_text_field($_GET['post_type']) : 'post';
        //only add filter to post type you want
        if (!isset($args['use_callback']) && $query_post_type == $args['post_type']) {
            $values = array();
            $pagenow = $args['pagenow'];
            $post_type = $args['post_type'];
            $field_name = $args['field_name'];
            $field_type = $args['field_type'];
            $field_sort = $args['field_sort'];
            $data_type = $args['data_type'];
            $meta_key = $args['meta_key'];
            $taxonomy = $args['taxonomy'];
            $has_column = $args['has_column'];
            $has_sort = $args['has_sort'];
            $column_prefix = $args['column_prefix'];
            $column_suffix = $args['column_suffix'];
            $field_prefix = $args['field_prefix'];
            $field_suffix = $args['field_suffix'];
            $placeholder = $args['placeholder'];

            //get data calls by datatype
            switch ($data_type) {
                case 'meta':
                case 'post':
                case 'term':
                case 'bedbath':
                case 'availability':
                case 'prop_codes_nh':
                case 'prop_codes_fp':
                    // case start
                    require_once (RENTPRESS_PLUGIN_DATA_ACCESS . 'data_layer.php');
                    $post_type_data = new WP_Query(array(
                        'post_type' => $post_type,
                        'posts_per_page' => -1,
                    ));
                    $post_type_data = $post_type_data->posts;
                    // case end
                    break;

                case 'neighborhood':
                    // case start
                    $neighborhood_data = new WP_Query(array(
                        'post_type' => 'rentpress_hood',
                        'posts_per_page' => -1,
                    ));
                    $neighborhood_data = $neighborhood_data->posts;
                    // case end
                    break;

                case 'availability':
                    // case start

                    // case end
                    break;
            }

            // get vaules for filter to populate
            if ($data_type == 'neighborhood') {
                if (isset($neighborhood_data)) {
                    foreach ($neighborhood_data as $neighborhood) {
                        $thisMeta = get_post_meta($neighborhood->ID);
                        $values[] = $neighborhood->post_title;
                    }
                }
            }

            if (isset($post_type_data)) {
                foreach ($post_type_data as $key => $data) {
                    switch ($data_type) {
                        case 'bedbath':
                        case 'meta':
                            // case start
                            $thisMeta = get_post_meta($data->ID);
                            if (isset($thisMeta[$meta_key][0])) {
                                // use meta key from args to get this posts value and add it to the filter
                                $values[] = is_numeric($thisMeta[$meta_key][0]) ? intval($thisMeta[$meta_key][0]) : $thisMeta[$meta_key][0];
                            }
                            // case end
                            break;

                        case 'post':
                            // case start
                            $values[] = $data->{$meta_key};
                            // case end
                            break;

                        case 'prop_codes_nh':
                            // case start
                            $thisMeta = get_post_meta($data->ID);
                            if (isset($thisMeta[$meta_key])) {
                                if (isset($thisMeta[$meta_key][0])) {
                                    $prop_codes = explode(',', $thisMeta[$meta_key][0]);
                                }
                                foreach ($prop_codes as $prop_code) {
                                    if ($prop_code) {
                                        $property = rentpress_getAllPropertyDataWithCodeOrPostID($prop_code);
                                        if (isset($property->property_name)) {
                                            $prop_name = $property->property_name ? $property->property_name : '';
                                            $values[] = array(
                                                'name' => $prop_name,
                                                'code' => $prop_code,
                                            );
                                        }
                                    }
                                }
                            }
                            // case end
                            break;

                        case 'prop_codes_fp':
                            // case start
                            $floorplan = rentpress_getFloorplanDataWithCodeOrPostID($data->ID);
                            $prop_code = isset($floorplan->floorplan_parent_property_code) ? $floorplan->floorplan_parent_property_code : '';
                            if ($prop_code) {
                                $property = rentpress_getAllPropertyDataWithCodeOrPostID($prop_code);
                                if (isset($property->property_name)) {
                                    $prop_name = $property->property_name ? $property->property_name : '';
                                    $values[] = array(
                                        'name' => $prop_name,
                                        'code' => $prop_code,
                                    );
                                }
                            }
                            // case end
                            break;

                        case 'term':
                            // case start
                            if (isset($taxonomy)) {
                                $terms = wp_get_post_terms($data->ID, $taxonomy);
                                // for each term assigned to this post, add them to the filters
                                if ($terms && isset($terms[0])) {
                                    foreach ($terms as $term) {
                                        $values[] = $term->name;
                                    }
                                }
                            }
                            // case end
                            break;
                    }
                }
            }

            // sort values for the field
            if (isset($field_sort)) {
                if ($field_sort && !is_array(isset($values[0]) ? $values[0] : '') && isset($values[0]) ? $values[0] : '') {
                    sort($values, $field_sort);
                }

                if (is_array(isset($values[0]) ? $values[0] : '') && isset($values[0]) ? $values[0] : '') {
                    usort($values, function ($a, $b) {
                        return $a['name'] <=> $b['name'];
                    });
                }
            }
            $values = array_unique($values, SORT_REGULAR);

            // change field HTML by $field_type
            switch ($field_type) {
                case 'select':
                    // case start
                    ?><select name="<?php echo esc_html($field_name); ?>">
  <option value=""><?php echo esc_html($placeholder); ?></option>

  <?php
$current_v = isset($_GET[$field_name]) ? sanitize_text_field($_GET[$field_name]) : '';
                    foreach ($values as $label => $value) {
                        $label = $value;
                        $val = $value;
                        if ($val === 0) {
                            $val = -1;
                        }
                        $selected = $value === $current_v ? ' selected="selected"' : '';

                        if ((is_array($value) && $data_type == 'prop_codes_fp') || (is_array($value) && $data_type == 'prop_codes_nh')) {
                            $val = isset($value['code']) ? $value['code'] : '';
                            $label = isset($value['name']) ? $value['name'] : '';
                        }
                        echo '<option value="' . esc_attr($val) . '" ' . $selected . '>' . esc_html($field_prefix . $label . $field_suffix) . '</option>';
                    }
                    ?>
</select>
<?php
// case end
                    break;

                case 'text':
                    // case start

                    // case end
                    break;

                case 'checkbox':
                    // case start
                    ?>
<label for="<?php esc_attr($field_name);?>">Show Available</label>
<input type="checkbox" name="<?php echo esc_attr($field_name); ?>">
<?php
// case end
                    break;
            }
        } else {
            // use the callback function in the args
            if ($args['callback_manage_posts']) {
                rentpress_custom_column_callback($args['callback_manage_posts'], $query, $args);
            }
        }
    });
}

function rentpress_add_custom_column_parse_query($args)
{
    add_filter('parse_query', function ($query) use ($args) {

        $query_post_type = (isset($_GET['post_type'])) ? sanitize_text_field($_GET['post_type']) : 'post';
        // make sure this is the correct post type

        if (isset($args['field_name'])) {
            $args['field_name'] = str_replace(' ', '-', $args['field_name']);
        }
        if ($query_post_type == $args['post_type'] && $args['pagenow'] == 'edit.php' && isset($_GET[$args['field_name']])) {

            // if this filter uses its own function the rest of this doent need to be run
            if (!isset($args['use_callback'])) {

                // make sure it has a name
                if (!empty($_GET[$args['field_name']]) || $_GET[$args['field_name']] == '0') {
                    $pagenow = $args['pagenow'];
                    $post_type = $args['post_type'];
                    $field_name = $args['field_name'];
                    $field_type = $args['field_type'];
                    $field_sort = $args['field_sort'];
                    $data_type = $args['data_type'];
                    $meta_key = $args['meta_key'];
                    $taxonomy = $args['taxonomy'];
                    $has_column = $args['has_column'];
                    $has_sort = $args['has_sort'];
                    $column_prefix = $args['column_prefix'];
                    $column_suffix = $args['column_suffix'];
                    $field_prefix = $args['field_prefix'];
                    $field_suffix = $args['field_suffix'];
                    $placeholder = $args['placeholder'];
                    $data_compare = $args['data_compare'];

                    // switch query based on data type
                    switch ($data_type) {
                        case 'meta':
                        case 'post':
                        case 'bedbath':
                        case 'prop_codes_nh':
                        case 'prop_codes_fp':
                        case 'availability':
                            // case start
                            if (!$query->get('meta_query')) {
                                $query_value = ($_GET[$field_name] === -1 || $_GET[$field_name] === '-1') ? 0 : sanitize_text_field($_GET[$field_name]);
                                // the query that does the filtering
                                $query->set('meta_query', [
                                    [
                                        'key' => $meta_key,
                                        'value' => $query_value,
                                        'compare' => $data_compare,
                                    ],
                                ]);
                            }
                            // case end
                            break;

                        case 'term':
                            // case start
                            // the query that does the filtering
                            $query->set(
                                'tax_query', array(
                                    'relation' => 'AND',
                                    array(
                                        'taxonomy' => $taxonomy,
                                        'field' => 'name',
                                        'terms' => array(sanitize_text_field($_GET[$field_name])),
                                    ),
                                )
                            );
                            // case end
                            break;

                        case 'neighborhood':
                            // case start
                            if (!$query->get('meta_query')) {
                                // get the neighborhood for this post by the name of the neighborhood
                                $meta_query = ['relation' => 'OR'];
                                $neighborhood = get_page_by_title(sanitize_text_field($_GET[$field_name]), OBJECT, 'rentpress_hood');
                                $thisMeta = get_post_meta($neighborhood->ID);
                                $propCodes = explode(',', $thisMeta['rentpress_custom_field_neighborhood_property_codes'][0]);
                                // for each code make a query to filter props by prop code
                                foreach ($propCodes as $prop) {
                                    if ($prop) {
                                        $meta_query[] = [
                                            'key' => 'rentpress_custom_field_property_code',
                                            'value' => $prop,
                                            'compare' => 'LIKE',
                                        ];
                                    }
                                }
                                // the query that does the filtering
                                $query->set('meta_query', $meta_query);
                            }
                            // case end
                            break;

                            // case 'availability':
                            //   // case start
                            //     if ( ! $query->get( 'meta_query' ) ) {
                            //       if (!isset($_GET[$field_name])) {
                            //         $available_int = '0';
                            //       } else {
                            //         $available_int = $_GET[$field_name];
                            //       }
                            //       if ($_GET[$field_name] == 'Only Available') {
                            //         $available_int = 0;
                            //       }
                            //       // the query that does the filtering
                            //       $query->set( 'meta_query', [
                            //         [
                            //           'key'     => $meta_key,
                            //           'value'   => $available_int,
                            //           'compare' => $data_compare,
                            //         ]
                            //       ] );
                            //     }
                            //   // case end
                            //   break;
                    }
                }

            } else if (isset($args['callback_parse_query'])) {
                // use the callback function in the args
                rentpress_custom_column_callback($args['callback_parse_query'], $query, $args);
            }
        }
    });
}

function rentpress_custom_column_sort($args)
{
    $pagenow = $args['pagenow'];
    $post_type = $args['post_type'];
    $field_name = $args['field_name'];
    $field_type = $args['field_type'];
    $field_sort = $args['field_sort'];
    $data_type = $args['data_type'];
    $meta_key = $args['meta_key'];
    $taxonomy = $args['taxonomy'];
    $has_column = $args['has_column'];
    $has_sort = $args['has_sort'];
    $column_prefix = $args['column_prefix'];
    $column_suffix = $args['column_suffix'];
    $column_header = $args['column_header'];
    $field_prefix = $args['field_prefix'];
    $field_suffix = $args['field_suffix'];
    $placeholder = $args['placeholder'];
    $data_compare = $args['data_compare'];
    $sort_type = $args['sort_type'];
    $sort_order = $args['sort_order'];

    add_filter('manage_edit-' . $post_type . '_sortable_columns', function ($columns) use ($field_name, $column_header, $placeholder) {
        $the_column_header = '';
        if (isset($column_header)) {
            $the_column_header = $column_header;
        } elseif (isset($placeholder)) {
            $the_column_header = $placeholder;
        } else {
            $the_column_header = $field_name;
        }
        $columns[$field_name] = $field_name;
        return $columns;
    }, 10, 3);

    add_action('pre_get_posts', function ($query) use ($args) {
        if (!is_admin()) {
            return;
        }

        if (isset($args['sort_type'])) {
            switch ($args['sort_type']) {
                case 'number':
                    // case start
                    $orderby = $query->get('orderby');
                    if ($args['field_name'] == $orderby) {
                        $query->set('meta_key', $args['meta_key']);
                        $query->set('orderby', 'meta_value_num');
                    }
                    // case end
                    break;

                case 'string':
                    // case start
                    $orderby = $query->get('orderby');
                    if ($args['field_name'] == $orderby) {
                        $query->set('meta_key', $args['meta_key']);
                        $query->set('orderby', 'meta_value');
                    }
                    // case end
                    break;
            }
        }

        if (isset($args['sort_order']) ? $args['sort_order'] : '') {
            $query->set('order', $args['sort_order']);
        }
    });
}

function rentpress_add_custom_column_filters($args)
{
    if (is_admin()) {
        // set args to vars and make sure they all have values
        global $pagenow, $wp_filter;
        $post_type = $args['post_type'];
        $field_name = $args['field_name'];
        $field_type = isset($args['field_config']['field_type']) ? $args['field_config']['field_type'] : '';
        $field_sort = isset($args['field_config']['field_sort']) ? $args['field_config']['field_sort'] : '';
        $sort_type = isset($args['sort_config']['sort_type']) ? $args['sort_config']['sort_type'] : '';
        $sort_order = isset($args['sort_config']['sort_order']) ? $args['sort_config']['sort_order'] : '';
        $data_type = isset($args['field_config']['data_type']) ? $args['field_config']['data_type'] : '';
        $meta_key = isset($args['field_config']['meta_key']) ? $args['field_config']['meta_key'] : '';
        $taxonomy = isset($args['field_config']['taxonomy']) ? $args['field_config']['taxonomy'] : '';
        $data_compare = isset($args['field_config']['data_compare']) ? $args['field_config']['data_compare'] : '';
        $has_column = isset($args['has_column']) ? $args['has_column'] : '';
        $has_sort = isset($args['has_sort']) ? $args['has_sort'] : '';
        $column_prefix = isset($args['field_config']['column_prefix']) ? $args['field_config']['column_prefix'] : '';
        $column_suffix = isset($args['field_config']['column_suffix']) ? $args['field_config']['column_suffix'] : '';
        $field_prefix = isset($args['field_config']['field_prefix']) ? $args['field_config']['field_prefix'] : '';
        $field_suffix = isset($args['field_config']['field_suffix']) ? $args['field_config']['field_suffix'] : '';
        $placeholder = isset($args['field_config']['field_placeholder']) ? $args['field_config']['field_placeholder'] : '';
        $callback_manage_posts = isset($args['callback_manage_posts']) ? $args['callback_manage_posts'] : '';
        $callback_parse_query = isset($args['callback_parse_query']) ? $args['callback_parse_query'] : '';
        $column_header = isset($args['column_config']['column_header']) ? $args['column_config']['column_header'] : '';
        $allow_false = isset($args['column_config']['allow_false']) ? $args['column_config']['allow_false'] : '';
        $column_sort = isset($args['column_config']['column_sort']) ? $args['column_config']['column_sort'] : '';
        $column_order = isset($args['column_config']['column_order']) ? $args['column_config']['column_order'] : '';
        $column_capitalization = isset($args['column_config']['column_capitalization']) ? $args['column_config']['column_capitalization'] : '';
        $column_display = isset($args['column_config']['column_display'][0]) ? $args['column_config']['column_display'] : '';

        // set values to a var
        $data = [
            'pagenow' => $pagenow,
            'post_type' => $post_type,
            'field_name' => $field_name,
            'field_type' => $field_type,
            'field_sort' => $field_sort,
            'data_type' => $data_type,
            'sort_type' => $sort_type,
            'meta_key' => $meta_key,
            'taxonomy' => $taxonomy,
            'has_column' => $has_column,
            'has_sort' => $has_sort,
            'column_prefix' => $column_prefix,
            'column_suffix' => $column_suffix,
            'field_prefix' => $field_prefix,
            'field_suffix' => $field_suffix,
            'placeholder' => $placeholder,
            'data_compare' => $data_compare,
            'callback_manage_posts' => $callback_manage_posts,
            'callback_parse_query' => $callback_parse_query,
            'sort_type' => $sort_type,
            'sort_order' => $sort_order,
            'column_header' => $column_header,
            'allow_false' => $allow_false,
            'column_sort' => $column_sort,
            'column_order' => $column_order,
            'column_capitalization' => $column_capitalization,
            'column_display' => $column_display,
        ];

        // make new collumns
        if ($has_column) {
            rentpress_add_custom_column_helper($data);
        }

        //this hook will create a new filter on the admin area for the specified post type
        rentpress_add_custom_column_restrict_manage_posts($data);

        //this hook will alter the main query according to the user's selection of the custom filter we created above:
        rentpress_add_custom_column_parse_query($data);

        if ($column_sort) {
            rentpress_custom_column_sort($data);
        }
    }
}

// example args to pass into the rentpress_add_custom_column_filters function
// $example_args = [
//   'post_type' => 'rentpress_property',      // post type this filter should be on
//   'field_name' => 'testing-field',          // the name attribute on the field
//   'has_column' => true,                     // create column based on the new filters data
//   'has_sort' => true,
//   'use_callback' => true,                   // use callback function in place of the premade logic
//   'callback_manage_posts' => 'callback',    // callback for the manage_posts hook
//   'callback_parse_query' => 'callback',     // callback for the parse_query hook
//   'column_config' => [
//     'allow_false' => true,
//     'column_header' => 'column header',
//     'column_sort' => 'sort',
//     'column_order' => 3,
//     'column_capitalization' => 'uppercase',
//     'column_display' => [
//       [
//         'compair_type' => '=', //needs empty value
//         'compair_value' => 7,
//         'style' => 'color: red;',
//         'replace' => 'no %val%',
//       ],
//     ],
//   ],
//   'sort_config' => [
//     'sort_type' => 'string',
//     'sort_order' => 'asc',
//   ],
//   'field_config' => [                       // args for the field
//     'field_capitalization' => 'uppercase',
//     'field_type' => 'select',               // the type of input field (only supports select dropdown)
//     'data_type' => 'meta',                  // the source of the data to filter on (this can use post, meta, term, prop_codes, neighborhood)
//     'taxonomy' => 'city',                   // taxonomy to filter on (this is only for the term data type)
//     'data_compare' => '=',                  // the compairison opertator that should be used to filter
//     'field_sort' => 'SORT_REGULAR',         // this sorts the filter values and will work with any valid sort() flags
//     'meta_key' => 'rentpress_custom_field_property_available_floorplans', // meta key to use for filtering and column display
//     'field_placeholder' => 'City',          // text for the default item on the dropdown
//     'field_prefix' => 'pre ',               // text to prefix the select options
//     'field_suffix' => ' post',              // text to suffix the select options
//     'column_prefix' => 'pre2 ',             // text to prefix the column if has_column is true
//     'column_suffix' => ' post2',            // text to suffix the column if has_column is true
//   ],
// ];