<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for Cache.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CacheTest
extends TestCase
{
    /**
     * Turn on caching for this test suite.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $_ENV['CACHE_ENABLE'] = true;
    }

    /**
     * Ensure that fetching a key that doesn't exist returns null.
     *
     * @return void
     */
    public function testGetBadKeyReturnsNull() {
        $this->assertNull(Cache::get('this-does-not-exist'));
    }

    /**
     * Make sure that a key and value round-trips.
     *
     * @return void
     */
    public function testGetGoodKeyReturnsCorrectValue() {
        Cache::set('test-key', 'test-value', 1);
        $this->assertEquals('test-value', Cache::get('test-key'));
    }
}

?>
