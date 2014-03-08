<?php

require_once dirname(dirname(__DIR__)) . '/config/routes.php';
require_once dirname(dirname(__DIR__)) . '/lib/autoloader.php';

/**
 * Test the routing.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class RoutingTest
extends TestCase
{
    /**
     * Test a known-good route to double-check {@link assertRoute}.
     *
     * @return void
     */
    public function testKnownGoodRoute() {
        $this->assertRoute('GET', '/blog');
    }

    /**
     * Test a known-bad route to double-check {@link assertRoute}.
     *
     * @return void
     */
    public function testKnownBadRoute() {
        $this->assertRoute('GET', '/does-not-exist', 404);
    }

    /**
     * Test a selection of routes derived from actual traffic.
     *
     * @return void
     */
    public function testSampleRoutes() {
        $root = dirname(dirname(__DIR__));
        $static_root = "$root/public";
        $sample_data = file_get_contents("$root/test/data/routes.txt");
        foreach (preg_split('/[\r\n]+/', $sample_data) as $line) {
            if (preg_match('/^([A-Z]+) (\S+)(?: (\d+))?/', $line, $matches)) {
                $method = $matches[1];
                $uri = $matches[2];
                $status = isset($matches[3]) ? $matches[3] : 200;
                if (!file_exists($static_root . $uri)) {
                    $this->assertRoute($method, $uri, $status);
                }
            }
        }
    }

    /**
     * Assert that a particular route results in a particular status.
     *
     * @param string $method          The HTTP method to use.
     * @param string $uri             The path portion of the URI to test.
     * @param int    $expected_status The HTTP status code which should be the
     *                                result of the call to the specified
     *                                route.
     *
     * @return void
     */
    private function assertRoute($method, $uri, $expected_status = 200) {
        global $ROUTES;
        $router = new Router($ROUTES);

        $request = new \Klein\Request(
            array(),
            array(),
            array(),
            array(
                'REQUEST_METHOD' => $method,
                'REQUEST_URI' => $uri,
            )
        );
        $response = new \Klein\Response();

        $output = $router->dispatch(
            $request,
            $response,
            false,
            \Klein\Klein::DISPATCH_CAPTURE_AND_RETURN
        );
        $this->assertEquals(
            $expected_status,
            $response->status()->getCode(),
            "$method $uri $expected_status"
        );
        $this->assertNotNull($output);
    }
}

?>
