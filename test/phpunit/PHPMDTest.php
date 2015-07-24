<?php

/**
 * Run PHPMD over the source.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PHPMDTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Run PHPMD over the source.
     *
     * @return void
     */
    public function testPHPMD() {
        $phpmd = new PHPMD\PHPMD();
        $output = new PHPMDTestWriter();
        $renderer = new PHPMD\Renderer\TextRenderer();
        $renderer->setWriter($output);
        $phpmd->setIgnorePattern(array('vendor'));
        $phpmd->processFiles(
            '.',
            'test/phpmd.xml',
            array($renderer),
            new PHPMD\RuleSetFactory()
        );
        $this->assertFalse($phpmd->hasViolations(), $output->getData());
    }
}

/**
 * A writer to trap the output of PHPMD.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PHPMDTestWriter
extends PHPMD\AbstractWriter
{
    /**
     * Init.
     *
     * @return void
     */
    public function __construct() {
        $this->data = "";
    }

    /**
     * Consume a  write.
     *
     * @param string $data The data to be "written".
     *
     * @return void
     */
    public function write($data) {
        $this->data .= $data;
    }

    /**
     * Get the output which has been written.
     *
     * @return string The output which has been passed to all calls to write.
     */
    public function getData() {
        return $this->data;
    }
}

?>
