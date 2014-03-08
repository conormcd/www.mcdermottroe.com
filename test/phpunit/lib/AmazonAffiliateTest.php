<?php

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
