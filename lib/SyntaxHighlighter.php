<?php

/**
 * A facade over GeSHi
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class SyntaxHighlighter {
    /**
     * Syntax highlight a block of code.
     *
     * @param array $matches The regex matches for the code block. The
     *                       language should be specified in array element 1
     *                       and the code should be in array element 2.
     *
     * @return string        The HTML for the syntax highlighted code.
     */
    public static function highlight($matches) {
        $key = join('_', array('syntax', $matches[1], md5($matches[2])));
        return Cache::run(
            $key,
            0,
            function () use ($matches) {
                $geshi = new GeSHi(trim($matches[2]), $matches[1]);
                $geshi->set_overall_class('codeblock');
                return $geshi->parse_code();
            }
        );
    }
}

?>
