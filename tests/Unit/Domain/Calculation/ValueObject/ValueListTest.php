<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Domain\Calculation\ValueObject;

use Groshy\Domain\Calculation\ValueObject\ValueList;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class ValueListTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_if_lists_have_different_keys()
    {
        self::expectException('InvalidArgumentException');
        $list1 = new ValueList([
            '2023-03-11' => Money::USD(99),
            '2023-03-12' => Money::USD(100),
        ]);
        $list2 = new ValueList([
            '2023-03-11' => Money::USD(99),
        ]);
        $list1->multiply($list2);
    }

    /**
     * @test
     */
    public function it_correctly_adds_two_value_lists()
    {
        $list1 = new ValueList([
            '2023-03-11' => Money::USD(99),
            '2023-03-12' => Money::USD(100),
        ]);
        $list2 = new ValueList([
            '2023-03-11' => Money::USD(99),
            '2023-03-12' => Money::USD(100),
        ]);
        $list3 = $list1->add($list2)->getValues();
        self::assertArrayHasKey('2023-03-11', $list3);
        self::assertArrayHasKey('2023-03-12', $list3);
        self::assertTrue(Money::USD(198)->equals($list3['2023-03-11']));
        self::assertTrue(Money::USD(200)->equals($list3['2023-03-12']));
    }

    /**
     * @test
     */
    public function it_correctly_multiplies_two_value_lists()
    {
        $list1 = new ValueList([
            '2023-03-11' => Money::USD(99),
            '2023-03-12' => Money::USD(100),
        ]);
        $list2 = new ValueList([
            '2023-03-11' => '10',
            '2023-03-12' => '100',
        ]);
        $list3 = $list1->multiply($list2)->getValues();
        self::assertArrayHasKey('2023-03-11', $list3);
        self::assertArrayHasKey('2023-03-12', $list3);
    }
}
