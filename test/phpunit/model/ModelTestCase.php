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
     * Basic test for eTag().
     *
     * @return void
     */
    public function testETag() {
        $etag = $this->createTestObject()->eTag();
        $this->assertNotNull($etag);
        $this->assertGreaterThan(0, strlen($etag));
    }

    /**
     * Check that the ETag is stable.
     *
     * @return void
     */
    public function testETagStable() {
        $instance = $this->createTestObject();
        $first = $instance->eTag();
        $second = $instance->eTag();
        $this->assertEquals($first, $second);
    }

    /**
     * Run a basic check on the metadata.
     *
     * @return void
     */
    public function testMetadata() {
        $instance = $this->createTestObject();

        $required_keys = array(
            'og:description',
            'og:image',
            'og:title',
            'og:type',
            'og:url',
            'twitter:card',
            'twitter:creator',
            'twitter:site',
        );
        foreach ($required_keys as $key) {
            $this->assertHasMetadata($instance, $key);
        }
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

    /**
     * Assert that some specific metadata exists for an object.
     *
     * @param object $obj The object to test.
     * @param string $key The name of the metadata.
     * @param string $msg The assertion message.
     *
     * @return void
     */
    protected function assertHasMetadata($obj, $key, $msg = '') {
        $metadata = $obj->metadata();
        if ($msg === '') {
            $msg = "Metadata contains $key";
        }

        $result = false;
        foreach ($metadata as $item) {
            $item_name = null;
            if (array_key_exists('name', $item)) {
                $item_name = $item['name'];
            } else if (array_key_exists('property', $item)) {
                $item_name = $item['property'];
            } else {
                break;
            }

            if ($item_name === $key) {
                $item_value = null;
                if (array_key_exists('content', $item)) {
                    $item_value = $item['content'];
                }
                $result = ($item_value !== null && $item_value !== '');
            }
        }

        $this->assertTrue($result, $msg);
    }
}

?>
