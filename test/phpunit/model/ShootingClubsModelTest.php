<?php

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
     * Try out ShootingCLubsModel with no filtering of clubs.
     *
     * @return void
     */
    public function testWithNoFilter() {
        $model = new ShootingClubsModel();
        $this->assertGreaterThan(1, count($model->clubs()));
    }

    /**
     * Make sure that filtering by club name works.
     *
     * @return void
     */
    public function testWithAFilter() {
        $test_club = 'Dublin University Rifle Club';

        $model = new ShootingClubsModel($test_club);
        $clubs = $model->clubs();

        $this->assertEquals(1, count($clubs));
        $this->assertEquals($test_club, $clubs[0]->name);
    }

    /**
     * Confirm that the data directory exists.
     *
     * @return void
     */
    public function testDataDirectoryExists() {
        $this->assertTrue(is_dir(ShootingClubsModel::dataDir()));
    }
}

?>
