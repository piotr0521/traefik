<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Groshy\Model\AssetListPriceCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AssetSecurityPriceRepositoryTest extends KernelTestCase
{
    private RepositoryInterface $assetSecurityRepository;
    private RepositoryInterface $assetSecurityPriceRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->assetSecurityRepository = static::getContainer()->get('app.repository.asset_security');
        $this->assetSecurityPriceRepository = static::getContainer()->get('app.repository.asset_security_price');
    }

    /**
     * @test
     */
    public function it_returns_list_of_prices_between_defined_dates_for_one_asset(): void
    {
        $asset = $this->assetSecurityRepository->findOneBy(['symbol' => 'AAPL']);
        $from = DateTime::createFromFormat('Y-m-d', '2022-06-01');
        $to = DateTime::createFromFormat('Y-m-d', '2022-06-15');
        /** @var AssetListPriceCollection $result */
        $result = $this->assetSecurityPriceRepository->findAllByAssetsAndInterval([$asset], $from, $to);
        self::assertCount(1, $result);
        self::assertCount(10, $result->first());
        foreach ($result->first() as $date => $element) {
            self::assertGreaterThanOrEqual('2022-06-01', $date);
            self::assertLessThanOrEqual('2022-06-15', $date);
        }
    }

    /**
     * @test
     */
    public function it_returns_list_of_prices_between_defined_dates_for_two_assets(): void
    {
        $assets = $this->assetSecurityRepository->findBy(['symbol' => ['AAPL', 'ABNB']]);
        $from = DateTime::createFromFormat('Y-m-d', '2022-06-01');
        $to = DateTime::createFromFormat('Y-m-d', '2022-06-15');
        /** @var AssetListPriceCollection $result */
        $result = $this->assetSecurityPriceRepository->findAllByAssetsAndInterval($assets, $from, $to);
        self::assertCount(2, $result);
        self::assertCount(10, $result->first());
        self::assertCount(10, $result->last());
    }

    /**
     * @test
     */
    public function it_returns_last_price_before_selected_date(): void
    {
        $asset1 = $this->assetSecurityRepository->findOneBy(['symbol' => 'AAPL']);
        $asset2 = $this->assetSecurityRepository->findOneBy(['symbol' => 'ABNB']);
        // 2022-06-05 is not a trading day
        $from = DateTime::createFromFormat('Y-m-d', '2022-06-05');
        /** @var AssetListPriceCollection $result */
        $result = $this->assetSecurityPriceRepository->findLastByAssetsAndBeforeDate([$asset1, $asset2], $from);
        self::assertCount(2, $result);
        self::assertTrue($result->containsKey($asset1->getId()));
        self::assertTrue($result->containsKey($asset2->getId()));

        self::assertCount(1, $result->get($asset1->getId()));
        self::assertCount(1, $result->get($asset2->getId()));

        self::assertTrue($result->get($asset1->getId())->containsKey($from));
        self::assertTrue($result->get($asset2->getId())->containsKey($from));

        self::assertEquals(14538, $result->get($asset1->getId())->get($from)->getAmount());
        self::assertEquals(11983, $result->get($asset2->getId())->get($from)->getAmount());
    }

    /**
     * @test
     */
    public function it_returns_empty_collection_if_there_is_no_price_history_before_provided_date(): void
    {
        $asset1 = $this->assetSecurityRepository->findOneBy(['symbol' => 'AAPL']);
        $asset2 = $this->assetSecurityRepository->findOneBy(['symbol' => 'ABNB']);
        $from = DateTime::createFromFormat('Y-m-d', '1900-06-05');
        /** @var AssetListPriceCollection $result */
        $result = $this->assetSecurityPriceRepository->findLastByAssetsAndBeforeDate([$asset1, $asset2], $from);
        self::assertCount(0, $result);
    }
}
