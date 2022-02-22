<?php

class rentPress_SettingsTabs_GeneralConfig {

	public static $menu_slug = 'rentPress-settings';

	public static $optionGroup = 'rentPress_wordpress_option_group';

	public static $apiCredentialsSettingsSectionID = 'rentPress_api_credentials';

	public static $isSiteAboutASinglePropertySettingsSectionID = 'rentPress_is_site_about_a_single_property';

	public static $renrpressSupportSectionID = 'rentpress_support_section';

	public function __construct() {
		$this->options = new rentPress_Options();

		$this->wp_menu_args = (object) [
			'parent_slug' => 'options-general.php',
			'page_title' =>  'RentPress',
			'menu_title' =>  'RentPress',
			'capability' => 'manage_options',
			'menu_slug' => 'rentPress-settings'
		];

		$this->fields_keys = [
			'api_token',
			'api_username',
			'is_site_about_a_single_property'
		];

		$this->fields=[];

		if (is_array($this->fields_keys)) {

			foreach ($this->fields_keys as $field_key) {

				$this->fields[ $field_key ] = (object) [

					'name' => $this->options->prefix($field_key),
					'value' => $this->options->getOption($field_key),

				];

			}

		}

		$this->fields = (object) $this->fields;

		add_action( 'admin_menu', [$this, 'generateMainOptionsPage'], 20);

		add_action( 'admin_init', [$this, 'wp_setting_sections_and_fields'], 20 );
	}

	/**
	 * Create the main RentPress plugin options page to be visible in the WP admin menu
	 * @return void
	 */
	public function generateMainOptionsPage()
	{
		add_menu_page(
			$this->wp_menu_args->page_title,
			$this->wp_menu_args->menu_title,
			$this->wp_menu_args->capability,
			$this->wp_menu_args->menu_slug,
			[ $this, 'render_settings_page' ],
			RENTPRESS_PLUGIN_ASSETS . '/images/icon.png',
			5
		);
	}

	/**
	 * Render the settings page and fields for RentPress general settings
	 * @return void
	 */
	public function render_settings_page()
	{
		$common_html=new rentPress_SettingsTabs_CommonHtml();

		$common_html->openingOptionsWrapper(); ?>
		    <h1>RentPress: General Settings</h1>
		    <?php
		    settings_errors();
		    $common_html->displayOptionsTabs(); ?>

		    <form method="post" action="options.php" enctype="multipart/form-data">
		    	<?php
		        settings_fields(self::$optionGroup);
		        do_settings_sections($this->wp_menu_args->menu_slug);
		        submit_button(); ?>
		    </form>
		<?php
		$common_html->closingOptionsWrapper();
	}

	public function wp_setting_sections_and_fields() {

		add_settings_section(
			self::$apiCredentialsSettingsSectionID,
			'Sync Credentials',
			function() { echo '<p>RentPress Sync license keys require <a href="https://via.30lines.com/vXP1UPFD" target="_blank" rel="noopener noreferrer">purchasing a subscription</a>.</p>'; },
			$this->wp_menu_args->menu_slug
		);
		
		add_settings_field(
			$this->fields->api_token->name,
			'License Key',
			[$this, 'rentPress_api_token'],
			$this->wp_menu_args->menu_slug,
			self::$apiCredentialsSettingsSectionID
		);

		add_settings_field(
			$this->fields->api_username->name,
			'Username',
			[$this, 'rentPress_api_username'],
			$this->wp_menu_args->menu_slug,
			self::$apiCredentialsSettingsSectionID
		);

		register_setting(self::$optionGroup, $this->fields->api_token->name);
		register_setting(self::$optionGroup, $this->fields->api_username->name);

		add_settings_section(
			self::$isSiteAboutASinglePropertySettingsSectionID,
			'Single Property Site',
			function() { echo '<p>Check this if your website promoting a single property. This can assist with overall site load speeds.</p>'; },
			$this->wp_menu_args->menu_slug
		);

		add_settings_field(
			$this->fields->is_site_about_a_single_property->name,
			'Single Property Website',
			[$this, 'rentPress_site_is_about_single_property'],
			$this->wp_menu_args->menu_slug,
			self::$isSiteAboutASinglePropertySettingsSectionID
		);

		register_setting(self::$optionGroup, $this->fields->is_site_about_a_single_property->name);


		add_settings_section(
			self::$renrpressSupportSectionID,
			'RentPress Support',
			function() { echo '<p style="font-size: 16px;">
		    	<b>Need Help? </b>Check the support site for assistance setting up RentPress: <a href="https://via.30lines.com/0F-Q2UnT" target="_blank" rel="noopener noreferrer">Get Started with RentPress</a>.
		    	</p><br />
		    </p><br />'; },
			$this->wp_menu_args->menu_slug
		);
	}

	/** General settings field renderings */

	/**
	 * Settings Section: API Credentials
	 * @return string [HTML of settings input]
	 */
	public function rentPress_api_token()
	{
		?>
			<input type='text'
				name='<?php echo $this->fields->api_token->name; ?>'
				value='<?php echo $this->fields->api_token->value; ?>'
				placeholder='API Key'>

		<?php
	}

	public function rentPress_api_username()
	{
		?>
			<input type='text'
				name='<?php echo $this->fields->api_username->name; ?>'
				value='<?php echo $this->fields->api_username->value; ?>'
				placeholder='API Username'>
		<?php
	}

	public function rentPress_site_is_about_single_property()
	{

		$isChecked = checked( $this->fields->is_site_about_a_single_property->value, 'true', false );
		?>
			<label>

				<input type='checkbox' id="rp_single_property_option"
					name='<?php echo $this->fields->is_site_about_a_single_property->name; ?>'
					value='true'
					<?php echo $isChecked; ?>>

				This website is for a single property
			</label>
		<?php
	}
}