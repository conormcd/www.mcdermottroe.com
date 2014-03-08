<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Test the BlogController class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogControllerTest
extends ControllerTestCase
{
    /**
     * A sample instance of the controller under test
     *
     * @return object An instance of Controller.
     */
    protected function sampleController() {
        return $this->create('BlogController');
    }

    /**
     * Ensure that it outputs Atom when asked to.
     *
     * @return void
     */
    public function testAtomOutput() {
        $req = new \Klein\Request();
        $req->format = 'atom';
        $controller = $this->create('BlogController', $req);

        $result = $this->runController($controller);

        $this->assertNotNull($result['output']);
        $this->assertRegexp('#^<\?xml.*</feed>$#s', $result['output']);
        $this->assertArrayHasKey('content-type', $result['headers']);
        $this->assertEquals(
            'application/atom+xml',
            $result['headers']['content-type']
        );
    }

    /**
     * Ensure that it outputs RSS when asked to.
     *
     * @return void
     */
    public function testRSSOutput() {
        $req = new \Klein\Request();
        $req->format = 'rss';
        $controller = $this->create('BlogController', $req);

        $result = $this->runController($controller);

        $this->assertNotNull($result['output']);
        $this->assertRegexp('#^<\?xml.*</rss>$#s', $result['output']);
        $this->assertArrayHasKey('content-type', $result['headers']);
        $this->assertEquals(
            'application/rss+xml',
            $result['headers']['content-type']
        );
    }
}

?>
