
<ul style="columns: 1;" class="rp-list rp-office-hours rp-office-hours-<?php echo $propertyCode; ?>"><?php
    if ($propertyImportSource == 'rent-cafe') :
        foreach ( $officeHours as $hours ) :
            if ( isset($hours->Iday) ) :
                if ($hours->Iday > 7) : ?>
                    <li><?php echo ($hours->Iday > 8)?"<b>Saturday - Sunday:</b> ":"<b>Monday - Friday</b>: "; ?>
                        <?php echo esc_html(date( 'g:i A', strtotime( $hours->StartTime ) )). " - "; ?>
                        <?php echo esc_html(date( 'g:i A', strtotime( $hours->EndTime ) )); ?></li><?php
                elseif ($hours->Iday < 8) : ?>
                    <li><?php echo "<b>".(dayByNumber($hours->Iday)).":</b> " .(date( 'g:i A', strtotime( $hours->StartTime ) )). " - " .(date( 'g:i A', strtotime( $hours->EndTime ) )); ?></li><?php
                endif; 
            endif;
        endforeach;
    elseif ($propertyImportSource == 'vaultware') :
        foreach (array_slice( $officeHours, 0, 7) as $hours ) :
            $dayName = "<b>".(date( 'l', strtotime(($hours->Day)))).":</b> ";
            if (is_numeric(strtotime($hours->OpenTime))) :
                $openTime = (date( 'g:i A', strtotime( $hours->OpenTime ) ));
            else: 
                $openTime = ( $hours->OpenTime );
            endif;
            if (is_numeric(strtotime($hours->CloseTime))) :
                $closeTime = " - " .(date( 'g:i A', strtotime( $hours->CloseTime ) ));
            else: 
                $closeTime = '';
            endif; ?>
            <li><?php echo $dayName.$openTime.$closeTime; ?></li><?php
        endforeach;
    elseif ($propertyImportSource == 'resman') :
        foreach ( $officeHours as $hours ) : ?>
            <li><?php echo "<b>".(date( 'l', strtotime(($hours->Day)))).":</b> " .(date( 'g:i A', strtotime( $hours->OpenTime ) )). " - " .(date( 'g:i A', strtotime( $hours->CloseTime ) ));?></li><?php
        endforeach;
    elseif ($propertyImportSource == 'encasa') : 
        foreach ( $officeHours as $hours ) : ;?>
            <li><?php echo "<b>".($hours->day).":</b> " .(date( 'g:i A', strtotime( $hours->openTime.':00' ) )). " - " .(date( 'g:i A', strtotime( $hours->closeTime.':00' ) ));?></li><?php
        endforeach;
    endif; ?>
</ul>