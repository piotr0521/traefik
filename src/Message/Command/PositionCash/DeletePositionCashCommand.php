<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCash;

use Groshy\Entity\PositionCash;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionCashCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCash $position
    ) {
    }
}
