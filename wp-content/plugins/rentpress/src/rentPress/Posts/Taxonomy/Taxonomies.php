<?php

/**
* RentPress taxonomy registration
* For Custom Fields Checkout: https://pippinsplugins.com/adding-custom-meta-fields-to-taxonomies/
*/
class rentPress_Posts_Taxonomy_Taxonomies
{

  public function create()
  {
        add_action( 'init', [ $this, 'propertyAndFloorPlanAssociation' ], 0 );
        add_action( 'init', [ $this, 'propertyTags' ], 0); 
        add_action( 'init', [ $this, 'propertyPets' ], 0);
        add_action( 'init', [ $this, 'propertyAmenities'], 0 );
        add_action( 'init', [ $this, 'propertyCities'], 0 );
        add_action( 'init', [ $this, 'floorplanFeatures'], 0 );
        add_action( 'init', [ $this, 'propertyType'], 0 );
	}

    /**
     * Initialize Property Taxonomy
     */
   public function propertyAndFloorPlanAssociation() {
        $args = $this->buildArgs('Property', 'Properties');

        // Don't show this on front end
        $args = array_merge(
            $args,
            [
                'public'                     => false,
                'publicly_queryable'         => true, // for rss feeds
                'hierarchical'               => false,
                'show_ui'                    => false,
                'show_admin_column'          => false,
                'show_in_nav_menus'          => false,
                'show_tagcloud'              => false
            ]
        );

        register_taxonomy( 'property_relationship', array( RENTPRESS_FLOORPLANS_CPT ), $args );
    }

    /**
     * Initialize Property Tags Taxonomy
     */
    public function propertyTags() {
        $args = $this->buildArgs('Tag', 'Tags');
        register_taxonomy( 'prop_tags', array( RENTPRESS_PROPERTIES_CPT ), $args );
    }

    /**
     * Initialize Property Pets Taxonomy
     */
    public function propertyPets() {
        $args = $this->buildArgs('Pet', 'Pets');
        register_taxonomy( 'prop_pet_restrictions', array( RENTPRESS_PROPERTIES_CPT ), $args );
    }

    public function floorplanFeatures() {
        $args = $this->buildArgs('Feature', 'Features');
        register_taxonomy( 'fp_features', array( RENTPRESS_FLOORPLANS_CPT ), $args );
    }

    public function propertyType() {
        $args = $this->buildArgs('Community Type', 'Communities');
        register_taxonomy( 'prop_type', array( RENTPRESS_PROPERTIES_CPT ), $args );
    }

     /**
     * Initialize Property Amenities Taxonomy
     */
    public function propertyAmenities() {
        $args = $this->buildArgs('Amenity', 'Amenities');
        register_taxonomy( 'prop_amenities', array( RENTPRESS_PROPERTIES_CPT ), $args );   
    }

     /**
     * Initialize Property and Neighborhood City Taxonomy
     */
    public function propertyCities() {
        $args = $this->buildArgs('City', 'Cities');
        register_taxonomy( 'prop_city', array( RENTPRESS_PROPERTIES_CPT, RENTPRESS_NEIGHBORHOODS_CPT ), $args );
    }    


