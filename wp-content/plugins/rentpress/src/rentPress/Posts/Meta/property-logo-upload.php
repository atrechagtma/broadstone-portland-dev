<?php

///////////////////////////////////////////////////////////////////////////////////////////////
/////////// add media image upload field for property logo image //////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'admin_init', 'add_post_gallery' );
add_action( 'admin_head-post.php', 'print_scripts' );
add_action( 'admin_head-post-new.php', 'print_scripts' );
add_action( 'save_post', 'update_post_gallery', 10, 2 );
 
/**
 * Add custom Meta Box to Posts post type
*/
function add_post_gallery()
{
    add_meta_box(
    'rentPress_post_gallery',
    'Property - Logo',
    'post_gallery_options',
    'properties',// here you can set post type name
    'normal',
    'core'
            );
}
 
/**
 * Print the Meta Box content
 */
function post_gallery_options()
{
    global $post;
    $gallery_data = get_post_meta( $post->ID, 'gallery_data', true );
 
    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'noncename' );
    ?>
 
<div id="dynamic_form">
    <div id="field_wrap">
        <div class="field_row clearfix">
            <div>Enter the URL for the property logo or upload/choose file from Media Library</div>
 
          <div class="field_left">
            <div class="form_field">
              <label>Logo Image URL</label>
              <input type="text" id="rp_prop_logo_input" class="meta_image_url" name="gallery[image_url][]" value="<?php if ( isset( $gallery_data['image_url'] ) ) { esc_html_e( $gallery_data['image_url'][0] ); } ?>"/>
            </div>
          </div>
 
          <div class="field_right">
            <input class="button" type="button" value="Choose File" onclick="add_image(this)" />
            <input class="button" type="button" value="Clear" onclick="clear_field()" />
          </div>

        </div>
    </div>
</div>
 
  <?php
}
 
/**
 * Print styles and scripts
 */
function print_scripts()
{
    // Check for correct post_type
    global $post;
    ?>  
    <style type="text/css">
      .field_left {
        float:left;
        margin-top: 1em;
      }
 
      .field_right {
        float:left;
        margin-left:10px;
        margin-top: 1em;
      }

      .button {
        margin-left:10px;
      }

      .clearfix {
        overflow: auto;
      }
 
      .clearfix::after {
        content: "";
        clear: both;
        display: table;
      }
 
      #dynamic_form {
        width:100%;
      }
 
      #dynamic_form input[type=text] {
        width:300px;
      }
 
      #dynamic_form .field_row {
        margin-bottom:10px;
        padding:10px;
      }
 
      #dynamic_form label {
        padding:0 6px;
      }
    </style>
 
    <script type="text/javascript">

        function add_image(obj) {
            var parent=jQuery(obj).parent().parent('div.field_row');
            var inputField = jQuery(parent).find("#rp_prop_logo_input");
 
            tb_show('', 'media-upload.php?TB_iframe=true');
 
            window.send_to_editor = function(html) {
                var url = jQuery(html).find('img').attr('src');
                inputField.val(url);
                jQuery(parent)
                .find("div.image_wrap")
                tb_remove();
            };
 
            return false;  
        }

        function clear_field(obj) {
            jQuery("#rp_prop_logo_input").val('');
        }

    </script>
    <?php
}
 
/**
 * Save post action, process fields
 */
function update_post_gallery( $post_id, $post_object ) 
{
    // Doing revision, exit earlier **can be removed**
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  
        return;
  
    // Verify authenticity
    if ( !wp_verify_nonce( $_POST['noncename'], plugin_basename( __FILE__ ) ) )
        return;
 
    if ( $_POST['gallery'] ) 
    {
        // Build array for saving post meta
        $gallery_data = array();
        for ($i = 0; $i < count( $_POST['gallery']['image_url'] ); $i++ ) 
        {
            if ( '' != $_POST['gallery']['image_url'][ $i ] ) 
            {
                $gallery_data['image_url'][]  = $_POST['gallery']['image_url'][ $i ];
            }
        }
 
        if ( $gallery_data ) 
            update_post_meta( $post_id, 'gallery_data', $gallery_data );
        else 
            delete_post_meta( $post_id, 'gallery_data' );
    } 
    // Nothing received, all fields are empty, delete option
    else 
    {
        delete_post_meta( $post_id, 'gallery_data' );
    }
}
