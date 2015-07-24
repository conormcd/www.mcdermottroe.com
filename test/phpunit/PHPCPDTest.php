<?php

use SebastianBergmann\FinderFacade\FinderFacade;
use SebastianBergmann\PHPCPD\Detector\Detector;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;

/**
 * Run PHPCPD over the source.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class PHPCPDTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Run PHPCPD over the source.
     *
     * @return void
     */
    public function testPHPCPD() {
        // Find the files to test
        $finder = new FinderFacade(
            array('.'),
            array('vendor'),
            array(),
            array()
        );
        $files = $finder->findFiles();
        $this->assertNotEmpty($files);

        $detector = new Detector(new DefaultStrategy(), null);
        $clones = $detector->copyPasteDetection($files);

        foreach ($clones as $clone) {
            $this->fail(
                "The following code snippets are duplicates: " .
                join(", ", array_keys($clone->getFiles())) .
                "\n"
            );
        }
    }
}

?>
