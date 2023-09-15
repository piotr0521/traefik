<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCollectable;

use Groshy\Entity\PositionCollectable;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionCollectableCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCollectable $position
    ) {
    }
}
