<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * A common set of tests for classes which inherit from PageableModel.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class PageableModelTestCase
extends TestCase
{
    /**
     * Proxy for the constructor for the class being tested.
     *
     * @param int $page     The page number to fetch.
     * @param int $per_page The size of the page.
     *
     * @return object       The object to run tests against.
     */
    protected abstract function createTestObject($page = null, $per_page = null);

    /**
     * Check that the default page is the first page of results.
     *
     * @return void
     */
    public function testDefaultPageIsFirstPage() {
        $page = $this->createTestObject();
        $this->assertEquals(1, $page->page);
        $this->assertEquals($page->getDefaultPerPage(), $page->per_page);
        $this->assertLessThanOrEqual(
            $page->getDefaultPerPage(),
            count($page->page())
        );
    }

    /**
     * Make sure that the first page gets a next link.
     *
     * @return void
     */
    public function testNextIsNotEmptyForFirstPage() {
        $page = $this->createTestObject();
        $this->assertNotEmpty($page->next());
    }

    /**
     * Make sure that the first page does not get a previous link.
     *
     * @return void
     */
    public function testPreviousIsEmptyForFirstPage() {
        $page = $this->createTestObject();
        $this->assertEmpty($page->previous());
    }

    /**
     * Test that we can create the last page.
     *
     * @return void
     */
    public function testLastPage() {
        $page = $this->createTestObject();
        $page = $this->createTestObject($page->numPages());
        $this->assertEquals($page->numPages(), $page->page);
        $this->assertEquals($page->getDefaultPerPage(), $page->per_page);
        $this->assertLessThanOrEqual(
            $page->getDefaultPerPage(),
            count($page->page())
        );
    }

    /**
     * Ensure that the last page does not have a next link.
     *
     * @return void
     */
    public function testNextIsEmptyForLastPage() {
        $page = $this->createTestObject();
        $page = $this->createTestObject($page->numPages());
        $this->assertEmpty($page->next());
    }

    /**
     * Ensure that the last page has a previous link.
     *
     * @return void
     */
    public function testPreviousIsNotEmptyForLastPage() {
        $page = $this->createTestObject();
        $page = $this->createTestObject($page->numPages());
        $this->assertNotEmpty($page->previous());
    }

    /**
     * Check that if we disable paging by setting the page size to a negative
     * number then all the results are returned as the only page.
     *
     * @return void
     */
    public function testDisablePagingReturnsAllResults() {
        $page = $this->createTestObject(null, -1);
        $this->assertEquals($page->all(), $page->page());
    }

    /**
     * Try a non-default page size and make sure it includes that page size in
     * the next and previous links.
     *
     * @return void
     */
    public function testNonDefaultPageSize() {
        $page = $this->createTestObject(2, 3);
        $this->assertEquals(2, $page->page);
        $this->assertEquals(3, $page->per_page);
        $this->assertEquals(3, count($page->page()));
        $this->assertRegexp('/per_page=3/', $page->next());
        $this->assertRegexp('/per_page=3/', $page->previous());
    }

    /**
     * Make sure that negative page numbers cause a 404.
     *
     * @return void
     */
    public function testNegativePageNumber() {
        $exception = $this->assertException(
            array($this, 'createTestObject'),
            array(-1)
        );
        $this->assertEquals(404, $exception->getCode());
    }

    /**
     * Make sure that a page number greater than the max page number causes a
     * 404.
     *
     * @return void
     */
    public function testTooLargePageNumber() {
        $too_large = $this->createTestObject()->numPages() + 1;
        $exception = $this->assertException(
            function() use ($too_large) {
                $page = $this->createTestObject($too_large);
                $page->page();
            }
        );
        $this->assertEquals(404, $exception->getCode());
    }
}

?>
