<?php

/*
 * Copyright (c) 2014, Conor McDermottroe
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
 * Load all the routes and wrap Klein a little bit.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Router {
    /** The instance of Klein we're using for routing. */
    private $_klein;

    /**
     * Load the routes into the router.
     *
     * @param array $routes The routes that should be loaded and executed.
     */
    public function __construct($routes) {
        $this->_klein = new \Klein\Klein();
        $this->loadRoutes($routes);
    }

    /**
     * Delegate to \Klein\Klein->dispatch
     *
     * @return void
     */
    public function dispatch() {
        return call_user_func_array(
            array($this->_klein, 'dispatch'),
            func_get_args()
        );
    }

    /**
     * Load a single, normal route that is dispatched to a controller.
     *
     * @param string $route      The Klein route specification
     * @param mixed  $controller The controller to handle the route.
     *
     * @return void
     */
    public function loadRoute($route, $controller) {
        $class = $controller['controller'];
        $method = null;
        if (array_key_exists('method', $controller)) {
            $method = strtolower($controller['method']);
        } else {
            $method = 'GET';
        }

        $this->_klein->respond(
            strtoupper($method),
            $route,
            function ($req, $res, $srv, $app, $klein) use ($class, $method) {
                assert($srv !== null);
                assert($app !== null);
                try {
                    call_user_func(
                        array(
                            new $class($klein, $req, $res),
                            $method
                        )
                    );
                } catch (Exception $e) {
                    (new ErrorController($klein, $req, $res, $e))->get();
                }
            }
        );
    }

    /**
     * Load a redirection route.
     *
     * @param string $route       The Klein route specification.
     * @param string $destination Where it should be redirected to.
     *
     * @return void
     */
    public function loadRedirectRoute($route, $destination) {
        $this->_klein->respond(
            'GET',
            $route,
            function ($request, $response) use ($destination) {
                $response->redirect(
                    $destination . $request->suffix,
                    301,
                    false
                );
            }
        );
    }

    /**
     * Load the routes into Klein
     *
     * @param array $routes The routes to be loaded into Klein.
     *
     * @return void
     */
    private function loadRoutes($routes) {
        foreach ($routes as $route => $controller) {
            if (!is_array($controller)) {
                $controller = array(
                    'method' => 'GET',
                    'controller' => $controller,
                );
            }

            if (array_key_exists('redirect', $controller)) {
                $this->loadRedirectRoute($route, $controller['redirect']);
            } else {
                $this->loadRoute($route, $controller);
            }
        }

        // Make sure we have a 404 handler.
        $this->_klein->respond(
            '404',
            function ($req, $res, $srv, $app, $klein) {
                assert($srv !== null);
                assert($app !== null);
                (new ErrorController($klein, $req, $res))->get();
            }
        );
    }
}

?>