    public function taxonomyLabels($singular, $plural)
    {
        $singular = ucwords($singular);
        $plural = ucwords($plural);
        return [
            'name'                       => _x( $plural, 'Taxonomy General Name', RENTPRESS_LANG_KEY ),
            'singular_name'              => _x( "$singular Association", 'Taxonomy Singular Name', RENTPRESS_LANG_KEY ),
            'menu_name'                  => __( $plural, RENTPRESS_LANG_KEY ),
            'all_items'                  => __( "All $plural", RENTPRESS_LANG_KEY ),
            'parent_item'                => __( "Parent $plural", RENTPRESS_LANG_KEY ),
            'parent_item_colon'          => __( "Parent $plural:", RENTPRESS_LANG_KEY ),
            'new_item_name'              => __( "New $singular Name", RENTPRESS_LANG_KEY ),
            'add_new_item'               => __( "Add New $singular", RENTPRESS_LANG_KEY ),
            "edit_item"                  => __( "Edit $singular", RENTPRESS_LANG_KEY ),
            "update_item"                => __( "Update $singular", RENTPRESS_LANG_KEY ),
            "view_item"                  => __( "View $singular", RENTPRESS_LANG_KEY ),
            "separate_items_with_commas" => __( "Separate $plural with commas", RENTPRESS_LANG_KEY ),
            "add_or_remove_items"        => __( "Add or remove $plural", RENTPRESS_LANG_KEY ),
            "choose_from_most_used"      => __( "Choose from the most used", RENTPRESS_LANG_KEY ),
            "popular_items"              => __( "Popular $plural", RENTPRESS_LANG_KEY ),
            "search_items"               => __( "Search $plural", RENTPRESS_LANG_KEY ),
            "not_found"                  => __( "Not Found", RENTPRESS_LANG_KEY ),
        ];
    }

    public function buildArgs($singular, $plural, $args = []) {
        $labels = $this->taxonomyLabels($singular, $plural);
        return array_merge([
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'rewrite'                    => array('slug' => strtolower( $plural ), 'with_front' => false)
        ], $args);
    }

}

//add taxonomy template to amenities
add_filter('template_include', 'rp_set_amenities_template');
function rp_set_amenities_template( $template ) {
    if( is_tax('prop_amenities') && !rp_is_template($template) )
        $template = RENTPRESS_PLUGIN_DIR.'templates/Taxonomies/taxonomy-template.php';
    return $template;
}

//add taxonomy template to pets
add_filter('template_include', 'rp_set_pets_template');
function rp_set_pets_template( $template ) {
    if( is_tax('prop_pet_restrictions') && !rp_is_template($template) )
        $template = RENTPRESS_PLUGIN_DIR.'templates/Taxonomies/taxonomy-template.php';
    return $template;
}

//add taxonomy template to floor plan features
add_filter('template_include', 'rp_set_fp_features_template');
function rp_set_fp_features_template( $template ) {
    if( is_tax('fp_features') && !rp_is_template($template) )
        $template = RENTPRESS_PLUGIN_DIR.'templates/Taxonomies/taxonomy-template.php';
    return $template;
}

//add taxonomy template to property type
add_filter('template_include', 'rp_set_prop_type_template');
function rp_set_prop_type_template( $template ) {
    if( is_tax('prop_type') && !rp_is_template($template) )
        $template = RENTPRESS_PLUGIN_DIR.'templates/Taxonomies/taxonomy-template.php';
    return $template;
}

//add taxonomy template to cities
add_filter('template_include', 'rp_set_cities_template');
function rp_set_cities_template( $template ) {
    if( is_tax('prop_city') && !rp_is_template($template) )
        $template = RENTPRESS_PLUGIN_DIR.'templates/Taxonomies/taxonomy-template.php';
    return $template;
}

function rp_is_template( $template_path ){
    //Get template name
    $template = basename($template_path);
    if( 1 == preg_match('/^taxonomy-template((-(\S*))?).php/',$template) )
    return true;
    return false;
}

