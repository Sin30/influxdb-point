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
            ['temperature' => 82],
            [
                'location' => 'us-midwest',
                'season' => 'summer',
            ],
            new \DateTime('2017-12-06 13:53:19')
            );
        $expected = 'weather,location=us-midwest,season=summer temperature=82 1512568399000000000';
        $actual = $point->getLineProtocol();
        $this->assertEquals($expected, $actual);
    }
}
