<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\QueryBuilder;
use Groshy\Entity\AssetType;

trait FilterByAssetTypeTrait
{
    public function filterByAssetType(QueryBuilder $qb, ?AssetType $type): QueryBuilder
    {
        if (!is_null($type)) {
            $types = $type->getChildren()->toArray();
            $types[] = $type;
            $qb->andWhere('asset.assetType IN (:types)')
                ->setParameter('types', $types);
        }

        return $qb;
    }
}
