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
