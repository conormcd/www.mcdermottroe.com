<?php

/*
 * Copyright (c) 2012, Conor McDermottroe
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

require_once dirname(__DIR__) . '/TestCase.php';

require_once dirname(dirname(dirname(__DIR__))) . '/lib/Mustache.php';

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
