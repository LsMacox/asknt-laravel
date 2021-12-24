<?php


namespace App\Services\Wialon;



/**
 * Trait WialonError
 * wialon errorCode to textMessage converter
 * @package App\Services\Wialon
 */
trait WialonError {

    /**
     * List of error messages with codes
     * @var string[]
     */
    protected static $errors = array(
        1 => 'Invalid session',
        2 => 'Invalid service',
        3 => 'Invalid result',
        4 => 'Invalid input',
        5 => 'Error performing request',
        6 => 'Unknown error',
        7 => 'Access denied',
        8 => 'Invalid user name or password',
        9 => 'Authorization server is unavailable, please try again later',
        1001 => 'No message for selected interval',
        1002 => 'Item with such unique property already exists',
        1003 => 'Only one request of given time is allowed at the moment'
    );

    /**
     * Error message generator
     * @param string|integer $code
     * @param string $text
     * @return string
     */
    public static function error ($code = '', string $text = '') {
        $code = intval($code);

        if ( isset(self::$errors[$code]) ) {
            $text = self::$errors[$code].' '.$text;
        }

        $message = sprintf('%d: %s', $code, $text);

        return sprintf('WialonError( %s )', $message);
    }
}
