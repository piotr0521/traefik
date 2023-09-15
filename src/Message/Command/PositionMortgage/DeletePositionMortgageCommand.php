<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionMortgage;

use Groshy\Entity\PositionMortgage;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionMortgageCommand implements DomainEventInterface
{
    public function __construct(
        public PositionMortgage $position
    ) {
    }
}
