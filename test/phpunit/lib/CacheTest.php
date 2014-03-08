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
     * Ensure that fetching a key that doesn't exist returns null.
     *
     * @return void
     */
    public function testGetBadKeyReturnsNull() {
        if (!function_exists('apc_store')) {
            $this->markTestIncomplete('APC is not installed/configured.');
        }
        $this->assertNull(Cache::get('this-does-not-exist'));
    }

    /**
     * Make sure that a key and value round-trips.
     *
     * @return void
     */
    public function testGetGoodKeyReturnsCorrectValue() {
        if (!function_exists('apc_store')) {
            $this->markTestIncomplete('APC is not installed/configured.');
        }
        Cache::set('test-key', 'test-value', 1);
        $this->assertEquals('test-value', Cache::get('test-key'));
    }
}

?>
