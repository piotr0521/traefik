<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\Position;
use Groshy\Entity\PositionEvent;
use Talav\Component\Resource\Repository\ResourceRepository;

final class PositionEventRepository extends ResourceRepository
{
    public function deleteByPosition(Position $position): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->delete($this->_entityName, 'positionEvent')
            ->andWhere('positionEvent.position = :position')
            ->setParameter('position', $position)
            ->getQuery()
            ->execute();
    }

    public function getFirstForPosition(Position $position): ?PositionEvent
    {
        return $this->createQueryBuilder('positionEvent')
            ->select(['positionEvent'])
            ->leftJoin('positionEvent.position', 'position')
            ->andWhere('positionEvent.position = :position')
            ->setParameter('position', $position)
            ->orderBy('positionEvent.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
