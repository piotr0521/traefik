<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\AssetCryptoPrice;

use AlphaVantage\Api\DigitalCurrency;
use AlphaVantage\Client as AlphaVantageClient;
use Groshy\Message\Command\AssetCryptoPrice\DownloadHistoryCommand;
use Groshy\Message\CommandHandler\AssetCryptoPrice\DownloadHistoryHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;
use Talav\Component\Resource\Repository\RepositoryInterface;

class DownloadHistoryHandlerTest extends KernelTestCase
{
    private ?RepositoryInterface $assetCryptoRepository;
    private ?RepositoryInterface $assetCryptoPriceRepository;
    private ?DownloadHistoryHandler $handler;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $data = Yaml::parseFile(realpath(__DIR__.'/../../../../var/ada.yaml'));
        $api = $this->createMock(DigitalCurrency::class);
        $client = $this->createMock(AlphaVantageClient::class);
        $client->expects($this->once())->method('__call')->willReturn($api);
        $api->expects($this->once())->method('digitalCurrencyDaily')->willReturn($data);
        static::getContainer()->set(AlphaVantageClient::class, $client);

        $this->handler = static::getContainer()->get(DownloadHistoryHandler::class);
        $this->assetCryptoRepository = static::getContainer()->get('app.repository.asset_crypto');
        $this->assetCryptoPriceRepository = static::getContainer()->get('app.repository.asset_crypto_price');
    }

    /**
     * @test
     */
    public function it_creates_downloads_price_history_and_stores_it(): void
    {
        $symbol = 'ADA';
        $asset = $this->assetCryptoRepository->findOneBy(['symbol' => $symbol]);
        $this->assertCount(0, $this->assetCryptoPriceRepository->findBy(['asset' => $asset]));
        $this->handler->__invoke(new DownloadHistoryCommand($symbol));
        $this->assertGreaterThan(100, $this->assetCryptoPriceRepository->findBy(['asset' => $asset]));
    }
}
