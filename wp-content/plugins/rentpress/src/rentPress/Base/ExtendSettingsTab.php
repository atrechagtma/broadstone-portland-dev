<?php 
abstract class rentPress_Base_ExtendSettingsTab{

	public function __construct( )
	{
		$this->options = new rentPress_Options();

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

		add_action( 'admin_init', [$this, 'wp_setting_sections_and_fields'], 30 );
	}


	public function get_options_keys_and_values($key) {

		return $this->fields->{ $key };

	}

	public function wp_setting_sections_and_fields() {}

}