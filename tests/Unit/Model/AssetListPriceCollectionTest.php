<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Model;

use DateTime;
use Groshy\Entity\AssetSecurity;
use Groshy\Entity\AssetSecurityPrice;
use Groshy\Model\AssetListPriceCollection;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class AssetListPriceCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_merges_two_collections()
    {
        $price1 = $this->createPrice();
        $price2 = $this->createPrice(date: '-1 month', amount: 200);

        $list = AssetListPriceCollection::factory([$price1, $price2]);
        self::assertCount(2, $list);
    }

    /**
     * @test
     */
    public function it_generates_final_values()
    {
        $price1 = $this->createPrice(date: '-1 month');
        $price2 = $this->createPrice(date: '-1 month', amount: 200);

        $list = AssetListPriceCollection::factory([$price1, $price2]);
        $list->closeRange(new DateTime());
        foreach ($list as $el) {
            self::assertCount(2, $el);
        }
    }

    private function createPrice(
        string $date = '- 10 days',
        int $amount = 100,
        ?UuidInterface $uuid = null,
    ): AssetSecurityPrice {
        if (is_null($uuid)) {
            $uuid = Uuid::uuid4();
        }
        $date1 = new DateTime($date);
        $money1 = new Money($amount, new Currency('USD'));
        $asset1 = new AssetSecurity();
        $asset1->setId($uuid);
        $price1 = new AssetSecurityPrice();
        $price1->setAsset($asset1);
        $price1->setPricedAt($date1);
        $price1->setPrice($money1);

        return $price1;
    }
}
