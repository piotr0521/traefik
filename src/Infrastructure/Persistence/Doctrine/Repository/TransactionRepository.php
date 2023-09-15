<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Groshy\Entity\AssetType;
use Groshy\Entity\Position;
use Groshy\Entity\Transaction;
use Pagerfanta\Pagerfanta;
use Talav\Component\Resource\Repository\ResourceRepository;
use Talav\Component\User\Model\UserInterface;

final class TransactionRepository extends ResourceRepository
{
    public function byType(AssetType $type, UserInterface $user): array
    {
        return $this->createQueryBuilder('transaction')
            ->select(['transaction', 'position', 'asset'])
            ->leftJoin('transaction.position', 'position')
            ->leftJoin('position.asset', 'asset')
            ->leftJoin('asset.assetType', 'assetType')
            ->andWhere('asset.assetType = :assetType OR assetType.parent = :assetType')
            ->andWhere('position.createdBy = :user')
            ->setParameter('assetType', $type)
            ->setParameter('user', $user)
            ->orderBy('transaction.transactionDate', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function byUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('transaction')
            ->select(['transaction'])
            ->leftJoin('transaction.position', 'position')
            ->andWhere('position.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('transaction.transactionDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function byPositionPager(Position $position, UserInterface $user): Pagerfanta
    {
        return $this->getPaginator($this->createQueryBuilder('transaction')
            ->select(['transaction', 'position', 'asset'])
            ->leftJoin('transaction.position', 'position')
            ->leftJoin('position.asset', 'asset')
            ->andWhere('position.createdBy = :user')
            ->andWhere('transaction.position = :position')
            ->setParameter('position', $position)
            ->setParameter('user', $user)
            ->orderBy('transaction.transactionDate', 'DESC'));
    }

    public function sumPositive(Position $position): int
    {
        return (int) $this->createQueryBuilder('transaction')
            ->select(['SUM(transaction.amount.amount)'])
            ->andWhere('transaction.position = :position')
            ->andWhere('transaction.amount.amount > 0')
            ->setParameter('position', $position)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function sumNegative(Position $position): int
    {
        return (int) $this->createQueryBuilder('transaction')
            ->select(['SUM(transaction.amount.amount)'])
            ->andWhere('transaction.position = :position')
            ->andWhere('transaction.amount.amount < 0')
            ->setParameter('position', $position)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function deleteByPosition(Position $position): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->delete($this->_entityName, 'transaction')
            ->andWhere('transaction.position = :position')
            ->setParameter('position', $position)
            ->getQuery()
            ->execute();
    }

    public function getBalanceTransaction(Position $position, DateTime $date): ?Transaction
    {
        return $this->createQueryBuilder('transaction')
            ->select(['transaction'])
            ->leftJoin('transaction.position', 'position')
            ->leftJoin('transaction.type', 'type')
            ->andWhere('transaction.position = :position')
            ->andWhere('transaction.transactionDate = :date')
            ->andWhere('type.isBalance = :flag')
            ->setParameter('position', $position)
            ->setParameter('date', $date)
            ->setParameter('flag', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByPositionsAndInterval(array $positions, DateTime $from, DateTime $to): iterable
    {
        return $this->createQueryBuilder('transaction')
            ->select(['transaction', 'position'])
            ->leftJoin('transaction.position', 'position')
            ->andWhere('transaction.position IN (:positions)')
            ->andWhere('transaction.transactionDate >= :from')
            ->andWhere('transaction.transactionDate <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('positions', $positions)
            ->orderBy('transaction.transactionDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
