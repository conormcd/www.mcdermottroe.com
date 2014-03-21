<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Test libraries that use getInstance methods to hand out instances of the
 * class (even if they can be created directly).
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FactoryTestCase
extends TestCase
{
    /**
     * Test getInstance().
     *
     * @return void
     */
    public function testGetInstance() {
        $class_under_test = preg_replace('/Test$/', '', $this->class);
        $instance = $class_under_test::getInstance();
        $this->assertNotNull($instance);
        $this->assertInstanceOf($class_under_test, $instance);
    }
}

?>
