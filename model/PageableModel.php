<?php

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

    /**
     * The number of the page to fetch, this is 1-indexed.
     */
    public $page;

    /**
     * The number of entries to show per page.
     */
    public $per_page;

    /**
     * Initialize a PageableModel.
     *
     * @param int $page     The number of the page to select (1-based).
     * @param int $per_page The number of items to display per page.
     */
    public function __construct($page = null, $per_page = null) {
        parent::__construct();
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
     * What "next" means in the context of the given model.
     *
     * @return string A word or short phrase that accurately describes what
     *                clicking the "next" link will do.
     */
    public function nextLabel() {
        return "Older";
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
     * What "previous" means in the context of the given model.
     *
     * @return string A word or short phrase that accurately describes what
     *                clicking the "previous" link will do.
     */
    public function previousLabel() {
        return "Newer";
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
