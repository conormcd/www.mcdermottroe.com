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
     * @return array An array of InstagramPhotoModel objects.
     */
    public function getStream() {
        $data_dir = dirname(dirname(dirname(__FILE__))) . '/data';
        $data = JSON::decode(
            file_get_contents("$data_dir/instagram_users_media_recent.json")
        );

        $images = array();
        foreach ($data['data'] as $image) {
            $images[] = new InstagramPhotoModel($image);
        }
        return $images;
    }
}

?>
