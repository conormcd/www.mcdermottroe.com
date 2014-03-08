<?php

/**
 * Handle requests for shooting club and range requests.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ShootingClubsController
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
        $this->action = 'shootingclubs';
        parent::__construct($klein, $request, $response);

        $club = $request->club ? $request->club : 'All';
        if ($club === 'All') {
            $this->model = new ShootingClubsModel();
        } else {
            $this->model = new ShootingClubModel($club);
        }

        $formats = array(
            'gpx' => 'application/gpx+xml',
            'kml' => 'application/vnd.google-earth.kml+xml',
        );
        foreach ($formats as $format => $content_type) {
            if ($request->format == $format) {
                $this->output_format = $format;
                $response->header('Content-Type', $content_type);
                $response->header(
                    'Content-Disposition',
                    "inline; filename=\"$club.$format\""
                );
                break;
            }
        }
    }
}

?>
