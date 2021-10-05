<?php

if (class_exists('WP_Customize_Control')) {
	class WP_Customize_Teeny_Control extends WP_Customize_Control {
		function __construct($manager, $id, $options) {
			parent::__construct($manager, $id, $options);

			global $num_customizer_teenies_initiated;
			$num_customizer_teenies_initiated = empty($num_customizer_teenies_initiated)
				? 1
				: $num_customizer_teenies_initiated + 1;
    	}
		function render_content() {
			global $num_customizer_teenies_initiated, $num_customizer_teenies_rendered;
			$num_customizer_teenies_rendered = empty($num_customizer_teenies_rendered)
				? 1
				: $num_customizer_teenies_rendered + 1;
			$value = $this->value();
			?>
			<label>
				<span class="customize-text_editor"><?php echo esc_html($this->label); ?></span>
				<input id="<?php echo $this->id ?>-link" class="wp-editor-area" type="hidden" <?php $this->link(); ?> value="<?php echo esc_textarea($value); ?>">
				<?php
				wp_editor($value, $this->id, [
					'textarea_name' => $this->id,
					'media_buttons' => false,
					'drag_drop_upload' => false,
					'teeny' => true,
					'quicktags' => false,
					'textarea_rows' => 13,
					// MAKE SURE TINYMCE CHANGES ARE LINKED TO CUSTOMIZER
					'tinymce' => [
						'setup' => "function (editor) {
							var cb = function () {
								var linkInput = document.getElementById('$this->id-link')
								linkInput.value = editor.getContent()
								linkInput.dispatchEvent(new Event('change'))
							}
							editor.on('Change', cb)
							editor.on('Undo', cb)
							editor.on('Redo', cb)
							editor.on('KeyUp', cb) // Remove this if it seems like an overkill
						}"
					]
				]);
				?>
			</label>
			<?php
			// PRINT THEM ADMIN SCRIPTS AFTER LAST EDITOR
			if ($num_customizer_teenies_rendered == $num_customizer_teenies_initiated)
				do_action('admin_print_footer_scripts');
    	}
	}
}

function gtma_theme_customizer_settings($wp_customize) {
	$wp_customize->add_panel( 'gtma_theme_options', 
	    array(
	        'priority'       => 200,
	        'title'            => __( 'Theme Options', 'gtma-starter-theme' ),
	        'description'      => __( 'Theme Options', 'gtma-starter-theme' ),
	    ) 
	);

	gtma_theme_customizer_general($wp_customize);
	gtma_theme_customizer_scripts($wp_customize);
}

function gtma_theme_customizer_general($wp_customize) {
	$wp_customize->add_section( 'gtma_theme_general_settings', 
	    array(
	        'title'         => __( 'General Settings', 'gtma-starter-theme' ),
	        'priority'      => 0,
	        'panel'         => 'gtma_theme_options',
	    ) 
	);
	# GOOGLE TRACKING CODE
	$wp_customize->add_setting( 'gtma_theme_google_is_dev',
	    array(
	        'default'           => 1,
	        'transport'         => 'refresh',
	    )
	);
	$wp_customize->add_control( 'gtma_theme_google_is_dev', 
	    array(
	        'type'        => 'checkbox',
	        'priority'    => 10,
	        'section'     => 'gtma_theme_general_settings',
	        'label'       => __( 'Is the website in development environment?', 'gtma-starter-theme' ),
	    ) 
	);
}

