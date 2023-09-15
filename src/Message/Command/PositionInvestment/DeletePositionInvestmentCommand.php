<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionInvestment;

use Groshy\Entity\PositionInvestment;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionInvestmentCommand implements DomainEventInterface
{
    public function __construct(
        public PositionInvestment $position
    ) {
    }
}
