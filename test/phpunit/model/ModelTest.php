<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the Model abstract class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ModelTest
extends TestCase
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
