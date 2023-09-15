<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionEvent;

use Groshy\Entity\PositionEvent;
use Groshy\Message\Dto\PositionEvent\UpdatePositionEventDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionEventCommand implements DomainEventInterface
{
    public function __construct(
        public PositionEvent $positionEvent,
        public UpdatePositionEventDto $dto
    ) {
    }
}