//Add image option to prop_amenities
if( ! class_exists( 'Showcase_Taxonomy_Images' ) ) {
  class Showcase_Taxonomy_Images {
    
    public function __construct( $term ) {
        $this->term = $term;
    }

    //Initialize the class and start calling our hooks and filters
     public function init() {
     // Image actions
    add_action( $this->term . '_add_form_fields', array( $this, 'add_category_image' ), 10, 2 );
    add_action( 'created_' . $this->term, array( $this, 'save_category_image' ), 10, 2 );
    add_action( $this->term . '_edit_form_fields', array( $this, 'update_category_image' ), 10, 2 );
    add_action( 'edited_' . $this->term, array( $this, 'updated_category_image' ), 10, 2 );
    add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
    add_action( 'admin_footer', array( $this, 'add_script' ) );
   }

   public function load_media() {
     if( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != $this->term ) {
       return;
     }
     wp_enqueue_media();
   }
  
   //Add a form field in the new category page
   public function add_category_image( $taxonomy ) { ?>
     <div class="form-field term-group">
       <label for="showcase-taxonomy-image-id"><?php _e( 'Image', 'showcase' ); ?></label>
       <input type="hidden" id="showcase-taxonomy-image-id" name="showcase-taxonomy-image-id" class="custom_media_url" value="">
       <div id="category-image-wrapper"></div>
       <p>
         <input type="button" class="button button-secondary showcase_tax_media_button" id="showcase_tax_media_button" name="showcase_tax_media_button" value="<?php _e( 'Add Image', 'showcase' ); ?>" />
         <input type="reset" style="color: #9C1E14;"class="button button-secondary showcase_tax_media_remove" id="showcase_tax_media_remove" name="showcase_tax_media_remove" value="<?php _e( 'Remove Image', 'showcase' ); ?>" />
       </p>
     </div>
   <?php }

   //Save the form field
   public function save_category_image( $term_id, $tt_id ) {
     if( isset( $_POST['showcase-taxonomy-image-id'] ) && '' !== $_POST['showcase-taxonomy-image-id'] ){
       add_term_meta( $term_id, 'showcase-taxonomy-image-id', absint( $_POST['showcase-taxonomy-image-id'] ), true );
     }
    }

    //Edit the form field
    public function update_category_image( $term, $taxonomy ) { ?>
      <tr class="form-field term-group-wrap">
        <th scope="row">
          <label for="showcase-taxonomy-image-id"><?php _e( 'Image', 'showcase' ); ?></label>
        </th>
        <td>
          <?php $image_id = get_term_meta( $term->term_id, 'showcase-taxonomy-image-id', true ); ?>
          <input type="hidden" id="showcase-taxonomy-image-id" name="showcase-taxonomy-image-id" value="<?php echo esc_attr( $image_id ); ?>">
          <div id="category-image-wrapper">
            <?php if( $image_id ) { ?>
              <?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
            <?php } ?>
          </div>
          <p>
            <input type="button" class="button showcase_tax_media_button" id="showcase_tax_media_button" name="showcase_tax_media_button" value="<?php _e( 'Add Image', 'showcase' ); ?>" />
            <input type="reset" style="color: #9C1E14;" class="delete button-secondary showcase_tax_media_remove" id="showcase_tax_media_remove" name="showcase_tax_media_remove" value="<?php _e( 'Remove Image', 'showcase' ); ?>" />
          </p>
        </td>
      </tr>
   <?php }

   //Update the form field value
   public function updated_category_image( $term_id, $tt_id ) {
     if( isset( $_POST['showcase-taxonomy-image-id'] ) && '' !== $_POST['showcase-taxonomy-image-id'] ){
       update_term_meta( $term_id, 'showcase-taxonomy-image-id', absint( $_POST['showcase-taxonomy-image-id'] ) );
     } else {
       update_term_meta( $term_id, 'showcase-taxonomy-image-id', '' );
     }
   }
 
   //Enqueue styles and scripts
   public function add_script() {
     if( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != $this->term ) {
       return;
     } ?>
     <script> jQuery(document).ready( function($) {
       _wpMediaViewsL10n.insertIntoPost = '<?php _e( "Insert", "showcase" ); ?>';
       function ct_media_upload(button_class) {
         var _custom_media = true, _orig_send_attachment = wp.media.editor.send.attachment;
         $('body').on('click', button_class, function(e) {
           var button_id = '#'+$(this).attr('id');
           var send_attachment_bkp = wp.media.editor.send.attachment;
           var button = $(button_id);
           _custom_media = true;
           wp.media.editor.send.attachment = function(props, attachment){
             if( _custom_media ) {
               $('#showcase-taxonomy-image-id').val(attachment.id);
               $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
               $( '#category-image-wrapper .custom_media_image' ).attr( 'src',attachment.url ).css( 'display','block' );
             } else {
               return _orig_send_attachment.apply( button_id, [props, attachment] );
             }
           }
           wp.media.editor.open(button); return false;
         });
       }
       ct_media_upload('.showcase_tax_media_button.button');
       $('body').on('click','.showcase_tax_media_remove',function(){
         $('#showcase-taxonomy-image-id').val('');
         $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
       });
       // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-category-ajax-response
       $(document).ajaxComplete(function(event, xhr, settings) {
         var queryStringArr = settings.data.split('&');
         if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
           var xml = xhr.responseXML;
           $response = $(xml).find('term_id').text();
           if($response!=""){
             // Clear the thumb image
             $('#category-image-wrapper').html('');
           }
          }
        });
      });
    </script>
   <?php }
}

