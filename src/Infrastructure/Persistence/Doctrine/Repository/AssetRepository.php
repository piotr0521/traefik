<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\Sponsor;
use Talav\Component\Resource\Repository\ResourceRepository;

final class AssetRepository extends ResourceRepository
{
    public function countBySponsor(Sponsor $sponsor): int
    {
        return $this->createQueryBuilder('asset')
            ->select(['COUNT(asset.id)'])
            ->andWhere('asset.sponsor = :sponsor')
            ->setParameter('sponsor', $sponsor)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
