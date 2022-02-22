<?php if ($phoneIsLink == 'true') { ?>
	<a id="single-prop-phone" class="rp-phone-number rp-phone-number-<?php echo $propertyCode; ?>" href="<?php echo $displayedPhoneNumberUri; ?>"><?php
} if ($showPhoneIcon == 'true') { ?>
	<span class="rp-phone-number rp-phone-number-<?php echo $propertyCode; ?>"><span class="rp-icon-phone"><?php } ?><?php echo $displayedPhoneNumber; ?></span></span><?php
if ($phoneIsLink == 'true') { ?></a><?php } ?>