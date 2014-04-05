<?php

/**
 * A class to select a number of blog posts.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogModel
extends PageableModel
{
    /** The year of publication for the blog post(s). */
    public $year;

    /** The month of publication for the blog post(s). */
    public $month;

    /** The day of publication for the blog post(s). */
    public $day;

    /** The URL-sanitized form of the blog post title. */
    public $slug;

    /** The overall title of the blog. */
    public $title;

    /** A subtitle of the blog. Only really used in the feeds. */
    public $subtitle;

    /** The last updated time. */
    public $updated;

    /**
     * Initialize the selection.
     *
     * @param int    $year     The year of publication of the blog post(s). If
     *                         this is null, then all blog posts are included
     *                         in the set.
     * @param int    $month    The month of publication of the blog post(s).
     *                         If this is null, then any blog posts from the
     *                         year provided in the $year parameter above will
     *                         be included in the set.
     * @param int    $day      The day of publication of the blog post(s). If
     *                         this is null, then any blog posts from the year
     *                         and month provided in the other parameters will
     *                         be included in the set.
     * @param string $slug     The URL-sanitized form of the blog post title.
     *                         If this is null, then any blog post with the
     *                         year, month and day provided in the other
     *                         parameters will be included in the set.
     * @param int    $page     The number (1-based) of the page of blog posts.
     * @param int    $per_page The number of blog posts to display on the page.
     */
    public function __construct($year, $month, $day, $slug, $page, $per_page) {
        parent::__construct($page, $per_page);
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->slug = $slug;

        $this->title = 'Conor McDermottroe';
        $this->subtitle = 'This might be a blog some day.';
        $this->timestamp = 0;
        foreach ($this->entries() as $entry) {
            $this->timestamp = max($this->timestamp, $entry->timestamp());
        }
    }

    /**
     * Get the link to the Atom feed for this selection of blog posts.
     *
     * @return string The link to the Atom feed for this selection of posts.
     */
    public function atomLink() {
        return $this->link() . '/feed/atom/';
    }

    /**
     * The date of publication of the most recent blog post in ISO8601 format.
     *
     * @return string The date of publication of the most recent blog post in
     *                ISO8601 format.
     */
    public function dateISO8601() {
        return Time::dateISO8601($this->timestamp);
    }

    /**
     * The date of publication of the most recent blog post in RSS date format.
     *
     * @return string The date of publication of the most recent blog post in
     *                RSS date format.
     */
    public function dateRSS() {
        return Time::dateRSS($this->timestamp);
    }

    /**
     * Return the selected page of blog entries.
     *
     * @return array The selected blog posts as BlogEntryModel objects.
     */
    public function entries() {
        $entries = array();
        foreach ($this->page() as $file) {
            $entries[] = new BlogEntryModel($file);
        }
        return $entries;
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#link()}.
     */
    public function link() {
        return '/' . join(
            array_filter(
                array('blog', $this->year, $this->month, $this->day, $this->slug)
            )
        );
    }

    /**
     * Get the link to the RSS feed for this selection of blog posts.
     *
     * @return string The link to the RSS feed for this selection of posts.
     */
    public function rssLink() {
        return $this->link() . '/feed/rss/';
    }

    /**
     * Get a list of all the blog posts available.
     *
     * @return array The full paths to each of the files containing blog
     *               posts.
     */
    public function all() {
        $files = array();
        $dir = dirname(__DIR__) . '/data/blog';
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if (preg_match($this->pattern(), $file)) {
                    $path = $dir . '/' . $file;
                    $files[] = $path;
                }
            }
        }
        rsort($files);
        return $files;
    }

    /**
     * Trigger some caching.
     *
     * @return int How many seconds blog pages should be cached for.
     */
    public function ttl() {
        return 86400;
    }

    /**
     * Construct the pattern to match file names of blog posts.
     *
     * @return string A regular expression which can be used to match blog
     *                post file names.
     */
    private function pattern() {
        $pattern = '\.md$/';
        if ($this->slug) {
            $pattern = $this->slug . $pattern;
        } else {
            $pattern = '.*' . $pattern;
        }
        foreach (array($this->day, $this->month, $this->year) as $part) {
            if ($part) {
                $pattern = sprintf('%02d', $part) . '-' . $pattern;
            }
        }
        return "/^$pattern";
    }
}

?>
