<?php

/**
 * Fake out Flickr by replacing any methods that would actually call out to the
 * Flickr API.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FakeFlickr
extends Flickr
{
    /**
     * Get a simple test instance.
     *
     * @return FakeFlickr An instance of this class.
     */
    public static function getInstance() {
        return new FakeFlickr(
            "I've got the key",
            "I've got the secret",
            "I've got the key to another way"
        );
    }

    /**
     * Get an instance of PhotoAlbumModel that can be tested.
     *
     * @return PhotoAlbumModel An album that can be tested.
     */
    public static function albumForTesting() {
        $instance = self::getInstance();
        $album = null;
        foreach ($instance->getAlbums() as $fake_album) {
            $album = $fake_album;
        }
        return $album;
    }

    /**
     * Get a valid album slug to allow tests to probe things that use the
     * Flickr library.
     *
     * @return string The short name of an album that can be tested.
     */
    public static function albumSlugForTesting() {
        return self::albumForTesting()->slug();
    }

    /**
     * Fake out Flickr.getAlbums with some fake data.
     *
     * @return An array of PhotoAlbumModel objects.
     */
    public function getAlbums() {
        return array(
            new PhotoAlbumModel(
                $this,
                1234,
                'A fake photo album',
                strtotime('1 week ago'),
                12345678
            )
        );
    }

    /**
     * Fake out Flickr.getPhotos with some fake data.
     *
     * @param PhotoAlbumModel $album The album to fetch the images from.
     *
     * @return An array of PhotoModel objects.
     */
    public function getPhotos($album) {
        $photo_id_offset = 12345678;

        $photos = array();
        for ($i = 0; $i < 15; $i++) {
            $photos[] = new PhotoModel(
                $album,
                $photo_id_offset + $i,
                $i,
                "Photo number $i in the set.",
                array(
                    "thumbnail" => "http://thumbnail/for/$i",
                    "large" => "http://large/for/$i",
                    "fullsize" => "http://fullsize/for/$i",
                )
            );
        }
        return $photos;
    }
}

?>
