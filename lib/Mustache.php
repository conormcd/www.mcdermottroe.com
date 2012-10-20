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

require_once __DIR__ . '/mustache/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

/**
 * A facade over Mustache.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Mustache {
    /** The Mustache_Engine we're using for loading templates. */
    private static $engine = null;

    /**
     * Render a template with some data.
     *
     * @param string $template The name of the template to render.
     * @param mixed  $data     The data to use to fill in the blanks.
     *
     * @return string          The rendered template.
     */
    public static function render($template, $data) {
        return self::getEngine()->render($template, $data);
    }

    /**
     * Get the current Mustache_Engine being used to render the content.
     *
     * @return object The current instance of Mustache_Engine being used.
     */
    public static function getEngine() {
        if (self::$engine === null) {
            self::setEngine(self::getDefaultEngine());
        }
        return self::$engine;
    }

    /**
     * Set the Mustache_Engine to be used for rendering content.
     *
     * @param object $engine The Mustache_Engine to use to render content.
     *
     * @return void
     */
    public static function setEngine($engine) {
        self::$engine = $engine;
    }

    /**
     * Build the default engine used to render content.
     *
     * @return object The default Mustache_Engine used to create content.
     */
    private static function getDefaultEngine() {
        return new Mustache_Engine(
            array(
                'loader' => new Mustache_Loader_FilesystemLoader(
                    dirname(__DIR__) . '/view'
                ),
                'partials_loader' => new Mustache_Loader_FilesystemLoader(
                    dirname(__DIR__) . '/view/partial'
                ),
            )
        );
    }
}

?>
