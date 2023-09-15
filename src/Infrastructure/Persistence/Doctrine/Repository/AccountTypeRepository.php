<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\AccountType;
use Talav\Component\Resource\Repository\ResourceRepository;

final class AccountTypeRepository extends ResourceRepository
{
    public function getCdType(): AccountType
    {
        return $this->findOneBy(['name' => AccountType::TYPE_CD]);
    }

    public function getCreditCardType(): AccountType
    {
        return $this->findOneBy(['name' => AccountType::TYPE_CREDIT_CARD]);
    }

    public function getLoanType(): AccountType
    {
        return $this->findOneBy(['name' => AccountType::TYPE_LOAN]);
    }

    public function getMortgageType(): AccountType
    {
        return $this->findOneBy(['name' => AccountType::TYPE_MORTGAGE]);
    }
}
