<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\Domain;

use PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    /**
     * @test
     */
    public function it_converts_back_and_forth()
    {
        $string = '2014-03-12T14:17:19.176169+00:00';
        $dateTime = DateTime::fromString($string);

        $this->assertEquals($string, $dateTime->toString());
    }

    /**
     * @test
     */
    public function it_creates_now()
    {
        $this->assertInstanceOf('Broadway\Domain\DateTime', DateTime::now());
    }

    /**
     * @test
     *
     * @dataProvider provideDatesAndIntervals
     */
    public function it_adds_intervals($dateTime, $interval, $expectedDateTime)
    {
        $dateTime = DateTime::fromString($dateTime)->add($interval);

        $this->assertEquals($expectedDateTime, $dateTime->toString());
    }

    /**
     * @test
     *
     * @dataProvider provideDatesAndIntervals
     */
    public function it_subtracts_intervals($expectedDateTime, $interval, $dateTime)
    {
        $dateTime = DateTime::fromString($dateTime)->sub($interval);

        $this->assertEquals($expectedDateTime, $dateTime->toString());
    }

    /**
     * @test
     */
    public function it_returns_a_new_instance_when_adding_interval()
    {
        $dateTime = DateTime::fromString('2015-03-14T00:00:00.000000+00:00');
        $newDateTime = $dateTime->add('PT0S');

        $this->assertNotSame($newDateTime, $dateTime);
    }

    /**
     * @test
     *
     * @dataProvider provideDateDiffs
     */
    public function it_diffs2_dates($date1, $date2, $expectedDiff)
    {
        $diff = DateTime::fromString($date1)->diff(DateTime::fromString($date2));

        $this->assertEquals($expectedDiff['ymdhis'], $diff->format('%y%m%d%h%i%s'));
        $this->assertEquals($expectedDiff['days'], $diff->days, '"days" is incorrect');
        $this->assertEquals($expectedDiff['invert'], $diff->invert, '"invert" is incorrect');
    }

    /**
     * @test
     */
    public function it_compares2_dates()
    {
        $this->assertTrue(DateTime::fromString('2014-01-01T01:01:01.123456+0000')->equals(DateTime::fromString('2014-01-01T01:01:01.123456+0000')));  // exact the same
        $this->assertTrue(DateTime::fromString('2014-01-01T01:01:01.123456+02:00')->equals(DateTime::fromString('2014-01-01T01:01:01.123456+0200'))); // different TimeZone format
        $this->assertTrue(DateTime::fromString('2014-01-01T13:37:42.000000+0000')->equals(DateTime::fromString('2014-01-01T13:37:42+0000')));         // with and without milliseconds
    }

    /**
     * @test
     *
     * @dataProvider provideGreaterThanDates
     */
    public function it_returns_if_a_date_is_gt_another_date($date1, $date2, $bool)
    {
        $this->assertSame($bool, DateTime::fromString($date1)->comesAfter(DateTime::fromString($date2)));
    }

    /**
     * @test
     */
    public function it_returns_the_native_date_time_object()
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, DateTime::now()->toNative());
    }

    public function provideDatesAndIntervals()
    {
        return [
            ['2015-03-14T00:00:00.000000+00:00', 'P6W',            '2015-04-25T00:00:00.000000+00:00'],
            ['2000-01-01T00:00:00.000000+00:00', 'P7Y5M4DT4H3M2S', '2007-06-05T04:03:02.000000+00:00'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider provideBeginningOfWeek
     */
    public function it_converts_to_the_beginning_of_week($dateTime, $expectedBeginningOfWeek)
    {
        $beginningOfWeek = DateTime::fromString($dateTime)->toBeginningOfWeek();

        $this->assertEquals($expectedBeginningOfWeek, $beginningOfWeek->toString());
    }

    public function provideBeginningOfWeek()
    {
        return [
            ['2015-03-14T00:00:00.000000+00:00', '2015-03-09T00:00:00.000000+00:00'],
            ['2015-03-09T00:00:00.000000+00:00', '2015-03-09T00:00:00.000000+00:00'],
            ['2015-03-15T23:59:59.000000+00:00', '2015-03-09T00:00:00.000000+00:00'],
        ];
    }

    public function provideDateDiffs()
    {
        return [
            ['2014-04-22T13:37:42.123456+02:00', '2014-04-23T13:37:42.123456+02:00', ['ymdhis' => '001000', 'days' => 1,  'invert' => 0]],
            ['2014-04-22T13:37:42.123456+02:00', '2014-05-24T13:37:42.123456+02:00', ['ymdhis' => '012000', 'days' => 32, 'invert' => 0]],
            ['2014-04-22T13:37:42.123456+00:00', '2014-04-22T13:37:42.123456+02:00', ['ymdhis' => '000200', 'days' => 0,  'invert' => 1]],
        ];
    }

    public function provideGreaterThanDates()
    {
        return [
            ['2014-05-01T12:00:00.000000+00:00', '2014-05-01T12:00:00.000000+00:00', false], // equal
            ['2014-04-22T13:37:42.123456+02:00', '2014-04-22T13:37:42.123456+00:00', false], // timezone
            ['2014-04-22T13:37:42.123456+00:00', '2014-04-22T12:37:42.123456+00:00', true],  // time
            ['2014-04-21T13:37:42.123456+00:00', '2014-04-22T13:37:42.123456+00:00', false],  // date
        ];
    }
}
