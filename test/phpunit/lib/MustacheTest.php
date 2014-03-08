<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for Mustache.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class MustacheTest
extends TestCase
{
    /**
     * Ensure that we can render a basic template.
     *
     * @return void
     */
    public function testRendersTemplates() {
        $this->setTestEngine();
        $content = Mustache::render('foo{{bar}}', array('bar' => 'bar'));
        $this->assertEquals('foobar', $content);
        $this->restoreDefaultEngine();
    }

    /**
     * Ensure that we can render a template containing a partial.
     *
     * @return void
     */
    public function testRendersPartials() {
        $this->setTestEngine();
        $content = Mustache::render(
            'foo{{bar}}{{>baz}}',
            array('bar' => 'bar')
        );
        $this->assertEquals('foobarbaz', $content);
        $this->restoreDefaultEngine();
    }

    /**
     * Check that calling getEngine returns a valid engine.
     *
     * @return void
     */
    public function testGetEngineReturnsDefaultEngine() {
        $this->restoreDefaultEngine();
        $engine = Mustache::getEngine();
        $this->assertTrue($engine instanceof Mustache_Engine);
    }

    /**
     * Helper to use the testing Mustache_Engine.
     *
     * @return void
     */
    private function setTestEngine() {
        Mustache::setEngine(
            new Mustache_Engine(array('partials' => array('baz' => 'baz')))
        );
    }

    /**
     * Helper to switch back to the defaul Mustache_Engine.
     *
     * @return void
     */
    private function restoreDefaultEngine() {
        Mustache::setEngine(null);
    }
}

?>
