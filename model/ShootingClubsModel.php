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
