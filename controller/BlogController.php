<?php

/**
 * Handle requests for blog posts.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class BlogController
extends Controller
{
    /**
     * Initialize.
     *
     * @param object $request  The Request object from klein.
     * @param object $response The Response object from klein.
     */
    public function __construct($request, $response) {
        parent::__construct($request, $response);

        $this->model = new BlogModel(
            $request->year,
            $request->month,
            $request->day,
            $request->slug,
            $request->page,
            $request->per_page
        );

        if ($request->format === 'atom') {
            $this->output_format = 'atom';
            $this->content_type = 'application/atom+xml';
        } else if ($request->format === '' || $request->format === 'rss') {
            $this->content_type = 'application/rss+xml';
            $this->output_format = 'rss';
        }
    }
}

?>
