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

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for ShootingClubsModel.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ShootingClubsModelTest
extends TestCase
{
    /**
     * Attempt to create an object for a club that does not exist.
     *
     * @return void
     */
    public function testMissingClub() {
        $exception = $this->assertException(
            function () {
                new ShootingClubsModel('Not a club');
            }
        );
        $this->assertEquals(404, $exception->getCode());
    }

    /**
     * Try out an object for all the real club data.
     *
     * @return void
     */
    public function testRealClub() {
        $test_clubs = array();
        $dir = dirname(dirname(dirname(__DIR__))) . ShootingClubsModel::DATA_DIR;
        if (($dirhandle = opendir($dir)) !== false) {
            while (($file = readdir($dirhandle)) !== false) {
                if (is_file("$dir/$file")) {
                    $test_clubs[] = preg_replace('/\.json$/', '', $file);
                }
            }
        } else {
            $this->fail("Failed to get real club data.");
        }

        foreach ($test_clubs as $test_club) {
            $club = new ShootingClubsModel($test_club);
            $this->assertEquals($test_club, $club->name);
            $this->assertGreaterThan(52, $club->latitude);
            $this->assertLessThan(55, $club->latitude);
            $this->assertGreaterThan(-9, $club->longitude);
            $this->assertLessThan(-4, $club->longitude);
            $this->assertRegexp(
                '/^(\d+&deg; \d+\' \d+\.\d+"[NSEW] *){2}$/',
                $club->coordinates()
            );
        }
    }

    /**
     * Try creating an object with bad JSON as the data.
     *
     * @return void
     */
    public function testClubBadJSON() {
        $this->doTestBadData('{');
    }

    /**
     * Attempt to assign data to a non-existent property of ShootingClubsModel.
     *
     * @return void
     */
    public function testClubBadProperty() {
        $this->doTestBadData(
            array(
                'name' => 'Test Club',
                'latitude' => 53,
                'longitude' => -6,
                'does_not_exist' => 'foo'
            )
        );
    }

    /**
     * Try and create a club without supplying a latitude.
     *
     * @return void
     */
    public function testClubNoLatitude() {
        $this->doTestBadData(
            array(
                'name' => 'Test Club',
                'longitude' => -6,
            )
        );
    }

    /**
     * Try and create a club without supplying a longitude.
     *
     * @return void
     */
    public function testClubNoLongitude() {
        $this->doTestBadData(
            array(
                'name' => 'Test Club',
                'latitude' => 53,
            )
        );
    }

    /**
     * Further north than the north pole?
     *
     * @return void
     */
    public function testClubTooFarNorth() {
        $this->doTestBadData(
            array(
                'name' => 'Test Club',
                'latitude' => 91,
                'longitude' => 0
            )
        );
    }

    /**
     * Further south than the south pole?
     *
     * @return void
     */
    public function testClubTooFarSouth() {
        $this->doTestBadData(
            array(
                'name' => 'Test Club',
                'latitude' => -91,
                'longitude' => 0
            )
        );
    }

    /**
     * Further east than the antimeridian?
     *
     * @return void
     */
    public function testClubTooFarEast() {
        $this->doTestBadData(
            array(
                'name' => 'Test Club',
                'latitude' => 0,
                'longitude' => 181
            )
        );
    }

    /**
     * Further west than the antimeridian?
     *
     * @return void
     */
    public function testClubTooFarWest() {
        $this->doTestBadData(
            array(
                'name' => 'Test Club',
                'latitude' => 0,
                'longitude' => -181
            )
        );
    }

    /**
     * Do a basic sanity check on decimageToDegreesMinutesSeconds.
     *
     * @return void
     */
    public function testDMSBasic() {
        $this->assertEquals(
            array(53, 0, 0),
            ShootingClubsModel::decimalToDegreesMinutesSeconds(53)
        );
        $this->assertEquals(
            array(-7, 0, 0),
            ShootingClubsModel::decimalToDegreesMinutesSeconds(-7)
        );
    }

    /**
     * Test creating a ShootingClubsModel from bad data.
     *
     * @param mixed $data Either a JSON string or an object to be converted
     *                    into JSON.
     *
     * @return exception  The exception thrown.
     */
    private function doTestBadData($data) {
        list($data_dir, $name) = $this->fakeClub($data);
        return $this->assertException(
            function () use ($name, $data_dir) {
                new ShootingClubsModel($name, $data_dir);
            }
        );
    }

    /**
     * Create a fake club for testing purposes.
     *
     * @param mixed $data Either a JSON string or an object to be converted
     *                    into JSON.
     *
     * @return array      The first element is the directory containing the
     *                    fake club data file. The second element is the name
     *                    of the fake club.
     */
    private function fakeClub($data) {
        if (!is_string($data)) {
            $data = JSON::encode($data);
        }
        $this->cleanFakeClub();
        $file = sys_get_temp_dir() . '/Test Club.json';
        $this->assertFalse(file_exists($file));
        if (($filehandle = fopen($file, 'w')) !== false) {
            fwrite($filehandle, $data);
            fclose($filehandle);
        }
        $this->assertTrue(file_exists($file));
        return array(sys_get_temp_dir(), 'Test Club');
    }

    /**
     * Clean up after creating a fake club.
     *
     * @return void
     */
    private function cleanFakeClub() {
        unlink(sys_get_temp_dir() . '/Test Club.json');
    }
}

?>
