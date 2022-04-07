<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://wordkeeper.com
 * @since      1.0.0
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/admin/partials
 */
?>
<br />
<br />
<?php if(!empty($message)): ?>
<div class="message">
		<?php echo htmlentities($message); ?>
</div>
<?php endif; ?>
<br />
<div class="wordkeeper-page-title">
	WordKeeper
	<span class="wordkeeper"><input id="save-settings" type="button" class="wordkeeper-button" value="Save" data-waiting="Saving..." data-action="wordkeeper_admin_ajax" /></span>
</div>
<div id="wordkeeper-system-main" class="wordkeeper-main wordkeeper">
	<div class="nav-tab-wrapper wordkeeper-nav-tab">
		<?php
			$tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'caching';
			$css_display_block = 'style="display:block";';
			$css_display_none  = 'style="display:none";';
		?>
		<?php if(current_user_can('publish_posts') || current_user_can('publish_pages')) : ?>
		<a class="nav-tab <?php if($tab == 'caching'):?> nav-tab-active <?php endif; ?>" href="?page=wordkeeper-system&tab=caching">
            Caching
      	</a>
   		<?php endif; ?>


   		<?php if( current_user_can('manage_options')): ?>
        <a class="nav-tab <?php if($tab == 'settings'):?> nav-tab-active <?php endif; ?>" href="?page=wordkeeper-system&tab=settings">
            Settings
        </a>
        <a class="nav-tab <?php if($tab == 'bots'):?> nav-tab-active <?php endif; ?>" href="?page=wordkeeper-system&tab=bots">
            Bots
        </a>		
    	<?php endif; ?>

	</div>

