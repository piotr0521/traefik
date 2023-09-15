<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Groshy\Domain\Enum\SecurityType;
use Groshy\Entity\AssetSecurity;
use Groshy\Entity\AssetType;
use Symfony\Component\Yaml\Yaml;
use Talav\Component\Resource\Manager\ManagerInterface;

final class AssetSecurityFixtures extends BaseFixture implements OrderedFixtureInterface
{
    private array $types = [
        'Stock' => SecurityType::STOCK,
        'Managed Fund' => SecurityType::MUTUAL_FUND,
        'Money Market Fund' => SecurityType::MUTUAL_FUND,
        'ETF' => SecurityType::ETF,
    ];

    public function __construct(
        private readonly ManagerInterface $assetTypeManager,
        private readonly ManagerInterface $assetSecurityManager,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function loadData(): void
    {
        return;
        $this->loadFile('stock.yaml');
//        $this->loadFile('etf.yaml');
//        $this->loadFile('managed_fund.yaml');
//        $this->loadFile('money_market_fund.yaml');
    }

    private function loadFile(string $filename): void
    {
        $counter = 0;
        $batchSize = 1000;
        $assetType = $this->loadType();
        $data = Yaml::parseFile(dirname(__FILE__).'/files/'.$filename);
        foreach ($data as $el) {
            $el['name'] = trim($el['name']);
            $el['symbol'] = trim($el['symbol']);
            if ('' == $el['symbol'] || '' == $el['name']) {
                continue;
            }
            ++$counter;
            /** @var AssetSecurity $asset */
            $asset = $this->assetSecurityManager->create();
            $asset->setName($el['name']);
            $asset->setAssetType($assetType);
            $asset->setSymbol($el['symbol']);
            $asset->setSecurityType($this->types[$el['fundType']]);
            $this->assetSecurityManager->update($asset);
            if (($counter % $batchSize) === 0) {
                $this->assetSecurityManager->flush();
                $this->em->clear();
                gc_collect_cycles();
                $assetType = $this->loadType();
            }
        }
        $this->assetSecurityManager->flush();
    }

    public function getOrder(): int
    {
        return 15;
    }

    private function loadType(): AssetType
    {
        return $this->assetTypeManager->getRepository()->findOneBy(['name' => 'Public Equity']);
    }
}
