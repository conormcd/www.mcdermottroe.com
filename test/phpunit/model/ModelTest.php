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
     * Test that methods using the cache method from Model work as expected.
     *
     * @return void
     */
    public function testMethodWhichIsCached() {
        $testmodel = new TestModel();
        $this->assertEquals('Cached', $testmodel->methodWhichIsCached());
        $this->assertEquals('Cached', $testmodel->methodWhichIsCached());
    }

    /**
     * Test that uncached methods are unaffected by Model.
     *
     * @return void
     */
    public function testMethodWhichIsNotCached() {
        $testmodel = new TestModel();
        $this->assertEquals('Not Cached', $testmodel->methodWhichIsNotCached());
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
     * @return string The string "Cached".
     */
    public function methodWhichIsCached() {
        return $this->cache(
            'cached_method',
            1,
            function () {
                return 'Cached';
            }
        );
    }

    /**
     * A sample uncached method.
     *
     * @return string The string "Not Cached".
     */
    public function methodWhichIsNotCached() {
        return 'Not Cached';
    }
}

?>
