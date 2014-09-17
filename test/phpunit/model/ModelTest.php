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
     * Test that methods using the cache method from Model work as expected.
     *
     * @return void
     */
    public function testMethodWhichIsCached() {
        $testmodel = $this->createTestObject();
        $first = $testmodel->methodWhichIsCached();
        $second = $testmodel->methodWhichIsCached();
        $this->assertEquals($first, $second);
    }

    /**
     * Test that uncached methods are unaffected by Model.
     *
     * @return void
     */
    public function testMethodWhichIsNotCached() {
        $testmodel = $this->createTestObject();
        $first = $testmodel->methodWhichIsNotCached();
        $second = $testmodel->methodWhichIsNotCached();
        $this->assertNotEquals($first, $second);
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

    /**
     * A sample cached method.
     *
     * @return float The time with fractional microseconds when the method was
     *               called. This value will be cached for 10 seconds.
     */
    public function methodWhichIsCached() {
        return $this->cache(
            'cached_method',
            10,
            function () {
                return microtime(true);
            }
        );
    }

    /**
     * A sample uncached method.
     *
     * @return float The time with fractional microseconds when the method was
     *               called.
     */
    public function methodWhichIsNotCached() {
        return microtime(true);
    }
}

?>
