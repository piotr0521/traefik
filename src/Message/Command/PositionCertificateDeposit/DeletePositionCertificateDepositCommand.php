<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCertificateDeposit;

use Groshy\Entity\PositionCertificateDeposit;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionCertificateDepositCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCertificateDeposit $position
    ) {
    }
}
