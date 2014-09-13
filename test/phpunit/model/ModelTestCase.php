<?php

/**
 * Common tests for models.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class ModelTestCase
extends TestCase
{
    /**
     * Get an instance of the Model under test so that tests common to all
     * models may be performed on it.
     *
     * @return Model An instance of the model to be tested.
     */
    protected abstract function createTestObject();

    /**
     * Basic test for newRelicJSHeader().
     *
     * @return void
     */
    public function testNewRelicJSHeader() {
        $javascript = $this->createTestObject()->newRelicJSHeader();
        $this->assertNotNull($javascript);
        $this->assertTrue(is_string($javascript));
    }

    /**
     * Basic test for newRelicJSFooter().
     *
     * @return void
     */
    public function testNewRelicJSFooter() {
        $javascript = $this->createTestObject()->newRelicJSFooter();
        $this->assertNotNull($javascript);
        $this->assertTrue(is_string($javascript));
    }

    /**
     * Basic test for ttl().
     *
     * @return void
     */
    public function testTTL() {
        $ttl = $this->createTestObject()->ttl();
        $this->assertNotNull($ttl);
        $this->assertGreaterThanOrEqual(0, $ttl);
    }
}

?>
