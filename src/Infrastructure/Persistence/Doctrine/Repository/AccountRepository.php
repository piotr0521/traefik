<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Domain\Enum\AccountSync;
use Groshy\Entity\Account;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\AccountType;
use Groshy\Entity\Institution;
use Talav\Component\Resource\Repository\ResourceRepository;
use Talav\Component\User\Model\UserInterface;

final class AccountRepository extends ResourceRepository
{
    public function getManualAccount(
        UserInterface $user,
        Institution $institution,
        AccountType $accountType,
        AccountHolder $accountHolder,
        ?string $name = null
    ): ?Account {
        $db = $this->createQueryBuilder('account')
            ->select(['account'])
            ->andWhere('account.createdBy = :user')
            ->andWhere('account.institution = :institution')
            ->andWhere('account.accountSync = :accountSync')
            ->andWhere('account.accountType = :accountType')
            ->andWhere('account.accountHolder = :accountHolder')
            ->setParameter('user', $user)
            ->setParameter('institution', $institution)
            ->setParameter('accountType', $accountType)
            ->setParameter('accountHolder', $accountHolder)
            ->setParameter('accountSync', AccountSync::MANUAL);
        if (!is_null($name)) {
            $db->andWhere('account.name = :name')
                ->setParameter('name', $name);
        }

        return $db->getQuery()
            ->getOneOrNullResult();
    }
}
