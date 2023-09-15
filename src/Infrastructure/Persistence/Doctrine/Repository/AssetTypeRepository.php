<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\AssetType;
use Talav\Component\Resource\Repository\ResourceRepository;

final class AssetTypeRepository extends ResourceRepository
{
    public function getSidebarMenu(bool $isAsset): iterable
    {
        return $this->createQueryBuilder('t')
            ->select(['t', 'c'])
            ->leftJoin('t.children', 'c')
            ->andWhere('t.parent IS NULL')
            ->andWhere('t.isAsset = :isAsset')
            ->setParameter('isAsset', $isAsset)
            ->orderBy('t.position, c.position')
            ->getQuery()
            ->getResult();
    }

    public function getDashboardType(): AssetType
    {
        return $this->findOneBy(['name' => 'Real Estate']);
    }
}
