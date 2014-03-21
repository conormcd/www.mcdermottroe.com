<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Test the Environment class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class EnvironmentTest
extends TestCase
{
    /**
     * Test the basic functionality of getting an environment variable.
     *
     * @return void
     */
    public function testGet() {
        $this->assertEquals(
            $_ENV['PHOTO_PROVIDER'],
            Environment::get('PHOTO_PROVIDER')
        );
    }

    /**
     * Make sure it throws an exception when a variable is missing.
     *
     * @return void
     */
    public function testGetThrowsException() {
        $this->assertException(
            function () {
                Environment::get('DOES_NOT_EXIST');
            }
        );
    }

    /**
     * Test configFiles()
     *
     * @return void
     */
    public function testConfigFiles() {
        $this->assertNotEmpty(Environment::configFiles());
    }

    /**
     * Test addConfigFile
     *
     * @return void
     */
    public function testAddConfigFile() {
        $file = dirname(dirname(__DIR__)) . '/data/env.json';
        $this->assertFileExists($file);
        $this->assertException(
            function () {
                Environment::get('FOO');
            }
        );
        Environment::addConfigFile($file);
        $this->assertEquals('BAR', Environment::get('FOO'));
    }
}

?>
