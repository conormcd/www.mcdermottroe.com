<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for the ErrorModel class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ErrorModelTest
extends ModelTestCase
{
    /**
     * Check the ErrorModel with an exception which has been thrown and hence
     * has a backtrace.
     *
     * @return void
     */
    public function testWithException() {
        $instance = $this->createTestObject();
        $this->assertEquals(503, $instance->code());
        $this->assertEquals("Test error", $instance->message());
        $this->assertGreaterThan(0, strlen($instance->trace()));
    }

    /**
     * Check the ErrorModel with an error message and a valid HTTP error code.
     *
     * @return void
     */
    public function testWithMessage() {
        $instance = new ErrorModel("Test error", 404);
        $this->assertEquals(404, $instance->code());
        $this->assertEquals("Test error", $instance->message());
        $this->assertEquals(0, strlen($instance->trace()));
    }

    /**
     * Check the ErrorModel with an error message and no code.
     *
     * @return void
     */
    public function testWithMessageNoCode() {
        $instance = new ErrorModel("Test error");
        $this->assertEquals(500, $instance->code());
        $this->assertEquals("Test error", $instance->message());
        $this->assertEquals(0, strlen($instance->trace()));
    }

    /**
     * Check the ErrorModel with an error message and a code which is not a
     * HTTP error code.
     *
     * @return void
     */
    public function testWithMessageNonHTTPCode() {
        $instance = new ErrorModel("Test error", 12345);
        $this->assertEquals(500, $instance->code());
        $this->assertEquals("Test error", $instance->message());
        $this->assertEquals(0, strlen($instance->trace()));
    }

    /**
     * Get a copy of ErrorModel for testing.
     *
     * @return ErrorModel An instance which can be tested.
     */
    protected function createTestObject() {
        try {
            throw new Exception("Test error", 503);
        } catch (Exception $e) {
            return new ErrorModel($e);
        }
    }
}

?>
