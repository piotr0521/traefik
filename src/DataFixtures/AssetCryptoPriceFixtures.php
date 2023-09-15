<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use DateTime;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Groshy\Entity\AssetCryptoPrice;
use Symfony\Component\Yaml\Yaml;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class AssetCryptoPriceFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $assetCryptoPriceManager,
        private readonly RepositoryInterface $assetCryptoRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function loadData(): void
    {
        return;
        $this->loadFile('btc.yaml', 'BTC');
        $this->loadFile('doge.yaml', 'DOGE');
        $this->loadFile('eth.yaml', 'ETH');
    }

    private function loadFile(string $filename, string $symbol): void
    {
        $asset = $this->assetCryptoRepository->findOneBy(['symbol' => $symbol]);
        $counter = 0;
        $batchSize = 1000;
        $data = Yaml::parseFile(dirname(__FILE__).'/files/'.$filename);
        foreach ($data['Time Series (Digital Currency Daily)'] as $key => $el) {
            $date = DateTime::createFromFormat('Y-m-d', $key);
            if ($date < new DateTime('-10 years')) {
                continue;
            }
            ++$counter;
            /** @var AssetCryptoPrice $price */
            $price = $this->assetCryptoPriceManager->create();
            $price->setPricedAt($date);
            $price->setPriceBaseUnit($el['4b. close (USD)']);
            $price->setAsset($asset);
            $this->assetCryptoPriceManager->update($price);
            if (($counter % $batchSize) === 0) {
                $this->assetCryptoPriceManager->flush();
                $this->em->clear();
                gc_collect_cycles();
                $asset = $this->assetCryptoRepository->findOneBy(['symbol' => $symbol]);
            }
        }
        $this->assetCryptoPriceManager->flush();
    }

    public function getOrder(): int
    {
        return 20;
    }
}
