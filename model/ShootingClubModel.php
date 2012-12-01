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

/**
 * Wrap a shooting club.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ShootingClubModel
extends Model
{
    /** The name of the club. */
    public $name;

    /**
     * The latitude portion of the coordinates for either the entrance or main
     * building of the club or range.
     */
    public $latitude;

    /**
     * The longitude portion of the coordinates for either the entrance or main
     * building of the club or range.
     */
    public $longitude;

    /**
     * Initialize.
     *
     * @param string $name     The name of the club.
     * @param string $data_dir An alternative to the default directory in which
     *                         the data is stored.
     */
    public function __construct($name, $data_dir = null) {
        $this->name = $name;
        if ($data_dir === null) {
            $data_dir = ShootingClubsModel::dataDir();
        }

        $this->initFromJSON($name, $data_dir);

        // Validate the data
        if (!isset($this->latitude)) {
            throw new Exception("No latitude found for $name");
        } else if ($this->latitude < -90 || $this->latitude > 90) {
            throw new Exception("Invalid latitude: {$this->latitude}");
        }
        if (!isset($this->longitude)) {
            throw new Exception("No longitude found for $name");
        } else if ($this->longitude < -90 || $this->longitude > 90) {
            throw new Exception("Invalid longitude: {$this->longitude}");
        }
    }

    /**
     * Get the human readable form of the coordinates.
     *
     * @return string A string like "53&deg; 1' 2\"N 6&deg; 3' 4\"W"
     */
    public function coordinates() {
        $lat = self::decimalToDegreesMinutesSeconds($this->latitude);
        $long = self::decimalToDegreesMinutesSeconds($this->longitude);

        return sprintf(
            "%d&deg; %d' %.2f\"%s %d&deg; %d' %.2f\"%s",
            abs($lat[0]),
            $lat[1],
            $lat[2],
            $lat[0] < 0 ? 'S' : 'N',
            abs($long[0]),
            $long[1],
            $long[2],
            $long[0] < 0 ? 'W' : 'E'
        );
    }

    /**
     * Initialise this object from a JSON file.
     *
     * @param string $name     The name of the club.
     * @param string $data_dir An alternative to the default directory in which
     *                         the data is stored.
     *
     * @return void
     */
    private function initFromJSON($name, $data_dir) {
        $file = "$data_dir/$name.json";
        if (file_exists($file)) {
            $properties = JSON::decode(file_get_contents($file));
            foreach ($properties as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                } else {
                    throw new Exception("No such property: $property");
                }
            }
        } else {
            throw new Exception("No such club: $name", 404);
        }
    }

    /**
     * Split a decimal number of degrees into degrees, minutes and seconds.
     *
     * @param float $lat_or_long A latitude or longitude in decimal form.
     *
     * @return array A three element array where the elements are the degrees,
     *               minutes and seconds of the value passed in. The minutes
     *               and seconds will always be positive and the degrees value
     *               will have the same sign as the value passed in.
     */
    public static function decimalToDegreesMinutesSeconds($lat_or_long) {
        $degrees = (int)$lat_or_long;
        $minutes_portion = ($lat_or_long - $degrees) * 60;
        $minutes = (int)$minutes_portion;
        $seconds = ($minutes_portion - $minutes) * 60;

        return array($degrees, abs($minutes), abs($seconds));
    }
}

?>