<div id="sections">
	<?php if(current_user_can('publish_posts') || current_user_can('publish_pages')) : ?>
	<section class="section" <?php if($tab == 'caching') { echo $css_display_block; } else { echo $css_display_none; }?> >
			<h2>Caching</h2>

			<p style="max-width: 600px;">
				The server's page cache helps ensure that your site stays both fast and stable.  While it does try to clear itself when you make significant changes, 
				you may need to manually clear it at times.  Click Purge All Caches below to clear the site's entire page cache.<br /><br />
			</p>
			
			<form enctype="application/x-www-form-urlencoded" method="post" id="purge-form" name="purge-form">
				<input type="button" name="purge-all" id="purge-all" value="Purge All Caches" class="wordkeeper-button" data-waiting="Purging..." data-action="wordkeeper_admin_ajax" <?php //if(strpos(__FILE__, 'staging_') !== false){echo 'disabled="disabled"';} ?> />
				<input type="hidden" name="purge" id="purge" value="purge-all" />
				<input type="hidden" name="form" value="purge-form" />
				<?php //wp_nonce_field( 'wordkeeper-purge-cache' ); ?>
			</form>
	</section>

	<?php endif; ?>
	<?php if( current_user_can('manage_options')): ?>
	<section class="section" <?php if($tab == 'settings') { echo $css_display_block; } else { echo $css_display_none; }?> >

		<h2>Settings</h2>	

		<p style="max-width: 600px;">
			While useful, WordPress heartbeat is often excessive in how often it runs behind the scenes.
			To improve stability, it's best to reduce both the number of areas where heartbeat can run and how often it runs from WP's default.
			Running it only in WP's edit pages and once a minute minimum is recommended.  
			For better results, run heartbeat even less frequently.<br /><br />
		</p>


		<form enctype="application/x-www-form-urlencoded" method="post" id="settings-form" name="settings-form">
			<strong style="display:inline-block;margin-bottom:10px;">Heartbeat Frequency (Seconds)</strong><br />
																																																					
										   
			<select name="heartbeat-frequency" id="heartbeat-frequency">
				<?php foreach($options['heartbeat-frequency'] as $value => $name): ?>
				<option value="<?php echo $value; ?>"<?php echo ($settings['heartbeat-frequency'] == $value) ? ' selected' : ''; ?>><?php echo $name; ?></option>
				<?php endforeach; ?>
			</select><br /><br />
			<strong style="display:inline-block;margin-bottom:10px;">Heartbeat Limitations</strong><br />
			<select name="heartbeat-permission" id="heartbeat-permission">
				<?php foreach($options['heartbeat-permission'] as $value => $name): ?>
				<option value="<?php echo $value; ?>"<?php echo ($settings['heartbeat-permission'] == $value) ? ' selected' : ''; ?>><?php echo $name; ?></option>
				<?php endforeach; ?>
			</select><br /><br />
			<input type="hidden" name="form" value="settings-form" />
			<?php wp_nonce_field( 'wordkeeper-heartbeat' ); ?>
		</form>
	</section>

	<section class="section" <?php if($tab == 'bots') { echo $css_display_block; } else { echo $css_display_none; }?> >
		<form enctype="application/x-www-form-urlencoded" method="post" id="bots-form" name="bots-form">
			<h2>SEO Analysis Bots</h2>
			
			<p style="max-width: 600px;">
				For most sites, SEO analysis bots are used more by competitors and self-interested parties than by the site owner or the site owner's SEO team.  
				Unless you need the bots below for your own SEO analysis, it's best to leave them blocked and only enable them temporarily when you need them for your own analysis.<br /><br />
			</p>

			<div style="display:flex;margin-bottom:25px;">
				<div style="margin-right: 100px;min-width:115px;">
					<div><strong>Allow Ahrefs</strong></div>
					<div class="toggle-btn <?php echo ($settings['ahrefs'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="ahrefs" <?php echo ($settings['ahrefs'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>

				<div style="margin-right: 100px;min-width:115px;">
					<div><strong>Allow Moz</strong></div>
					<div class="toggle-btn <?php echo ($settings['moz'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="moz" <?php echo ($settings['moz'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>
			</div>

			<div style="display:flex;margin-bottom:25px;">
				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow SemRush</strong></div>
					<div class="toggle-btn <?php echo ($settings['semrush'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="semrush" <?php echo ($settings['semrush'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Screaming Frog</strong></div>
					<div class="toggle-btn <?php echo ($settings['screaming-frog'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="screaming-frog" <?php echo ($settings['screaming-frog'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>
			</div>

			<div style="display:flex;margin-bottom:40px;">
				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Majestic</strong></div>
					<div class="toggle-btn <?php echo ($settings['majestic'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="majestic" <?php echo ($settings['majestic'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>
				
				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow DataForSEO</strong></div>
					<div class="toggle-btn <?php echo ($settings['dataforseo'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="dataforseo" <?php echo ($settings['dataforseo'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>				
			</div>

			<h2>Country Specific Bots</h2>

			<p style="max-width: 600px;">
				Some search engines cater primarily to specific geographic regions or languages.  
				If your primary demographic is outside of these regions or languages, allowing these bots to index your site
				only serves to distract your site from serving your primary audience.  You should leave unneeded search engines disabled.<br /><br />
			</p>

			<div style="display:flex;margin-bottom:25px;">

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Yandex</strong></div>
					<div class="toggle-btn <?php echo ($settings['yandex'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="yandex" <?php echo ($settings['yandex'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Baidu</strong></div>
					<div class="toggle-btn <?php echo ($settings['baidu'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="baidu" <?php echo ($settings['baidu'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>				

			</div>

			<div style="display:flex;margin-bottom:25px;">

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Huawei</strong></div>
					<div class="toggle-btn <?php echo ($settings['huawei'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="huawei" <?php echo ($settings['huawei'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Seznam</strong></div>
					<div class="toggle-btn <?php echo ($settings['seznam'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="seznam" <?php echo ($settings['seznam'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>				

			</div>

			<div style="display:flex;margin-bottom:25px;">

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Mail.RU</strong></div>
					<div class="toggle-btn <?php echo ($settings['mailru'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="mailru" <?php echo ($settings['mailru'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Qwant</strong></div>
					<div class="toggle-btn <?php echo ($settings['qwant'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="qwant" <?php echo ($settings['qwant'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>				

			</div>

			<div style="display:flex;margin-bottom:25px;">

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Sogou</strong></div>
					<div class="toggle-btn <?php echo ($settings['sogou'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="sogou" <?php echo ($settings['sogou'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>

				<div style="margin-right:100px;min-width:115px;">
					<div><strong>Allow Coccoc</strong></div>
					<div class="toggle-btn <?php echo ($settings['coccoc'] === true) ? 'active' : ''; ?>">
						<input type="checkbox" class="toggle-value" name="coccoc" <?php echo ($settings['coccoc'] === true) ? ' checked' : ''; ?> />
						<span class="round-btn"></span>
					</div>
				</div>				

			</div>			
		</form>
	</section>	
	<?php endif;?>

</div>
</div>
<script>
  var wordkeeper_nonce = '<?php echo wp_create_nonce('wordkeeper_ajax'); ?>';
  var wordkeeper_waiting = '<?php echo plugin_dir_url(dirname(__FILE__)); ?>images/waiting2.gif';
</script>