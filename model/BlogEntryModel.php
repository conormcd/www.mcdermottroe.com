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

require_once dirname(__DIR__) . '/lib/autoloader.php';
require_once dirname(__DIR__) . '/lib/markdown/markdown.php';

/**
 * Wrap a blog entry.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogEntryModel
extends Model
{
    /** The path to the file containing the blog post. */
    private $file;

    /** The direct link to this blog post. */
    public $link;

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
        $this->file = $blog_markdown_file;
        $this->link = preg_replace(
            '#^(\d{4})-(\d\d)-(\d\d)-(.*?)\.md$#',
            '/blog/$1/$2/$3/$4/',
            basename($this->file)
        );
    }

    /**
     * The body of the blog post.
     *
     * @return string The rendered HTML for the body of the blog post.
     */
    public function body() {
        $replacements = array(
            '#<h1>.*?</h1>#s' => '',
            '#{{amazon_link:(.*?)}}#s' => array('AmazonAffiliate', 'link'),
            '#{{amazon_bug:(.*?)}}#s' => array('AmazonAffiliate', 'bug'),
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
        return date('j F Y', $this->timestamp());
    }

    /**
     * The date of publication of the blog post in ISO8601 format.
     *
     * @return string The date of publication of the blog post in ISO8601 
     *                format.
     */
    public function dateISO8601() {
        return preg_replace('/\+00:00$/', 'Z', gmdate('c', $this->timestamp()));
    }

    /**
     * The date of publication of the blog post in RSS format.
     *
     * @return string The date of publication of the blog post in RSS format.
     */
    public function dateRSS() {
        return date(DATE_RSS, $this->timestamp());
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
        $file = basename($this->file);
        $matches = array();
        if (preg_match('/^(\d{4}-\d\d-\d\d)-/', $file, $matches)) {
            $time = new DateTime(
                "{$matches[1]} 00:00:00",
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
     * The full HTML of the entire blog post file. This will be modified 
     * before being rendered.
     *
     * @return The HTML of the entire blog post.
     */
    private function html() {
        $markdown = file_get_contents($this->file);
        return $this->cache(
            'blog_html_' . md5($markdown),
            0,
            function () use ($markdown) {
                return Markdown($markdown);
            }
        );
    }
}

?>
