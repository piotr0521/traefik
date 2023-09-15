<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Groshy\Entity\AssetType;
use Groshy\Entity\Position;
use Groshy\Entity\PositionValue;
use Talav\Component\Resource\Repository\ResourceRepository;
use Talav\Component\User\Model\UserInterface;

final class PositionValueRepository extends ResourceRepository
{
    use FilterByAssetTypeTrait;

    public function getByInterval(DateTime $from, DateTime $to, array $positionIds): iterable
    {
        return $this->createQueryBuilder('value')
            ->select(['value', 'position'])
            ->leftJoin('value.position', 'position')
            ->andWhere('position.id IN (:ids)')
            ->andWhere('value.date >= :from')
            ->andWhere('value.date <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('ids', $positionIds)
            ->orderBy('value.date')
            ->getQuery()
            ->getResult();
    }

    public function findAllByPositionsAndInterval(array $positions, DateTime $from, DateTime $to): array
    {
        return $this->createQueryBuilder('value')
            ->select(['value', 'position'])
            ->leftJoin('value.position', 'position')
            ->leftJoin('position.asset', 'asset')
            ->andWhere('position IN (:positions)')
            ->andWhere('value.date >= :from')
            ->andWhere('value.date <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('positions', $positions)
            ->orderBy('value.date')
            ->getQuery()
            ->getResult();
    }

    public function getFirstDate(UserInterface $user, ?AssetType $type = null, ?Position $position = null): ?DateTime
    {
        $query = $this->createQueryBuilder('value')
            ->leftJoin('value.position', 'position')
            ->leftJoin('position.asset', 'asset')
            ->andWhere('position.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('value.date')
            ->setMaxResults(1);
        $query = $this->filterByAssetType($query, $type);
        if (!is_null($position)) {
            $query->andWhere('value.position = :position')
                ->setParameter('position', $position);
        }
        $result = $query->getQuery()->getOneOrNullResult();
        if (is_null($result)) {
            return null;
        }

        return $result->getValueDate();
    }

    public function getLastByPosition(Position $position): ?PositionValue
    {
        return $this->createQueryBuilder('value')
            ->select(['value'])
            ->andWhere('value.position = :position')
            ->orderBy('value.date', 'DESC')
            ->setParameter('position', $position)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLastBeforeDateForPosition(DateTime $to, Position $position): ?PositionValue
    {
        $result = $this->findLastByPositionsAndBeforeDate([$position], $to);
        if (0 == count($result)) {
            return null;
        }

        return $result[0];
    }

    // Returns a list of last values for every position before the specific date.
    // This need to set initial values for every position for graph building
    public function findLastByPositionsAndBeforeDate(array $positions, DateTime $before): array
    {
        // can be optimized for 1 position id
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addEntityResult('Groshy\Entity\PositionValue', 'v');
        $rsm->addFieldResult('v', 'id', 'id');

        $query = $this->_em->createNativeQuery('
            WITH t1 AS ( 
                SELECT id, position_id, date, amount_amount, RANK() OVER (
                    PARTITION BY position_id 
                    ORDER BY date DESC, amount_amount DESC 
                ) AS rank FROM position_value WHERE date <= :to AND position_id IN (:ids)
            ) SELECT id FROM t1 WHERE rank = 1', $rsm);
        $query->setParameter('to', $before);
        $query->setParameter('ids', $positions);

        // run regular query to avoid working with partial objects
        return $this->createQueryBuilder('value')
            ->select(['value', 'position'])
            ->leftJoin('value.position', 'position')
            ->andWhere('value.id IN (:ids)')
            ->setParameter('ids', $query->getSingleColumnResult())
            ->getQuery()
            ->getResult();
    }

    public function deleteByPosition(Position $position): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->delete($this->_entityName, 'value')
            ->andWhere('value.position = :position')
            ->setParameter('position', $position)
            ->getQuery()
            ->execute();
    }

    /**
     * @return DateTime[]
     */
    public function getDateListAfter(DateTime $date, Position $position): iterable
    {
        $result = $this->createQueryBuilder('value')
            ->select('value.date')
            ->andWhere('value.position = :position')
            ->andWhere('value.date > :date')
            ->orderBy('value.date', 'ASC')
            ->setParameter('position', $position)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleColumnResult();

        return array_map(function ($el) {return DateTime::createFromFormat('!Y-m-d', $el); }, $result);
    }
}
