<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Groshy\Entity\AssetSecurityPrice;
use Talav\Component\Resource\Repository\ResourceRepository;

final class AssetSecurityPriceRepository extends ResourceRepository
{
    public function findAllByAssetsAndInterval(array $assetIds, DateTime $from, DateTime $to): array
    {
        return $this->createQueryBuilder('price')
            ->select('price', 'asset')
            ->leftJoin('price.asset', 'asset')
            ->andWhere('asset.id IN (:assetIds)')
            ->andWhere('price.pricedAt >= :from')
            ->andWhere('price.pricedAt <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('assetIds', $assetIds)
            ->orderBy('price.pricedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLastByAssetsAndBeforeDate(array $assetIds, DateTime $before): array
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addEntityResult(AssetSecurityPrice::class, 'asp');
        $rsm->addFieldResult('asp', 'id', 'id');

        $query = $this->_em->createNativeQuery('
            WITH t1 AS ( 
                SELECT id, asset_id, priced_at, RANK() OVER (
                    PARTITION BY asset_id 
                    ORDER BY priced_at DESC 
                ) AS rank FROM asset_security_price WHERE priced_at <= :before AND asset_id IN (:ids)
            ) SELECT id FROM t1 WHERE rank = 1', $rsm);
        $query->setParameter('before', $before);
        $query->setParameter('ids', $assetIds);

        // run regular query to avoid working with partial objects
        return $this->createQueryBuilder('price')
            ->select(['price', 'asset'])
            ->leftJoin('price.asset', 'asset')
            ->andWhere('price.id IN (:ids)')
            ->setParameter('ids', $query->getSingleColumnResult())
            ->getQuery()
            ->getResult();
    }
}
