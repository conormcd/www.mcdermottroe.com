<?php

if (!function_exists('json_last_error_msg')) {
    /**
     * Implement json_last_error_msg in versions of PHP which don't include it.
     *
     * @return string A message corresponding to the last error which was
     *                encountered while encoding/decoding JSON.
     */
    // @codingStandardsIgnoreStart
    function json_last_error_msg() {
        $error = json_last_error();
        foreach (get_defined_constants() as $name => $value) {
            if (preg_match('/^JSON_ERROR_/', $name)) {
                if ($error == $value) {
                    $error = $name;
                    break;
                }
            }
        }
        return "JSON error: $error";
    }
    // @codingStandardsIgnoreEnd
}

/**
 * A thin wrapper around the json_encode and json_decode functions in order to
 * provide slightly better error handling.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class JSON {
    /**
     * Encode an object into a JSON string.
     *
     * @param mixed $object The object to encode. This must be either an object
     *                      or an array.
     *
     * @return string The encoded version of the object.
     */
    public static function encode($object) {
        set_error_handler(array('JSON', 'errorHandler'));
        $string = json_encode($object);
        restore_error_handler();

        // The JSON spec says that the top level must be an array or object but
        // the PHP encoder is a bit more lenient. We reject any out-of-spec
        // values here.
        if (preg_match('/^[^{\[]/', $string)) {
            return self::errorHandler(
                "Bad input for encode, not an object or array"
            );
        }

        // I prefer to default to an empty object in the ambiguous case.
        $string = preg_replace('/\[\]/', '{}', $string);

        return $string;
    }

    /**
     * Decode a JSON string.
     *
     * @param string $string The string to decode.
     *
     * @return The object which had been encoded in the JSON string.
     */
    public static function decode($string) {
        set_error_handler(array('JSON', 'errorHandler'));
        $object = json_decode($string, true);
        restore_error_handler();

        if ($object === null) {
            $err = json_last_error();
            if ($err !== JSON_ERROR_NONE) {
                return self::errorHandler();
            }
        }

        return $object;
    }

    /**
     * A simple error handler to turn a PHP error into an exception.
     *
     * @param int    $errno  The internal PHP error code.
     * @param string $errstr The error message.
     *
     * @return void
     */
    private static function errorHandler($errno = null, $errstr = null) {
        if ($errno === null && $errstr === null) {
            $message = json_last_error_msg();
        } else if ($errstr === null) {
            $message = $errno;
        } else {
            $message = $errstr;
        }
        throw new Exception($message);
    }
}

?>
