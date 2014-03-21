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
        $affiliate_tag = Environment::get('AMAZON_AFFILIATE_TAG');
        $affiliate_link_code = Environment::get('AMAZON_AFFILIATE_LINK_CODE');

        $bug = AmazonAffiliate::bug(array('', 'My_Test_ASIN'));
        $this->assertRegexp("/My_Test_ASIN/", $bug);
        $this->assertRegexp("/$affiliate_tag/", $bug);
        $this->assertRegexp("/$affiliate_link_code/", $bug);
    }

    /**
     * Pull out the values of the Amazon environment variables.
     *
     * @return array The values of the Amazon environment variables.
     */
    private function amazonEnvValues() {
        $ret = array();
        $ret[] = Environment::get('AMAZON_AFFILIATE_TAG');
        $ret[] = Environment::get('AMAZON_AFFILIATE_LINK_CODE');
        $ret[] = Environment::get('AMAZON_AFFILIATE_CAMP');
        $ret[] = Environment::get('AMAZON_AFFILIATE_CREATIVE');
        return $ret;
    }
}

?>
