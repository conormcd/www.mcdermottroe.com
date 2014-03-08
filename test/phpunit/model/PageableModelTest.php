<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

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
    private $_data;

    /**
     * See PageableModel.
     *
     * @param int $page     The page to represent.
     * @param int $per_page The size of pages.
     */
    public function __construct($page = null, $per_page = null) {
        parent::__construct($page, $per_page);
        $this->_data = array();
        for ($i = 0; $i < 100; $i++) {
            $this->_data[] = $i;
        }
    }

    /**
     * Get all the data.
     *
     * @return array All the data.
     */
    public function all() {
        return $this->_data;
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
