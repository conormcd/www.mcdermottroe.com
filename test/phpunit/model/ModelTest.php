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
        $testmodel = new TestModel();
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
        $testmodel = new TestModel();
        $first = $testmodel->methodWhichIsNotCached();
        $second = $testmodel->methodWhichIsNotCached();
        $this->assertNotEquals($first, $second);
    }

    /**
     * Basic test for newRelicJSHeader().
     *
     * @return void
     */
    public function testNewRelicJSHeader() {
        $testmodel = new TestModel();
        $javascript = $testmodel->newRelicJSHeader();
        $this->assertNotNull($javascript);
        $this->assertTrue(is_string($javascript));
    }

    /**
     * Basic test for newRelicJSFooter().
     *
     * @return void
     */
    public function testNewRelicJSFooter() {
        $testmodel = new TestModel();
        $javascript = $testmodel->newRelicJSFooter();
        $this->assertNotNull($javascript);
        $this->assertTrue(is_string($javascript));
    }

    /**
     * Basic test for ttl().
     *
     * @return void
     */
    public function testTTL() {
        $testmodel = new TestModel();
        $ttl = $testmodel->ttl();
        $this->assertNotNull($ttl);
        $this->assertGreaterThanOrEqual(0, $ttl);
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
