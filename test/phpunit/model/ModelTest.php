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
     * The description of the model.
     *
     * @return string The description.
     */
    public function description() {
        return 'The description of the model.';
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return 'deadbeef';
    }

    /**
     * Generate the metadata for a static file.
     *
     * @return array See Model#metadata.
     */
    public function metadata() {
        $data = parent::metadata();
        $data[] = array('property' => 'og:title', 'content' => 'Test model');
        $data[] = array('property' => 'og:type', 'content' => 'website');
        $data[] = array('property' => 'og:url', 'content' => '/test_model');
        $data[] = array('name' => 'twitter:card', 'content' => 'summary');
        return $data;
    }
}

?>
