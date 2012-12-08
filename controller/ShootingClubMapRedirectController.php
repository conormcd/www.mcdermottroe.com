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
     * @param object $request  The _Request object from klein.
     * @param object $response The _Response object from klein.
     */
    public function __construct($request, $response) {
        $this->action = 'shootingclubs';
        parent::__construct($request, $response);

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
