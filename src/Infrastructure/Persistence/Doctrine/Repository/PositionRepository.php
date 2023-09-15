<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Groshy\Entity\AssetType;
use Talav\Component\Resource\Repository\ResourceRepository;
use Talav\Component\User\Model\UserInterface;

final class PositionRepository extends ResourceRepository
{
    use FilterByAssetTypeTrait;

    public function byType(AssetType $type, UserInterface $user): array
    {
        return $this->createQueryBuilder('position')
            ->select(['position'])
            ->leftJoin('position.asset', 'asset')
            ->leftJoin('asset.assetType', 'assetType')
            ->andWhere('asset.assetType = :assetType OR assetType.parent = :assetType')
            ->andWhere('position.createdBy = :user')
            ->setParameter('assetType', $type)
            ->setParameter('user', $user)
            ->orderBy('position.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function byTypeName(string $name, UserInterface $user): array
    {
        return $this->createQueryBuilder('position')
            ->select(['position'])
            ->leftJoin('position.asset', 'asset')
            ->leftJoin('asset.assetType', 'assetType')
            ->andWhere('position.createdBy = :user')
            ->andWhere('assetType.name = :name')
            ->setParameter('name', $name)
            ->setParameter('user', $user)
            ->orderBy('position.startDate', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    // List of ids of active positions and assets in the time frame
    public function getByInterval(DateTime $from, DateTime $to, UserInterface $user, ?AssetType $type = null): array
    {
        $query = $this->createQueryBuilder('position')
            ->select(['position', 'asset'])
            ->leftJoin('position.asset', 'asset')
            ->where('position.createdBy = :user')
            ->andWhere('position.startDate <= :to OR position.startDate IS NULL')
            ->andWhere('position.completeDate >= :from OR position.completeDate IS NULL')
            ->setParameter('user', $user)
            ->setParameter('from', $from)
            ->setParameter('to', $to);

        return $this->filterByAssetType($query, $type)->getQuery()->getResult();
    }

    public function groupBySponsor(UserInterface $user): array
    {
        return $this->createQueryBuilder('position')
            ->select(['sponsor.id as id', 'SUM(value.amount.amount) as total'])
            ->leftJoin('position.asset', 'asset')
            ->leftJoin('position.lastValue', 'value')
            ->innerJoin('asset.sponsor', 'sponsor')
            ->andWhere('position.createdBy = :user')
            ->setParameter('user', $user)
            ->groupBy('sponsor.name')
            ->orderBy('total', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getArrayResult();
    }

    public function groupByYear(UserInterface $user): array
    {
        return $this->createQueryBuilder('position')
            ->select(['YEAR(position.startDate) as year', 'SUM(value.amount.amount) as total'])
            ->leftJoin('position.lastValue', 'value')
            ->andWhere('position.createdBy = :user')
            ->andWhere('YEAR(position.startDate) > 0')
            ->setParameter('user', $user)
            ->groupBy('year')
            ->orderBy('year')
            ->getQuery()
            ->getArrayResult();
    }

    public function getAssetTypeId(UserInterface $user): array
    {
        return $this->createQueryBuilder('position')
            ->select(['assetType.id'])
            ->leftJoin('position.asset', 'asset')
            ->leftJoin('asset.assetType', 'assetType')
            ->andWhere('position.createdBy = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function getAssetIdByPositionIds(array $positionIds): array
    {
        return $this->createQueryBuilder('position')
            ->select(['position'])
            ->leftJoin('position.asset', 'asset')
            ->leftJoin('asset.assetType', 'assetType')
            ->andWhere('assetType.isQuantity = :isQuantity')
            ->andWhere('position.id IN (:ids)')
            ->setParameter('isQuantity', true)
            ->setParameter('ids', $positionIds)
            ->getQuery()
            ->getResult();
    }
}
