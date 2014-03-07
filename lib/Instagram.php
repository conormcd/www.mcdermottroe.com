<?php

/*
 * Copyright (c) 2014, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * A simple interface to Instagram.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Instagram {
    /**
     * Create a new facade over Instagram.
     *
     * @param string $client_id     The Instagram client ID.
     * @param string $client_secret The Instagram client secret.
     * @param int    $user_id       The Instagram user ID for me.
     */
    public function __construct($client_id, $client_secret, $user_id) {
        $this->_client_id = $client_id;
        $this->_client_secret = $client_secret;
        $this->_user_id = $user_id;
    }

    /**
     * Get the stream of recent photos.
     *
     * @return array An array of associative arrays, each of which is a
     *               reference to a photo on Instagram.
     */
    public function getStream() {
        $url  = "https://api.instagram.com/v1/users/{$this->_user_id}/media/recent";
        $url .= "?client_id={$this->_client_id}";
        $url .= "&client_secret={$this->_client_secret}";

        $key = 'INSTAGRAM_API_REQUEST' . md5($url);
        $images = Cache::get($key);
        if (!$images) {
            $images = array();
            $stream = JSON::decode(file_get_contents($url), true);
            if ($stream !== null) {
                foreach ($stream['data'] as $image) {
                    $images[] = array(
                        'timestamp' => $image['created_time'],
                        'link' => $image['link'],
                        'image' => $image['images']['standard_resolution']['url'],
                        'caption' => $image['caption']['text'],
                        'title' => Time::day($image['created_time']),
                        'isInstagramPhoto' => true,
                    );
                }
                Cache::set($key, $images, 3600);
            }
        }
        return $images;
    }
}

?>
