<?php

/**
 * Utilities for dealing with embedded YouTube videos.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class YouTubeEmbed {
    /**
     * Create a block of HTML for embedding a YouTube video.
     *
     * @param array $matches The regex matches for the YouTube video. The
     *                       YouTube ID should be in array element 1.
     *
     * @return string        The HTML for the embedded video.
     */
    public static function embed($matches) {
        $yt_id = $matches[1];
        return <<<HTML
<iframe
    class="youtube-player"
    type="text/html"
    src="http://www.youtube.com/embed/$yt_id"
    allowfullscreen
    frameborder="0"
>
</iframe>
HTML;
    }
}

?>
