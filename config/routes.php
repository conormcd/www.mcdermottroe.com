<?php

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
    '@^/shooting(/.*)?' => array('redirect' => 'http://www.shooting.ie/'),
    '/' => 'FrontPageController',
    $blog_route_regex => 'BlogController',
    '/photos/[:album]/[i:start]/[i:perpage]/?' => 'PhotosController',
    '/photos/[:album]/[i:start]/?' => 'PhotosController',
    '/photos/[:album]/?' => 'PhotosController',
    '/photos/?' => 'PhotosController',
    '/[about|tech:action]/?' => 'Controller',
    '@/(?:css|js).*' => 'StaticFileController',
);

?>
