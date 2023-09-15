<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

// use Groshy\Entity\TransactionType;
use Groshy\Repository\TransactionType;
use Talav\Component\Resource\Repository\ResourceRepository;

final class TransactionTypeRepository extends ResourceRepository
{
    public function allIndexedByShortName(): array
    {
        $return = [];
        /** @var TransactionType $type */
        foreach ($this->findAll() as $type) {
            $return[$type->getShortName()->value] = $type;
        }

        return $return;
    }
}
