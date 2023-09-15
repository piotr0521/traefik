<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Entity;

use Groshy\Entity\PositionStatsData;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class PositionStatsDataTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_iterator_without_null_values()
    {
        $data = new PositionStatsData();
        foreach ($data->getIterator() as $key => $value) {
            self::assertNotNull($value);
            self::assertContains($key, ['count']);
        }
        $data = new PositionStatsData(active: 2);
        foreach ($data->getIterator() as $key => $value) {
            self::assertNotNull($value);
            self::assertContains($key, ['count', 'active']);
        }
    }

    /**
     * @test
     */
    public function it_merges_two_objects()
    {
        $data1 = new PositionStatsData(count: 1, active: 2, capitalCommitted: Money::USD(100));
        $data2 = new PositionStatsData(count: 1, active: 3, new:1, capitalCommitted: Money::USD(200));
        $data1->merge($data2);
        $result = iterator_to_array($data1->getIterator());
        self::assertCount(4, $result);
        self::assertArrayHasKey('count', $result);
        self::assertArrayHasKey('active', $result);
        self::assertArrayHasKey('new', $result);
        self::assertArrayHasKey('capitalCommitted', $result);
        self::assertEquals(2, $result['count']);
        self::assertEquals(5, $result['active']);
        self::assertEquals(1, $result['new']);
        self::assertTrue(Money::USD(300)->equals($result['capitalCommitted']));
    }
}
