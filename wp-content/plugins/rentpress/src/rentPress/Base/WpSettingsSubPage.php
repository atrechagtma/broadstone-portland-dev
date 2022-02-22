<?php 

abstract class rentPress_Base_WpSettingsSubPage {

	public function __construct( )
	{
		$this->options = new rentPress_Options();

		$this->wp_menu_args = (object) $this->wp_menu_args;

		$this->fields = [];

		if (is_array($this->fields_keys)) {

			foreach ($this->fields_keys as $field_key) {
				
				$this->fields[ $field_key ] = (object) [

					'name' => $this->options->prefix($field_key),
					'value' => $this->options->getOption($field_key),

				];

			}

		}
		
		$this->fields = (object) $this->fields;

		add_action( 'admin_menu', [$this, 'add_menu_and_page'], 40);

		add_action( 'admin_init', [$this, 'wp_setting_sections_and_fields'], 30 );
	}


	public function get_options_keys_and_values($key) {

		return $this->fields->{ $key };

	}

	public function add_menu_and_page() 
	{ 
		add_submenu_page( 
			rentPress_SettingsTabs_GeneralConfig::$menu_slug, // The slug name for the parent menu
			$this->wp_menu_args->page_title, // The text to be displayed in the title tags of the page when the menu is selected.
			$this->wp_menu_args->menu_title, // The text to be used for the menu.
			'manage_options', // The capability required for this menu to be displayed to the user.
			$this->wp_menu_args->page_slug, // The slug name to refer to this menu by (should be unique for this menu).
			[$this, 'render_settings_page'] //The function to be called to output the content for this page
		);

	}

	public function render_settings_page() {}

	public function wp_setting_sections_and_fields() {}
}