<?php

declare(strict_types=1);

namespace Groshy\Provider;

use DateTime;
use Groshy\Entity\Asset;
use Groshy\Entity\Position;
use Groshy\Model\AssetListPriceCollection;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class AssetListPriceCollectionFactory
{
    public function __construct(
        private readonly RepositoryInterface $assetSecurityPriceRepository,
        private readonly RepositoryInterface $assetCryptoPriceRepository,
    ) {
    }

    public function buildList(array $positions, DateTime $from, DateTime $to): AssetListPriceCollection
    {
        $assets = array_map(fn (Position $value): Asset => $value->getAsset(), $positions);
        $list = new AssetListPriceCollection();
        $list->merge(AssetListPriceCollection::factory($this->assetSecurityPriceRepository->findAllByAssetsAndInterval($assets, $from, $to)));
        $list->merge(AssetListPriceCollection::factoryForcedDate($this->assetSecurityPriceRepository->findLastByAssetsAndBeforeDate($assets, $from), $from));
        $list->merge(AssetListPriceCollection::factory($this->assetCryptoPriceRepository->findAllByAssetsAndInterval($assets, $from, $to)));
        $list->merge(AssetListPriceCollection::factoryForcedDate($this->assetCryptoPriceRepository->findLastByAssetsAndBeforeDate($assets, $from), $from));
        $list->closeRange($to);

        return $list;
    }
}
