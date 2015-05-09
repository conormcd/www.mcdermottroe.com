<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for SyntaxHighlighter.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class SyntaxHighlighterTest
extends TestCase
{
    /**
     * Test snippets in a few languages.
     */
    private static $_testCases = array(
        'php' => '<?php phpinfo(); ?>',
    );

    /**
     * Make sure it can highlight all the code samples in the test cases.
     *
     * @return void
     */
    public function testHighlightsCode() {
        foreach (self::$_testCases as $lang => $codeblock) {
            $html = SyntaxHighlighter::highlight(array('', $lang, $codeblock));
            $this->assertStringStartsWith(
                "<pre class=\"$lang codeblock\"",
                $html
            );
        }
    }
}

?>
