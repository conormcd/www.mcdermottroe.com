<?php

/**
 * A class to represent a collection of ShootingClubModel objects.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ShootingClubsModel
extends Model
{
    /** The directory where all the club data is stored. */
    const DATA_DIR = '/data/shooting/clubs/';

    /** If we only want one club, this is the name of the club. */
    private $_club;

    /**
     * Initialize.
     *
     * @param string $club The name of the club to show, if any.
     */
    public function __construct($club = null) {
        $this->_club = $club !== 'All' ? $club : null;
    }

    /**
     * Get the full path to the directory where the data is.
     *
     * @return The full path to the data directory.
     */
    public static function dataDir() {
        return dirname(__DIR__) . self::DATA_DIR;
    }

    /**
     * Get the clubs represented by this collection.
     *
     * @return array An array of ShootingClubModel objects.
     */
    public function clubs() {
        $clubs = array();
        $data_dir = self::dataDir();
        if ($this->_club) {
            $clubs[] = new ShootingClubModel($this->_club, $data_dir);
        } else {
            if (($dirhandle = opendir($data_dir)) !== false) {
                while (($file = readdir($dirhandle)) !== false) {
                    if (is_file("$data_dir/$file")) {
                        $clubs[] = new ShootingClubModel(
                            preg_replace('/\.json$/', '', $file),
                            $data_dir
                        );
                    }
                }
            }
        }
        return $clubs;
    }
}

?>
