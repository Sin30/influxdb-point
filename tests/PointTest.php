<?php

namespace Sin30\InfluxDB\Tests;

use PHPUnit\Framework\TestCase;
use Sin30\InfluxDB\Point;

class PointTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldWork()
    {
        $point = new Point('weather',
            ['humidity' => '20%', 'temperature' => 82],
            [
                'location' => 'us-midwest',
                'season' => 'summer',
            ],
            new \DateTime('2017-12-06 13:53:19')
            );
        $expected = 'weather,location=us-midwest,season=summer humidity="20%",temperature=82 1512568399000000000';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itCanOmitTime()
    {
        $point = new Point('weather',
            ['humidity' => '20%', 'temperature' => 82],
            [
                'location' => 'us-midwest',
                'season' => 'summer',
            ]
        );
        $expected = 'weather,location=us-midwest,season=summer humidity="20%",temperature=82';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itCanOmitTags()
    {
        $point = new Point('weather',
            ['humidity' => '20%', 'temperature' => 82],
            [],
            new \DateTime('2017-12-06 13:53:19')
        );
        $expected = 'weather humidity="20%",temperature=82 1512568399000000000';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itCanOmitBothTagsAndTime()
    {
        $point = new Point('weather',
            ['humidity' => '20%', 'temperature' => 82]
        );
        $expected = 'weather humidity="20%",temperature=82';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itWillSortTagKeys()
    {
        $point = new Point('weather',
            ['humidity' => '20%', 'temperature' => 82],
            ['B' => 'b', 'A' => 'a']
        );
        $expected = 'weather,A=a,B=b humidity="20%",temperature=82';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itWillSortFieldKeys()
    {
        $point = new Point('weather',
            ['B' => 'b', 'A' => 'a']
        );
        $expected = 'weather A="a",B="b"';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itWillHandleFourFieldValueTypes()
    {
        $point = new Point('weather',
            ['humidity' => '20%', 'raining' => false, 'temperature' => 82, 'wind' => 8.3]
        );
        $expected = 'weather humidity="20%",raining=false,temperature=82,wind=8.3';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itShouldRemoveUnwantedCharsInMeasurementTagKeyTagValueAndFieldKey()
    {
        $point = new Point('\'" weather "\'',
            ['\'" humidity "\'' => '20%'],
            ['\'" location "\'' => '\'" us-midwest "\'']
        );
        $expected = 'weather,location=us-midwest humidity="20%"';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function itShouldEscapeUnwantedCharsInMeasurementTagKeyTagValueAndFieldKey()
    {
        $point = new Point('w,e=a ther',
            ['h,u=m idity' => '20%'],
            ['l,o=c ation' => 'us-m,i=d west']
        );
        $expected = 'w\,e\=a\ ther,l\,o\=c\ ation=us-m\,i\=d\ west h\,u\=m\ idity="20%"';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }
}
