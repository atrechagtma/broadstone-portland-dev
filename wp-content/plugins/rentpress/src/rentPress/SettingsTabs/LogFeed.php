<?php

class rentPress_SettingsTabs_LogFeed extends rentPress_Base_WpSettingsSubPage{

	public static $headerLinks = 'rentPress_logs_header_section';

	public static $optionGroup = 'rentPress_support_logs_option_group';

	public static $logPaths = [
		'info' => RENTPRESS_PLUGIN_DIR . 'Log/rentPress_info.log',
		'events' => RENTPRESS_PLUGIN_DIR . 'Log/rentPress_event.log',
		'warning' => RENTPRESS_PLUGIN_DIR . 'Log/rentPress_warning.log',
		'errors' => RENTPRESS_PLUGIN_DIR . 'Log/rentPress_error.log'
	];

	public function __construct() {

		$this->wp_menu_args = [
			'menu_title' => 'Logs',
			'page_title' => 'RentPress: Activity Logs',
			'page_slug' => 'rentpress_support_logs'
		];

		$this->fields_keys=[];

		parent::__construct();

		foreach (self::$logPaths as $file_location) {
			if (! is_file($file_location)){
			    //Some simple example content.
			    $contents = $file_location;
			    //Save our content to the file.
			    file_put_contents($file_location, $contents);
			}
		}

	}

	public function render_settings_page() {
		$common_html=new rentPress_SettingsTabs_CommonHtml();

		$common_html->openingOptionsWrapper(); ?>
		    <h1>RentPress: Activity & Debug Logs</h1>
		    
		    <?php 
			    settings_errors(); 
			    $common_html->displayOptionsTabs(); 
		    ?>

		    <form method="post" action="options.php">
		    	<?php settings_fields(self::$optionGroup); ?>

		        <?php do_settings_sections($this->wp_menu_args->page_slug) ;?>

		    	<?php 
		    	foreach (self::$logPaths as $key => $path) :
		    		if ( file_exists($path) && is_readable($path) ) :
			    		$label = ucwords($key);
			    		echo "<h1>{$label} Log</h1>";
			    		echo '<textarea style="width:100%;min-height:20rem;" disabled="disabled">';
							echo self::readFileMaybeBackwards($path, 100, true); 
						echo '</textarea>';
					endif;
		    	endforeach; ?>
		    </form>

		<?php $common_html->closingOptionsWrapper();
	}

	public function wp_setting_sections_and_fields() {

		add_settings_section(
		self::$headerLinks,
		'RentPress website logs:',
		function() { echo '<p style="font-size: 14px;">
		    	These text areas below display the last 100 lines of each type of log file in RentPress with the most recent at the top. 
		    	To look further back, open the logs by visiting the plugin files.
		    </p><br />'; },
		    $this->wp_menu_args->page_slug
		);

	}

	private function readFileMaybeBackwards($filename, $lines, $revers = false)
	{
	    $offset = -1;
	    $c = ''; // Character
	    $read = ''; // lines
	    $i = 0; // line counter
	    $fh = @fopen($filename, "r"); // file handle
	    while( $lines && fseek($fh, $offset, SEEK_END) >= 0 ) {
	        $c = fgetc($fh);
	        if($c == "\n" || $c == "\r"){
	            $lines--;
	            if ( $revers ) {
	                $read[$i] = strrev($read[$i]);
	                $i++;
	            }
	        }
	        if ( $revers ) {
	        	$read[$i] = $read[$i].$c;
	        } else {
	        	$read = $read[$i].$c;
	        }
	        $offset--;
	    }
	    fclose ($fh);
	    if ( $revers ) {
	        if ($read[$i] == "\n" || $read[$i] == "\r"){
	            array_pop($read);
	        } else {
	        	$read[$i] = strrev($read[$i]);
	        }
	        return implode('',$read);
	    }
	    return strrev(rtrim($read,"\n\r"));
	}	

}