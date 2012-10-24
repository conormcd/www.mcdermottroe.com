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

/**
 * Tests for the PageableModel abstract class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PageableModelTest
extends PageableModelTestCase
{
    /**
     * Proxy for the TestPageableModel constructor.
     *
     * @param int $page     The page number to fetch.
     * @param int $per_page The size of the page.
     *
     * @return object An instance of TestPageableModel.
     */
    public function createTestObject($page = null, $per_page = null) {
        return new TestPageableModel($page, $per_page);
    }

    /**
     * The largest page number which can be returned from a TestPageableModel.
     *
     * @return int The largest page number which is legit in a test on this 
     *             class.
     */
    public function maxPageNumber() {
        $model = new TestPageableModel();
        return count($model->all()) / PageableModel::DEFAULT_PER_PAGE;
    }
}

/**
 * A test implementation of PageableModel so that we can exercise it.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestPageableModel
extends PageableModel
{
    /** Fake link base. */
    const LINK = "http://this.is.a/link";

    /** The data to be paged through. */
    private $data;

    /**
     * See PageableModel.
     *
     * @param int $page     The page to represent.
     * @param int $per_page The size of pages.
     */
    public function __construct($page = null, $per_page = null) {
        parent::__construct($page, $per_page);
        $this->data = array();
        for ($i = 0; $i < 100; $i++) {
            $this->data[] = $i;
        }
    }

    /**
     * Get all the data.
     *
     * @return array All the data.
     */
    public function all() {
        return $this->data;
    }

    /**
     * Get the root of a link to the pages of data.
     *
     * @return string The root of the link to the pages.
     */
    public function link() {
        return self::LINK;
    }
}

?>
