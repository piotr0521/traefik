<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\AssetCryptoPrice;

use AlphaVantage\Client as AlphaVantageClient;
use AlphaVantage\Exception\RuntimeException;
use DateInterval;
use DateTime;
use Groshy\Entity\AssetCryptoPrice;
use Groshy\Message\Command\AssetCryptoPrice\DownloadHistoryCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class DownloadHistoryHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $assetCryptoPriceManager,
        private readonly RepositoryInterface $assetCryptoRepository,
        private readonly AlphaVantageClient $client
    ) {
    }

    public function __invoke(DownloadHistoryCommand $message): void
    {
        $symbol = $message->symbol;
        $asset = $this->assetCryptoRepository->findOneBy(['symbol' => $symbol]);
        /** @var array<AssetCryptoPrice> $lastPrices */
        $lastPrices = $this->assetCryptoPriceManager->getRepository()->findBy(['asset' => $asset], ['pricedAt' => 'DESC'], 1);
        if (0 == count($lastPrices)) {
            $firstDate = new DateTime('-10 years');
        } else {
            $firstDate = $lastPrices[0]->getPricedAt()->add(DateInterval::createFromDateString('1 day'));
        }
        try {
            $data = $this->client->digitalCurrency()->digitalCurrencyDaily($symbol, 'USD');
        } catch (RuntimeException $e) {
            return;
        }
        if (!isset($data['Time Series (Digital Currency Daily)'])) {
            return;
        }
        foreach ($data['Time Series (Digital Currency Daily)'] as $key => $el) {
            $date = DateTime::createFromFormat('Y-m-d', $key);
            if ($date < $firstDate) {
                continue;
            }
            /** @var AssetCryptoPrice $price */
            $price = $this->assetCryptoPriceManager->create();
            $price->setPricedAt($date);
            $price->setPriceBaseUnit($el['4a. close (USD)']);
            $price->setAsset($asset);
            $this->assetCryptoPriceManager->update($price);
        }
        $this->assetCryptoPriceManager->flush();
    }
}