$taxonomy_to_add = array( 'prop_city', 'prop_amenities', 'prop_pet_restrictions', 'fp_features', 'prop_type' );

foreach ($taxonomy_to_add as $tax) {
    $Showcase_Taxonomy_Images = new Showcase_Taxonomy_Images($tax);
    $Showcase_Taxonomy_Images->init(); 
}
}

//creates new city term
function create_new_city( $term ) {
wp_insert_term(
        $term,
        'prop_city'
    );
}

//adds new term to a prop
function add_city_to_prop( $postID, $tagID ) {
    wp_set_object_terms( $postID, $tagID, 'prop_city', false );
}

function create_city_terms() {
    //get props
    $propertiesArgs = array(
            'post_type' => 'properties', 
            'posts_per_page' => -1,
            'order' => 'ASC',
            );
    $propertiesQry = new WP_Query($propertiesArgs);
    $properties = $propertiesQry->posts;

    if ($properties) { // only run if property exists
      //make a new city for each prop
      foreach ( $properties as $property ) {
          $propertyMeta = get_post_meta( $property->ID );
          if ($propertyMeta['propCity'][0]) { // only run if a city exists
            $propertyCity = $propertyMeta['propCity'][0];
            $numberOfUnits = $propertyMeta['propUnitsCaptured'][0];
            //creates new city from this props city field
            create_new_city( $propertyCity );
            //adds city created above to this prop
            add_city_to_prop( $property->ID, array( strtolower($propertyCity) ) );
        }
      }
    }
}
add_action( 'init', 'create_city_terms' );

//creates new pet term
function create_new_pet () {
    $terms = array('Cat Friendly', 'Dog Friendly');
    foreach ($terms as $term) {
        wp_insert_term(
          $term, 
          'prop_pet_restrictions'
        );
    }
}
add_action( 'init', 'create_new_pet' );

//creates floor plan feature term
// function create_new_fp_feature () {
//     $terms = array('Den', 'Townhome', 'Loft');
//     foreach ($terms as $term) {
//         wp_insert_term(
//           $term,  
//           'fp_features'
//         );
//     // }
// }
// add_action( 'init', 'create_new_fp_feature' );

//creates floor plan feature term
// function create_new_prop_type () {
//         wp_insert_term(
//           $term, 
//           'prop_type'
//         );
// }
// add_action( 'init', 'create_new_prop_type' );

