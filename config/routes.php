<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
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

/*
 * The blog route is a little complex, but it matches Wordpress for backwards
 * compatibility. I don't know how to represent it in klein syntax so here it
 * is in a regex (prefixed with @ so that klein recognizes it as such).
 *
 * It's not possible to add the PCRE_EXTENDED modifier to a regex in klein, so
 * it's built here with spaces and then the spaces are stripped afterwards.
 */
$blog_route_regex = <<<REGEX
@^/
    blog
    (?:
        /(?P<year>\d{4})
        (?:
            /(?P<month>\d\d)
            (?:
                /(?P<day>\d\d)
                (?:
                    /(?P<slug>[a-z0-9-]+)
                )?
            )?
        )?
    )?
    (?:/feed(?:/(?P<format>rss|atom))?)?
/?$
REGEX;
$blog_route_regex = preg_replace('/\s+/', '', $blog_route_regex);

$ROUTES = array(
    '@^/contact(?:\.php|/)?$' => array('redirect' => '/about'),
    '@^/computer-stuff(?P<suffix>.*)' => array('redirect' => '/tech'),
    '@^/shooting/?$' => array('redirect' => '/shooting/clubs/locations/'),
    '/' => 'FrontPageController',
    $blog_route_regex => 'BlogController',
    '/photos/[:album]/[i:start]/[i:perpage]/?' => 'PhotosController',
    '/photos/[:album]/[i:start]/?' => 'PhotosController',
    '/photos/[:album]/?' => 'PhotosController',
    '/photos/?' => 'PhotosController',
    '/shooting/clubs/locations/map.php' => 'ShootingClubMapRedirectController',
    '/shooting/clubs/locations/[|gpx|kml:format][.php]?' =>
        'ShootingClubsController',
    '/[about|tech:action]/?' => 'Controller',
    '@/(?:css|js).*' => 'StaticFileController',
);

?>
