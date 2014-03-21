<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';
require_once __DIR__ . '/FactoryTestCase.php';

/**
 * Test ExceptionTracker.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ExceptionTrackerTest
extends FactoryTestCase
{
    /**
     * Basic setup.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->class = get_class();
    }
}

?>
