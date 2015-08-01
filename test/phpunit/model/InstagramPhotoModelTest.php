<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the InstagramPhotoModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class InstagramPhotoModelTest
extends ModelTestCase
{
    /**
     * Get a test copy of InstagramPhotoModel.
     *
     * @return InstagramPhotoModel An instance of InstagramPhotoModel which can
     *                             be tested.
     */
    public function createTestObject() {
        $instagram = FakeInstagram::getInstance();
        $photos = $instagram->getStream();
        return $photos[0];
    }
}

?>
