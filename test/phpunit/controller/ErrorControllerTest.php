<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Test the ErrorController class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ErrorControllerTest
extends ControllerTestCase
{
    /**
     * Create a basic ErrorController to do the common controller tests.
     *
     * @param Exception $exception An optional exception to handle with the
     *                             exception controller.
     *
     * @return object An instance of ErrorController.
     */
    public function sampleController($exception = null) {
        return $this->create(
            function ($klein, $req, $res) use ($exception) {
                return new ErrorController($klein, $req, $res, $exception);
            }
        );
    }

    /**
     * Test an ErrorController with an exception.
     *
     * @return void
     */
    public function testWithException() {
        // Generate the exception to test.
        $exception = null;
        try {
            throw new Exception('Test exception', 503);
        } catch (Exception $e) {
            $exception = $e;
        }

        $result = $this->runController($this->sampleController($exception));

        $this->assertEquals(503, $result['status']);
        $this->assertNotNull($result['output']);
    }

    /**
     * Test an ErrorController with an exception which has as bad error code.
     *
     * @return void
     */
    public function testWithExceptionWithBadErrorCode() {
        // Generate the exception to test.
        $exception = null;
        try {
            throw new Exception('Test exception', 12345);
        } catch (Exception $e) {
            $exception = $e;
        }

        $result = $this->runController($this->sampleController($exception));

        $this->assertEquals(500, $result['status']);
        $this->assertNotNull($result['output']);
    }
}

?>
