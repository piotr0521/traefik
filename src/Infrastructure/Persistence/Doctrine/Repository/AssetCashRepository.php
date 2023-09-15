<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\Asset;
use Groshy\Entity\AssetCash;
use Talav\Component\Resource\Repository\ResourceRepository;

final class AssetCashRepository extends ResourceRepository
{
    public function getCashAsset(): AssetCash
    {
        return $this->findOneBy(['name' => Asset::ASSET_CASH]);
    }
}
