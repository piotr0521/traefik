<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCollectable;

use Groshy\Message\Dto\PositionCollectable\CreatePositionCollectableDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionCollectableCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionCollectableDto $dto
    ) {
    }
}
