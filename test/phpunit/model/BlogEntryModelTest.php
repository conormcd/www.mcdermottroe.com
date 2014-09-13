<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for BlogEntryModel.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogEntryModelTest
extends ModelTestCase
{
    /**
     * Initialise the list of temporary files to be cleaned up.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->_test_files = array();
    }

    /**
     * Clean up temporary files.
     *
     * @return void
     */
    public function tearDown() {
        foreach ($this->_test_files as $file) {
            unlink($file);
        }
        parent::tearDown();
    }

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
        $post = $this->generateTestPost('');

        $this->assertException(array($post, 'title'));
        $this->assertEquals($post->date(), '1st January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertEmpty($post->body());
        $this->assertEmpty($post->summary());
    }

    /**
     * Test the case where the markdown file has a title and nothing else.
     *
     * @return void
     */
    public function testBlogPostWithTitle() {
        $post = $this->generateTestPost(
<<<MARKDOWN
# Title Goes Here
MARKDOWN
        );

        $this->assertEquals($post->title(), 'Title Goes Here');
        $this->assertEquals($post->date(), '1st January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertEmpty($post->body());
        $this->assertEmpty($post->summary());
    }

    /**
     * Ensure that the date of the blog post is taken from the filename.
     *
     * @return void
     */
    public function testDateTakenFromFileName() {
        $post = $this->generateTestPost('', '2012-06-01-');

        $this->assertException(array($post, 'title'));
        $this->assertEquals($post->date(), '1st June 2012');
        $this->assertEquals($post->dateISO8601(), '2012-05-31T23:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Fri, 01 Jun 2012 00:00:00 +0100');
        $this->assertEmpty($post->body());
        $this->assertEmpty($post->summary());
    }

    /**
     * Test the case where the file name does not contain a date.
     *
     * @return void
     */
    public function testFileNameWithNoDate() {
        $post = $this->generateTestPost('', 'not-a-date-');

        $this->assertException(array($post, 'title'));
        $this->assertException(array($post, 'date'));
        $this->assertEmpty($post->body());
        $this->assertEmpty($post->summary());
    }

    /**
     * Test the case where the file contains both a title and a body.
     *
     * @return void
     */
    public function testBlogPostWithTitleAndBody() {
        $post = $this->generateTestPost(
<<<MARKDOWN
# Title

Body
MARKDOWN
        );

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1st January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertEquals($post->body(), '<p>Body</p>');
        $this->assertEquals($post->summary(), 'Body');
    }

    /**
     * Test the case where the file contains an Amazon affiliate link.
     *
     * @return void
     */
    public function testBlogPostWithAnAmazonLink() {
        $post = $this->generateTestPost(
<<<MARKDOWN
# Title

<a href="{{amazonlink:ABCD1234}}">Link</a>
MARKDOWN
        );

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1st January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertRegExp(
            '#http://[^/]*amazon\.com/.*ABCD1234#',
            $post->body()
        );
    }

    /**
     * Test the case where the file contains an Amazon affiliate tracking bug.
     *
     * @return void
     */
    public function testBlogPostWithAnAmazonBug() {
        $post = $this->generateTestPost(
<<<MARKDOWN
# Title

{{amazonbug:ABCD1234}}
MARKDOWN
        );

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1st January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertRegExp(
            '#<img.*src=.http://[^/]*amazon\.com/.*ABCD1234#s',
            $post->body()
        );
    }

    /**
     * Check the case where the code block has no syntax hint.
     *
     * @return void
     */
    public function testBlogPostWithCodeBlock() {
        $post = $this->generateTestPost(
<<<MARKDOWN
# Title

    if (true) {
        print "Woo!";
    }
MARKDOWN
        );

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1st January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertRegExp(
            '#<pre><code>if \(true\) \{.*print "Woo!";.*\}.*</code></pre>#s',
            $post->body()
        );
    }

    /**
     * Check the case where the code block has a syntax hint.
     *
     * @return void
     */
    public function testBlogPostWithSyntaxHighlightedCodeBlock() {
        $post = $this->generateTestPost(
<<<MARKDOWN
# Title

    {{lang:php}}
    if (true) {
        print "Woo!";
    }
MARKDOWN
        );

        $this->assertEquals($post->title(), 'Title');
        $this->assertEquals($post->date(), '1st January 2012');
        $this->assertEquals($post->dateISO8601(), '2012-01-01T00:00:00Z');
        $this->assertEquals($post->dateRSS(), 'Sun, 01 Jan 2012 00:00:00 +0000');
        $this->assertRegExp(
            '#^<pre class="php codeblock" #',
            $post->body()
        );
    }

    /**
     * Check that isBlogEntry() returns true.
     *
     * @return void
     */
    public function testIsBlogEntry() {
        $post = $this->generateTestPost('');
        $this->assertTrue($post->isBlogEntry());
    }

    /**
     * Create a BlogEntryModel from some test data.
     *
     * @param string $content     The contents of the backing file.
     * @param string $name_prefix The prefix for the backing file name.
     *
     * @return BlogEntryModel An instance of BlogEntryModel that can be used to run tests.
     */
    private function generateTestPost($content, $name_prefix = '2012-01-01-') {
        $filename = tempnam(sys_get_temp_dir(), $name_prefix);
        if (($file = fopen($filename, 'w')) !== false) {
            fwrite($file, $content);
            fclose($file);
        }
        $this->_test_files[] = $filename;
        return new BlogEntryModel($filename);
    }
}

?>
