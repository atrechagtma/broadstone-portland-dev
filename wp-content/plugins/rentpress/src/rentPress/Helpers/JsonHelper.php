<?php 

/**
 * Helps us handle JSON information easier
 */
class rentPress_Helpers_JsonHelper {

    protected static $_messages = array(
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    );

    public static function encode($value, $options = 0) {
        $result = json_encode($value, $options);

        if ( $result )  return $result;

        return static::$_messages[json_last_error()];
    }

    public static function decode($json, $assoc = false) {
    	if ( $json == '' || ! isset($json) ) return [];

        $result = json_decode($json, $assoc);

        if ( $result || ( is_array($result) && count($result) == 0) ) return $result;

        return '[JSON Error]: '.static::$_messages[json_last_error()];
    }

}
