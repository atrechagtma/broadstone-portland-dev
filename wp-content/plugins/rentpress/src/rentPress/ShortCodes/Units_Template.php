<div class="shortcode-units">
	<?php if (count($queriedUnits) == 0) : ?>

		<div class="no-units-response">
			<div class="no-units-response-message">
				<p><b>There are currently no available apartments for this floor plan.</b><br />
				Please visit the property page for <a href="<?php echo $propertyPostLink ?>"><?php echo $propertyTitle; ?></a> to view other floor plans, or <a href="/search/">visit the property search page</a> to browse other property listings.</p>
			</div>
			<div class='rp-single-no-units-wrapper'>
				<h4 class='rp-single-no-units-headline'>No Apartments Available</h4>
				<p class='rp-single-no-units-text'>This is one of our most popular layouts. No apartments are currently available. You can browse our <a class='rp-primary-accent' href='/floorplans/'>other options</a>.</p>
				<br>
			</div>
		</div>

	<?php else : ?>

		<div class="unit-table-wrap">
			<table class="stack units-table">
				<thead>
					<tr>
						<th>Unit</th>
						<?php if (($globalPriceDisable == 'true' ) || ($disabledPricing == 'true')) : else: ?>
						<th>Price / Mo</th>
						<?php endif; ?>
						<th>SQ. FT.</th>
						<th>Availablity</th>
						<th>Contact</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($queriedUnits as $unit) : $Unit = $rentPress_Service['unit_meta']->fromUnit($unit);

						// set the request info link destination
			            if ( ($override_request_link !== 'true' ) || ($requestURL == '') ):
		                    $requestMoreInfoUrl=get_site_url().'/contact/'.'?unit='. $unit->Information->Name .'&property_code='. $propertyCode;;
			            else:
			                $requestMoreInfoUrl=$requestURL;
			            endif;

			            if ($globalApplyOverride) : //check for global apply url
			            	$applyLink = $globalApplyOverride;
			            elseif (($propertyApplyOverrride !=='') && ($propertyApplyLinkIsOverridden == true)) : //use property apply link if set
			            	$applyLink = $propertyApplyOverrrideURL;
		            	else: //use the unit apply link
		            		$applyLink = $unit->Information->AvailabilityURL;
			            endif;

			            ?>

						<tr data-unit-code="<?php echo $unit->Identification->UnitCode; ?>" data-unit-available-on="<?php echo $unit->Information->AvailableOn; ?>">
							<td><?php echo $unit->Information->Name; ?></td>
							<?php if (($globalPriceDisable == 'true' ) || ($disabledPricing == 'true')) : else: ?>
							<td 
								data-is-price
								data-defualt-rent="<?php echo $Unit->rent(); ?>"
								data-rent-terms="<?php echo esc_attr(json_encode((array)$unit->Rent->TermRent)); ?>">
								<?php echo '$'.$Unit->rent(); ?>
							</td>
							<?php endif; ?>
							<td><?php echo $unit->SquareFeet->Max; ?></td>
							<td>
								<?php 
									if ($unit->Information->isAvailable) {
										echo "Available Now";
									}
									else {
										echo "Available ". $unit->Information->AvailableOn;
									}
								?>	
							</td>
				      		<td>
				      			<?php if ($show_waitlist_ctas == true ) {
				      			}?>
				      			<a href="<?php echo esc_url($requestMoreInfoUrl);?>" class="button">Request Info</a> | 
				      			<a href="<?php echo $applyLink; ?>" class="button alt-btn">Apply Now <i class="fa fa-angle-double-right"></i></a>
				      		</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	<?php endif; ?>

</div>