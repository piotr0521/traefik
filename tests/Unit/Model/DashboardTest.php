<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Model;

use Groshy\Model\Dashboard;
use PHPUnit\Framework\TestCase;

final class DashboardTest extends TestCase
{
    use DashboardValueTrait;

    /**
     * @test
     */
    public function it_returns_empty_array_if_no_data()
    {
        self::assertCount(2, Dashboard::toDashData([]));
    }

    /**
     * @test
     */
    public function it_returns_all_top_level_keys()
    {
        $value1 = $this->getAssetDashValue();
        $data = Dashboard::toDashData([$value1]);
        self::assertCount(5, array_keys($data));
        self::assertArrayHasKey('position', $data);
        self::assertArrayHasKey('type', $data);
        self::assertArrayHasKey('root_type', $data);
        self::assertArrayHasKey('total', $data);
        self::assertArrayHasKey('balance', $data);
    }

    /**
     * @test
     */
    public function it_considers_liabilities_for_total_calculation()
    {
        $value1 = $this->getAssetDashValue(value: '200', valueDate: '2022-06-01');
        $value2 = $this->getLiabilityDashValue(value: '100', valueDate:'2022-06-15');
        $data = Dashboard::toDashData([$value1, $value2]);
        $total = $data['total']['total'];
        self::assertEquals(200 - 100, $total['value']['current']);
        self::assertEquals(-100, $total['value']['change']['amount']);
        self::assertEquals(-100, $total['value']['change']['amount']);
        self::assertEquals(-50, $total['value']['change']['percent']);
    }
}
