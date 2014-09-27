<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for JSON.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class JSONTest
extends TestCase
{
    /**
     * Test a few values can be encoded into an expected format.
     *
     * @return void
     */
    public function testEncode() {
        $samples = array(
            '{}' => array(),
            '[1,2,3]' => array(1, 2, 3),
            '{"foo":"bar"}' => array('foo' => 'bar'),
            '{"foo":{}}' => array('foo' => array()),
            '{"foo":[1,2,3]}' => array('foo' => array(1, 2, 3)),
        );
        foreach ($samples as $expected => $object) {
            $this->assertEquals($expected, JSON::encode($object));
        }
    }

    /**
     * Test a few values can be decoded into an expected format.
     *
     * @return void
     */
    public function testDecode() {
        $samples = array(
            '{}' => array(),
            '[1,2,3]' => array(1, 2, 3),
            '{"foo":"bar"}' => array('foo' => 'bar'),
            '{"foo":{}}' => array('foo' => array()),
            '{"foo":[1,2,3]}' => array('foo' => array(1, 2, 3)),
        );
        foreach ($samples as $json => $expected) {
            $this->assertEquals($expected, JSON::decode($json));
        }
    }

    /**
     * Check that a selection of values can be round-tripped in both directions
     * through the JSON class.
     *
     * @return void
     */
    public function testRoundTrip() {
        $encode_decode = array(
            array(),
            array(1,2,3),
            array('foo' => 'bar', 'baz' => 'quux'),
        );
        foreach ($encode_decode as $sample) {
            $encoded = JSON::encode($sample);
            $decoded = JSON::decode($encoded);
            $this->assertEquals($sample, $decoded);
        }

        $decode_encode = array(
            '{}',
            '[1,2,3]',
            '{"foo":"bar"}',
            '{"foo":{}}',
        );
        foreach ($decode_encode as $sample) {
            $decoded = JSON::decode($sample);
            $encoded = JSON::encode($decoded);
            $this->assertEquals($sample, $encoded);
        }
    }

    /**
     * Make sure that encode refuses to encode some values but let decode
     * decode the same ones. I don't want to spread bad JSON around, but I
     * don't want to choke on it unnecessarily.
     *
     * @return void
     */
    public function testEncodeLessForgivingThanDecode() {
        // null
        $this->assertEquals(null, JSON::decode('null'));
        $this->assertException(
            function () {
                JSON::encode(null);
            }
        );

        // true
        $this->assertEquals(true, JSON::decode('true'));
        $this->assertException(
            function () {
                JSON::encode(true);
            }
        );

        // false
        $this->assertEquals(false, JSON::decode('false'));
        $this->assertException(
            function () {
                JSON::encode(false);
            }
        );

        // a naked int
        $this->assertEquals(1, JSON::decode('1'));
        $this->assertException(
            function () {
                JSON::encode(1);
            }
        );

        // a naked double
        $this->assertEquals(3.14, JSON::decode('3.14'));
        $this->assertException(
            function () {
                JSON::encode(3.14);
            }
        );

        // a raw string
        $this->assertEquals("foo", JSON::decode('"foo"'));
        $this->assertException(
            function () {
                JSON::encode("foo");
            }
        );
    }

    /**
     * Ensure that it's possible to encode an object.
     *
     * @return void
     */
    public function testObjectsGetEncoded() {
        $object = new stdclass();
        $object->member = 'value';
        $this->assertEquals(
            '{"member":"value"}',
            JSON::encode($object)
        );
    }

    /**
     * Trigger JSON_ERROR_DEPTH.
     *
     * @return void
     */
    public function testOverlyNestedJSONCausesException() {
        $json = '{}';
        for ($i = 0; $i < 1024; $i++) {
            $json = "{\"inner\": $json}";
        }
        $this->assertException(
            function () use ($json) {
                JSON::decode($json);
            }
        );
    }

    /**
     * Trigger JSON_ERROR_CTRL_CHAR.
     *
     * @return void
     */
    public function testControlCharactersInInputCausesException() {
        $this->assertException(
            function () {
                JSON::decode("{\"foo\":\"\x01\"}");
            }
        );
    }

    /**
     * Trigger JSON_ERROR_SYNTAX.
     *
     * @return void
     */
    public function testMalformedJSONCausesSyntaxError() {
        $this->assertException(
            function () {
                JSON::decode('{"foo":}');
            }
        );
    }

    /**
     * Trigger JSON_ERROR_UTF8.
     *
     * @return void
     */
    public function testMalformedUTF8CausesException() {
        $this->assertException(
            function () {
                JSON::decode("{\"Bad UTF-8\": \"\x80\"}");
            }
        );
    }

    /**
     * Trigger JSON_ERROR_RECURSION or the old PHP error for recursion.
     *
     * @return void
     */
    public function testRecursiveStructureCausesException() {
        $array = array();
        $array = array('foo' => &$array);
        $this->assertException(
            function () {
                JSON::encode($array);
            }
        );
    }

    /**
     * Trigger JSON_ERROR_INF_OR_NAN or the old PHP errors for Inf/NaN.
     *
     * @return void
     */
    public function testInfOrNaNCausesException() {
        if ($this->isHHVMOlderThan33()) {
            $this->markTestSkipped('This test fails on old version of HHVM');
        }
        $this->assertException(
            function () {
                JSON::encode(array('Infinity' => INF));
            },
            null,
            "Failed to throw an exception on encoding INF"
        );
        $this->assertException(
            function () {
                JSON::encode(array('NaN' => NAN));
            },
            null,
            "Failed to throw an exception on encoding NAN"
        );
    }

    /**
     * Trigger JSON_ERROR_UNSUPPORTED_TYPE or the old PHP error for an
     * unsupported type.
     *
     * @return void
     */
    public function testUnsupportedTypeCausesException() {
        if ($this->isHHVMOlderThan33()) {
            $this->markTestSkipped('This test fails on old version of HHVM');
        }
        $this->assertException(
            function () {
                JSON::encode(array('Resource' => fopen('/dev/null', 'r')));
            }
        );
    }

    /**
     * Check if we're running on an old version of HHVM.
     *
     * @return boolean True iff we're running on HHVM *and* the HHVM version is
     *                 older than 3.3.0. On versions older than that some of
     *                 the error handling for json_encode was not fully
     *                 implemented.
     */
    private function isHHVMOlderThan33() {
        $version = $this->phpVersion();
        if ($version['implementation'] == 'hhvm') {
            if ($version['implementation_version'] < 3003000) {
                return true;
            }
        }
        return false;
    }
}

?>
