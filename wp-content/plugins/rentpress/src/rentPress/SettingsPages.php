<?php 
/**
* RentPress test settings page
*/
class rentPress_SettingsPages
{
	public function __construct() {

		$rootSettingsPage=new rentPress_SettingsTabs_GeneralConfig();
		
		$this->sub_pages=[
			new rentPress_SettingsTabs_FeedConfig(),
			new rentPress_SettingsTabs_TemplateOptions(),
			new rentPress_SettingsTabs_IntegrationsConfig(),
			new rentPress_SettingsTabs_LogFeed()
		];
	}
}
