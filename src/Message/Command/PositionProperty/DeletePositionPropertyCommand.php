<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionProperty;

use Groshy\Entity\PositionProperty;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionPropertyCommand implements DomainEventInterface
{
    public function __construct(
        public PositionProperty $position
    ) {
    }
}
