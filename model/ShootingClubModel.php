<?php

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
     * The address of the club/range as an associative array where the keys are
     * hCard field names and the values are the corresponding hCard values.
     */
    public $address;

    /** The URL of the website for the club/range, if any. */
    public $url;

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
     * The latitude of the club/range in degrees, minutes and seconds.
     *
     * @return string The latitude of the club/range in degrees, minutes and
     *                seconds.
     */
    public function latitudeDMS() {
        return self::formatDMS(
            self::decimalToDegreesMinutesSeconds($this->latitude),
            array('S', 'N')
        );
    }

    /**
     * The longitude of the club/range in degrees, minutes and seconds.
     *
     * @return string The longitude of the club/range in degrees, minutes and
     *                seconds.
     */
    public function longitudeDMS() {
        return self::formatDMS(
            self::decimalToDegreesMinutesSeconds($this->longitude),
            array('W', 'E')
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

    /**
     * Format the result of decimalToDegreesMinutesSeconds as a human-readable
     * string.
     *
     * @param array $dms        The result from a call to
     *                          decimalToDegreesMinutesSeconds.
     * @param array $directions A two element array, either array('S', 'N') or
     *                          array('W', 'E').
     *
     * @return string           A string showing the degrees, minutes and
     *                          seconds form of the data produced by the call
     *                          to decimalToDegreesMinutesSeconds.
     */
    public static function formatDMS($dms, $directions) {
        return sprintf(
            "%d&deg; %d' %.2f\"%s",
            abs($dms[0]),
            $dms[1],
            $dms[2],
            $dms[0] < 0 ? $directions[0] : $directions[1]
        );
    }
}

?>
