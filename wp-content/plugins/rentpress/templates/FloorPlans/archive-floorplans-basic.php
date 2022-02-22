<?php 
        
    $isShortcode = (isset($isShortcode)) ? true : false ;

    if (!$isShortcode) {
        get_header();
    }

    require RENTPRESS_PLUGIN_DIR."/templates/FloorPlans/archive-floorplans-basic-data.php";

?>
<noscript>
    <?php include (RENTPRESS_PLUGIN_DIR."/templates/FloorPlans/archive-floorplans-basic-noscript.php"); ?>
</noscript>

<?php 
	if ($rentPressOptions->getOption('hide_floorplan_availability_counter') == true) : ?>
	    <style type="text/css">
	        .rp-num-avail {
	            display: none;
	        }
	    </style>
<?php endif;

	if (ae_detect_ie()) {
	    include (RENTPRESS_PLUGIN_DIR."/templates/FloorPlans/archive-floorplans-basic-noscript.php");
	} else {
	    include (RENTPRESS_PLUGIN_DIR."/templates/FloorPlans/archive-floorplans-basic-default.php");
	    if (!$isShortcode) {
	    	include RENTPRESS_PLUGIN_DIR . 'misc/template-schema/search-results-page-schema.php';
	    }
	}

?>

<noscript><div></noscript>

<?php if (!$isShortcode) {
        get_footer(); 
    }
?>