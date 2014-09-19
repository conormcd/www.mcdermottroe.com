<?php

/**
 * Handle requests for photo albums and photos.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PhotosController
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
        $this->model = new PhotosModel(
            $request->album,
            $request->start,
            $request->perpage
        );
        if ($request->perpage == 1) {
            $this->view = 'photo';
        }
    }
}

?>
