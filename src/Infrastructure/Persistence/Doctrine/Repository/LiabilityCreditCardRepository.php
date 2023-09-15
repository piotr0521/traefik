<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\Asset;
use Groshy\Entity\LiabilityCreditCard;
use Talav\Component\Resource\Repository\ResourceRepository;

final class LiabilityCreditCardRepository extends ResourceRepository
{
    public function getCreditCardLiability(): LiabilityCreditCard
    {
        return $this->findOneBy(['name' => Asset::LIABILITY_CREDIT_CARD]);
    }
}
