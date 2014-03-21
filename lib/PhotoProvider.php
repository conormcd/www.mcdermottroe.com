<?php

/**
 * The definition of a photo provider which we can deal with in an abstract
 * sense to improve portability and ease testing.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class PhotoProvider {
    /**
     * Get an instance of the configured photo provider.
     *
     * @return PhotoProvider An instance of this class.
     */
    public static function getInstance() {
        $provider_name = Environment::get('PHOTO_PROVIDER');
        $instance = $provider_name::getInstance();
        return $instance;
    }

    /**
     * Get a list of the albums available.
     *
     * @return array An array of PhotoAlbumModel objects, one per album.
     */
    public abstract function getAlbums();

    /**
     * Get a single album by name.
     *
     * @param string $album_short_name The short name of the album to fetch.
     *
     * @return PhotoAlbumModel The album requested.
     */
    public abstract function getAlbum($album_short_name);

    /**
     * Get a list of the photos in an album.
     *
     * @param PhotoAlbumModel $album The album from which to retrieve the
     *                               photos.
     *
     * @return array An array of PhotoModel objects.
     */
    public abstract function getPhotos($album);
}

?>
