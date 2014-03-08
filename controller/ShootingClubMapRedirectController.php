<?php

/**
 * Handle requests for map.php calls which redirect to Google Maps.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ShootingClubMapRedirectController
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

        // Figure out the club name and appropriate zoom level.
        $club = 'All';
        $zoom = 6;
        if ($request->club && $request->club != 'All') {
            $club = $request->club;
            $zoom = 14;
        }

        // Construct the URL for the KML link
        $domain = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        $kml_url = sprintf(
            'http://%s%s/shooting/clubs/locations/kml.php?club=%s',
            $domain,
            ($port == 80 ? '' : ":$port"),
            urlencode($club)
        );

        // Construct the redirect link.
        $redirect = sprintf(
            'http://maps.google.com/maps?q=%s&t=h&z=%d',
            rawurlencode($kml_url),
            $zoom
        );

        // Do the redirect.
        $response->redirect($redirect);
    }
}

?>
