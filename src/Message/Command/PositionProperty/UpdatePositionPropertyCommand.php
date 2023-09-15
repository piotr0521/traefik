<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionProperty;

use Groshy\Entity\PositionProperty;
use Groshy\Message\Dto\PositionProperty\UpdatePositionPropertyDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionPropertyCommand implements DomainEventInterface
{
    public function __construct(
        public PositionProperty $resource,
        public UpdatePositionPropertyDto $dto
    ) {
    }
}
