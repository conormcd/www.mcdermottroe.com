<?php

require_once dirname(__DIR__) . '/lib/autoloader.php';

/**
 * Cache warming for Flickr requests.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FlickrTask
extends Task
{
    /**
     * Run the task.
     *
     * @param array $args The command line arguments for this script.
     *
     * @return void
     */
    public function run($args) {
        // Split the albums into ones that should be refreshed daily and ones
        // that only need to be refreshed monthly.
        $flickr = Flickr::getInstance();
        $albums = $flickr->getAlbums();
        $album_buckets = array('monthly' => array(), 'daily' => array());
        foreach ($albums as $album) {
            $cache = $flickr->albumCacheLifetime($album);
            if ($cache <= 0 || $cache > (30 * 86400)) {
                $bucket = 'monthly';
            } else {
                $bucket = 'daily';
            }
            $album_buckets[$bucket][] = $album;
        }

        // Now refresh the appropriate bucket.
        switch ($bucket = array_shift($args)) {
            case 'monthly':
            case 'daily':
                break;
            default:
                $bucket = 'daily';
        }

        foreach ($album_buckets[$bucket] as $album) {
            $album->photos();
        }

    }
}

exit((new FlickrTask())->execute());

?>
