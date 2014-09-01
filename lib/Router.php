<?php

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
     * @param Klein $klein  The klein instance to use for dispatching routes.
     */
    public function __construct($routes, $klein = null) {
        $this->_klein = $klein ? $klein : new \Klein\Klein();
        $this->_klein->onError(array($this, 'onError'));
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
            function ($req, $res, $srv, $app) use ($class, $method) {
                assert($srv !== null);
                assert($app !== null);
                call_user_func(array(new $class($req, $res), $method));
            }
        );
    }

    /**
     * Load a redirection route.
     *
     * @param string $route  The Klein route specification.
     * @param string $dest   Where it should be redirected to.
     * @param array  $params Additional URL parameters that can be appended to
     *                       the dest URL.
     * @param string $frag   A URL fragment to add to the URL if necessary.
     *
     * @return void
     */
    public function loadRedirectRoute($route, $dest, $params, $frag) {
        $indirect = function ($request, $redirect_parameter) {
            if (preg_match('/^req->(.*)/', $redirect_parameter, $matches)) {
                $member = $matches[1];
                return $request->$member;
            }
            return $redirect_parameter;
        };
        $this->_klein->respond(
            'GET',
            $route,
            function ($request, $response) use ($dest, $params, $frag, $indirect) {
                $target_url = '';
                foreach ($params as $k => $v) {
                    $target_url .= $target_url ? "&" : "?";
                    $v = $indirect($request, $v);
                    $target_url .= "$k=$v";
                }
                $target_url = $dest . $request->suffix . $target_url;
                $target_url .= ($frag) ? ('#' . $indirect($request, $frag)) : "";
                $response->redirect($target_url, 301, false);
            }
        );
    }

    /**
     * An error handler to redirect errors into the ErrorController.
     *
     * @param \Klein\Klein $klein     The Klein object which is routing the
     *                                request.
     * @param string       $message   The message of the exception.
     * @param string       $type      The type of the exception.
     * @param Exception    $exception The exception to be tracked.
     *
     * @return void
     */
    public function onError($klein, $message, $type, $exception) {
        assert($message !== null);
        assert($type !== null);

        // Track the exception
        ExceptionTracker::getInstance()->captureException($exception);

        (new ErrorController(
            $klein->request(),
            $klein->response(),
            $exception
        ))->get();
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
                if (!array_key_exists('params', $controller)) {
                    $controller['params'] = array();
                }
                if (!array_key_exists('fragment', $controller)) {
                    $controller['fragment'] = array();
                }
                $this->loadRedirectRoute(
                    $route,
                    $controller['redirect'],
                    $controller['params'],
                    $controller['fragment']
                );
            } else {
                $this->loadRoute($route, $controller);
            }
        }

        // Make sure we have a 404 handler.
        $this->_klein->respond(
            '404',
            function ($req, $res, $srv, $app) {
                assert($srv !== null);
                assert($app !== null);
                (new ErrorController($req, $res))->get();
            }
        );
    }
}

?>
