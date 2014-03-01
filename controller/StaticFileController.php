<?php

/*
 * Copyright (c) 2013, Conor McDermottroe
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
 * Handle requests for static files. This should only ever be hit in 
 * development since the web server should serve all static file requests 
 * directly.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class StaticFileController
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
        $this->action = 'staticfile';
        parent::__construct($klein, $request, $response);
    }

    /**
     * Process GET requests.
     *
     * @return void
     */
    public function get() {
        $uri = $this->request->uri();
        $public_dir = realpath(dirname(__DIR__) . '/public');
        $localpath = realpath("$public_dir$uri");
        $filetypes = array(
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
        );

        if (file_exists($localpath)) {
            if (preg_match("#^$public_dir#", $localpath)) {
                foreach ($filetypes as $ext => $mime_type) {
                    if (preg_match("/\.$ext$/", $localpath)) {
                        $content_type = $mime_type;
                    }
                }

                if ($content_type) {
                    $this->response->header('Content-Type', $content_type);
                }
                print file_get_contents($localpath);
            }
        } else {
            throw new Exception('File not found', 404);
        }
    }
}

?>
