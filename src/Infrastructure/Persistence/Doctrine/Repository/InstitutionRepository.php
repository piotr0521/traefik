<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Talav\Component\Resource\Repository\ResourceRepository;

final class InstitutionRepository extends ResourceRepository
{
    public function getPlaidInstitutions(): iterable
    {
        return $this->createQueryBuilder('i')
            ->select(['i'])
            ->andWhere('i.plaidId IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
