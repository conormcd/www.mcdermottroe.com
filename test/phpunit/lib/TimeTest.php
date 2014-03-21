<?php

require_once dirname(dirname(dirname(__DIR__))) . '/lib/autoloader.php';

/**
 * Tests for Time.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TimeTest
extends TestCase
{
    /**
     * Test Time::day()
     *
     * @return void
     */
    public function testDay() {
        $test_data = array(
            strtotime('2014-01-01 12:00:00') => '1st January 2014',
            strtotime('2014-01-02 12:00:00') => '2nd January 2014',
            strtotime('2014-01-03 12:00:00') => '3rd January 2014',
            strtotime('2014-01-04 12:00:00') => '4th January 2014',
            strtotime('2014-01-05 12:00:00') => '5th January 2014',
            strtotime('2014-01-06 12:00:00') => '6th January 2014',
            strtotime('2014-01-07 12:00:00') => '7th January 2014',
            strtotime('2014-01-08 12:00:00') => '8th January 2014',
            strtotime('2014-01-09 12:00:00') => '9th January 2014',
            strtotime('2014-01-10 12:00:00') => '10th January 2014',
            strtotime('2014-01-11 12:00:00') => '11th January 2014',
            strtotime('2014-01-12 12:00:00') => '12th January 2014',
            strtotime('2014-01-13 12:00:00') => '13th January 2014',
            strtotime('2014-01-14 12:00:00') => '14th January 2014',
            strtotime('2014-01-15 12:00:00') => '15th January 2014',
            strtotime('2014-01-16 12:00:00') => '16th January 2014',
            strtotime('2014-01-17 12:00:00') => '17th January 2014',
            strtotime('2014-01-18 12:00:00') => '18th January 2014',
            strtotime('2014-01-19 12:00:00') => '19th January 2014',
            strtotime('2014-01-20 12:00:00') => '20th January 2014',
            strtotime('2014-01-21 12:00:00') => '21st January 2014',
            strtotime('2014-01-22 12:00:00') => '22nd January 2014',
            strtotime('2014-01-23 12:00:00') => '23rd January 2014',
            strtotime('2014-01-24 12:00:00') => '24th January 2014',
            strtotime('2014-01-25 12:00:00') => '25th January 2014',
            strtotime('2014-01-26 12:00:00') => '26th January 2014',
            strtotime('2014-01-27 12:00:00') => '27th January 2014',
            strtotime('2014-01-28 12:00:00') => '28th January 2014',
            strtotime('2014-01-29 12:00:00') => '29th January 2014',
            strtotime('2014-01-30 12:00:00') => '30th January 2014',
            strtotime('2014-01-31 12:00:00') => '31st January 2014',
            strtotime('2014-02-01 12:00:00') => '1st February 2014',
            strtotime('2014-03-01 12:00:00') => '1st March 2014',
            strtotime('2014-04-01 12:00:00') => '1st April 2014',
            strtotime('2014-05-01 12:00:00') => '1st May 2014',
            strtotime('2014-06-01 12:00:00') => '1st June 2014',
            strtotime('2014-07-01 12:00:00') => '1st July 2014',
            strtotime('2014-08-01 12:00:00') => '1st August 2014',
            strtotime('2014-09-01 12:00:00') => '1st September 2014',
            strtotime('2014-10-01 12:00:00') => '1st October 2014',
            strtotime('2014-11-01 12:00:00') => '1st November 2014',
            strtotime('2014-12-01 12:00:00') => '1st December 2014',
        );
        foreach ($test_data as $input => $output) {
            $this->assertEquals($output, Time::day($input));
        }
    }

    /**
     * Test Time::dateISO8601()
     *
     * @return void
     */
    public function testDateISO8601() {
        $test_data = array(
            1388577600 => '2014-01-01T12:00:00Z',
            1401624000 => '2014-06-01T12:00:00Z',
        );
        foreach ($test_data as $input => $output) {
            $this->assertEquals($output, Time::dateISO8601($input));
        }
    }

    /**
     * Test Time::dateRSS()
     *
     * @return void
     */
    public function testDateRSS() {
        $test_data = array(
            1388577600 => 'Wed, 01 Jan 2014 12:00:00 +0000',
            1401624000 => 'Sun, 01 Jun 2014 13:00:00 +0100',
        );
        foreach ($test_data as $input => $output) {
            $this->assertEquals($output, Time::dateRSS($input));
        }
    }
}

?>
