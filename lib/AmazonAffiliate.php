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

require_once dirname(__DIR__) . '/config/environment.php';

/**
 * Utilities for dealing with Amazon affiliate links.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class AmazonAffiliate {
    /**
     * Create an Amazon affiliate link.
     *
     * @param array $matches The regex matches from the tag specifying the
     *                       link. The ASIN for the product should be in array
     *                       entry 1.
     *
     * @return string        The URL for the product page.
     */
    public static function link($matches) {
        $asin = $matches[1];
        $link = '';
        $params = array(
            'ie' => 'UTF8',
            'tag' => $_ENV['AMAZON_AFFILIATE_TAG'],
            'linkCode' => $_ENV['AMAZON_AFFILIATE_LINK_CODE'],
            'camp' => $_ENV['AMAZON_AFFILIATE_CAMP'],
            'creative' => $_ENV['AMAZON_AFFILIATE_CREATIVE'],
            'creativeASIN' => $asin
        );
        foreach ($params as $k => $v) {
            $link .= $link ? '&amp;' : '?';
            $link .= "$k=$v";
        }
        return "http://www.amazon.com/gp/product/$asin?$link";
    }

    /**
     * Create an Amazon affiliate tracking bug image tag.
     *
     * @param array $matches The regex matches from the tag specifying the
     *                       bug. The ASIN for the product should be in array
     *                       entry 1.
     *
     * @return string        A HTML img tag for the bug.
     */
    public static function bug($matches) {
        $asin = $matches[1];
        $link = '';
        $params = array(
            't' => $_ENV['AMAZON_AFFILIATE_TAG'],
            'l' => $_ENV['AMAZON_AFFILIATE_LINK_CODE'],
            'o' => 1,
            'a' => $asin
        );
        foreach ($params as $k => $v) {
            $link .= $link ? '&amp;' : '?';
            $link .= "$k=$v";
        }
        $link = "http://www.assoc-amazon.com/e/ir$link";
        $bug = <<<HTML
            <img
                style="border:none !important; margin:0px !important;"
                src="$link"
                alt="" width="1" height="1"
            />
HTML;
        return trim($bug);
    }
}

?>
