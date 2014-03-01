<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

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
        if ($string === false) {
            return self::errorHandler();
        }

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
