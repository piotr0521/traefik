<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\Asset;
use Groshy\Entity\LiabilityLoan;
use Talav\Component\Resource\Repository\ResourceRepository;

final class LiabilityLoanRepository extends ResourceRepository
{
    public function getLoanAsset(): LiabilityLoan
    {
        return $this->findOneBy(['name' => Asset::LIABILITY_LOAN]);
    }
}