// adds field to edit page
function mj_taxonomy_edit_custom_meta_field($term) {
    $t_id = $term->term_id;
    $term_meta = get_option( "taxonomy_$t_id" ); 
   ?>
    <tr class="form-field">
    <th scope="row" valign="top"><label for="term_meta[rp_tax_shortcode]"><?php _e( 'Add Shortcode', 'MJ' ); ?></label></th>
        <td>
            <input type="text" name="term_meta[rp_tax_shortcode]" id="term_meta[rp_tax_shortcode]" value="<?php echo esc_attr( stripslashes( $term_meta['rp_tax_shortcode'] ) ) ? esc_attr( stripslashes($term_meta['rp_tax_shortcode']) ) : ''; ?>">
            <p class="description"><?php _e( 'Enter a Shortcode','MJ' ); ?></p>
        </td>
    </tr>
<?php
}
//amenities
add_action( 'prop_amenities_edit_form_fields','mj_taxonomy_edit_custom_meta_field', 10, 2 );
//prop city
add_action( 'prop_city_edit_form_fields','mj_taxonomy_edit_custom_meta_field', 10, 2 );
// prop pet restrictions
add_action( 'prop_pet_restrictions_edit_form_fields','mj_taxonomy_edit_custom_meta_field', 10, 2 );
// floor plan features
add_action( 'fp_features_edit_form_fields','mj_taxonomy_edit_custom_meta_field', 10, 2 );
// prop type
add_action( 'prop_type_edit_form_fields','mj_taxonomy_edit_custom_meta_field', 10, 2 );

//saves shortcode field
function mj_save_taxonomy_custom_meta_field( $term_id ) {
    if ( isset( $_POST['term_meta'] ) ) {
        $t_id = $term_id;
        $term_meta = get_option( "taxonomy_$t_id" );
        $cat_keys = array_keys( $_POST['term_meta'] );
        foreach ( $cat_keys as $key ) {
            if ( isset ( $_POST['term_meta'][$key] ) ) {
                $term_meta[$key] = $_POST['term_meta'][$key];
            }
        }
        // Save the option array.
        update_option( "taxonomy_$t_id", $term_meta );
    }
}
//amenities
add_action( 'edited_prop_amenities', 'mj_save_taxonomy_custom_meta_field', 10, 2 );  
add_action( 'create_prop_amenities', 'mj_save_taxonomy_custom_meta_field', 10, 2 );
//prop city
add_action( 'edited_prop_city', 'mj_save_taxonomy_custom_meta_field', 10, 2 );  
add_action( 'create_prop_city', 'mj_save_taxonomy_custom_meta_field', 10, 2 );
// prop pet restrictions
add_action( 'edited_prop_pet_restrictions', 'mj_save_taxonomy_custom_meta_field', 10, 2 );  
add_action( 'create_prop_pet_restrictions', 'mj_save_taxonomy_custom_meta_field', 10, 2 );
// floor plan features
add_action( 'edited_fp_features', 'mj_save_taxonomy_custom_meta_field', 10, 2 );  
add_action( 'create_fp_features', 'mj_save_taxonomy_custom_meta_field', 10, 2 );
// prop type
add_action( 'edited_prop_type', 'mj_save_taxonomy_custom_meta_field', 10, 2 );  
add_action( 'create_prop_type', 'mj_save_taxonomy_custom_meta_field', 10, 2 );


//create cities page
function create_pages_fly($pageName) {
  $createPage = array(
    'post_title'    => $pageName,
    'post_status'   => 'publish',
    'post_author'   => 1,
    'post_type'     => 'page',
    'post_name'     => $pageName,
  );

  // Insert the post into the database
  wp_insert_post( $createPage );
}

function create_page_if_null($target) {
    if( get_page_by_title($target) == NULL ) {
        create_pages_fly($target);
    }
}

function check_pages_live(){
    create_page_if_null('Cities');
}

add_action('init','check_pages_live');

//add page template to cities page
function wpd_plugin_page_template( $page_template ){
    if ( is_page( 'Cities' ) ) {
        $page_template = RENTPRESS_PLUGIN_DIR . 'templates/Taxonomies/cities-template.php';
    }
    return $page_template;
}

add_filter( 'page_template', 'wpd_plugin_page_template' );

// city romance copy

