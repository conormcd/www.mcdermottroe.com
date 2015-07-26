<?php

/**
 * Wrap a blog entry.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogEntryModel
extends Model
{
    /**
     * The path to the file containing the blog post.
     */
    private $_file;

    /**
     * The direct link to this blog post.
     */
    public $link;

    /**
     * A local cache for the markdown content in the file.
     */
    private $_markdown;

    /**
     * Initialize.
     *
     * @param string $blog_markdown_file The full path to the file containing
     *                                   the blog post.
     */
    public function __construct($blog_markdown_file) {
        if (!file_exists($blog_markdown_file)) {
            throw new Exception(
                "File does not exist: $blog_markdown_file",
                404
            );
        }
        $this->_file = $blog_markdown_file;
        $this->link = preg_replace(
            '#^(\d{4})-(\d\d)-(\d\d)-(.*?)\.md$#',
            '/blog/$1/$2/$3/$4/',
            basename($this->_file)
        );

        parent::__construct();

        $this->_metadata['og:type'] = 'article';
        $this->_metadata['og:title'] = array($this, 'title');
        $this->_metadata['og:url'] = $this->link;
        $this->_metadata['article:author']
            = 'https://www.facebook.com/conor.mcdermottroe';
        $this->_metadata['article:published_time'] = array($this, 'updatedTime');
        $this->_metadata['article:modified_time'] = array($this, 'updatedTime');
    }

    /**
     * The body of the blog post.
     *
     * @return string The rendered HTML for the body of the blog post.
     */
    public function body() {
        $replacements = array(
            '#<h1>.*?</h1>#s' => '',
            '#{{amazonlink:(.*?)}}#s' => array('AmazonAffiliate', 'link'),
            '#{{amazonbug:(.*?)}}#s' => array('AmazonAffiliate', 'bug'),
            '#{{youtube:(.*?)}}#s' => array('YouTubeEmbed', 'embed'),
            '#<pre><code>{{lang:(.*?)}}(.*?)</code></pre>#s' => array(
                'SyntaxHighlighter',
                'highlight'
            ),
        );
        $body = $this->html();
        foreach ($replacements as $regex => $replacement) {
            if (is_callable($replacement)) {
                $body = preg_replace_callback($regex, $replacement, $body);
            } else {
                $body = preg_replace($regex, $replacement, $body);
            }
        }
        return trim($body);
    }

    /**
     * The date of publication of the blog post.
     *
     * @return string The human-readable date of publication of the blog post.
     */
    public function date() {
        return Time::day($this->timestamp());
    }

    /**
     * The date of publication of the blog post in RSS format.
     *
     * @return string The date of publication of the blog post in RSS format.
     */
    public function dateRSS() {
        return Time::dateRSS($this->timestamp());
    }

    /**
     * Identifier for templates, because Mustache can't run real code.
     *
     * @return boolean Always true.
     */
    public function isBlogEntry() {
        return true;
    }

    /**
     * A summary for the blog post - only used in the RSS output.
     *
     * @return string A summary for the blog post.
     */
    public function summary() {
        $summary = $this->body();
        if (preg_match('/^<p>(.*?)<\/p>/s', $summary, $matches)) {
            $summary = $matches[1];
        }
        return $summary;
    }

    /**
     * The date of publication of the blog post.
     *
     * @return string The UNIX epoch timestamp for the blog post.
     */
    public function timestamp() {
        $file = basename($this->_file);
        $matches = array();
        if (preg_match('/^(\d{4}-\d\d-\d\d)-/', $file, $matches)) {
            $time = new DateTime(
                "{$matches[1]} 12:00:00",
                new DateTimeZone('Europe/Dublin')
            );
            return $time->getTimestamp();
        } else {
            throw new Exception("Invalid file name: $file", 500);
        }
    }

    /**
     * Generate the title of the blog post.
     *
     * @return string The title of the blog post.
     */
    public function title() {
        $title = trim(
            preg_replace('/^.*<h1>(.*?)<\/h1>.*/s', '$1', $this->html())
        );
        if (!$title) {
            throw new Exception("Blog post had no title!", 500);
        }
        return $title;
    }

    /**
     * Describe this blog entry.
     *
     * @return string A description of this blog entry.
     */
    public function description() {
        $description = '';
        $markdown = $this->markdown();
        foreach (preg_split('/[\r\n]+/', $markdown) as $line) {
            $matches = array();
            if (preg_match('/^\[description]: # \((.*)\)/', $line, $matches)) {
                $description .= ' ' . $matches[1];
            }
        }
        return ltrim($description);
    }

    /**
     * An image to use for the metadata for this blog post.
     *
     * @return string The URL of the image to use.
     */
    public function image() {
        $markdown = $this->markdown();
        if (preg_match('/<img.*src="(.*?)"/ms', $markdown, $matches)) {
            return $matches[1];
        } else {
            return parent::image();
        }
    }

    /**
     * The ETag value for this model.
     *
     * @return string The value to be used in the ETag header.
     */
    public function eTag() {
        return md5(
            join(
                '',
                array(
                    $this->body(),
                    $this->updatedTime(),
                )
            )
        );
    }

    /**
     * The full HTML of the entire blog post file. This will be modified
     * before being rendered.
     *
     * @return The HTML of the entire blog post.
     */
    private function html() {
        $markdown = $this->markdown();
        return Cache::run(
            'blog_html_' . md5($markdown),
            0,
            function () use ($markdown) {
                return \Michelf\MarkdownExtra::defaultTransform($markdown);
            }
        );
    }

    /**
     * Retrieve the markdown text from the file backing the blog entry.
     *
     * @return string The Markdown text for the blog post.
     */
    private function markdown() {
        if (!$this->_markdown) {
            $this->_markdown = file_get_contents($this->_file);
        }
        return $this->_markdown;
    }
}

?>
