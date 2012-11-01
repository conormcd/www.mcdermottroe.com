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

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for AmazonAffiliate.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class AmazonAffiliateTest
extends TestCase
{
    /**
     * Make sure that AmazonAffiliate::link actually produces a link.
     *
     * @return void
     */
    public function testLinkProducesLink() {
        $link = AmazonAffiliate::link(array('', 'My_Test_ASIN'));
        $this->assertNotNull($link);
        $this->assertStringStartsWith('http://', $link);
    }

    /**
     * Make sure that AmazonAffiliate::link contains the data we primed it
     * with.
     *
     * @return void
     */
    public function testLinkContainsData() {
        $link = AmazonAffiliate::link(array('', 'My_Test_ASIN'));
        $this->assertRegexp("/My_Test_ASIN/", $link);
        foreach ($this->amazonEnvValues() as $value) {
            $this->assertRegexp("/$value/", $link);
        }
    }

    /**
     * Check that AmazonAffiliate::bug generates an image tag.
     *
     * @return void
     */
    public function testBugProducesImageTag() {
        $bug = AmazonAffiliate::bug(array('', 'My_Test_ASIN'));
        $this->assertNotNull($bug);
        $this->assertRegexp('/^<img[^>]*>$/s', $bug);
    }

    /**
     * Make sure that AmazonAffiliate::bug includes the critical pieces of
     * data.
     *
     * @return void
     */
    public function testBugContainsData() {
        $bug = AmazonAffiliate::bug(array('', 'My_Test_ASIN'));
        $this->assertRegexp("/My_Test_ASIN/", $bug);
        $this->assertRegexp("/{$_ENV['AMAZON_AFFILIATE_TAG']}/", $bug);
        $this->assertRegexp("/{$_ENV['AMAZON_AFFILIATE_LINK_CODE']}/", $bug);
    }

    /**
     * Pull out the values of the Amazon environment variables.
     *
     * @return array The values of the Amazon environment variables.
     */
    private function amazonEnvValues() {
        $ret = array();
        foreach ($_ENV as $key => $value) {
            if (preg_match('/^AMAZON_/', $key)) {
                $ret[] = $value;
            }
        }
        return $ret;
    }
}

?>
