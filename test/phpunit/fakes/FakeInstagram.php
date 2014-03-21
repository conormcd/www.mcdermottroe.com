<?php

/**
 * A fake version of the Instagram class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FakeInstagram
extends Instagram
{
    /**
     * Override {@link Instagram#getStream()}.
     *
     * @return array An array of associative arrays, each of which is a
     *               reference to a photo on Instagram.
     */
    public function getStream() {
        return array(
            array(
                'timestamp' => time(),
                'link' => 'http://a.fake/instagram/link',
                'image' => 'http://a.fake/instagram/image',
                'caption' => 'This is a fake Instagram photo',
                'title' => Time::day(time()),
                'isInstagramPhoto' => true,
            ),
        );
    }
}

?>
