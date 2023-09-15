<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCertificateDeposit;

use Groshy\Message\Dto\PositionCertificateDeposit\CreatePositionCertificateDepositDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionCertificateDepositCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionCertificateDepositDto $dto
    ) {
    }
}
