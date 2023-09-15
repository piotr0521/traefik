<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Groshy\Entity\AssetCrypto;
use Groshy\Entity\AssetType;
use Symfony\Component\Yaml\Yaml;
use Talav\Component\Resource\Manager\ManagerInterface;

final class AssetCryptoFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $assetTypeManager,
        private readonly ManagerInterface $assetCryptoManager,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function loadData(): void
    {
        return;
        $filename = 'crypto_all.yaml';
        $counter = 0;
        $batchSize = 1000;
        $assetType = $this->loadType();
        $data = Yaml::parseFile(dirname(__FILE__).'/files/'.$filename);
        $allowed = $this->loadAllowedCurrencies();
        foreach ($data as $el) {
            $el['name'] = trim($el['name']);
            $el['symbol'] = trim($el['symbol']);
            if ('' == $el['symbol'] || '' == $el['name']) {
                continue;
            }
            if (strlen($el['symbol']) > 10) {
                continue;
            }
            if (!in_array($el['symbol'], $allowed)) {
                continue;
            }
            ++$counter;
            /** @var AssetCrypto $asset */
            $asset = $this->assetCryptoManager->create();
            $asset->setName($el['name']);
            $asset->setAssetType($assetType);
            $asset->setSymbol($el['symbol']);
            $this->assetCryptoManager->update($asset);
            if (($counter % $batchSize) === 0) {
                $this->assetCryptoManager->flush();
                $this->em->clear();
                gc_collect_cycles();
                $assetType = $this->loadType();
            }
        }
        $this->assetCryptoManager->flush();
    }

    public function getOrder(): int
    {
        return 15;
    }

    private function loadType(): AssetType
    {
        return $this->assetTypeManager->getRepository()->findOneBy(['name' => 'Cryptocurrency']);
    }

    private function loadAllowedCurrencies(): array
    {
        $result = [];
        if (($handle = fopen(dirname(__FILE__).'/files/digital_currency_list.csv', 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $result[] = trim($data[0]);
            }
            fclose($handle);
        }

        return $result;
    }
}
