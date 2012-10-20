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

require_once dirname(__DIR__) . '/TestCase.php';

require_once dirname(dirname(dirname(__DIR__))) . '/lib/AmazonAffiliate.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lib/Cache.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lib/SyntaxHighlighter.php';
require_once dirname(dirname(dirname(__DIR__))) . '/model/Model.php';
require_once dirname(dirname(dirname(__DIR__))) . '/model/BlogEntryModel.php';

/**
 * Tests for BlogEntryModel.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogEntryModelTest
extends TestCase
{
    /**
     * Test the case where the markdown file does not exist.
     *
     * @return void
     */
    public function testMissingFile() {
        $exception_thrown = false;
        try {
            new BlogEntryModel('/does/not/exist');
        } catch (Exception $e) {
            $exception_thrown = true;
        }
        $this->assertTrue(
            $exception_thrown,
            'The BlogEntryModel should have thrown an exception.'
        );
    }

    /**
     * Test the case where the markdown file is empty.
     *
     * @return void
     */
    public function testEmptyBlogPost() {
        $file = $this->generateTestFile('');
        $post = new BlogEntryModel($file);

        $this->assertException(array($post, 'title'));
        $this->assertEquals($post->date(), '1 January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertEmpty($post->body());
        $this->assertEmpty($post->summary());

        unlink($file);
    }

    /**
     * Test the case where the markdown file has a title and nothing else.
     *
     * @return void
     */
    public function testBlogPostWithTitle() {
        $file = $this->generateTestFile(
<<<MARKDOWN
# Title Goes Here
MARKDOWN
        );
        $post = new BlogEntryModel($file);

        $this->assertEquals($post->title(), 'Title Goes Here');
        $this->assertEquals($post->date(), '1 January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertEmpty($post->body());
        $this->assertEmpty($post->summary());

        unlink($file);
    }

    /**
     * Ensure that the date of the blog post is taken from the filename.
     *
     * @return void
     */
    public function testDateTakenFromFileName() {
        $file = $this->generateTestFile('', '2012-06-01-');
        $post = new BlogEntryModel($file);

        $this->assertException(array($post, 'title'));
        $this->assertEquals($post->date(), '1 June 2012');
        $this->assertEquals($post->dateISO8601(), '2012-05-31T23:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Fri, 01 Jun 2012 00:00:00 +0100');
        $this->assertEmpty($post->body());
        $this->assertEmpty($post->summary());

        unlink($file);
    }

    /**
     * Test the case where the file name does not contain a date.
     *
     * @return void
     */
    public function testFileNameWithNoDate() {
        $file = $this->generateTestFile('', 'not-a-date-');
        $post = new BlogEntryModel($file);

        $this->assertException(array($post, 'title'));
        $this->assertException(array($post, 'date'));
        $this->assertEmpty($post->body());
        $this->assertEmpty($post->summary());

        unlink($file);
    }

    /**
     * Test the case where the file contains both a title and a body.
     *
     * @return void
     */
    public function testBlogPostWithTitleAndBody() {
        $file = $this->generateTestFile(
<<<MARKDOWN
# Title

Body
MARKDOWN
        );
        $post = new BlogEntryModel($file);

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1 January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertEquals($post->body(), '<p>Body</p>');
        $this->assertEquals($post->summary(), 'Body');

        unlink($file);
    }

    /**
     * Test the case where the file contains an Amazon affiliate link.
     *
     * @return void
     */
    public function testBlogPostWithAnAmazonLink() {
        $_ENV['AMAZON_AFFILIATE_TAG'] = 'test_tag';
        $_ENV['AMAZON_AFFILIATE_LINK_CODE'] = 'test_link_code';
        $_ENV['AMAZON_AFFILIATE_CAMP'] = 'test_camp';
        $_ENV['AMAZON_AFFILIATE_CREATIVE'] = 'test_creative';
        $file = $this->generateTestFile(
<<<MARKDOWN
# Title

<a href="{{amazon_link:ABCD1234}}">Link</a>
MARKDOWN
        );
        $post = new BlogEntryModel($file);

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1 January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertRegExp(
            '#http://[^/]*amazon\.com/.*ABCD1234#',
            $post->body()
        );

        unlink($file);
    }

    /**
     * Test the case where the file contains an Amazon affiliate tracking bug.
     *
     * @return void
     */
    public function testBlogPostWithAnAmazonBug() {
        $_ENV['AMAZON_AFFILIATE_TAG'] = 'test_tag';
        $_ENV['AMAZON_AFFILIATE_LINK_CODE'] = 'test_link_code';
        $file = $this->generateTestFile(
<<<MARKDOWN
# Title

{{amazon_bug:ABCD1234}}
MARKDOWN
        );
        $post = new BlogEntryModel($file);

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1 January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertRegExp(
            '#<img.*src=.http://[^/]*amazon\.com/.*ABCD1234#s',
            $post->body()
        );

        unlink($file);
    }

    /**
     * Check the case where the code block has no syntax hint.
     *
     * @return void
     */
    public function testBlogPostWithCodeBlock() {
        $file = $this->generateTestFile(
<<<MARKDOWN
# Title

    if (true) {
        print "Woo!";
    }
MARKDOWN
        );
        $post = new BlogEntryModel($file);

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1 January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertRegExp(
            '#<pre><code>if \(true\) \{.*print "Woo!";.*\}.*</code></pre>#s',
            $post->body()
        );

        unlink($file);
    }

    /**
     * Check the case where the code block has a syntax hint.
     *
     * @return void
     */
    public function testBlogPostWithSyntaxHighlightedCodeBlock() {
        $file = $this->generateTestFile(
<<<MARKDOWN
# Title

    {{lang:php}}
    if (true) {
        print "Woo!";
    }
MARKDOWN
        );
        $post = new BlogEntryModel($file);

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1 January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertRegExp(
            '#^<pre class="php codeblock" #',
            $post->body()
        );

        unlink($file);
    }

    /**
     * Create a temporary file which contains some test content.
     *
     * @param string $content     The contents of the file.
     * @param string $name_prefix The prefix for the file name.
     *
     * @return string             The full path to a file containing that 
     *                            content.
     */
    private function generateTestFile($content, $name_prefix = '2012-01-01-') {
        $filename = tempnam(sys_get_temp_dir(), $name_prefix);
        if (($file = fopen($filename, 'w')) !== false) {
            fwrite($file, $content);
            fclose($file);
        }
        return $filename;
    }
}

?>
