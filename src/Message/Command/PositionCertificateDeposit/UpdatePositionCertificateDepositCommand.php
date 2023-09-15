<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCertificateDeposit;

use Groshy\Entity\PositionCertificateDeposit;
use Groshy\Message\Dto\PositionCertificateDeposit\UpdatePositionCertificateDepositDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionCertificateDepositCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCertificateDeposit $resource,
        public UpdatePositionCertificateDepositDto $dto
    ) {
    }
}
