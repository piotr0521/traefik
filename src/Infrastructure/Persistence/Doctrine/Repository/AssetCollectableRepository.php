<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\Asset;
use Groshy\Entity\AssetCollectable;
use Talav\Component\Resource\Repository\ResourceRepository;

final class AssetCollectableRepository extends ResourceRepository
{
    public function getCollectableAsset(): AssetCollectable
    {
        return $this->findOneBy(['name' => Asset::ASSET_COLLECTABLE]);
    }
}
