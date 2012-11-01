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

require_once dirname(__DIR__) . '/lib/klein/klein.php';

// Aliases
$aliases = array(
    '@^/contact(?:\.php|/)?$' => '/about',
    '@^/computer-stuff(?P<suffix>.*)' => '/tech',
    '@^/shooting/?$' => '/shooting/clubs/locations/',
);
foreach ($aliases as $regex => $redirect) {
    respond(
        'GET',
        $regex,
        function($request, $response) use ($redirect) {
            if ($request->suffix) {
                $redirect .= $request->suffix;
            }
            $response->redirect($redirect, 301, false);
        }
    );
}

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
    (?:
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
    )?
/?$
REGEX;
$blog_route_regex = preg_replace('/\s+/', '', $blog_route_regex);

/**
 * A helper for responding to GET requests.
 *
 * @param string $route           The route in klein syntax.
 * @param string $controller_name The name of the controller class to use.
 *
 * @return void
 */
function respondToGet($route, $controller_name) {
    respond(
        'GET',
        $route,
        function($request, $response) use ($controller_name) {
            try {
                (new $controller_name($request, $response))->get();
            } catch (Exception $e) {
                (new ErrorController($request, $response, $e))->get();
            }
        }
    );
}

/*
 * Declare all the routes
 */
respondToGet($blog_route_regex, 'BlogController');
respondToGet(
    '/photos/?[:album]?/?[i:start]?/?[i:perpage]?/?',
    'PhotosController'
);
respondToGet(
    '/shooting/clubs/locations/[|gpx|kml|map:format][.php]?',
    'ShootingClubsController'
);
respondToGet('/[about|tech:action]/?', 'Controller');
respond(
    '404',
    function($request, $response) {
        (new ErrorController($request, $response))->get();
    }
);

?>
