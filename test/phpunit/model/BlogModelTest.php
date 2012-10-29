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

require_once __DIR__ . '/PageableModelTestCase.php';

require_once dirname(dirname(dirname(__DIR__))) . '/lib/Cache.php';
require_once dirname(dirname(dirname(__DIR__))) . '/model/Model.php';
require_once dirname(dirname(dirname(__DIR__))) . '/model/PageableModel.php';
require_once dirname(dirname(dirname(__DIR__))) . '/model/BlogModel.php';
require_once dirname(dirname(dirname(__DIR__))) . '/model/BlogEntryModel.php';

/**
 * Tests for the BlogModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogModelTest
extends PageableModelTestCase
{
    /**
     * Proxy for the BlogModel constructor.
     *
     * @param int $page     The page number to fetch.
     * @param int $per_page The size of the page.
     * @param int $year     The year value for the BlogModel constructor.
     * @param int $month    The month value for the BlogModel constructor.
     * @param int $day      The day value for the BlogModel constructor.
     * @param int $slug     The slug value for the BlogModel constructor.
     *
     * @return object An instance of BlogModel.
     */
    public function createTestObject(
        $page = null,
        $per_page = null,
        $year = null,
        $month = null,
        $day = null,
        $slug = null
    ) {
        return new BlogModel($year, $month, $day, $slug, $page, $per_page);
    }

    /**
     * Test the BlogModel when only the year is specified.
     *
     * @return void
     */
    public function testYearOnly() {
        $blog = new BlogModel(2009, null, null, null, null, -1);
        $this->assertGreaterThan(0, $blog->entries());
        foreach ($blog->entries() as $post) {
            $this->assertEquals(2009, date('Y', $post->timestamp()));
        }
    }

    /**
     * Test the BlogModel when only the year and month specified.
     *
     * @return void
     */
    public function testMonth() {
        $blog = new BlogModel(2009, 3, null, null, null, -1);
        $this->assertGreaterThan(0, $blog->entries());
        foreach ($blog->entries() as $post) {
            $this->assertEquals('2009-03', date('Y-m', $post->timestamp()));
        }
    }

    /**
     * Test the BlogModel when the year, month and day are specified but the
     * slug is left null.
     *
     * @return void
     */
    public function testDay() {
        $blog = new BlogModel(2009, 3, 10, null, null, -1);
        $this->assertGreaterThan(0, $blog->entries());
        foreach ($blog->entries() as $post) {
            $this->assertEquals('2009-03-10', date('Y-m-d', $post->timestamp()));
        }
    }

    /**
     * Test the BlogModel with a year, month, day and slug.
     *
     * @return void
     */
    public function testSlug() {
        $blog = new BlogModel(2009, 3, 10, 'blogging', null, -1);
        $this->assertGreaterThan(0, $blog->entries());
        foreach ($blog->entries() as $post) {
            $this->assertEquals('2009-03-10', date('Y-m-d', $post->timestamp()));
            $this->assertEquals('Blogging', $post->title());
        }
    }

    /**
     * Ensure that the results of dateISO8601 calls return dates in the correct
     * pattern.
     *
     * @return void
     */
    public function testISO8601Dates() {
        $blog = new BlogModel(null, null, null, null, null, -1);
        $this->assertRegexp(
            '/\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\d(?:Z|[+-]\d\d\d\d)/',
            $blog->dateISO8601()
        );
    }

    /**
     * Ensure that the results of dateRSS calls return dates in the correct
     * pattern.
     *
     * @return void
     */
    public function testRSSDates() {
        $blog = new BlogModel(null, null, null, null, null, -1);
        $this->assertRegexp(
            '/\w\w\w, \d\d \w\w\w \d\d\d\d \d\d:\d\d:\d\d [+-]\d\d\d\d/',
            $blog->dateRSS()
        );
    }

    /**
     * Make sure that links from atomLink include /feed/atom/ at the end of
     * their return values.
     *
     * @return void
     */
    public function testAtomLink() {
        $blog = new BlogModel(null, null, null, null, null, null);
        $this->assertRegexp('#/feed/atom/$#', $blog->atomLink());
    }

    /**
     * Make sure that links from rssLink include /feed/rss/ at the end of their
     * return values.
     *
     * @return void
     */
    public function testRSSLink() {
        $blog = new BlogModel(null, null, null, null, null, null);
        $this->assertRegexp('#/feed/rss/$#', $blog->rssLink());
    }
}

?>
