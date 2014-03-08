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
     * @param object $klein    The Klein main object.
     * @param object $request  The Request object from klein.
     * @param object $response The Response object from klein.
     */
    public function __construct($klein, $request, $response) {
        $this->action = $request->perpage == 1 ? 'photo' : 'photos';
        parent::__construct($klein, $request, $response);
        $this->model = new PhotosModel(
            $request->album,
            $request->start,
            $request->perpage
        );
    }
}

?>
