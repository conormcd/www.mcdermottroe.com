<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for YouTubeEmbed.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class YouTubeEmbedTest
extends TestCase
{
    /**
     * Test YouTubeEmbed#embed
     *
     * @return void
     */
    public function testEmbed() {
        $html = YouTubeEmbed::embed(array(null, 'YOUTUBEID'));
        $this->assertNotNull($html);
        $this->assertRegexp("/YOUTUBEID/", $html);
    }
}

?>
