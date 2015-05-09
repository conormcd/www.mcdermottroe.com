<?php

/**
 * A facade over Mustache.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Mustache {
    /**
     * The Mustache_Engine we're using for loading templates.
     */
    private static $_engine = null;

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
        if (self::$_engine === null) {
            self::setEngine(self::getDefaultEngine());
        }
        return self::$_engine;
    }

    /**
     * Set the Mustache_Engine to be used for rendering content.
     *
     * @param object $engine The Mustache_Engine to use to render content.
     *
     * @return void
     */
    public static function setEngine($engine) {
        self::$_engine = $engine;
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
