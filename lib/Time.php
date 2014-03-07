<?php

/*
 * Copyright (c) 2014, Conor McDermottroe
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

/**
 * A utility class for time operations.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Time {
    /**
     * Return the human-readable day format of the time.
     *
     * @param int $timestamp The UNIX epoch time to format.
     *
     * @return string A string representing the day portion of the timestamp
     *                passed in.
     */
    public static function day($timestamp) {
        return self::ordinalDay(date('j', $timestamp)) . date(' F Y', $timestamp);
    }

    /**
     * Return the ISO8601 string for the timestamp provided.
     *
     * @param int $timestamp The UNIX epoch time to format.
     *
     * @return string The passed-in timestamp in ISO8601 format.
     */
    public static function dateISO8601($timestamp) {
        return preg_replace('/\+00:00$/', 'Z', gmdate('c', $timestamp));
    }

    /**
     * Format a timestamp in the RSS standard format.
     *
     * @param int $timestamp The UNIX epoch time to format.
     *
     * @return string An RSS compatible timestamp.
     */
    public static function dateRSS($timestamp) {
        return date(DATE_RSS, $timestamp);
    }

    /**
     * Get the English ordinal form of a day number.
     *
     * @param int $day_number The day of the month.
     *
     * @return string The day of the month as a cardinal number. e.g. "1st" for
     *                1, etc.
     */
    private static function ordinalDay($day_number) {
        if (in_array($day_number, array(1, 21, 31))) {
            return $day_number . 'st';
        } else if (in_array($day_number, array(2, 22))) {
            return $day_number . 'nd';
        } else if (in_array($day_number, array(3, 23))) {
            return $day_number . 'rd';
        } else if ($day_number >= 4 && $day_number < 31) {
            return $day_number . 'th';
        } else {
            throw new Exception("Invalid day: $day_number");
        }
    }
}

?>
