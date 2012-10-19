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

require_once __DIR__ . '/geshi/geshi.php';

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
        $result = Cache::get($key);
        if ($result === null) {
            $geshi = new GeSHi(trim($matches[2]), $matches[1]);
            $geshi->set_overall_class('codeblock');
            $result = $geshi->parse_code();
            Cache::set($key, $result, 0);
        }
        return $result;
    }
}

?>
