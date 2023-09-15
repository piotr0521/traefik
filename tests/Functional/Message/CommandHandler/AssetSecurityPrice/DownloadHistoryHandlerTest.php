<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\AssetSecurityPrice;

use AlphaVantage\Api\TimeSeries;
use AlphaVantage\Client as AlphaVantageClient;
use Groshy\Message\Command\AssetSecurityPrice\DownloadHistoryCommand;
use Groshy\Message\CommandHandler\AssetSecurityPrice\DownloadHistoryHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;
use Talav\Component\Resource\Repository\RepositoryInterface;

class DownloadHistoryHandlerTest extends KernelTestCase
{
    private ?RepositoryInterface $assetSecurityRepository;
    private ?RepositoryInterface $assetSecurityPriceRepository;
    private ?DownloadHistoryHandler $handler;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $data = Yaml::parseFile(realpath(__DIR__.'/../../../../var/mar.yaml'));
        $api = $this->createMock(TimeSeries::class);
        $client = $this->createMock(AlphaVantageClient::class);
        $client->expects($this->once())->method('__call')->willReturn($api);
        $api->expects($this->once())->method('daily')->willReturn($data);
        static::getContainer()->set(AlphaVantageClient::class, $client);

        $this->handler = static::getContainer()->get(DownloadHistoryHandler::class);
        $this->assetSecurityRepository = static::getContainer()->get('app.repository.asset_security');
        $this->assetSecurityPriceRepository = static::getContainer()->get('app.repository.asset_security_price');
    }

    /**
     * @test
     */
    public function it_creates_downloads_price_history_and_stores_it(): void
    {
        $symbol = 'MAR';
        $asset = $this->assetSecurityRepository->findOneBy(['symbol' => $symbol]);
        $this->assertCount(0, $this->assetSecurityPriceRepository->findBy(['asset' => $asset]));
        $this->handler->__invoke(new DownloadHistoryCommand($symbol));
        $this->assertGreaterThan(100, $this->assetSecurityPriceRepository->findBy(['asset' => $asset]));
    }
}
