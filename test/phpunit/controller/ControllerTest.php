<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Test the Controller class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ControllerTest
extends ControllerTestCase
{
    /**
     * A sample instance of the controller under test
     *
     * @return object An instance of Controller.
     */
    protected function sampleController() {
        return $this->create('Controller');
    }
}

?>
