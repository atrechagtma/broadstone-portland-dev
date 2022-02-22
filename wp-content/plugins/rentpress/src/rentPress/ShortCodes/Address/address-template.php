<?php 
?>

<p class="rp-address rp-address-<?php echo $propertyCode; ?>">
	<?php if ($showPropName == 'true') { ?>
    	<span id="rp-prop-title"><b><?php echo $propertyName; ?></b></span><br>
	<?php } ?>
    <span class="rp-property-address"><?php echo $currentProperty->address(); ?></span></br>
    <span><?php echo $currentProperty->city(); ?></span>,
    <span><?php echo $currentProperty->state(); ?>, <?php echo $currentProperty->zip(); ?></span><br>
    <?php if ($showMapLink == 'true') { ?>
    	<a id="single-prop-directions" href="https://www.google.com/maps/dir/<?php echo $googleMapAddress; ?>" target="_blank" rel="noreferrer noopener"><?php if ($showMapIcon == 'true') { ?><span class="rp-icon-map"></span><?php } ?> Directions</a>
	<?php } ?>
</p>
