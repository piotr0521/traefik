<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionLoan;

use Groshy\Entity\PositionLoan;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionLoanCommand implements DomainEventInterface
{
    public function __construct(
        public PositionLoan $position
    ) {
    }
}
