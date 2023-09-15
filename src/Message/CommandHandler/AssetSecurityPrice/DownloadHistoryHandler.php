<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\AssetSecurityPrice;

use AlphaVantage\Api\TimeSeries;
use AlphaVantage\Client as AlphaVantageClient;
use AlphaVantage\Exception\RuntimeException;
use DateInterval;
use DateTime;
use Groshy\Entity\AssetSecurityPrice;
use Groshy\Message\Command\AssetSecurityPrice\DownloadHistoryCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class DownloadHistoryHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $assetSecurityPriceManager,
        private readonly RepositoryInterface $assetSecurityRepository,
        private readonly AlphaVantageClient $client
    ) {
    }

    public function __invoke(DownloadHistoryCommand $message): void
    {
        $symbol = $message->symbol;
        $asset = $this->assetSecurityRepository->findOneBy(['symbol' => $symbol]);
        /** @var array<AssetSecurityPrice> $lastPrices */
        $lastPrices = $this->assetSecurityPriceManager->getRepository()->findBy(['asset' => $asset], ['pricedAt' => 'DESC'], 1);
        if (0 == count($lastPrices)) {
            $firstDate = new DateTime('-10 years');
            $format = TimeSeries::OUTPUT_TYPE_FULL;
        } else {
            $firstDate = $lastPrices[0]->getPricedAt()->add(DateInterval::createFromDateString('1 day'));
            $format = (new DateTime('now'))->diff($lastPrices[0]->getPricedAt())->format('%a') > 100 ? TimeSeries::OUTPUT_TYPE_FULL : TimeSeries::OUTPUT_TYPE_COMPACT;
        }
        try {
            $data = $this->client->timeSeries()->daily($symbol, $format);
        } catch (RuntimeException $e) {
            return;
        }
        if (!isset($data['Time Series (Daily)'])) {
            return;
        }
        foreach ($data['Time Series (Daily)'] as $key => $el) {
            $date = DateTime::createFromFormat('Y-m-d', $key);
            if ($date < $firstDate) {
                continue;
            }
            /** @var AssetSecurityPrice $price */
            $price = $this->assetSecurityPriceManager->create();
            $price->setPricedAt($date);
            $price->setPriceBaseUnit($el['4. close']);
            $price->setAsset($asset);
            $this->assetSecurityPriceManager->update($price);
        }
        $this->assetSecurityPriceManager->flush();
    }
}