function gtma_theme_customizer_scripts($wp_customize) {
	$wp_customize->add_section( 'gtma_theme_scripts', 
	    array(
	        'title'         => __( 'Scripts', 'gtma-starter-theme' ),
	        'priority'      => 10,
	        'panel'         => 'gtma_theme_options',
	    ) 
	);
	# GOOGLE TRACKING CODE
	$wp_customize->add_setting( 'gtma_theme_google_tracking_code',
	    array(
	        'default'           => null,
	        // 'sanitize_callback' => 'sanitize_text_field',
	        'transport'         => 'refresh',
	    )
	);
	$wp_customize->add_control( 'gtma_theme_google_tracking_code', 
	    array(
	        'type'        => 'textarea',
	        'priority'    => 10,
	        'section'     => 'gtma_theme_scripts',
	        'label'       => __( 'Google Tracking code', 'gtma-starter-theme' ),
	        'description' => __( 'The javascript tracking codes will be added inside the </head> tag.', 'gtma-starter-theme' ),
	        'input_attrs' => array(
	            'placeholder' => __( 'Add javascript code here...', 'gtma-starter-theme' ),
	        )
	    ) 
	);
	# TRACKING CODES
	$wp_customize->add_setting( 'gtma_theme_tracking_codes',
	    array(
	        'default'           => null,
	        // 'sanitize_callback' => 'sanitize_text_field',
	        'transport'         => 'refresh',
	    )
	);
	$wp_customize->add_control( 'gtma_theme_tracking_codes', 
	    array(
	        'type'        => 'textarea',
	        'priority'    => 30,
	        'section'     => 'gtma_theme_scripts',
	        'label'       => __( 'Other Tracking codes', 'gtma-starter-theme' ),
	        'description' => __( 'The javascript tracking codes will be added inside the </head> tag.', 'gtma-starter-theme' ),
	        'input_attrs' => array(
	            'placeholder' => __( 'Add javascript code here...', 'gtma-starter-theme' ),
	        )
	    ) 
	);
	# SCRIPT BEFORE </HEAD>
	$wp_customize->add_setting( 'gtma_theme_script_ending_head',
	    array(
	        'default'           => null,
	        // 'sanitize_callback' => 'sanitize_text_field',
	        'transport'         => 'refresh',
	    )
	);
	$wp_customize->add_control( 'gtma_theme_script_ending_head', 
	    array(
	        'type'        => 'textarea',
	        'priority'    => 40,
	        'section'     => 'gtma_theme_scripts',
	        'label'       => __( 'Custom Script before </head>', 'gtma-starter-theme' ),
	        'description' => __( 'The javascript code will be added inside the </head> tag.', 'gtma-starter-theme' ),
	        'input_attrs' => array(
	            'placeholder' => __( 'Add javascript code here...', 'gtma-starter-theme' ),
	        )
	    ) 
	);
	# SCRIPT BEFORE </BODY>
	$wp_customize->add_setting( 'gtma_theme_script_ending_body',
	    array(
	        'default'           => null,
	        // 'sanitize_callback' => 'sanitize_text_field',
	        'transport'         => 'refresh',
	    )
	);
	$wp_customize->add_control( 'gtma_theme_script_ending_body', 
	    array(
	        'type'        => 'textarea',
	        'priority'    => 50,
	        'section'     => 'gtma_theme_scripts',
	        'label'       => __( 'Custom Script before </body>', 'gtma-starter-theme' ),
	        'description' => __( 'The javascript code will be added inside the </body> tag.', 'gtma-starter-theme' ),
	        'input_attrs' => array(
	            'placeholder' => __( 'Add javascript code here...', 'gtma-starter-theme' ),
	        )
	    ) 
	);
}

function gtma_theme_cta_banner_menu($wp_customize) {
	$wp_customize->add_section( 'gtma_theme_cta_banner', 
	    array(
	        'title'         => __( 'CTA Banner', 'gtma-starter-theme' ),
	        'priority'      => 100,
	        'panel'         => '',
	    ) 
	);
	$wp_customize->add_setting( 'gtma_theme_cta_is_active',
	    array(
	    	'type' => 'theme_mod',
	        'default'           => 0,
	        'transport'         => 'refresh',
	    )
	);
	$wp_customize->add_control( 'gtma_theme_cta_is_active', 
	    array(
	        'type'        => 'checkbox',
	        'priority'    => 10,
	        'section'     => 'gtma_theme_cta_banner',
	        'label'       => __( 'CTA Banner active', 'gtma-starter-theme' ),
	    ) 
	);

	$wp_customize->add_setting( 'gtma_theme_cta_bg_color',
	    array(
	        'transport'         => 'refresh',
	    )
	);
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'gtma_theme_cta_bg_color', array(
        'label' => 'Background Color',
        'section' => 'gtma_theme_cta_banner',
        'settings' => 'gtma_theme_cta_bg_color'
 
    )));

    $wp_customize->add_setting( 'gtma_theme_cta_text_color',
	    array(
	        'transport'         => 'refresh',
	    )
	);
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'gtma_theme_cta_text_color', array(
        'label' => 'Text Color',
        'section' => 'gtma_theme_cta_banner',
        'settings' => 'gtma_theme_cta_text_color'
 
    )));

    $wp_customize->add_setting( 'gtma_theme_cta_content',
	    array(
	        'transport'         => 'refresh',
	    )
	);
    $wp_customize->add_control( new WP_Customize_Teeny_Control($wp_customize, 'gtma_theme_cta_content', [
		'label'       => __( 'CTA Content', 'gtma-starter-theme' ),
		'section'     => 'gtma_theme_cta_banner',
		'settings' => 'gtma_theme_cta_content'
	]));
}

add_action('customize_register', 'gtma_theme_customizer_settings');
add_action('customize_register', 'gtma_theme_cta_banner_menu');
