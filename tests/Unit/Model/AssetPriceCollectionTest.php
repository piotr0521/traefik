<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Model;

use DateTime;
use Groshy\Model\AssetPriceCollection;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class AssetPriceCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_merges_two_collections()
    {
        $date1 = new DateTime();
        $collection1 = new AssetPriceCollection();
        $collection1->set($date1, new Money(100, new Currency('USD')));

        $date2 = new DateTime('-1 month');
        $collection2 = new AssetPriceCollection();
        $collection2->set($date2, new Money(50, new Currency('USD')));

        $collection1->merge($collection2);
        self::assertCount(2, $collection1);
        self::assertArrayHasKey($date1->format('Y-m-d'), $collection1);
        self::assertArrayHasKey($date2->format('Y-m-d'), $collection1);
    }

    /**
     * @test
     */
    public function it_adds_final_record_to_close_the_range()
    {
        $date = new DateTime('-1 month');
        $collection = new AssetPriceCollection();
        $collection->set($date, new Money(100, new Currency('USD')));
        $collection->closeRange(new DateTime());

        self::assertCount(2, $collection);
        self::assertArrayHasKey($date->format('Y-m-d'), $collection);
        self::assertArrayHasKey((new DateTime())->format('Y-m-d'), $collection);
    }
}
