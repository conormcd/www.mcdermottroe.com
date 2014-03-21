<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for Router.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class RouterTest
extends TestCase
{
    /**
     * Test Router#dispatch and make sure it delegates to Klein.
     *
     * @return void
     */
    public function testDispatch() {
        $router = new Router(array(), new FakeKlein());

        $request = new \Klein\Request();
        $response = new \Klein\Response();

        $this->assertEquals(array(), $router->dispatch());
        $this->assertEquals(array($request), $router->dispatch($request));
        $this->assertEquals(
            array($request, $response),
            $router->dispatch($request, $response)
        );
        $this->assertEquals(
            array($request, $response, true),
            $router->dispatch($request, $response, true)
        );
        $this->assertEquals(
            array($request, $response, true, \Klein\Klein::DISPATCH_NO_CAPTURE),
            $router->dispatch(
                $request,
                $response,
                true,
                \Klein\Klein::DISPATCH_NO_CAPTURE
            )
        );
    }

    /**
     * Test Router#loadRoute
     *
     * @return void
     */
    public function testLoadRoute() {
        $klein = new FakeKlein();
        $router = new Router(array(), $klein);
        $router->loadRoute(
            '@.*',
            array('method' => 'GET', 'controller' => 'Dummy')
        );
        $this->assertRoute($klein, '@.*', 'GET');
    }

    /**
     * Test Router#loadRoute with a route with no method
     *
     * @return void
     */
    public function testLoadRouteNoMethod() {
        $klein = new FakeKlein();
        $router = new Router(array(), $klein);
        $router->loadRoute('@.*', array('controller' => 'Dummy'));
        $this->assertRoute($klein, '@.*', 'GET');
    }

    /**
     * Check that a route exists and has the right method.
     *
     * @param \Klein\Klein $klein  The instance of Klein behind Router.
     * @param string       $path   The route path.
     * @param string       $method The HTTP method which the route should
     *                             respond to.
     *
     * @return void
     */
    private function assertRoute($klein, $path, $method = 'GET') {
        $found_route = false;
        foreach ($klein->routes() as $route) {
            if ($route->getPath() === $path) {
                $found_route = true;
                $this->assertEquals($method, $route->getMethod());
            }
        }
        $this->assertTrue($found_route);
    }
}

/**
 * A simple fake Klein object to allow us to test #dispatch.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FakeKlein
extends \Klein\Klein
{
    /**
     * A dummy dispatch which just returns its arguments.
     *
     * @param \Klein\Request          $request       See \Klein\Klein#dispatch.
     * @param \Klein\AbstractResponse $response      See \Klein\Klein#dispatch.
     * @param boolean                 $send_response See \Klein\Klein#dispatch.
     * @param int                     $capture       See \Klein\Klein#dispatch.
     *
     * @return The arguments that are passed to it.
     */
    public function dispatch(
        \Klein\Request $request = null,
        \Klein\AbstractResponse $response = null,
        $send_response = true,
        $capture = \Klein\Klein::DISPATCH_NO_CAPTURE
    ) {
        if (!($request === null || $request instanceof \Klein\Request)) {
            throw new Exception("Bad request for dispatch.");
        }
        if (!($response === null || $response instanceof \Klein\AbstractResponse)) {
            throw new Exception("Bad response for dispatch.");
        }
        if (!is_bool($send_response)) {
            throw new Exception("Bad send_response for dispatch.");
        }
        if (!is_int($capture)) {
            throw new Exception("Bad capture for dispatch.");
        }
        return func_get_args();
    }
}

?>
