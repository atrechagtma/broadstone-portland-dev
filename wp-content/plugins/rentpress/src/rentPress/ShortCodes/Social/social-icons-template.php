<p class="rp-single-prop-share-social">
    <?php if ($propertyFacebook != '') { ?>
        <a id="rp-facebook" href="<?php echo $propertyFacebook; ?>" target="_blank" rel="noreferrer noopener"><span class="rp-icon-facebook"></span><span <?php echo $nameStyles; ?>> Facebook</span></a>
    <?php } if ($propertyTwitter != '') { ?>
        <a id="rp-twitter" href="<?php echo $propertyTwitter; ?>" target="_blank" rel="noreferrer noopener"><span class="rp-icon-twitter"></span><span <?php echo $nameStyles; ?>> Twitter</a>
    <?php } if ($propertyInstagram != '') { ?>
        <a id="rp-instagram" href="<?php echo $propertyInstagram; ?>" target="_blank" rel="noreferrer noopener"><span class="rp-icon-instagram"></span><span <?php echo $nameStyles; ?>> Instagram</span></a>
    <?php } ?>
</p>