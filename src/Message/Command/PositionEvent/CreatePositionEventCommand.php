<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionEvent;

use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionEventCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionEventDto $dto
    ) {
    }
}
