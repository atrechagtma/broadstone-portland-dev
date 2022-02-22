<?php 

/** Settings Page form wrapper methods */
	
class rentPress_SettingsTabs_CommonHtml{
		
	public static function openingOptionsWrapper()
	{
		echo '<div id="rp-settings-page-wrapper">';
	}

	public static function closingOptionsWrapper()
	{
		echo '</div>';
	}

	public static function displayOptionsTabs()
	{
		global $submenu, $wpdb;

		if (isset($submenu[ rentPress_SettingsTabs_GeneralConfig::$menu_slug ])) {
			$tabs=array_map(
				function($item) {
					return (object) [
						'slug' => $item[2],
						'text' => $item[0],
					];
				},
				$submenu[ rentPress_SettingsTabs_GeneralConfig::$menu_slug ]
			);			
		}
		else {
			$tabs=[];
		}
		
		?>
		
		<h2 class="rp-admin-nav">
			<?php foreach ($tabs as $tab) : ?>
			    <a href="?page=<?php echo $tab->slug; ?>" class="nav-tab <?php echo $_GET['page'] == $tab->slug ? 'rp-nav-tab-active' : ''; ?>">
				    <?php echo $tab->text; ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<div style="clear: both;"></div>

		<?php
		// Only allow option to resync properties if account credentials are provided.
		if ( get_option('rentPress_api_token') && get_option('rentPress_api_username') && $_GET['page'] != 'rentPress_log_display_plugin_settings' ) :  ?>
	        <div class="rp-syncing-respond"></div>

			<div class="rp-sync-ctas">
				<h4 style="color:white; margin-top: 1rem; margin-bottom:1rem; margin-top: 0; vertical-align: middle;">
					Connected to RentPress Sync. You can resync your pricing and availability information whenever you like!
				</h4>
				
				<a id="rp-sync-properties" style="vertical-align: middle; float:right;">
					Resync Pricing and Availability
				</a>

				<div style="clear:both;"></div>
			</div> <?php
		endif;
	}

}