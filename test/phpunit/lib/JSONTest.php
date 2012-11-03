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
        $this->assertException(
            function () {
                JSON::encode(array('Infinity' => log(0)));
            }
        );
        $this->assertException(
            function () {
                JSON::encode(array('NaN' => acos(8)));
            }
        );
    }

    /**
     * Trigger JSON_ERROR_UNSUPPORTED_TYPE or the old PHP error for an 
     * unsupported type.
     *
     * @return void
     */
    public function testUnsupportedTypeCausesException() {
        $this->assertException(
            function () {
                JSON::encode(array('Resource' => fopen('/dev/null', 'r')));
            }
        );
    }
}

?>