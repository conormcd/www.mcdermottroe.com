<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the Model abstract class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ModelTest
extends ModelTestCase
{
    /**
     * Turn on caching for these tests.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $_ENV['CACHE_ENABLE'] = true;
    }

    /**
     * Get an instance of TestModel to test.
     *
     * @return TestModel An instance of TestModel.
     */
    protected function createTestObject() {
        return new TestModel();
    }
}

/**
 * A test implementation of Model so that we can exercise it.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestModel
extends Model
{
    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return 'deadbeef';
    }
}

?>
