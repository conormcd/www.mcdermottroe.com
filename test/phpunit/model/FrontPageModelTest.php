<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the FrontPageModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FrontPageModelTest
extends PageableModelTestCase
{
    /**
     * Test FrontPageModel.entries
     *
     * @return void
     */
    public function testEntries() {
        $page = $this->createTestObject();
        $entries = $page->entries();
        $this->assertNotEmpty($entries);
    }

    /**
     * Test FrontPageModel.entries is cached.
     *
     * @return void
     */
    public function testEntriesCached() {
        $_ENV['CACHE_ENABLE'] = true;
        $page = $this->createTestObject();
        $first = $page->entries();
        $second = $page->entries();
        $this->assertEquals($first, $second);
        $_ENV['CACHE_ENABLE'] = false;
    }
}

?>
