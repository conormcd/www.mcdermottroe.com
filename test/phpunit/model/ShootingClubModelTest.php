<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for ShootingClubModel.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ShootingClubModelTest
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
                new ShootingClubModel('Not a club');
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
        $dir = ShootingClubsModel::dataDir();
        $this->assertTrue(is_dir($dir));
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
            $club = new ShootingClubModel($test_club, $dir);
            $this->assertEquals($test_club, $club->name);
            $this->assertGreaterThan(52, $club->latitude);
            $this->assertLessThan(55, $club->latitude);
            $this->assertGreaterThan(-10, $club->longitude);
            $this->assertLessThan(-4, $club->longitude);
            $this->assertRegexp(
                '/^\d+&deg; \d+\' \d+\.\d+"[NS]$/',
                $club->latitudeDMS()
            );
            $this->assertRegexp(
                '/^\d+&deg; \d+\' \d+\.\d+"[EW]$/',
                $club->longitudeDMS()
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
     * Attempt to assign data to a non-existent property of ShootingClubModel.
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
            ShootingClubModel::decimalToDegreesMinutesSeconds(53)
        );
        $this->assertEquals(
            array(-7, 0, 0),
            ShootingClubModel::decimalToDegreesMinutesSeconds(-7)
        );
    }

    /**
     * Test creating a ShootingClubModel from bad data.
     *
     * @param mixed $data Either a JSON string or an object to be converted
     *                    into JSON.
     *
     * @return exception  The exception thrown.
     */
    private function doTestBadData($data) {
        list($data_dir, $name) = $this->createFakeClub($data);
        return $this->assertException(
            function () use ($name, $data_dir) {
                new ShootingClubModel($name, $data_dir);
            }
        );
    }

    /**
     * Get the details of a fake club to test with.
     *
     * @return array An associative array containing the directory containing
     *               the backing file ('dir'), the name of the club ('name')
     *               and the full path to the backing file ('file').
     */
    private function fakeClub() {
        $club['dir'] = sys_get_temp_dir();
        $club['name'] = 'Test Club';
        $club['file'] = sprintf('%s/%s.json', $club['dir'], $club['name']);
        return $club;
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
    private function createFakeClub($data) {
        if (!is_string($data)) {
            $data = JSON::encode($data);
        }
        $this->cleanFakeClub();

        $club = $this->fakeClub();
        $this->assertFalse(file_exists($club['file']));
        if (($filehandle = fopen($club['file'], 'w')) !== false) {
            fwrite($filehandle, $data);
            fclose($filehandle);
        }
        $this->assertTrue(file_exists($club['file']));

        return array($club['dir'], $club['name']);
    }

    /**
     * Clean up after creating a fake club.
     *
     * @return void
     */
    private function cleanFakeClub() {
        $club = $this->fakeClub();
        if (file_exists($club['file'])) {
            unlink($club['file']);
        }
    }
}

?>
