<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCollectable;

use Groshy\Entity\PositionCollectable;
use Groshy\Message\Dto\PositionCollectable\UpdatePositionCollectableDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionCollectableCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCollectable $resource,
        public UpdatePositionCollectableDto $dto
    ) {
    }
}
