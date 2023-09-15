<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use DateTime;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Groshy\Entity\AssetSecurityPrice;
use Symfony\Component\Yaml\Yaml;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class AssetSecurityPriceFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $assetSecurityPriceManager,
        private readonly RepositoryInterface $assetSecurityRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function loadData(): void
    {
        return;
        $this->loadFile('aapl.yaml', 'AAPL');
        $this->loadFile('abnb.yaml', 'ABNB');
        $this->loadFile('amzn.yaml', 'AMZN');
        $this->loadFile('googl.yaml', 'GOOGL');
        $this->loadFile('bkng.yaml', 'BKNG');
        $this->loadFile('t.yaml', 'T');
    }

    private function loadFile(string $filename, string $symbol): void
    {
        $asset = $this->assetSecurityRepository->findOneBy(['symbol' => $symbol]);
        $counter = 0;
        $batchSize = 1000;
        $data = Yaml::parseFile(dirname(__FILE__).'/files/'.$filename);
        foreach ($data['Time Series (Daily)'] as $key => $el) {
            $date = DateTime::createFromFormat('Y-m-d', $key);
            if ($date < new DateTime('-10 years')) {
                continue;
            }
            ++$counter;
            /** @var AssetSecurityPrice $price */
            $price = $this->assetSecurityPriceManager->create();
            $price->setPricedAt(DateTime::createFromFormat('Y-m-d', $key));
            $price->setPriceBaseUnit($el['4. close']);
            $price->setAsset($asset);
            $this->assetSecurityPriceManager->update($price);
            if (($counter % $batchSize) === 0) {
                $this->assetSecurityPriceManager->flush();
                $this->em->clear();
                gc_collect_cycles();
                $asset = $this->assetSecurityRepository->findOneBy(['symbol' => $symbol]);
            }
        }
        $this->assetSecurityPriceManager->flush();
    }

    public function getOrder(): int
    {
        return 20;
    }
}
