<?php

/**
 * A composite of all the content for the front page.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FrontPageModel
extends PageableModel
{
    /**
     * Initialise with the metadata
     *
     * @param int $page     The number of the page to select (1-based).
     * @param int $per_page The number of items to display per page.
     *
     * @return void
     */
    public function __construct($page = null, $per_page = null) {
        parent::__construct($page, $per_page);
        $this->_metadata['og:title'] = 'Conor McDermottroe';
        $this->_metadata['og:type'] = 'website';
    }
    /**
     * Return the selected page of entries.
     *
     * @return array A page of things for the front page of the site.
     */
    public function entries() {
        return $this->page();
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#link()}.
     */
    public function link() {
        return '/';
    }

    /**
     * Get a list of all the things that may go on the front page.
     *
     * @return array An array of mixed objects and arrays which have been
     *               returned from the various models representing the sources
     *               of data we fetch from.
     */
    public function all() {
        return Cache::run(
            'FRONT_PAGE_DATA',
            $this->ttl(),
            function () {
                $all = array();

                // Mix in the blog
                $blog = new BlogModel(null, null, null, null, 1, -1);
                foreach ($blog->entries() as $blog_entry) {
                    $all[$blog_entry->timestamp()] = $blog_entry;
                }

                // Mix in the photo albums
                $photos = new PhotosModel(null, 1, -1);
                foreach ($photos->albums() as $album) {
                    $all[$album->timestamp()] = $album;
                }

                krsort($all);
                $all = array_values($all);

                return $all;
            }
        );
    }

    /**
     * Metadata description of the front page.
     *
     * @return string
     */
    public function description() {
        return "The personal web pages of Conor McDermottroe.";
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        $tags = '';
        foreach ($this->entries() as $item) {
            $tags .= $item->eTag();
        }
        return md5($tags);
    }

    /**
     * The last time the front page was updated.
     *
     * @return int The UNIX epoch time for when the front page was last
     *             updated.
     */
    public function timestamp() {
        $max_timestamp = null;
        foreach ($this->page() as $item) {
            if ($max_timestamp !== null) {
                $max_timestamp = max($max_timestamp, $item->timestamp());
            } else {
                $max_timestamp = $item->timestamp();
            }
        }
        return $max_timestamp;
    }

    /**
     * Trigger some caching.
     *
     * @return int The maximum number of seconds pages using this model should
     *             be cached for.
     */
    public function ttl() {
        return 900;
    }
}

?>
