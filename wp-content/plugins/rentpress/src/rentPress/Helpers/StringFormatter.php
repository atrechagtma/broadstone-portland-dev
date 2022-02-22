<?php
/**
 * RentPress String Formatter
 *
 * Helps us format strings. If we had '12.00' and wanted '$12' we would make a method here for that, or
 * if we wanted to go to upper or lower case, truncate, append ellipses, you get the idea.
 */

class rentPress_Helpers_StringFormatter
{

  /**
  * Removes any decimal points from a string if their values are zero
  * @return string
  */
  public function removeDecimalPointsIfZeroValues($value, $replaceThis = '.00', $withThis = '', $decimalPoints = 2, $decimalRepresentation = ".", $thousandsSeperator = ",")
  {
    $log = new rentPress_Logging_Log();
    // check received value
    if ( !is_string($value) ) {
      return $log->scream()->error('[StringFormatter Error] Value provided is not a string.');
    }

    return str_replace(
      $replaceThis,
      $withThis,
      number_format(
        $value,
        $decimalPoints,
        $decimalRepresentation,
        $thousandsSeperator
      )
    );
  }
  
  /**
  * This method formats phone number strings
  *
  * @param phone    A string of numbers that is either ten or eleven digits
  * @param style    A style character that you would like to separate the digits with
  * @return string  It will return the formatted phone number
  */
  public static function formatPhoneNumber($phoneNumber, $style = '')
  {
    $log = new rentPress_Logging_Log();

    // Capture phone number length
    $numberLength = strlen($phoneNumber);

    // If the number is available and if it is in supported United States number lengths
    if ( isset($phoneNumber) && in_array($numberLength, [10, 11])) {

      switch (strlen($phoneNumber)) {
        case 10:
          if($style==''){
              return '('.substr($phoneNumber, 0, 3).') '.substr($phoneNumber, 3, 3).'-'.substr($phoneNumber,6);
          } else {
              return substr($phoneNumber, 0, 3).$style.substr($phoneNumber, 3, 3).$style.substr($phoneNumber,6);
          }
          
        default:
          if($style==''){
              return '+'.substr($phoneNumber, 0, 1).' ('.substr($phoneNumber, 1, 3).') '.substr($phoneNumber, 4, 3).'-'.substr($phoneNumber,7);
          } else {
              return substr($phoneNumber, 0, 1).$style.substr($phoneNumber, 1, 3).$style.substr($phoneNumber, 4, 3).$style.substr($phoneNumber,7);
          }
      } // end switch case

    } // end if statement
    
    $log->warning('Could not reformat phone number '. $phoneNumber .' because it is of invalid format. The number needs to be cleansed of all special characters.');

    return $phoneNumber;
  }

}
