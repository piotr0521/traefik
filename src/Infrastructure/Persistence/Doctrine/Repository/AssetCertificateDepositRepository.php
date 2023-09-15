<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Persistence\Doctrine\Repository;

use Groshy\Entity\Asset;
use Groshy\Entity\AssetCertificateDeposit;
use Talav\Component\Resource\Repository\ResourceRepository;

final class AssetCertificateDepositRepository extends ResourceRepository
{
    public function getCertificateDepositAsset(): AssetCertificateDeposit
    {
        return $this->findOneBy(['name' => Asset::ASSET_CERTIFICATE_DEPOSIT]);
    }
}
