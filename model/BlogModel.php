<?php

/**
 * A class to select a number of blog posts.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogModel
extends PageableModel
{
    /**
     * The year of publication for the blog post(s).
     */
    public $year;

    /**
     * The month of publication for the blog post(s).
     */
    public $month;

    /**
     * The day of publication for the blog post(s).
     */
    public $day;

    /**
     * The URL-sanitized form of the blog post title.
     */
    public $slug;

    /**
     * The last updated time.
     */
    public $updated;

    /**
     * A cache for the current page of blog entries.
     */
    private $_entries;

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

        $this->timestamp = 0;
        foreach ($this->entries() as $entry) {
            $this->timestamp = max($this->timestamp, $entry->timestamp());
        }

        $this->_metadata['og:title'] = array($this, 'title');
        $this->_metadata['og:type'] = 'website';
        $this->_metadata['og:url'] = array($this, 'link');
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
        if (!$this->_entries) {
            $this->_entries = array();
            foreach ($this->page() as $file) {
                $this->_entries[] = new BlogEntryModel($file);
            }
        }
        return $this->_entries;
    }

    /**
     * {@inheritdoc}
     *
     * @return array See {@link PageableModel#link()}.
     */
    public function link() {
        return '/' . join(
            '/',
            array_filter(
                array('blog', $this->year, $this->month, $this->day, $this->slug)
            )
        );
    }

    /**
     * Generate the metadata for this blog.
     *
     * @return array An array of arrays. Each of the inner arrays is an
     *               associative array and must contain a content key and
     *               either a name or a property key.
     */
    public function metadata() {
        if (count($this->entries()) == 1) {
            $entries = $this->entries();
            return $entries[0]->metadata();
        } else {
            return parent::metadata();
        }
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
        if (count($files) == 0) {
            throw new Exception('No such post', 404);
        }
        rsort($files);
        return $files;
    }

    /**
     * Description of this blog.
     *
     * @return string A description of the blog entries represented by this
     *                model.
     */
    public function description() {
        if (count($this->entries()) == 1) {
            $entries = $this->entries();
            return $entries[0]->description();
        }
        return $this->title();
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        $tags = '';
        foreach ($this->entries() as $entry) {
            $tags .= $entry->eTag();
        }
        return md5($tags);
    }

    /**
     * The last time the blog was updated.
     *
     * @return int The UNIX epoch time for the last time the blog was updated.
     */
    public function timestamp() {
        return $this->timestamp;
    }

    /**
     * Trigger some caching.
     *
     * @return int How many seconds blog pages should be cached for.
     */
    public function ttl() {
        return 86400 + rand(0, 3600);
    }

    /**
     * Construct a useful value for the page title.
     *
     * @return string The string that should be used for the page title.
     */
    public function title() {
        $entries = $this->entries();
        if (count($entries) == 1) {
            return $entries[0]->title();
        }

        $day = null;
        $month = null;
        $year = $this->year;
        if ($this->month) {
            $month = preg_replace('/^0*/', '', $this->month);
            $month = strftime("%B", mktime(0, 0, 0, $month, 15, $year));
        }
        if ($this->day) {
            $day = preg_replace('/^0*/', '', $this->day);
            $fmt = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
            $day = $fmt->format($day);
        }
        if ($day || $month || $year) {
            $title = "Blog posts by Conor McDermottroe from";
            if ($day) {
                return "$title the $day of $month $year";
            } else if ($month) {
                return "$title $month $year";
            } else {
                return "$title $year";
            }
        }

        return "Conor McDermottroe's blog";
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
