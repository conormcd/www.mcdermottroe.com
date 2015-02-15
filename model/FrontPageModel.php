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

                // Mix in the photos from Instagram
                $instagram = Instagram::getInstance();
                foreach ($instagram->getStream() as $photo) {
                    $all[$photo['timestamp']] = $photo;
                }

                krsort($all);
                $all = array_values($all);

                return $all;
            }
        );
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        $tags = '';
        foreach ($this->entries() as $item) {
            if (method_exists($item, 'eTag')) {
                $tags .= $item->eTag();
            } else {
                $tags .= var_export($item, true);
            }
        }
        return md5($tags);
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
