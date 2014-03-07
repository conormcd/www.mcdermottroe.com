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

/**
 * An abstract model wrapping paging functionality for other models.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class PageableModel
extends Model
{
    /**
     * The default number of entries per page if the implementing model doesn't
     * override it.
     */
    const DEFAULT_PER_PAGE = 5;

    /** The number of the page to fetch, this is 1-indexed. */
    public $page;

    /** The number of entries to show per page. */
    public $per_page;

    /**
     * Initialize a PageableModel.
     *
     * @param int $page     The number of the page to select (1-based).
     * @param int $per_page The number of items to display per page.
     */
    public function __construct($page = null, $per_page = null) {
        $this->page = $page ? intval($page) : 1;
        $this->per_page = $per_page ? intval($per_page) : $this->getDefaultPerPage();
        if ($this->page < 1) {
            throw new Exception("Bad page number.", 404);
        }
    }

    /**
     * Return the specified page of results.
     *
     * @return array The page of results described by the constructor
     *               parameters.
     */
    public function page() {
        if ($this->page > $this->numPages()) {
            throw new Exception(
                sprintf(
                    "Bad page number (%d of %d)",
                    $this->page,
                    $this->numPages()
                ),
                404
            );
        }
        if ($this->per_page > 0) {
            $min = ($this->page - 1) * $this->per_page;
            $min = max(0, $min);
            return array_slice($this->all(), $min, $this->per_page);
        } else {
            return $this->all();
        }
    }

    /**
     * A link to the next page of results, if applicable.
     *
     * @return string A link which, if fetched, will return the next page of
     *                results.
     */
    public function next() {
        $link = "";
        if ($this->page < $this->numPages()) {
            $link = $this->link() . '?page=' . ($this->page + 1);
            if ($this->per_page !== $this->getDefaultPerPage()) {
                $link .= "&per_page={$this->per_page}";
            }
        }
        return $link;
    }

    /**
     * A link to the previous page of results, if applicable.
     *
     * @return string A link which, if fetched, will return the previous page
     *                of results.
     */
    public function previous() {
        $link = "";
        if ($this->page > 1) {
            if ($this->page != 2) {
                $link .= '?page=' . ($this->page - 1);
            }
            if ($this->per_page !== $this->getDefaultPerPage()) {
                $link .= $link ? '&' : '?';
                $link .= "per_page={$this->per_page}";
            }
            $link = $this->link() . $link;
        }
        return $link;
    }

    /**
     * The number of pages of data availble.
     *
     * @return The highest valid page number.
     */
    public function numPages() {
        if ($this->per_page > 0) {
            return ceil(count($this->all()) / $this->per_page);
        } else {
            return 1;
        }
    }

    /**
     * The default number of items per page for this model.
     *
     * @return int The default number of items per page for this model.
     */
    public function getDefaultPerPage() {
        return self::DEFAULT_PER_PAGE;
    }

    /**
     * Fetch all the data for the items requested. This data will then be paged
     * using {@link #page}.
     *
     * @return array The data to page through.
     */
    public abstract function all();

    /**
     * Construct the base of the link to a page of data, but without the paging
     * parameters.
     *
     * @return string The link, minus the paging data.
     */
    protected abstract function link();
}

?>
