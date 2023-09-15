<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionEvent;

use Groshy\Entity\PositionEvent;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionEventCommand implements DomainEventInterface
{
    public function __construct(
        public PositionEvent $positionEvent
    ) {
    }
}
