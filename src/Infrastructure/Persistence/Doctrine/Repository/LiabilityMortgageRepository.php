<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\Asset;
use Groshy\Entity\LiabilityMortgage;
use Talav\Component\Resource\Repository\ResourceRepository;

final class LiabilityMortgageRepository extends ResourceRepository
{
    public function getMortgageAsset(): LiabilityMortgage
    {
        return $this->findOneBy(['name' => Asset::LIABILITY_MORTGAGE]);
    }
}
