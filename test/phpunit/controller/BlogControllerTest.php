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
     * Ensure that it outputs Atom when asked to.
     *
     * @return void
     */
    public function testAtomOutput() {
        $req = $this->req();
        $req->format = 'atom';
        $controller = $this->create($req);

        $res = $controller->get();

        $this->assertNotNull($res->body());
        $this->assertRegexp('#^<\?xml.*</feed>$#s', $res->body());
        $this->assertTrue($res->headers()->exists('Content-Type'));
        $this->assertEquals(
            'application/atom+xml',
            $res->headers()->get('Content-Type')
        );
    }

    /**
     * Ensure that it outputs RSS when asked to.
     *
     * @return void
     */
    public function testRSSOutput() {
        $req = $this->req();
        $req->format = 'rss';
        $controller = $this->create($req);

        $res = $controller->get();

        $this->assertNotNull($res->body());
        $this->assertRegexp('#^<\?xml.*</rss>$#s', $res->body());
        $this->assertTrue($res->headers()->exists('Content-Type'));
        $this->assertEquals(
            'application/rss+xml',
            $res->headers()->get('Content-Type')
        );
    }
}

?>