// adds local favorites area to taxonomy edit page
function rp_edit_city_romance($term) {
    $t_id = $term->term_id;
    $term_meta = get_option( "taxonomy_$t_id" );
   ?>
   <tr>
    <th scope="row" valign="top"><label for="term_meta[rp_city_romance]"><?php _e( 'City Romance', 'MJ' ); ?></label></th>
        <td><?php

          $content = ( stripslashes( $term_meta['rp_city_romance'] ) );
          $editor_id = 'rp_city_romance';
          $settings = array(
            'media_buttons' => false, 
            'textarea_rows' => '10',
            'textarea_name' => 'term_meta[rp_city_romance]');

          ?>
          <div type="text" name="term_meta[rp_city_romance]" id="term_meta[rp_city_romance]" value="<?php $content ? $content : ''; ?>">
            <?php wp_editor($content, $editor_id, $settings); ?>
          </div>
          <p class="description"><?php _e( 'Add extended romance copy about your city.','MJ' ); ?></p>
          <?php

          ?>
        </td>
      </tr>
<?php }
//prop city
add_action( 'prop_city_edit_form_fields','rp_edit_city_romance', 10, 2 );

// stripslashes( get_option( 'taxonomy_' . $term_id )['rp_city_romance'] );

//saves local favorites field
function rp_save_city_romance( $term_id ) {
    if ( isset( $_POST['term_meta'] ) ) {
        $t_id = $term_id;
        $term_meta = get_option( "taxonomy_$t_id" );
        $cat_keys = array_keys( $_POST['term_meta'] );
        foreach ( $cat_keys as $key ) {
            if ( isset ( $_POST['term_meta'][$key] ) ) {
                $term_meta[$key] = $_POST['term_meta'][$key];
            }
        }
        // Save the option array.
        update_option( "taxonomy_$t_id", $term_meta );
    }
}
//prop city
add_action( 'edited_prop_city', 'rp_save_city_romance', 10, 2 );  
add_action( 'create_prop_city', 'rp_save_city_romance', 10, 2 );


// adds local favorites area to taxonomy edit page
function rp_edit_local_favorites($term) {
    $t_id = $term->term_id;
    $term_meta = get_option( "taxonomy_$t_id" );
   ?>
   <tr>
    <th scope="row" valign="top"><label for="term_meta[rp_local_favs]"><?php _e( 'Local Favorites', 'MJ' ); ?></label></th>
        <td><?php

          $content = ( stripslashes( $term_meta['rp_local_favs'] ) );
          $editor_id = 'rp_local_favs';
          $settings = array(
            'media_buttons' => false, 
            'textarea_rows' => '10',
            'textarea_name' => 'term_meta[rp_local_favs]');

          ?>
          <div type="text" name="term_meta[rp_local_favs]" id="term_meta[rp_local_favs]" value="<?php $content ? $content : ''; ?>">
            <?php wp_editor($content, $editor_id, $settings); ?>
          </div>
          <p class="description"><?php _e( 'Add a list of local favorites in your city.','MJ' ); ?></p>
          <?php

          ?>
        </td>
      </tr>
<?php }
//prop city
add_action( 'prop_city_edit_form_fields','rp_edit_local_favorites', 10, 2 );

// stripslashes( get_option( 'taxonomy_' . $term_id )['rp_local_favs'] );

//saves local favorites field
function rp_save_local_favorites( $term_id ) {
    if ( isset( $_POST['term_meta'] ) ) {
        $t_id = $term_id;
        $term_meta = get_option( "taxonomy_$t_id" );
        $cat_keys = array_keys( $_POST['term_meta'] );
        foreach ( $cat_keys as $key ) {
            if ( isset ( $_POST['term_meta'][$key] ) ) {
                $term_meta[$key] = $_POST['term_meta'][$key];
            }
        }
        // Save the option array.
        update_option( "taxonomy_$t_id", $term_meta );
    }
}
//prop city
add_action( 'edited_prop_city', 'rp_save_local_favorites', 10, 2 );  
add_action( 'create_prop_city', 'rp_save_local_favorites', 10, 2 );
