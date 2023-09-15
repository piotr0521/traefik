<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionLoan;

use Groshy\Entity\PositionLoan;
use Groshy\Message\Dto\PositionLoan\UpdatePositionLoanDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionLoanCommand implements DomainEventInterface
{
    public function __construct(
        public PositionLoan $resource,
        public UpdatePositionLoanDto $dto
    ) {
    }
}
